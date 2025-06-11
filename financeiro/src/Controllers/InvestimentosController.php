<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\NumbersHelper;
use MF\Model\Model;
use src\Models\Categorias\CategoriasEntity;
use src\Models\Investimentos\InvestimentosDAO;
use src\Models\Investimentos\InvestimentosEntity;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\Movimentos\MovimentosEntity;
use src\Models\Objetivos\ObjetivosDAO;
use src\Models\Objetivos\ObjetivosEntity;
use src\Models\Proprietarios\ProprietariosDAO;
use src\Models\Proprietarios\ProprietariosEntity;
use src\Models\Rendimentos\RendimentosEntity;

class InvestimentosController extends Controller {

    const APLICACAO = '12';
    const RESGATE = '10';

    public function index() {
        $model_investimentos = new InvestimentosDAO();

        $contas = $model_investimentos->selectAll(new InvestimentosEntity, [], [], []);
        $invests = $model_investimentos->selectAll(new InvestimentosEntity, [], [], ['nomeBanco' => 'ASC']);
        $objs = $model_investimentos->selectAll(new ObjetivosEntity, [], [], ['saldoAtual' => 'DESC']);

        $this->view->settings = [
            'action'   => $this->index_route . '/cadastrar_rendimento',
            'redirect' => $this->index_route . '/contas_investimentos_index',
            'title'    => 'Indicadores',
            'url_obj'  => $this->index_route . '/consultar_objetivos?idContaInvest=',
        ];

        $this->view->data['contas'] = $contas;
        $this->view->data['invests'] = $invests;
        $this->view->data['objs'] = $objs;

        $this->renderPage(
            main_route: $this->index_route . '/contas_investimentos_index', 
            conteudo: 'contas_investimentos_index'
        );
    }

    public function definirMovimentoDoInvestimento()
    {
        $model = new Model();

        $this->view->data['tipo_movimento'] = $_GET['action'];
        $this->view->data['invests'] = $model->selectAll(new InvestimentosEntity, [], [], ['nomeBanco' => 'ASC', 'tituloInvest' => 'ASC']);
        $this->view->data['options_list'] = json_encode($model->selectAll(new ObjetivosEntity, [], [], []));
        $this->view->data['categorias'] = $model->selectAll(new CategoriasEntity, [], [], ['tipo' => 'ASC', 'categoria' => 'ASC']);
        $this->view->data['url_buscar_mov_mensal'] = $this->index_route . '/buscaMovMensal?buscar=';
        $this->view->data['div_buscar_mov_mensal'] = 'id-content-return';
        $this->view->data['lista_proprietarios'] = $model->selectAll(new ProprietariosEntity, [], [], []);

        $this->renderSimple('definido_movimento_investimento');
    }

    public function consultarObjetivos()
    {
        $id_invest = $_GET['idContaInvest'];

        $model_objetivos = new ObjetivosDAO();

        $lista_objetivos = $model_objetivos->consultarObjetivosPorInvestimento($id_invest);

        $this->view->settings = [
            'action'   => $this->index_route . '/editar_objetivo',
            'redirect' => $this->index_route . '/extrato_investimentos',
        ];

        $this->view->data['lista_objetivos'] = $lista_objetivos;

        $this->renderInModal(titulo: 'Objetivos do investimento conta', conteudo: 'objetivos_modal');
    }

    public function editarObjetivo()
    {
        $model_objetivos = new ObjetivosDAO();

        if ($this->isSetPost()) {
            try {
                $id = $_POST['idObj'];
                $_POST['vlrObj'] = NumbersHelper::formatBRtoUS($_POST['vlrObj']);
                $_POST['percentObjContaInvest'] = NumbersHelper::formatBRtoUS($_POST['percentObjContaInvest']);

                $conta_invest = $_POST['idContaInvest'];
                $percentual_old = $_POST['percentObjContaInvestOLD'];

                if (!isset($_POST['finalizado'])) {
                    $_POST['finalizado'] = 'F';
                }

                unset($_POST['idObj']);
                unset($_POST['idContaInvest']);
                unset($_POST['percentObjContaInvestOLD']);

                if ($_POST['percentObjContaInvest'] > $percentual_old) {
                    $validation = $this->validarPercentualUso($conta_invest, ($_POST['percentObjContaInvest'] - $percentual_old));

                    if (!$validation['status']) {
                        throw new Exception($validation['msg']);
                    }
                }

                $ret = $model_objetivos->atualizar(new ObjetivosEntity, $_POST, ['idObj' => $id]);

                if (!isset($ret['result']) || empty($ret['result'])) {
                    throw new Exception('O objetivo não foi atualizado.');
                }

                $array_retorno = array(
					'result'   => true,
					'mensagem' => 'Objetivo id ' . $id . ' atualizado com sucesso.',
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

    private function validarPercentualUso($id_conta_invest, $percentual)
    {
        $utilizado = (new ObjetivosDAO())->consultarPercentualDisponivel($id_conta_invest, $percentual);

        if ($utilizado !== false && ($percentual + $utilizado) > 100) {
            return [
                'status' => false,
                'msg'    => 'Atenção! A Conta Invest informada já está ' . $utilizado . '% comprometida.'
            ];
        }

        return ['status' => true];
    }

    public function investimentos()
    {
        $this->view->settings = [
            'action'   => $this->index_route . '/cad_investimentos',
            'redirect' => $this->index_route . '/investimentos',
            'title'    => 'Cadastro de Investimentos',
        ];

        $this->view->data['lista_proprietarios'] = (new ProprietariosDAO())->selectAll(new ProprietariosEntity, [], [], []);

        $this->renderPage(main_route: $this->index_route . '/investimentos', conteudo: 'investimentos', base_interna: 'base_cruds');
    }

    public function cadastrarInvestimentos()
    {
        if ($this->isSetPost()) { 
            try {
                if (isset($_POST['cadContaInvest'])) {
                    unset($_POST['cadContaInvest']);
                }
        
                $_POST['saldoAtual'] = $_POST['saldoInicial'];
                $ret = (new InvestimentosDAO())->cadastrar(new InvestimentosEntity, $_POST);

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

    public function objetivos()
    {
        $model = new Model();

        $this->view->settings = [
            'action'   => $this->index_route . '/cad_objetivos',
            'redirect' => $this->index_route . '/objetivos',
            'title'    => 'Cadastro de Objetivos',
        ];

        $this->view->data['invests'] = $model->selectAll(new InvestimentosEntity, [], [], ['nomeBanco' => 'ASC', 'tituloInvest' => 'ASC']);

        $this->renderPage(main_route: $this->index_route . '/objetivos', conteudo: 'objetivos', base_interna: 'base_cruds');
    }

    public function cadastrarObjetivos()
    {
        if ($this->isSetPost()) {
            try {
                $ret = (new ObjetivosDAO())->cadastrar(new ObjetivosEntity, $_POST);

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

    public function movimentarInvestimentos()
    {
        $model = new Model();

        if ($this->isSetPost()) {
            try {
                $ret = array();

                if (isset($_POST['tipoMovimento']) && $_POST['tipoMovimento'] == 'pagamento') {
                    //Resgate
                    $item = array(
                        'nomeMovimento' => 'Resgate para pagar ' . $_POST['nomeMovimento'],
                        'dataMovimento' => $_POST['dataMovimento'],
                        'idCategoria'   => 10, //Resgate
                        'valor'         => NumbersHelper::formatBRtoUS($_POST['valor']),
                        'proprietario'  => $_POST['proprietario'],
                        'idContaInvest' => $_POST['idContaInvest']
                    );
    
                    $ret = (new MovimentosDAO())->cadastrar(new MovimentosEntity, $item);
                    $id_movimento = $ret['result'];

                    $this->inserirMovimentacaodeAplicacao($_POST['idContaInvest'], $_POST['idObjetivo'], $id_movimento, '10', NumbersHelper::formatBRtoUS($_POST['valor']), $_POST['dataMovimento']);

                    //Movimento
                    $arr_cat = explode(' - sinal: ' , $_POST['idCategoria']);
                    $_POST['idCategoria'] = $arr_cat[0];
                    $sinal = $arr_cat[1];
                    $_POST['valor'] = $sinal . NumbersHelper::formatBRtoUS($_POST['valor']);

                    unset($_POST['idObjetivo']);
                    unset($_POST['tipoMovimento']);
                    unset($_POST['idContaInvest']);

                    //Inserção de Movimento
                    $ret = (new MovimentosDAO())->cadastrar(new MovimentosEntity, $_POST);
                }

                if (isset($_POST['tipoMovimento']) && $_POST['tipoMovimento'] == 'transferencia') {
                    $ret = $this->cadastrarTransferenciaEntreInvestimentos();
                }

                if (!in_array(false, $ret)) {
					$array_retorno = array(
						'result'   => $ret['result'],
						'mensagem' => $this->msg_retorno_sucesso
					);

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

				echo json_encode($array_retorno);
                exit;
			}
        }

        $this->view->settings = [
            'action'   => $this->index_route . '/investimentos_movimentar',
            'redirect' => $this->index_route . '/investimentos_movimentar',
            'url_ajax' => $this->index_route . '/definir_movimento_investimento?action=',
            'title'    => 'Movimento entre Investimentos',
            'div_ajax' => 'id-destino'
        ];

        $this->view->data['categorias'] = $model->selectAll(new CategoriasEntity, [], [], ['tipo' => 'ASC', 'categoria' => 'ASC']);

        $this->renderPage(main_route: $this->index_route . '/investimentos_movimentar', conteudo: 'investimentos_movimentar', base_interna: 'base_cruds');
    }

     private function cadastrarTransferenciaEntreInvestimentos()
    {
        //Resgate
        list($id_invest, $proprietario) = explode('@', $_POST['idContaInvestOrigem']);
                        
        $item = array(
                    'nomeMovimento' => 'Resgate - movimento entre investimentos',
                    'dataMovimento' => date("Y-m-d"),
                    'idCategoria'   => 10, //Resgate
                    'valor'         => $_POST['valor'],
                    'proprietario'  => $proprietario,
                    'idContaInvest' => $id_invest
                );

        $ret = (new MovimentosDAO())->cadastrar(new MovimentosEntity, $item);

        $id_movimento = $ret['result'];

        $this->inserirMovimentacaodeAplicacao($id_invest, $_POST['idObjetivoOrigem'], $id_movimento, '10', $_POST['valor'], date('Y-m-d'));

        //Aplicação
        list($id_invest, $proprietario) = explode('@', $_POST['idContaInvestDestino']);
        
        $item = array(
            'nomeMovimento' => 'Aplicação - movimento entre investimentos',
            'dataMovimento' => date("Y-m-d"),
            'idCategoria'   => 12, //Aplicação
            'valor'         => ($_POST['valor'] * -1),
            'proprietario'  => $proprietario,
            'idContaInvest' => $id_invest
        );

        $ret = (new MovimentosDAO())->cadastrar(new MovimentosEntity, $item);

        $id_movimento = $ret['result'];

        $this->inserirMovimentacaodeAplicacao($id_invest, $_POST['idObjetivoDestino'], $id_movimento, '12', $_POST['valor'], date('Y-m-d'));

        return $ret;
    }

    public function inserirMovimentacaodeAplicacao($id_conta_invest, $id_objetivo, $id_movimento, $id_categoria, $valor, $data_rend)
    {
        $model = new Model();

        switch ($id_categoria) {
            case self::APLICACAO:
                $tipo = 4;

                $valor_aplicado = $valor; 
                if ($valor_aplicado < 0) {
                    $valor_aplicado = ($valor_aplicado * -1); //veio negativo, pois aplicação é saída de dinheiro da conta corrente, mas é entrada em aplicações.
                }

                if ($id_objetivo != '' && $id_objetivo != '0') {
                    $objetivo = $model->selectAll(new ObjetivosEntity, [['idObj', '=', $id_objetivo]], [], [])[0];

                    $item = [
                        'saldoAtual' => $objetivo['saldoAtual'] + $valor_aplicado
                    ];
                    $item_where = ['idObj' => $objetivo['idObj']];
                    $model->atualizar(new ObjetivosEntity, $item, $item_where);
                } else {
                    $objetivos = $model->selectAll(new ObjetivosEntity, [['idContaInvest', '=', $id_conta_invest]], [], []);

                    foreach ($objetivos as $value) {
                        $item = [
                            'saldoAtual' => $value['saldoAtual'] + ($valor_aplicado * ($value['percentObjContaInvest'] / 100))
                        ];
                        $item_where = ['idObj' => $value['idObj']];
                        $model->atualizar(new ObjetivosEntity, $item, $item_where);
                    }
                }

                break;
            case self::RESGATE:
                $tipo = 3;

                $valor_aplicado = $valor; 
                if ($valor_aplicado > 0) {
                    $valor_aplicado = ($valor_aplicado * -1); //veio positivo, pois resgate é entrada de dinheiro da conta corrente, mas é saída em aplicações.
                }

                $saldo_atual = $model->getSaldoAtual(new ObjetivosEntity, $id_objetivo);
                $item = [
                    'saldoAtual' => ($saldo_atual + $valor_aplicado)
                ];
                $item_where = [
                    'idObj' => $id_objetivo
                ];
                $model->atualizar(new ObjetivosEntity, $item, $item_where);

                break;
            default:
                $tipo = '';
        }

        $item = [
            'idContaInvest'   => $id_conta_invest,
            'valorRendimento' => $valor_aplicado,
            'dataRendimento'  => $data_rend,
            'tipo'            => $tipo,
            'idMovimento'     => $id_movimento,
            'idObj'           => $id_objetivo
        ];

        $model->cadastrar(new RendimentosEntity, $item);

        $saldo_atual = $model->getSaldoAtual(new InvestimentosEntity, $id_conta_invest);
        $item = [
            'saldoAtual' => ($saldo_atual + $valor_aplicado)
        ];
        $item_where = [
            'idContaInvest' => $id_conta_invest
        ];
        $model->atualizar(new InvestimentosEntity, $item, $item_where);
    }
}