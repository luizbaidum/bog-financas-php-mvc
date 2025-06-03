<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Model\Model;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\Categorias\CategoriasEntity;
use src\Models\Investimentos\InvestimentosEntity;
use src\Models\Movimentos\MovimentosEntity;
use src\Models\Objetivos\ObjetivosDAO;
use src\Models\Objetivos\ObjetivosEntity;
use src\Models\Rendimentos\RendimentosDAO;
use src\Models\Rendimentos\RendimentosEntity;

class MovimentosController extends Controller {
    public function index() {
        $model = new Model();
        $model_movimentos = new MovimentosDAO();

        $action = $_GET['action'] ?? null;
        $id = $_GET['idMovimento'] ?? null;

        $title = 'Cadastro de Movimento';
        $url_action = '/cad_movimentos';
        if ($id != '') {
            $url_action = '/edit_movimento';
            $title = 'Edição de Movimento';
            $mov = $model_movimentos->consultarMovimento($id);
        }

        $this->view->settings = [
            'action'   => $this->index_route . $url_action,
            'redirect' => $this->index_route . '/home',
            'title'    => $title,
        ];

        $this->view->data['options_list'] = json_encode($model->selectAll(new ObjetivosEntity, [], [], []));
        $this->view->data['categorias'] = $model->selectAll(new CategoriasEntity, [], [], ['tipo' => 'ASC', 'categoria' => 'ASC']);
        $this->view->data['invests'] = $model->selectAll(new InvestimentosEntity, [], [], ['nomeBanco' => 'ASC']);
        $this->view->data['movimento'] = $mov[0] ?? null;
        $this->view->data['url_buscar_mov_mensal'] = $this->index_route . '/buscaMovMensal?buscar=';
        $this->view->data['div_buscar_mov_mensal'] = 'id-content-return';

        $this->renderPage(
            main_route: $this->index_route . '/movimentos', 
            conteudo: 'movimentos', 
            base_interna: 'base_cruds'
        );
    }

    public function exibirResultados()
    {
        $ano_filtro = $_GET['anoFiltro'];
		$mes_filtro = $_GET['mesFiltro'];

        $model_movimentos = new MovimentosDAO();

        $ret = $model_movimentos->getResultado($ano_filtro, $mes_filtro);

        $data = [];
        if ($ret) {
            foreach ($ret as $val) {
                if ($val['tipo'] == 'R') {
                    if (strpos($val['categoria'], 'Resgate') !== false) {
                        if (isset($total_resgate[$val['proprietario']])) {
                            $total_resgate[$val['proprietario']] += $val['total'];
                        } else {
                            $total_resgate[$val['proprietario']] = $val['total'];
                        }

                        if (isset($data[$val['proprietario']]['Resgate'])) {
                            $data[$val['proprietario']]['Resgate'] += $val['total'];
                        } else {
                            $data[$val['proprietario']]['Resgate'] = $val['total'];
                        }
                    } else {
                        if (isset($total_receita[$val['proprietario']])) {
                            $total_receita[$val['proprietario']] += $val['total'];
                        } else {
                            $total_receita[$val['proprietario']] = $val['total'];
                        }

                        if (isset($data[$val['proprietario']]['Receitas'])) {
                            $data[$val['proprietario']]['Receitas'] += $val['total'];
                        } else {
                            $data[$val['proprietario']]['Receitas'] = $val['total'];
                        }
                    }
                } elseif ($val['tipo'] == 'D') {
                    if (isset($total_despesa[$val['proprietario']])) {
                        $total_despesa[$val['proprietario']] += $val['total'];
                    } else {
                        $total_despesa[$val['proprietario']] = $val['total'];
                    }

                    if (isset($data[$val['proprietario']]['Despesas'])) {
                        $data[$val['proprietario']]['Despesas'] += $val['total'];
                    } else {
                        $data[$val['proprietario']]['Despesas'] = $val['total'];
                    }
                } elseif ($val['tipo'] == 'A') {
                    if (isset($total_aplicacao[$val['proprietario']])) {
                        $total_aplicacao[$val['proprietario']] += $val['total'];
                    } else {
                        $total_aplicacao[$val['proprietario']] = $val['total'];
                    }

                    if (isset($data[$val['proprietario']]['Aplicação'])) {
                        $data[$val['proprietario']]['Aplicação'] += $val['total'];
                    } else {
                        $data[$val['proprietario']]['Aplicação'] = $val['total'];
                    }
                }
            }
        }

        $this->view->data['data'] = $data;
        $this->view->data['total_resgate'] = $total_resgate ?? 0;
        $this->view->data['total_receita'] = $total_receita ?? 0;
        $this->view->data['total_despesa'] = $total_despesa ?? 0;
        $this->view->data['total_aplicacao'] = $total_aplicacao ?? 0;

        $this->renderInModal(titulo: 'Demonstrativo', conteudo: 'exibir_resultado');
    }

    public function editarMovimento()
    {
        $model_movimentos = new MovimentosDAO();
        $model_rendimentos = new RendimentosDAO();
        $model_objetivos = new ObjetivosDAO();

        try {
            $id_movimento = $_POST['idMovimento'];
            $id_conta_invest = $_POST['idContaInvest'];
            $id_objetivo = $_POST['idObjetivo'] ?? '';
            $id_objetivo_old = $_POST['idObjOld'] ?? '0';

            unset($_POST['idMovimento']);
            unset($_POST['idObjetivo']);
            unset($_POST['idObjOld']);

            $arr_cat = explode(' - sinal: ' , $_POST['idCategoria']);
            $_POST['idCategoria'] = $arr_cat[0];
            $sinal = $arr_cat[1];

            if ($sinal == '-' && $_POST['valor'] > 0) {
                $_POST['valor'] = $_POST['valor'] * -1;
            } elseif ($sinal == '+' && $_POST['valor'] < 0) {
                $_POST['valor'] = $_POST['valor'] * -1;
            }

            $values = $_POST;
            $where = array(
                'idMovimento' => $id_movimento
            );

            $ret = $model_movimentos->atualizar(new MovimentosEntity, $values, $where);

            $rendimento = $model_rendimentos->selectAll(new RendimentosEntity, [['idMovimento', '=', $id_movimento]], [], []);

            if (isset($rendimento[0]['idRendimento'])) {
                $rendimento = $rendimento[0];
                $old_id = $rendimento['idRendimento'];
                $old_invest = $rendimento['idContaInvest'];
                $old_valor = $rendimento['valorRendimento'];
                $old_tipo = $rendimento['tipo'];
                $old_data = $rendimento['dataRendimento'];
                $old_movimento = $rendimento['idMovimento'];

                $conta_invest = $model_rendimentos->selectAll(new InvestimentosEntity, [['idContaInvest', '=', $old_invest]], [], [])[0];

                if ($old_tipo == '4' || $old_tipo == '2') {
                    $saldo = $conta_invest['saldoAtual'] - $old_valor;
                } elseif ($old_tipo == '3' || $old_tipo == '1') {
                    $saldo = $conta_invest['saldoAtual'] + abs($old_valor);
                }

                $model_rendimentos->atualizar(
                    new InvestimentosEntity, 
                    ['saldoAtual' => $saldo], 
                    ['idContaInvest' => $old_invest]
                );

                if (empty($id_objetivo_old)) {
                    $objetivos = $model_objetivos->selectAll(new ObjetivosEntity, [['idContaInvest', '=', $old_invest]], [], []);

                    foreach ($objetivos as $value) {
                        $item = [
                            'saldoAtual' => ($saldo * ($value['percentObjContaInvest'] / 100))
                        ];
                        $item_where = ['idObj' => $value['idObj']];
                        $model_objetivos->atualizar(new ObjetivosEntity, $item, $item_where);
                    }
                } else {
                    $objetivo = $model_objetivos->selectAll(new ObjetivosEntity, [['idObj', '=', $id_objetivo_old]], [], [])[0];

                    if ($old_tipo == '4' || $old_tipo == '2') {
                        $saldo_obj = $objetivo['saldoAtual'] - $old_valor;
                    } elseif ($old_tipo == '3' || $old_tipo == '1') {
                        $saldo_obj = $objetivo['saldoAtual'] + abs($old_valor);
                    }

                    $item = [
                        'saldoAtual' => $saldo_obj
                    ];
                    $item_where = ['idObj' => $id_objetivo_old];
                    $model_objetivos->atualizar(new ObjetivosEntity, $item, $item_where);
                }

                $model_rendimentos->delete(new RendimentosEntity, 'idRendimento', $old_id);
            }

            if (!empty($id_conta_invest)) {
                (new InvestimentosController())->inserirMovimentacaodeAplicacao($id_conta_invest, $id_objetivo, $id_movimento, $_POST['idCategoria'], $_POST['valor'], $_POST['dataMovimento']);
            }

            if (!isset($ret['result']) || empty($ret['result'])) {
                throw new Exception('O Movimento não foi atualizado.');
            }

            $array_retorno = array(
                'result'   => true,
                'mensagem' => 'Movimento id ' . $id_movimento . ' atualizado com sucesso.',
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

    public function deletarMovimento()
    {
        if ($this->isSetPost()) {

            $model_rendimentos = new RendimentosDAO();
            $model_movimentos = new MovimentosDAO();

            try {
                foreach ($_POST['itens'] as $id) {
                    $rend = $model_rendimentos->selectAll(new RendimentosEntity, [['idMovimento', '=', $id]], [], []);

                    if (!empty($rend)) {
                        $model_movimentos->arr_nao_afetados[] = $id;
                    } else {
                        $ret = $model_movimentos->delete(new MovimentosEntity, 'idMovimento', $id);
                        if ($ret != false) {
                            $model_movimentos->arr_afetados[] = $id;
                        } else {
                            $model_movimentos->arr_nao_afetados[] = $id;
                        }
                    }
                }

                $array_retorno = array(
					'result'   => true,
					'mensagem' => 'Movimentos excluídos: ' . implode(', ', $model_movimentos->arr_afetados) . '. Movimentos não excluídos: ' . implode(', ', $model_movimentos->arr_nao_afetados),
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

    public function cadastrarMovimentos()
    {
        if ($this->isSetPost()) {
            try {
                if ($_POST['idCategoria'] == '') {
                    throw new Exception('Por favor, escolher categoria.'); 
                }

                $arr_cat = explode(' - sinal: ' , $_POST['idCategoria']);
                $_POST['idCategoria'] = $arr_cat[0];
                $sinal = $arr_cat[1];

                $id_conta_invest = $_POST['idContaInvest'];
                $id_objetivo = $_POST['idObjetivo'] ?? '';
                unset($_POST['idObjetivo']);

                //Inserção de Movimento
                $_POST['valor'] = $sinal . $_POST['valor'];
                $ret = (new MovimentosDAO())->cadastrar(new MovimentosEntity, $_POST);

                if (!$ret['result']) {
                    throw new Exception($this->msg_retorno_falha);
                }

                $id_movimento = $ret['result'];

                //Inserção de Rendimento (invest ou retirada)
                if (!empty($id_conta_invest)) {
                    (new InvestimentosController())->inserirMovimentacaodeAplicacao($id_conta_invest, $id_objetivo, $id_movimento, $_POST['idCategoria'], $_POST['valor'], $_POST['dataMovimento']);
                }

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
}