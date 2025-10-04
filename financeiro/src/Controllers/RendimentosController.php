<?php
namespace src\Controllers;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use MF\Controller\Controller;
use MF\Helpers\NumbersHelper;
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

        if ($this->isSetPost()) {
            try {
                $tipo_preju = '1';
                $tipo_lucro = '2';

                $objeto = new RendimentosEntity();
                $objeto->idContaInvest = $_POST['idContaInvest'];

                $saldo_atual = $model_investimentos->getSaldoAtual(new InvestimentosEntity, $_POST['idContaInvest']);
                $vlr_atual = NumbersHelper::formatBRtoUS($_POST['valorAtual']);
                $vlr_rendeu = $vlr_atual - $saldo_atual;

                $objeto->tipo = $tipo_preju;
                if ($vlr_rendeu > 0) {
                    $objeto->tipo = $tipo_lucro;
                }

                $objeto->valorRendimento = $vlr_rendeu;
                $objeto->dataRendimento = $_POST['dataRendimento'];

                $ret_a = $model_rendimentos->cadastrar(new RendimentosEntity, $objeto);

                if (!isset($ret_a['result']) || empty($ret_a['result'])) {
                    throw new Exception($this->msg_retorno_falha);
                }

                $item = [
                    'saldoAtual'    => ($saldo_atual + $vlr_rendeu),
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

                (new InvestimentosController())->aplicarObjetivo(null, $vlr_rendeu, $_POST['idContaInvest']);

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