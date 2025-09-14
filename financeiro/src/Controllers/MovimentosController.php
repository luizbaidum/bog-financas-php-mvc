<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\NumbersHelper;
use MF\Model\Model;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\Categorias\CategoriasEntity;
use src\Models\Investimentos\InvestimentosEntity;
use src\Models\Movimentos\MovimentosEntity;
use src\Models\Objetivos\ObjetivosDAO;
use src\Models\Objetivos\ObjetivosEntity;
use src\Models\Proprietarios\ProprietariosEntity;
use src\Models\Rendimentos\RendimentosDAO;
use src\Models\Rendimentos\RendimentosEntity;

class MovimentosController extends Controller {
    public function index() {
        $model = new Model();
        $model_movimentos = new MovimentosDAO();

        $action = $_GET['action'] ?? null;
        $id = $_GET['idMovimento'] ?? null;

        $url_action = '/cad_movimentos';
        if ($action == 'edit') {
            $url_action = '/edit_movimento';
            $mov = $model_movimentos->consultarMovimento($id);
        }

        $this->view->settings = [
            'action'   => $this->index_route . $url_action,
            'redirect' => $this->index_route . '/home',
            'title'    => 'Movimento',
        ];

        $this->view->data['options_list'] = json_encode($model->selectAll(new ObjetivosEntity, [], [], []));
        $this->view->data['categorias'] = $model->selectAll(new CategoriasEntity, [['status', '=', '"1"']], [], ['tipo' => 'ASC', 'categoria' => 'ASC']);
        $this->view->data['invests'] = $model->selectAll(new InvestimentosEntity, [['status', '=', '"1"']], [], ['nomeBanco' => 'ASC', 'tituloInvest' => 'ASC']);
        $this->view->data['movimento'] = $mov[0] ?? null;
        $this->view->data['url_buscar_mov_mensal'] = $this->index_route . '/buscaMovMensal?buscar=';
        $this->view->data['div_buscar_mov_mensal'] = 'id-content-return';
        $this->view->data['lista_proprietarios'] = $model->selectAll(new ProprietariosEntity, [], [], []);
        $this->view->data['titulo_card'] = $action == 'edit' ? 'Edição' : 'Cadastro';

        $this->renderPage(
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
        $lista_id_prop = [];
        if ($ret) {
            foreach ($ret as $val) {
                $lista_id_prop[$val['idProprietario']] = $val['proprietario'];

                if ($val['tipo'] == 'RA') {
                    if (isset($total_resgate[$val['idProprietario']])) {
                        $total_resgate[$val['idProprietario']] += $val['total'];
                    } else {
                        $total_resgate[$val['idProprietario']] = $val['total'];
                    }

                    if (isset($data[$val['idProprietario']]['Resgate'])) {
                        $data[$val['idProprietario']]['Resgate'] += $val['total'];
                    } else {
                        $data[$val['idProprietario']]['Resgate'] = $val['total'];
                    }
                } elseif ($val['tipo'] == 'R') {
                    if (isset($total_receita[$val['idProprietario']])) {
                        $total_receita[$val['idProprietario']] += $val['total'];
                    } else {
                        $total_receita[$val['idProprietario']] = $val['total'];
                    }

                    if (isset($data[$val['idProprietario']]['Receitas'])) {
                        $data[$val['idProprietario']]['Receitas'] += $val['total'];
                    } else {
                        $data[$val['idProprietario']]['Receitas'] = $val['total'];
                    }
                } elseif ($val['tipo'] == 'D') {
                    if (isset($total_despesa[$val['idProprietario']])) {
                        $total_despesa[$val['idProprietario']] += $val['total'];
                    } else {
                        $total_despesa[$val['idProprietario']] = $val['total'];
                    }

                    if (isset($data[$val['idProprietario']]['Despesas'])) {
                        $data[$val['idProprietario']]['Despesas'] += $val['total'];
                    } else {
                        $data[$val['idProprietario']]['Despesas'] = $val['total'];
                    }
                } elseif ($val['tipo'] == 'A') {
                    if (isset($total_aplicacao[$val['idProprietario']])) {
                        $total_aplicacao[$val['idProprietario']] += $val['total'];
                    } else {
                        $total_aplicacao[$val['idProprietario']] = $val['total'];
                    }

                    if (isset($data[$val['idProprietario']]['Aplicação'])) {
                        $data[$val['idProprietario']]['Aplicação'] += $val['total'];
                    } else {
                        $data[$val['idProprietario']]['Aplicação'] = $val['total'];
                    }
                }
            }
        }

        $this->view->data['lista_id_prop'] = $lista_id_prop;
        $this->view->data['data'] = $data;
        $this->view->data['total_resgate'] = $total_resgate ?? 0;
        $this->view->data['total_receita'] = $total_receita ?? 0;
        $this->view->data['total_despesa'] = $total_despesa ?? 0;
        $this->view->data['total_aplicacao'] = $total_aplicacao ?? 0;

        $this->renderInModal(titulo: 'Demonstrativo', conteudo: 'exibir_resultado');
    }

    public function editarMovimento()//depurar
    {
        $model_movimentos = new MovimentosDAO();
        $model_rendimentos = new RendimentosDAO();
        $model_objetivos = new ObjetivosDAO();

        try {
            $obj_movimento = new MovimentosEntity();

            $arr_cat = explode(' - sinal: ' , $_POST['idCategoria']);
            $sinal = $arr_cat[1];

            $id_objetivo_old = $_POST['idObjOld'] ?? 0;
            $id_objetivo_new = $_POST['idObjetivo'] ?? 0;

            $obj_movimento->idMovimento = $_POST['idMovimento'];
            $obj_movimento->nomeMovimento = $_POST['nomeMovimento'];
            $obj_movimento->dataMovimento = $_POST['dataMovimento'];
            $obj_movimento->idCategoria = $arr_cat[0];
            $obj_movimento->valor = NumbersHelper::formatBRtoUS($_POST['valor']);
            $obj_movimento->idProprietario = $_POST['idProprietario'];
            $obj_movimento->idContaInvest = !empty($_POST['idContaInvest']) ? $_POST['idContaInvest'] : 0;
            $obj_movimento->observacao = $_POST['observacao'];
            $obj_movimento->idMovMensal = $_POST['idMovMensal'] ?? 0;

            if ($sinal == '-' && $obj_movimento->valor > 0) {
                $obj_movimento->valor = $obj_movimento->valor * -1;
            } elseif ($sinal == '+' && $obj_movimento->valor < 0) {
                $obj_movimento->valor = $obj_movimento->valor * -1;
            }

            $where = array(
                'idMovimento' => $obj_movimento->idMovimento
            );

            $ret = $model_movimentos->atualizar(new MovimentosEntity, $obj_movimento, $where);

            $rendimento = $model_rendimentos->selectAll(new RendimentosEntity, [['idMovimento', '=', $obj_movimento->idMovimento]], [], []);

            if (isset($rendimento[0]['idRendimento'])) {
                $rendimento = $rendimento[0];
                $old_id = $rendimento['idRendimento'];
                $old_invest = $rendimento['idContaInvest'];
                $old_valor = $rendimento['valorRendimento'];
                $old_tipo = $rendimento['tipo'];
                $old_data = $rendimento['dataRendimento'];
                $old_movimento = $rendimento['idMovimento'];

                $conta_invest = $model_rendimentos->selectAll(new InvestimentosEntity, [['idContaInvest', '=', $old_invest]], [], [])[0];

                if ($old_tipo == '4' || $old_tipo == '2') { //aplicação ou lucro
                    $saldo = $conta_invest['saldoAtual'] - abs($old_valor);
                } elseif ($old_tipo == '3' || $old_tipo == '1') { //resgate ou prejuízo
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

                    if ($old_tipo == '4' || $old_tipo == '2') { //aplicação ou lucro
                        $saldo_obj = $objetivo['saldoAtual'] - abs($old_valor);
                    } elseif ($old_tipo == '3' || $old_tipo == '1') { //resgate ou prejuízo
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

            if (!empty($obj_movimento->idContaInvest)) {
                (new InvestimentosController())->inserirMovimentacaodeAplicacao(
                    $obj_movimento->idContaInvest,
                    $id_objetivo_new,
                    $obj_movimento->idMovimento,
                    $obj_movimento->idCategoria,
                    $obj_movimento->valor,
                    $obj_movimento->dataMovimento
                );
            }

            if (!isset($ret['result']) || empty($ret['result'])) {
                throw new Exception('O Movimento não foi atualizado.');
            }

            $array_retorno = array(
                'result'   => true,
                'mensagem' => 'Movimento id ' . $obj_movimento->idMovimento . ' atualizado com sucesso.',
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

                $obj_movimento = new MovimentosEntity();

                $arr_cat = explode(' - sinal: ' , $_POST['idCategoria']);
                $sinal = $arr_cat[1];

                $obj_movimento->nomeMovimento = $_POST['nomeMovimento'];
                $obj_movimento->dataMovimento = $_POST['dataMovimento'];
                $obj_movimento->idCategoria = $arr_cat[0];
                $obj_movimento->valor = $sinal . NumbersHelper::formatBRtoUS($_POST['valor']);
                $obj_movimento->idProprietario = $_POST['idProprietario'];
                $obj_movimento->idContaInvest = !empty($_POST['idContaInvest']) ? $_POST['idContaInvest'] : 0;
                $obj_movimento->observacao = $_POST['observacao'];
                $obj_movimento->idMovMensal = $_POST['idMovMensal'] ?? 0;

                $id_objetivo = $_POST['idObjetivo'] ?? '';

                $ret = (new MovimentosDAO())->cadastrar(new MovimentosEntity, $obj_movimento);

                if (!$ret['result']) {
                    throw new Exception($this->msg_retorno_falha);
                }

                $obj_movimento->idMovimento = $ret['result'];

                //Inserção de Rendimento (invest ou retirada)
                if (!empty($obj_movimento->idContaInvest)) {
                    (new InvestimentosController())->inserirMovimentacaodeAplicacao(
                        $obj_movimento->idContaInvest,
                        $id_objetivo,
                        $obj_movimento->idMovimento,
                        $obj_movimento->idCategoria,
                        $obj_movimento->valor,
                        $obj_movimento->dataMovimento
                    );
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

    public function exibirObs()
    {
        $id_movimento = $_GET['idMovimento'];
        $model_movimentos = new MovimentosDAO();
        $this->view->data['observacao'] = $model_movimentos->consultarObservacao($id_movimento);

        $this->renderInModal(titulo: 'Observação movimento ' . $id_movimento, conteudo: 'observacao');
    }
}