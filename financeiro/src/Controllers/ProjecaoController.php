<?php
namespace src\Controllers;

use MF\Controller\Controller;
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

        $data_projecao = $model_rendimentos->buscarProjecao($_POST['origem']);
        $data_realizado = $model_rendimentos->buscarProjecao($_POST['origem']);
        $data_posicao_inicial = $model_rendimentos->buscarPosicaoInicial($_POST['origem']);
        list($ret_projecao, $ret_realizado) = $this->calcularProjecao($data_projecao, $data_realizado, $data_posicao_inicial);

        echo '<pre>';
        print_r($ret_projecao);
        print_r($ret_realizado);
        echo '</pre>';

        $this->view->data['projecao'] = json_encode($ret_projecao);
        $this->view->data['realizado'] = json_encode($ret_realizado);
        $this->renderSimple('tabela_projecao');
    }

    private function calcularProjecao($data_projecao, $data_realizado, $data_posicao_inicial)
    {
        $ret_projecao = array();
        $ret_realizado = array();
        $total_rendimentos = array_sum($data_projecao);
        $media_mensal = count($data_projecao) > 0 ? $total_rendimentos / count($data_projecao) : 0;
        $meses = MonthAndYear::getMonthsInNumber();

        foreach ($meses as $mes) {
            if ($mes == 'Todos') {
                continue;
            }

            if (str_starts_with($mes, '0')) {
                $mes = str_replace('0', '', $mes);
            }

            if (! isset($data_projecao[$mes])) {
                $data_projecao[$mes] = 0;
            }

            if (! isset($data_realizado[$mes])) {
                $data_realizado[$mes] = 0;
            }

            $valor = $data_projecao[$mes] < 0 ? $media_mensal - abs($data_projecao[$mes] ?? 0) : ($media_mensal * 0.85);
            if ($valor > 2000) {
                $valor = 2000;
            }

            if ($mes == 1) {
                $ret_projecao[$mes] = $data_posicao_inicial + $valor;
            } else {
                $ret_projecao[$mes] = $ret_projecao[$mes - 1] + $valor;
            }

            if ($mes == 1) {
                $ret_realizado[$mes] = $data_posicao_inicial + $ret_realizado[1];
            } else {
                $ret_realizado[$mes] = $ret_realizado[$mes - 1] + $valor;
            }
        }

        return [$ret_projecao, $ret_realizado];
    }
}
