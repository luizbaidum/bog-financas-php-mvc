<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\NumbersHelper;
use MF\Model\Model;
use src\Models\Categorias\CategoriasDAO;
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

    private $categoria_A;
    private $categoria_RA;

    public function __construct() 
    {
        $categorias = (new CategoriasDAO())->selecionarCategoriasTipoAeRA();

        $this->categoria_A = $categorias['A'];
        $this->categoria_RA = $categorias['RA'];

        parent::__construct();
    }

    public function index() 
    {
        $model_investimentos = new InvestimentosDAO();

        $objs = $model_investimentos->selectAll(new ObjetivosEntity, [['finalizado', '=', '"F"']], [], ['saldoAtual' => 'DESC']);
        $invests = $model_investimentos->getAllContas(true);

        $this->view->settings = [
            'action'    => $this->index_route . '/cadastrar_rendimento',
            'redirect'  => $this->index_route . '/contas-investimentos-index',
            'title'     => 'Investimentos',
            'url_obj'   => $this->index_route . '/consultar_objetivos?idContaInvest='
        ];

        $this->view->data['invests'] = $invests;
        $this->view->data['objs'] = $objs;
        $this->view->data['arr_projecao'] = $invests;

        $this->renderPage(
            conteudo: 'contas_investimentos_index'
        );
    }

    public function definirMovimentoDoInvestimento()
    {
        $model = new Model();

        $this->view->data['tipo_movimento'] = $_GET['action'];
        $this->view->data['invests'] = $model->selectAll(new InvestimentosEntity, [['status', '=', '"1"']], [], ['nomeBanco' => 'ASC', 'tituloInvest' => 'ASC']);
        $this->view->data['options_list'] = json_encode($model->selectAll(new ObjetivosEntity, [], [], []));
        $this->view->data['categorias'] = $model->selectAll(new CategoriasEntity, [['status', '=', '"1"']], [], ['tipo' => 'ASC', 'categoria' => 'ASC']);
        $this->view->data['url_buscar_mov_mensal'] = $this->index_route . '/buscaMovMensal?buscar=';
        $this->view->data['div_buscar_mov_mensal'] = 'id-content-return';
        $this->view->data['lista_proprietarios'] = $model->selectAll(new ProprietariosEntity, [], [], []);
        $this->view->data['titulo_card'] = 'Cadastro';

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

        $this->renderInModal(titulo: 'Objetivos do investimento', conteudo: 'objetivos_modal');
    }

    public function editarObjetivo()
    {
        $model_objetivos = new ObjetivosDAO();

        if ($this->isSetPost()) {
            try {
                $id = $_POST['idObj'];
                $_POST['vlrObj'] = NumbersHelper::formatBRtoUS($_POST['vlrObj']);
                $_POST['percentObjContaInvest'] = NumbersHelper::formatBRtoUS($_POST['percentObjContaInvest']);
                $_POST['finalizado'] = isset($_POST['finalizado'][$id]) && $_POST['finalizado'][$id] == 'T' ? 'T' : 'F';

                $conta_invest = $_POST['idContaInvest'];
                $percentual_old = $_POST['percentObjContaInvestOLD'];

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
        $utilizado = (new ObjetivosDAO())->consultarPercentualDisponivel($id_conta_invest);

        if ($utilizado !== false && ($percentual + $utilizado) > 100) {
            return ['status' => false,
                    'msg'    => 'Atenção! A Conta Invest informada já está ' . $utilizado . '% comprometida.'];
        }

        return ['status' => true];
    }

    public function validarPercentualUsoJson()
    {
        $id_conta_invest = $_GET['idContaInvest'];
        $percentual = $_GET['percentual'];

        if (empty($_GET['idContaInvest'])) {
            echo json_encode(['status' => false,
                              'msg'    => 'O id da conta investimento não foi encontrado.']);
            exit;
        }

        $utilizado = (new ObjetivosDAO())->consultarPercentualDisponivel($id_conta_invest);

        if ($utilizado !== false && ($percentual + $utilizado) > 100) {
            echo json_encode(['status' => false,
                              'msg'    => 'A Conta Invest informada já está ' . NumbersHelper::formatUStoBR($utilizado) . '% comprometida.']);
            exit;
        }

        echo json_encode(['status' => true]);
    }

    public function investimentos()
    {
        $this->view->settings = [
            'action'    => $this->index_route . '/cad_investimentos',
            'redirect'  => $this->index_route . '/investimentos',
            'title'     => 'Investimentos',
            'extra_url' => $this->index_route . '/edit-status-investimento?id=',
        ];

        $todas_contas = (new InvestimentosDAO())->getAllContas();

        $this->view->data['lista_proprietarios'] = (new ProprietariosDAO())->selectAll(new ProprietariosEntity, [], [], []);
        $this->view->data['todas_contas'] = $todas_contas;

        $this->renderPage(conteudo: 'investimentos', base_interna: 'base_cruds', extra: 'listagem_investimentos');
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
            'action'    => $this->index_route . '/cad-objetivos',
            'redirect'  => $this->index_route . '/objetivos',
            'title'     => 'Objetivos',
            'extra_url' => $this->index_route . '/edit-status-objetivo?id=',
        ];

        $this->view->data['invests'] = $model->selectAll(new InvestimentosEntity, [['status', '=', '"1"']], [], ['nomeBanco' => 'ASC', 'tituloInvest' => 'ASC']);
        $this->view->data['lista_obj'] = $model->selectAll(new ObjetivosEntity, [], [], []);

        $this->renderPage(conteudo: 'objetivos', base_interna: 'base_cruds', extra: 'listagem_objetivos');
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

                if (isset($_POST['tipoMovimento'])) {
                    if ($_POST['tipoMovimento'] == 'pagamento') {
                        $obj_movimento = new MovimentosEntity();

                        $arr_cat = explode(' - sinal: ' , $_POST['idCategoria']);
                        $sinal = $arr_cat[1];

                        $obj_movimento->nomeMovimento = 'Resgate para pagar ' . $_POST['nomeMovimento'];
                        $obj_movimento->dataMovimento = $_POST['dataMovimento'];
                        $obj_movimento->idCategoria = $this->categoria_RA;
                        $obj_movimento->valor = NumbersHelper::formatBRtoUS($_POST['valor']);
                        $obj_movimento->idProprietario = $_POST['idProprietario'];
                        $obj_movimento->idContaInvest = !empty($_POST['idContaInvest']) ? $_POST['idContaInvest'] : 0;
                        $obj_movimento->observacao = $_POST['observacao'];

                        $ret = (new MovimentosDAO())->cadastrar(new MovimentosEntity, $obj_movimento);
                        $obj_movimento->idMovimento = $ret['result'];

                        $id_objetivo = $_POST['idObjetivo'] ?? '';

                        $this->inserirMovimentacaodeAplicacao(
                            $obj_movimento->idContaInvest, 
                            $id_objetivo, 
                            $obj_movimento->idMovimento, 
                            $obj_movimento->idCategoria, 
                            $obj_movimento->valor, 
                            $obj_movimento->dataMovimento
                        );

                        $obj_movimento->nomeMovimento = $_POST['nomeMovimento'];
                        $obj_movimento->idCategoria = $arr_cat[0]; //Definido pelo usuário
                        $obj_movimento->valor = $sinal . NumbersHelper::formatBRtoUS($_POST['valor']);
                        $obj_movimento->idMovMensal = $_POST['idMovMensal'] ?? 0;
                        unset($obj_movimento->idContaInvest);
                        unset($obj_movimento->idMovimento);

                        //Inserção de Movimento
                        $ret = (new MovimentosDAO())->cadastrar(new MovimentosEntity, $obj_movimento);
                    }

                    if ($_POST['tipoMovimento'] == 'transferencia') {
                        $ret = $this->cadastrarTransferenciaEntreInvestimentos(
                            $_POST['idContaInvestOrigem'], 
                            $_POST['idObjetivoOrigem'], 
                            $_POST['valor'], 
                            $_POST['idContaInvestDestino'], 
                            $_POST['idObjetivoDestino']
                        );
                    }
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
            'action'   => $this->index_route . '/investimentos-movimentar',
            'redirect' => $this->index_route . '/investimentos-movimentar',
            'url_ajax' => $this->index_route . '/definir_movimento_investimento?action=',
            'title'    => 'Movimento entre Investimentos',
            'div_ajax' => 'id-destino'
        ];

        $this->view->data['categorias'] = $model->selectAll(new CategoriasEntity, [['status', '=', '"1"']], [], ['tipo' => 'ASC', 'categoria' => 'ASC']);

        $this->renderPage(conteudo: 'investimentos_movimentar', base_interna: 'base_cruds');
    }

    private function cadastrarTransferenciaEntreInvestimentos($invest_origem, $objetivo_origem, $valor, $invest_destino, $objetivo_destino)
    {
        //Resgate
        list($id_invest, $id_proprietario) = explode('@', $invest_origem);

        $item = array(
                    'nomeMovimento'   => 'Resgate - movimento entre investimentos',
                    'dataMovimento'   => date("Y-m-d"),
                    'idCategoria'     => $this->categoria_RA,
                    'valor'           => $valor,
                    'idProprietario'  => $id_proprietario,
                    'idContaInvest'   => $id_invest
                );

        $ret = (new MovimentosDAO())->cadastrar(new MovimentosEntity, $item);

        $id_movimento = $ret['result'];

        $this->inserirMovimentacaodeAplicacao($id_invest, $objetivo_origem, $id_movimento, $this->categoria_RA, $valor, date('Y-m-d'));

        //Aplicação
        list($id_invest, $id_proprietario) = explode('@', $invest_destino);

        $item = array(
            'nomeMovimento'   => 'Aplicação - movimento entre investimentos',
            'dataMovimento'   => date("Y-m-d"),
            'idCategoria'     => $this->categoria_A,
            'valor'           => ($valor * -1),
            'idProprietario'  => $id_proprietario,
            'idContaInvest'   => $id_invest
        );

        $ret = (new MovimentosDAO())->cadastrar(new MovimentosEntity, $item);

        $id_movimento = $ret['result'];

        $this->inserirMovimentacaodeAplicacao($id_invest, $objetivo_destino, $id_movimento, $this->categoria_A, $valor, date('Y-m-d'));

        return $ret;
    }

    public function inserirMovimentacaodeAplicacao($id_conta_invest, $id_objetivo, $id_movimento, $id_categoria, $valor, $data_rend)
    {
        $model = new Model();

        switch ($id_categoria) {
            case $this->categoria_A:
                $tipo = 4;

                $valor_aplicado = $valor;
                if ($valor_aplicado < 0) {
                    $valor_aplicado = ($valor_aplicado * -1); //veio negativo, pois aplicação é saída de dinheiro da conta corrente, mas é entrada em aplicações.
                }

                if (!empty($id_objetivo)) {
                    $objetivo = $model->selectAll(new ObjetivosEntity, [['idObj', '=', $id_objetivo]], [], [])[0];

                    $item = [
                        'saldoAtual' => $objetivo['saldoAtual'] + $valor_aplicado
                    ];
                    $item_where = ['idObj' => $objetivo['idObj']];
                    $model->atualizar(new ObjetivosEntity, $item, $item_where);
                } else {
                    $objetivos = $model->selectAll(new ObjetivosEntity, [['idContaInvest', '=', $id_conta_invest]], [], []);

                    if (!empty($objetivos)) {
                        foreach ($objetivos as $value) {
                            $item = [
                                'saldoAtual' => $value['saldoAtual'] + ($valor_aplicado * ($value['percentObjContaInvest'] / 100))
                            ];
                            $item_where = ['idObj' => $value['idObj']];

                            $model->atualizar(new ObjetivosEntity, $item, $item_where);
                        }
                    }
                }

                break;
            case $this->categoria_RA:
                $tipo = 3;

                $valor_aplicado = $valor; 
                if ($valor_aplicado > 0) {
                    $valor_aplicado = ($valor_aplicado * -1); //veio positivo, pois resgate é entrada de dinheiro da conta corrente, mas é saída em aplicações.
                }

                if (!empty($id_objetivo)) {
                    $saldo_atual = $model->getSaldoAtual(new ObjetivosEntity, $id_objetivo);
                    $item = [
                        'saldoAtual' => ($saldo_atual + $valor_aplicado)
                    ];
                    $item_where = [
                        'idObj' => $id_objetivo
                    ];

                    $model->atualizar(new ObjetivosEntity, $item, $item_where);
                } else {
                    $objetivos = $model->selectAll(new ObjetivosEntity, [['idContaInvest', '=', $id_conta_invest]], [], []);

                    if (!empty($objetivos)) {
                        foreach ($objetivos as $value) {
                            $item = [
                                'saldoAtual' => $value['saldoAtual'] + ($valor_aplicado * ($value['percentObjContaInvest'] / 100))
                            ];
                            $item_where = ['idObj' => $value['idObj']];

                            $model->atualizar(new ObjetivosEntity, $item, $item_where);
                        }
                    }
                }

                break;
            default:
                $tipo = '';
        }

        $obj_rendimento = new RendimentosEntity();

        $obj_rendimento->idContaInvest = $id_conta_invest;
        $obj_rendimento->valorRendimento = $valor_aplicado;
        $obj_rendimento->tipo = $tipo;
        $obj_rendimento->dataRendimento = $data_rend;
        $obj_rendimento->idMovimento = $id_movimento;
        $obj_rendimento->idObj = (empty($id_objetivo) ? 0 : $id_objetivo);

        $model->cadastrar(new RendimentosEntity, $obj_rendimento);

        $saldo_atual = $model->getSaldoAtual(new InvestimentosEntity, $id_conta_invest);
        $item = [
            'saldoAtual' => ($saldo_atual + $valor_aplicado)
        ];
        $item_where = [
            'idContaInvest' => $id_conta_invest
        ];
        $model->atualizar(new InvestimentosEntity, $item, $item_where);
    }

    public function editarStatus()
    {
        $id_conta_invest = $_GET['id'];
        $status = $_GET['status'];

        if (!empty($id_conta_invest) && $status != '') {
            try {
                $ret = (new InvestimentosDAO())->atualizar(new InvestimentosEntity,
                            ['status' => $status],
                            ['idContaInvest' =>  $id_conta_invest]
                        );

                if ($ret['result']) {
                    $array_retorno = array(
                        'result'   => $ret['result'],
                        'mensagem' => 'idContaInvest ' . $id_conta_invest . ' alterada com sucesso.'
                    );

                    echo json_encode($array_retorno);
                } else {
                    throw new Exception('Erro ao alterar idContaInvest ' . $id_conta_invest . '.');
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

    public function editarStatusObjetivo()
    {
        $id_obj = $_GET['id'];
        $status = $_GET['status'];

        if (!empty($id_obj) && $status != '') {
            try {
                $ret = (new ObjetivosDAO())->atualizar(new ObjetivosEntity,
                            ['finalizado' => $status],
                            ['idObj' => $id_obj]
                        );

                if ($ret['result']) {
                    $array_retorno = array(
                        'result'   => $ret['result'],
                        'mensagem' => 'idObjetivo ' . $id_obj . ' alterado com sucesso.'
                    );

                    echo json_encode($array_retorno);
                } else {
                    throw new Exception('Erro ao alterar idObjetivo ' . $id_obj . '.');
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