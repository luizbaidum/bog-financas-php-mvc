<?php
namespace src\Controllers;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use MF\Controller\Controller;
use src\Models\Rendimentos\RendimentosDAO;
use src\Models\Investimentos\InvestimentosDAO;
use src\Models\Investimentos\InvestimentosEntity;
use src\Models\Objetivos\ObjetivosDAO;
use src\Models\Objetivos\ObjetivosEntity;
use src\Models\Rendimentos\RendimentosEntity;

class RendimentosController extends Controller {
    public function index() {
        $model_rendimentos = new RendimentosDAO();
        $model_investimentos = new InvestimentosDAO();

        $saldos = $model_investimentos->getSaldosIniciais();
        $rendi = $model_rendimentos->getEvolucaoRendimentos();

        foreach ($saldos as $s) {
            foreach ($rendi as $k => $r) {
                if ($r['idContaInvest'] == $s['idContaInvest']) {
                    $rendi[$k]['valor'] = $r['valor'] + $s['saldoInicial'];
                    break;
                }
            }
        }

        foreach ($rendi as $k => $r) {
            $id = $rendi[($k - 1)]['idContaInvest'] ?? 0;
            $valor = $rendi[($k - 1)]['valor'] ?? 0;

            if ($id > 0 && $id == $r['idContaInvest']) {
                $rendi[$k]['valor'] = $r['valor'] + $valor;
            }
        }

        $this->view->data['ret'] = json_encode($rendi);
        $this->renderPage(
            conteudo: 'evolucao_rendimentos'
        );
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

                $this->calcularTxRendimentoAM($_POST['idContaInvest'], $_POST['dataRendimento'], $model_rendimentos);

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

    private function calcularTxRendimentoAM(string $id_conta_invest, string $data_rend, RendimentosDAO $model) : void
    {
        $rendimentos = $model->selecionarDoisUltimosRendimentos($id_conta_invest, $data_rend);

        if (count($rendimentos) > 1) {
            $data_ultima = new DateTime($rendimentos[0]['dataRendimento']);
            $data_anterior = new DateTime($rendimentos[1]['dataRendimento']);
            $intervalo = new DateInterval('P1D');
            $periodo = new DatePeriod($data_anterior, $intervalo, $data_ultima);

            if (!empty($periodo)) {
                $fds = 0;
                foreach ($periodo as $data) {
                    //6 é sábado e 7 é domingo
                    if ($data->format('N') >= 6) {
                        $fds++;
                    }
                }

                $intervalo_total = ($data_anterior->diff($data_ultima))->days;
                $uteis = $intervalo_total - $fds;

                $rendimento_total = $rendimentos[0]['valorRendimento'];

                if ($rendimento_total != 0 && $uteis > 0) {
                    $rendimento_mes = ($rendimento_total / $uteis) * 22;

                    $item = [
                        'ultimoRendimentoAM' => $rendimento_mes
                    ];
                    $item_where = [
                        'idContaInvest' => $id_conta_invest
                    ];

                    $model->atualizar(new InvestimentosEntity, $item, $item_where);
                }
            }
        }
    }
}