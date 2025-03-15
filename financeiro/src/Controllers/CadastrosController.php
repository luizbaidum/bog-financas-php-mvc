<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Model\Model;
use src\Models\Movimentos\MovimentosEntity;
use src\Models\Categorias\CategoriasDAO;
use src\Models\Categorias\CategoriasEntity;
use src\Models\Investimentos\InvestimentosDAO;
use src\Models\Investimentos\InvestimentosEntity;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\MovimentosMensais\MovimentosMensaisDAO;
use src\Models\MovimentosMensais\MovimentosMensaisEntity;
use src\Models\Objetivos\ObjetivosDAO;
use src\Models\Objetivos\ObjetivosEntity;
use src\Models\Orcamento\OrcamentoDAO;
use src\Models\Orcamento\OrcamentoEntity;
use src\Models\Preferencias\PreferenciasDAO;
use src\Models\Preferencias\PreferenciasEntity;
use src\Models\Rendimentos\RendimentosDAO;
use src\Models\Rendimentos\RendimentosEntity;

class CadastrosController extends Controller {

    private string $msg_retorno_falha = 'O cadastro não teve sucesso. Verifique os dados e tente novamente. Se o erro persistir, entre em contato com o suporte.';
    private string $msg_retorno_sucesso = 'Cadastro realizado.';

    public function categorias()
    {
        $this->view->settings = [
            'action'   => $this->index_route . '/cad_categorias',
            'redirect' => $this->index_route . '/categorias',
            'title'    => 'Cadastro de Categoria',
        ];

        $this->renderPage(main_route: $this->index_route . '/categorias', conteudo: 'categorias', base_interna: 'base_cruds');
    }

    public function cadastrarCategorias()
    {
        if ($this->isSetPost()) {
            try {
                $_POST['tipo'] = strtoupper($_POST['tipo']);

                if ($_POST['tipo'] != 'R' && $_POST['tipo'] != 'D' && $_POST['tipo'] != 'A') {
                    throw new Exception('Atenção: Definir tipo como R, D ou A.');
                }

                if ($_POST['sinal'] != '+' && $_POST['sinal'] != '-') {
                    throw new Exception('Atenção: Definir sinal como + ou -.');
                }

                $ret = (new CategoriasDAO())->cadastrar(new CategoriasEntity, $_POST);

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

    public function cadastrarMovimentos()
    {
        define('APLICACAO', '12');
        define('RESGATE', '10');

        if ($this->isSetPost()) {
            try {
                if ($_POST['idCategoria'] == '') {
                    throw new Exception('Por favor, escolher categoria.'); 
                }

                $model = new Model();

                $arr_cat = explode(' - sinal: ' , $_POST['idCategoria']);
                $_POST['idCategoria'] = $arr_cat[0];
                $sinal = $arr_cat[1];

                //Validator::cadastrarMovimentos();

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
                    switch ($_POST['idCategoria']) {
                        case APLICACAO:
                            $tipo = 4;
                            $valor_aplicado = ($_POST['valor'] * -1); //veio negativo, pois aplicação é saída de dinheiro da conta corrente, mas é entrada em aplicações.

                            $objetivos = $model->selectAll(new ObjetivosEntity, [['idContaInvest', '=', $id_conta_invest]], [], []);

                            foreach ($objetivos as $value) {
                                $item = [
                                    'saldoAtual' => $value['saldoAtual'] + ($valor_aplicado * ($value['percentObjContaInvest'] / 100))
                                ];
                                $item_where = ['idObj' => $value['idObj']];
                                $model->atualizar(new ObjetivosEntity, $item, $item_where);
                            }

                            break;
                        case RESGATE:
                            $tipo = 3;
                            $valor_aplicado = ($_POST['valor'] * -1); //veio positivo, pois resgate é entrada de dinheiro da conta corrente, mas é saída em aplicações.

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
                        'dataRendimento'  => $_POST['dataMovimento'],
                        'tipo'            => $tipo,
                        'idMovimento'     => $id_movimento
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

    public function Investimentos()
    {
        $this->view->settings = [
            'action'   => $this->index_route . '/cad_investimentos',
            'redirect' => $this->index_route . '/investimentos',
            'title'    => 'Cadastro de Investimentos',
        ];

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
    
    public function orcamento()
    {
        $model = new Model();

        $this->view->settings = [
            'action'   => $this->index_route . '/cad_orcamento',
            'redirect' => $this->index_route . '/orcamento',
            'title'    => 'Cadastro de Orçamento por Categoria',
        ];

        $this->view->data['categorias'] = $model->selectAll(new CategoriasEntity, [], [], ['categoria' => 'ASC']);
        $this->view->data['months'] = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Todos');

        $this->renderPage(main_route: $this->index_route . '/orcamento', conteudo: 'orcamento', base_interna: 'base_cruds');
    }

    public function orcamentoDoRealizado()
    {
        $this->view->settings = [
            'action'   => $this->index_route . '/cad_orcamento_do_realizado',
            'url_ajax' => $this->index_route . '/buscar_orcamento_do_realizado',
            'redirect' => $this->index_route . '/orcamento_do_realizado',
            'title'    => 'Importação de Orçamento',
            'div_ajax' => 'id-content-importar'
        ];

        $this->renderPage(main_route: $this->index_route . '/orcamento_do_realizado', conteudo: 'orcamento_do_realizado', base_interna: 'base_cruds');
    }

    public function cadastrarOrcamento()
    {
        if ($this->isSetPost()) {
            try {
                $arr_cat = explode(' - sinal: ' , $_POST['idCategoria']);
                $_POST['idCategoria'] = $arr_cat[0];
                $sinal = $arr_cat[1];
    
                if ($sinal == '-' && !strpos($_POST['valor'], '-'))
                    $_POST['valor'] = $sinal . $_POST['valor'];
    
                $ret = (new OrcamentoDAO())->cadastrar(new OrcamentoEntity(), $_POST);

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
                    $sinal = $_POST['sinal'][$k];
    
                    $item['valor'] = $_POST['valor'][$k];
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

    public function lancarMovimentoMensal()
    {
        if ($this->isSetPost()) {
            $item = array();

            $model_movimentos_mensais = new MovimentosMensaisDAO();

            try {
                foreach ($_POST['idMovMensal'] as $id) {
                    $arr_cat = explode(' - sinal: ', $_POST['idCategoria'][$id]);
                    $sinal = $arr_cat[1];

                    $item['nomeMovimento'] = $_POST['nomeMovimento'][$id];
                    $item['dataMovimento'] = $_POST['dataMovimento'][$id];
                    $item['proprietario'] = $_POST['proprietario'][$id];
                    $item['idCategoria'] = $arr_cat[0];
                    $item['valor'] = $sinal . $_POST['valor'][$id];

                    $ret = $model_movimentos_mensais->cadastrar(new MovimentosEntity(), $item);

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

    public function cadastrarRendimento()
    {
        $model_rendimentos = new RendimentosDAO();
        $model_investimentos = new InvestimentosDAO();
        $model_objetivos = new ObjetivosDAO();

        if ($this->isSetPost()) {
            try {
                if ($_POST['tipo'] == '1')
                    $_POST['valorRendimento'] = ($_POST['valorRendimento'] * -1);

                $ret_a = $model_rendimentos->cadastrar(new RendimentosEntity, $_POST);

                if (!isset($ret_a['result']) || empty($ret_a['result'])) {
                    throw new Exception($this->msg_retorno_falha);
                }

                $saldo_atual = $model_investimentos->getSaldoAtual(new InvestimentosEntity, $_POST['idContaInvest']);
                $item = [
                    'saldoAtual'    => ($saldo_atual + $_POST['valorRendimento']),
                    'saldoAnterior' => $saldo_atual,
                    'dataAnterior'  => date('Y-m-d')
                ];
                $item_where = [
                    'idContaInvest' => $_POST['idContaInvest']
                ];

                $ret_b = $model_investimentos->atualizar(new InvestimentosEntity, $item, $item_where);

                if (!isset($ret_b['result']) || empty($ret_b['result'])) {
                    throw new Exception($this->msg_retorno_falha);
                }

                $objetivos = $model_objetivos->selectAll(new ObjetivosEntity, [['idContaInvest', '=', $_POST['idContaInvest']]], [], []);

                foreach ($objetivos as $value) {
                    $item = [
                        'saldoAtual' => $value['saldoAtual'] + ($_POST['valorRendimento'] * ($value['percentObjContaInvest'] / 100))
                    ];
                    $item_where = ['idObj' => $value['idObj']];
                    $ret_c = $model_objetivos->atualizar(new ObjetivosEntity, $item, $item_where);

                    if (!isset($ret_c['result']) || empty($ret_c['result'])) {
                        throw new Exception($this->msg_retorno_falha . '<br>' . 'Os cálculos do objetivo id: ' . $value['idObj'] . ' e subsequentes não foram salvos.');
                    }
                }

                if ($ret_a['result'] > 0 && $ret_b['result'] > 0) {
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

    public function cadastrarPreferencia()
    {
        if ($this->isSetPost()) {
            $model_preferencias = new PreferenciasDAO();

            try {
                $item = $_POST;
                $item['status'] = 'T';
                $ret = $model_preferencias->cadastrar(new PreferenciasEntity, $item);

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
?>