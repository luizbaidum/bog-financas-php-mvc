<?php
namespace src\Controllers;

use MF\Controller\Controller;
use src\Models\Investimentos\InvestimentosDAO;
use src\Models\Rendimentos\RendimentosDAO;
use src\System\MonthAndYear;

class ProjecaoController extends Controller {
    public function index()
    {
        $this->view->settings = [
            'action'   => $this->index_route . '/gerar-projecao',
            'redirect' => $this->index_route . '/projecao',
            'title'    => 'Projeção',
            'div'      => 'id-tabela-projecao'
        ];

        $this->view->data['months'] = MonthAndYear::getMonths();
        $this->view->data['years'] = MonthAndYear::getYears();

        $this->renderPage(
            conteudo: 'projecao_index'
        );
    }

    public function gerarProjecao()
    {
        $model_rendimentos = new RendimentosDAO();

        $data_realizado = $model_rendimentos->buscarProjecao($_POST['origem']);

        if ((int) $_POST['destino'] > (int) date('Y')) {
            $data_posicao_inicial = (new InvestimentosDAO())->buscarSaldoAtualTotal();
        } else {
            $data_posicao_inicial = $model_rendimentos->buscarPosicaoInicial($_POST['destino']);
        }

        list($ret_projecao, $ret_realizado) = $this->calcularProjecao($data_realizado, $data_posicao_inicial);

        $this->view->data['projecao'] = json_encode($ret_projecao);
        $this->view->data['realizado'] = json_encode($ret_realizado);
        $this->view->data['ano_selecionado'] = $_POST['destino'];

        $this->renderSimple('tabela_projecao');
    }

    private function calcularProjecao($data_realizado, $data_posicao_inicial)
    {
        $ret_projecao = array();
        $ret_realizado = array();
        $total_rendimentos = array_sum($data_realizado);
        $media_mensal = count($data_realizado) > 0 ? $total_rendimentos / count($data_realizado) : 0;
        $meses = MonthAndYear::getMonthsInNumber();

        foreach ($meses as $mes) {
            if ($mes == 'Todos') {
                continue;
            }

            if (str_starts_with($mes, '0')) {
                $mes = str_replace('0', '', $mes);
            }

            if (! isset($data_realizado[$mes])) {
                $data_realizado[$mes] = 0;
            }

            $ret_realizado[$mes] = $data_realizado[$mes];

            if ($media_mensal < 0) {
                $valor = $media_mensal;
            } else {
                $valor = $data_realizado[$mes] < 0 ? $media_mensal - abs($data_realizado[$mes]) : ($media_mensal * 0.85);
                if ($valor > 2000) {
                    $valor = 2000;
                }
            }

            if ($mes == 1) {
                $ret_projecao[1] = $data_posicao_inicial + $valor;
                $ret_realizado[1] = $data_posicao_inicial + $ret_realizado[1];
            } else {
                $ret_projecao[$mes] = $ret_projecao[$mes - 1] + $valor;
                $ret_realizado[$mes] = $ret_realizado[$mes - 1] + $data_realizado[$mes];
            }
        }

        return [$ret_projecao, $ret_realizado];
    }
}