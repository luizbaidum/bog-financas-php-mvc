<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\NumbersHelper;
use MF\Model\Model;
use src\Models\Categorias\CategoriasEntity;
use src\Models\Movimentos\MovimentosEntity;
use src\Models\MovimentosMensais\MovimentosMensaisDAO;
use src\Models\MovimentosMensais\MovimentosMensaisEntity;
use src\Models\Proprietarios\ProprietariosDAO;
use src\Models\Proprietarios\ProprietariosEntity;

class MovimentosMensaisController extends Controller {
    public function index() {
        $model_movimentos_mensais = new MovimentosMensaisDAO();

        $this->view->settings = [
            'action'   => $this->index_route . '/cad-mov-mensal',
            'redirect' => $this->index_route . '/movimentos-mensais-index',
            'title'    => 'Movimentos Mensais',
            'url_new'  => $this->index_route . '/movimentos-mensais',
            'url_edit' => $this->index_route . '/movimentos-mensais?action=edit&idMovMensal=',
        ];

        $this->view->data['arr_mensais'] = $model_movimentos_mensais->getMensais();
        $this->view->data['lista_proprietarios'] = (new ProprietariosDAO())->selectAll(new ProprietariosEntity, [], [], []);

        $this->renderPage(
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

        $action = $_GET['action'] ?? null;
        $id = $_GET['idMovMensal'] ?? null;

        $url_action = '/cad-movimentos-mensais';
        if ($action == 'edit') {
            $url_action = '/edit-mov-mensal';
            $mov_m = $model->selectAll(new MovimentosMensaisEntity, [['idMovMensal', '=', $id]], [], []);
        }

        $this->view->settings = [
            'action'   => $this->index_route . $url_action,
            'redirect' => $this->index_route . '/movimentos-mensais',
            'title'    => 'Mov. Mensal',
        ];

        $this->view->data['categorias'] = $model->selectAll(new CategoriasEntity, [['status', '=', '1']], [], ['tipo' => 'ASC', 'categoria' => 'ASC']);
        $this->view->data['lista_proprietarios'] = (new ProprietariosDAO())->selectAll(new ProprietariosEntity, [], [], []);
        $this->view->data['mov_m'] = $mov_m[0] ?? null;
        $this->view->data['titulo_card'] = $action == 'edit' ? 'Edição' : 'Cadastro';

        $this->renderPage(conteudo: 'movimentos_mensais', base_interna: 'base_cruds');
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

                    $item['idMovMensal']    = $id;
                    $item['nomeMovimento']  = $_POST['nomeMovimento'][$id];
                    $item['dataMovimento']  = $_POST['dataMovimento'][$id];
                    $item['idProprietario'] = $_POST['idProprietario'][$id];
                    $item['idCategoria']    = $arr_cat[0];
                    $item['valor']          = $sinal . $_POST['valor'][$id];

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

    public function editarMovimentoMensal()
    {
        try {
            $model = new Model();
            $obj_mov_mensal = new MovimentosMensaisEntity();
            $id_mov_m = $_POST['idMovMensal'];

            $arr_cat = explode(' - sinal: ' , $_POST['idCategoria']);
            $sinal = $arr_cat[1];
            $valor_despesa = NumbersHelper::formatBRtoUS($_POST['valorDespesa']);

            $obj_mov_mensal->dataRepete = $_POST['dataRepete'];
            $obj_mov_mensal->idCategoria = $arr_cat[0];
            $obj_mov_mensal->nomeMovimento = $_POST['nomeMovimento'];
            $obj_mov_mensal->idProprietario = $_POST['idProprietario'];

            if ($sinal == '-' && $valor_despesa > 0) {
                $obj_mov_mensal->valorDespesa = $valor_despesa;
            } elseif ($sinal == '+' && $valor_despesa < 0) {
                $obj_mov_mensal->valorDespesa = $valor_despesa;
            }

            $item_where = [
                'idMovMensal' => $id_mov_m
            ];

            $ret = $model->atualizar(new MovimentosMensaisEntity, $obj_mov_mensal, $item_where);

            if (!isset($ret['result']) || empty($ret['result'])) {
                throw new Exception('O Movimento Mensal não foi atualizado.');
            }

            $array_retorno = array(
                'result'   => true,
                'mensagem' => 'Movimento Mensal id ' . $id_mov_m . ' atualizado com sucesso.',
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