<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Model\Model;
use src\Models\Categorias\CategoriasEntity;
use src\Models\Movimentos\MovimentosEntity;
use src\Models\MovimentosMensais\MovimentosMensaisDAO;
use src\Models\MovimentosMensais\MovimentosMensaisEntity;

class MovimentosMensaisController extends Controller {
    public function index() {
        $model_movimentos_mensais = new MovimentosMensaisDAO();

        $this->view->settings = [
            'action'   => $this->index_route . '/cad_mov_mensal',
            'redirect' => $this->index_route . '/movimentos_mensais_index',
            'title'    => 'Movimentos Mensais',
        ];

        $this->view->data['arr_mensais'] = $model_movimentos_mensais->getMensais();
        
        $this->renderPage(
            main_route: $this->index_route . '/movimentos_mensais_index', 
            conteudo: 'movimentos_mensais_index', 
            base_interna: 'base_cruds'
        );
    }

    public function buscarMovMensal() {
        $buscar = $_GET['buscar'];

        $model_movimentos_mensais = new MovimentosMensaisDAO();

        $ret = $model_movimentos_mensais->buscar($buscar);
        $this->view->data['ret'] = $ret;

        $this->renderSimple('ret_mov_mensais');
    }

    public function movimentosMensais()
    {
        $model = new Model();

        $this->view->settings = [
            'action'   => $this->index_route . '/cad_movimentos_mensais',
            'redirect' => $this->index_route . '/movimentos_mensais',
            'title'    => 'Cadastro de Mov. Mensais',
        ];

        $this->view->data['categorias'] = $model->selectAll(new CategoriasEntity, [], [], ['tipo' => 'ASC', 'categoria' => 'ASC']);

        $this->renderPage(main_route: $this->index_route . '/movimentos_mensais', conteudo: 'movimentos_mensais', base_interna: 'base_cruds');
    }

    public function cadastrarMovimentosMensais()
    {
        if ($this->isSetPost()) {
            try {
                $ret = (new MovimentosMensaisDAO())->cadastrar(new MovimentosMensaisEntity, $_POST);

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

    public function lancarMovimentoMensalComoMovimento()
    {
        if ($this->isSetPost()) {
            $item = array();

            $model_movimentos_mensais = new MovimentosMensaisDAO();

            try {
                foreach ($_POST['idMovMensal'] as $id) {
                    $arr_cat = explode(' - sinal: ', $_POST['idCategoria'][$id]);
                    $sinal = $arr_cat[1];

                    $item['idMovMensal'] = $id;
                    $item['nomeMovimento'] = $_POST['nomeMovimento'][$id];
                    $item['dataMovimento'] = $_POST['dataMovimento'][$id];
                    $item['proprietario'] = $_POST['proprietario'][$id];
                    $item['idCategoria'] = $arr_cat[0];
                    $item['valor'] = $sinal . $_POST['valor'][$id];

                    $ret = $model_movimentos_mensais->cadastrar(new MovimentosEntity, $item);

                    if (!isset($ret['result']) || empty($ret['result'])) {
                        throw new Exception($this->msg_retorno_falha . '<br>' . 'O lançamento: ' . $item['nomeMovimento'] . ' e subsequentes não foram salvos.');
                    }//testar se o Exception interrompe o foreach

                    $success[] = $ret['result'];
                }

                if (isset($success) && count($success) > 0) {
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