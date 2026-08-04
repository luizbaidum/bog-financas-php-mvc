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
        $ret = $this->calcularProjecao($data_projecao);

        echo '<pre>';
        print_r($ret);
        print_r($data_realizado);
        echo '</pre>';

        $this->view->data['resultado'] = $ret;
        $this->renderSimple('tabela_projecao');
    }

    private function calcularProjecao($data_projecao)
    {
        $ret = array();
        $total_rendimentos = array_sum($data_projecao);
        $media_mensal = count($data_projecao) > 0 ? $total_rendimentos / count($data_projecao) : 0;
        $meses = MonthAndYear::getMonthsInNumber();

        foreach ($meses as $mes) {
            if ($mes == 'Todos') {
                continue;
            }

            $mes = str_replace('0', '', $mes);
            $ret[$mes] = ($data_projecao[$mes] ?? 0) < 0 ? $media_mensal - abs($data_projecao[$mes] ?? 0) : ($media_mensal * 0.85);
            if ($ret[$mes] > 2000) {
                $ret[$mes] = 2000;
            }
        }

        return $ret;
    }
}
