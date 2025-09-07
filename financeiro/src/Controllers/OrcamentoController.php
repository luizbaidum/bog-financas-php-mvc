<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\NumbersHelper;
use MF\Model\Model;
use src\Models\Orcamento\OrcamentoDAO;
use MF\View\SetButtons;
use src\Models\Categorias\CategoriasEntity;
use src\Models\Orcamento\OrcamentoEntity;
use src\Models\Proprietarios\ProprietariosEntity;

class OrcamentoController extends Controller {
    public function index() {
        $model_orcamento = new OrcamentoDAO();
        $buttons = new SetButtons();

        $ano_filtro = $_GET['anoFiltro'] ?? '';
        $mes_filtro = $_GET['mesFiltro'] ?? '';

        if ($mes_filtro != '') {
            $orcamentos = $model_orcamento->orcamentosPorProprietario($ano_filtro, $mes_filtro);
        } else {
            $orcamentos = $model_orcamento->orcamentosPorProprietario();
        }

        $this->view->settings = [
            'action'     => '',
            'redirect'   => $this->index_route . '/orcamento_index',
            'title'      => 'Orçamento',
            'url_search' => $this->index_route . '/orcamento_index'
        ];

        $buttons->setButton(
            'Apagar',
            $this->index_route . '/delete_itens_orcamento',
            'px-2 btn btn-danger action-delete'
        );

        $this->view->buttons = $buttons->getButtons();
        $this->view->data['orcamentos'] = $orcamentos;

        $this->renderPage(
            conteudo: 'orcamento_index'
        );
    }

    public function buscarOrcamentoDoRealizado()
    {
        $model_orcamento = new OrcamentoDAO();

        list($ano_origem, $mes_origem) = explode('-', $_POST['mesAnoOrigem']);

        $lista = $model_orcamento->buscarMediasDespesas($ano_origem, $mes_origem);

        $this->view->data['lista'] = $lista;

        $this->renderSimple('tabela_orcamento_importado');
    }

    public function deletarItensOrcamento()
    {
        if ($this->isSetPost()) {

            $model_orcamento = new OrcamentoDAO();

            try {
                foreach ($_POST['itens'] as $id) {
                    $ret = $model_orcamento->delete(new OrcamentoEntity, 'idOrcamento', $id);

                    if ($ret != false) {
                        $model_orcamento->arr_afetados[] = $id;
                    } else {
                        $model_orcamento->arr_nao_afetados[] = $id;
                    }
                }

                $array_retorno = array(
					'result'   => true,
					'mensagem' => 'Orçamentos excluídos: ' . implode(', ', $model_orcamento->arr_afetados) . '. Orçamentos não excluídos: ' . implode(', ', $model_orcamento->arr_nao_afetados),
				);

				echo json_encode($array_retorno);

            } catch (Exception $e) {
                $array_retorno = array(
					'result'   => false,
					'mensagem' => $e->getMessage(),
				);

				echo json_encode($array_retorno);
            }
        }
    }

    public function orcamento()
    {
        $model = new Model();

        $this->view->settings = [
            'action'   => $this->index_route . '/cad_orcamento',
            'redirect' => $this->index_route . '/orcamento',
            'title'    => 'Cadastro de Orçamento por Categoria',
        ];

        $this->view->data['categorias'] = $model->selectAll(new CategoriasEntity, [['status', '=', '1']], [], ['categoria' => 'ASC']);
        $this->view->data['months'] = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Todos');
        $this->view->data['lista_proprietarios'] = $model->selectAll(new ProprietariosEntity, [], [], []);

        $this->renderPage(conteudo: 'orcamento', base_interna: 'base_cruds');
    }

    public function orcamentoDoRealizado()
    {
        $this->view->settings = [
            'action'   => $this->index_route . '/cad_orcamento_do_realizado',
            'url_ajax' => $this->index_route . '/buscar-orcamento-do-realizado',
            'redirect' => $this->index_route . '/orcamento_do_realizado',
            'title'    => 'Importação de Orçamento',
            'div_ajax' => 'id-content-importar'
        ];

        $this->renderPage(conteudo: 'orcamento_do_realizado', base_interna: 'base_cruds');
    }

    public function cadastrarOrcamento()
    {
        if ($this->isSetPost()) {
            try {
                $arr_cat = explode(' - sinal: ' , $_POST['idCategoria']);
                $sinal = $arr_cat[1];

                $valor = NumbersHelper::formatBRtoUS($_POST['valor']);

                if ($sinal == '-' && !strpos($valor, '-'))
                    $valor = $sinal . $valor;

                $item = [
                    'dataOrcamento' => $_POST['dataOrcamento'],
                    'idCategoria'   => $arr_cat[0],
                    'idProprietario'=> $_POST['idProprietario'],
                    'valor'         => $valor
                ];
    
                $ret = (new OrcamentoDAO())->cadastrar(new OrcamentoEntity(), $item);

                if ($ret['result']) {
					$array_retorno = array(
						'result'   => $ret['result'],
						'mensagem' => $this->msg_retorno_sucesso
					);

					echo json_encode($array_retorno);
				} else {
					throw new Exception($this->msg_retorno_falha);
				}
            } catch (Exception $e) {
                $array_retorno = array(
					'result'   => false,
					'mensagem' => $e->getMessage(),
				);

				echo json_encode($array_retorno);
            }
        }
    }

    public function cadastrarOrcamentoDoRealizado()
    {
        if ($this->isSetPost()) {
            try {
                $ret = [];

                foreach ($_POST['idCategoria'] as $k => $categoria) {
                    $item['idCategoria'] = $categoria;
                    $item['idProprietario'] = $_POST['idProprietario'][$k];
                    $sinal = $_POST['sinal'][$k];
    
                    $item['valor'] = NumbersHelper::formatBRtoUS($_POST['valor'][$k]);
                    if ($sinal == '-' && $item['valor'] > 0) {
                        $item['valor'] = $item['valor'] * -1;
                    }

                    if ($sinal == '+' && $item['valor'] < 0) {
                        $item['valor'] = $item['valor'] * -1;
                    }
    
                    $item['dataOrcamento'] = $_POST['destino'] . '-01';

                    $bd = (new OrcamentoDAO())->cadastrar(new OrcamentoEntity(), $item);

                    if ($bd['result'] == '' || $bd['result'] == 0) {
                        throw new Exception('Nem todos os orçamentos foram cadastrados.');
                    }
    
                    $ret[] = $bd;
                }

                if (count($ret) == 0) {
                    throw new Exception($this->msg_retorno_falha);
                } else {
                    $array_retorno = array(
						'result'   => true,
						'mensagem' => $this->msg_retorno_sucesso
					);

					echo json_encode($array_retorno);
                }
            } catch (Exception $e) {
                $array_retorno = array(
					'result'   => false,
					'mensagem' => $e->getMessage(),
				);

				echo json_encode($array_retorno);
            }
        }
    }
}