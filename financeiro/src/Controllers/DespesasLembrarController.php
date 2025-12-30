<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\NumbersHelper;
use MF\View\SetButtons;
use src\Models\Categorias\CategoriasEntity;
use src\Models\DespesasLembrar\DespesasLembrarDAO;
use src\Models\DespesasLembrar\DespesasLembrarEntity;
use src\Models\Investimentos\InvestimentosDAO;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\Movimentos\MovimentosEntity;
use src\Models\Proprietarios\ProprietariosEntity;

class DespesasLembrarController extends Controller {
    public function index() 
    {
        $model_investimentos = new InvestimentosDAO();
        $model_despesas_lembrar = new DespesasLembrarDAO();
        $buttons = new SetButtons();

        $this->view->settings = [
            'action'   => $this->index_route . '/cadastrar-lembrar-despesas',
            'redirect' => $this->index_route . '/lembrar-despesas',
            'title'    => 'Despesas para Lembrar'
        ];

        $this->view->data = [
            'movimento'           => [],
            'lista_pgtos'         => $this->getMethods('Pagamento'),
            'lista_proprietarios' => $model_investimentos->selectAll(new ProprietariosEntity, [], [], []),
            'categorias'          => $model_despesas_lembrar->selectAll(
                                        new CategoriasEntity, 
                                        [['status', '=', '"1"']], 
                                        [], 
                                        ['tipo' => 'ASC', 'categoria' => 'ASC'],
                                        ['idCategoria' => ['12', '10']]
                                    )
        ];

        $ano_filtro = $_GET['anoFiltro'] ?? '';
        $mes_filtro = $_GET['mesFiltro'] ?? '';
        $pesquisa = $_GET['pesquisa'] ?? '';
        if ($pesquisa != '') {
            $pesquisa = trim($pesquisa, ' ');
        }

        if ($mes_filtro != '' || $pesquisa != '') {
			$this->view->data['mov_cadastrados'] = $model_despesas_lembrar->indexTable($pesquisa, $ano_filtro, $mes_filtro);
        } else {
            $this->view->data['mov_cadastrados'] = $model_despesas_lembrar->indexTable('');
        }

        $buttons->setButton(
            'Apagar',
            $this->index_route . '/delete-desp-lembrar',
            'px-2 btn btn-danger action-delete',
            'right'
        );

        $this->view->buttons = $buttons->getButtons();
        $this->renderPage(
            conteudo: 'despesas_lembrar'
        );
    }

    public function cadastrar()
    {
        if ($this->isSetPost()) {
            $model_despesas_lembrar = new DespesasLembrarDAO();
            $model_despesas_lembrar->iniciarTransacao();
            try {
                $sinal = '-';
                $lancar_mov = false;
                if (! empty($_POST['idCategoria']) && isset($_POST['lancarMovimento'])) {
                    $arr_cat = explode(' - sinal: ' , $_POST['idCategoria']);
                    $sinal = $arr_cat[1];
                    $lancar_mov = true;
                }

                $obj_lembrar = new DespesasLembrarEntity();

                $obj_lembrar->data                  = $_POST['data'];
                $obj_lembrar->valor                 = $sinal . NumbersHelper::formatBRtoUS($_POST['valor']);
                $obj_lembrar->idProprietarioPagante = $_POST['idProprietarioPagante'];
                $obj_lembrar->idProprietarioReal    = $_POST['idProprietarioReal'];
                $obj_lembrar->descricao             = $_POST['descricao'];
                $obj_lembrar->metodoPgto            = $_POST['metodoPgto'];

                $ret = $model_despesas_lembrar->cadastrar(new DespesasLembrarEntity, $obj_lembrar);
                $obj_lembrar->idDespLembrar = $ret['result'];

                if ($lancar_mov) {
                    $obj_movimento = new MovimentosEntity();

                    $obj_movimento->nomeMovimento  = $obj_lembrar->descricao;
                    $obj_movimento->dataMovimento  = $obj_lembrar->data;
                    $obj_movimento->idCategoria    = $arr_cat[0];
                    $obj_movimento->valor          = $obj_lembrar->valor;
                    $obj_movimento->idProprietario = $obj_lembrar->idProprietarioPagante;
                    $obj_movimento->observacao     = $_POST['observacao'];

                    $ret = (new MovimentosDAO())->cadastrar(new MovimentosEntity, $obj_movimento);

                    $obj_movimento->idMovimento = $ret['result'];

                    (new DespesasLembrarDAO())->atualizar(
                        new DespesasLembrarEntity, 
                        ['idMovimento'   => $obj_movimento->idMovimento],
                        ['idDespLembrar' => $obj_lembrar->idDespLembrar]);
                }

                if (!  $ret['result']) {
                    throw new Exception($this->msg_retorno_falha);
                }

                if ($ret['result']) {
					$array_retorno = array(
						'result'   => $ret['result'],
						'mensagem' => $this->msg_retorno_sucesso
					);

                    $model_despesas_lembrar->finalizarTransacao();

					echo json_encode($array_retorno);
                    exit;
				} else {
					throw new Exception($this->msg_retorno_falha);
				}
            } catch (Exception $e) {
				$array_retorno = array(
					'result'   => false,
					'mensagem' => $e->getMessage(),
				);

                $model_despesas_lembrar->cancelarTransacao();

				echo json_encode($array_retorno);
                exit;
			}
        }
    }

    public function deletarDespLembrar()
    {
        if ($this->isSetPost()) {

            $model_desp_lembrar = new DespesasLembrarDAO();
            $model_desp_lembrar->iniciarTransacao();

            try {
                foreach ($_POST['itens'] as $id) {
                    $mov_real = $model_desp_lembrar->verificarMovReal($id);

                    if (! empty($mov_real)) {
                        $model_desp_lembrar->arr_nao_afetados[] = $id;
                    } else {
                        $ret = $model_desp_lembrar->delete(new DespesasLembrarEntity, 'idDespLembrar', $id);
                        if ($ret != false) {
                            $model_desp_lembrar->arr_afetados[] = $id;
                        } else {
                            $model_desp_lembrar->arr_nao_afetados[] = $id;
                        }
                    }
                }

                $array_retorno = array(
					'result'   => true,
					'mensagem' => 'Movimentos excluídos: ' . implode(', ', $model_desp_lembrar->arr_afetados) . '. Movimentos não excluídos: ' . implode(', ', $model_desp_lembrar->arr_nao_afetados),
				);

                $model_desp_lembrar->finalizarTransacao();

				echo json_encode($array_retorno);
                exit;

            } catch (Exception $e) {
                $array_retorno = array(
					'result'   => false,
					'mensagem' => $e->getMessage(),
				);

                $model_desp_lembrar->cancelarTransacao();

				echo json_encode($array_retorno);
                exit;
            }
        }
    }
}
?>