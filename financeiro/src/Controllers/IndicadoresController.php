<?php
namespace src\Controllers;

use MF\Controller\Controller;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\Orcamento\OrcamentoDAO;

class IndicadoresController extends Controller {
    public function index() {
        $model_movimentos = new MovimentosDAO();
        $model_orcamento = new OrcamentoDAO();

        $ano_filtro = $_GET['anoFiltro'] ?? '';
        $mes_filtro = $_GET['mesFiltro'] ?? '';

        $this->view->settings = [
            'action'     => '',
            'redirect'   => $this->index_route . '/indicadores_index',
            'title'      => 'Indicadores',
            'url_search' => $this->index_route . '/indicadores_index'
        ];

        $arr_receitas = [];
        $arr_despesas = [];
        $arr_aplica = [];
        $arr_resgata = [];

        $indicadores_original = $model_movimentos->indicadoresRelatorio();
        $orcamentos = $model_orcamento->orcamentosIndicadores();
        if ($mes_filtro != '') {
            $indicadores_original = $model_movimentos->indicadoresRelatorio($ano_filtro, $mes_filtro);
            $orcamentos = $model_orcamento->orcamentosIndicadores($ano_filtro, $mes_filtro);
        }

        foreach ($indicadores_original as $x => $v) {
            switch ($v['tipo']) {
                case 'R':
                    $arr_receitas[$x] = $v;
                break;
                case 'D':
                    $arr_despesas[$x] = $v;
                break;
                case 'A':
                    $arr_aplica[$x] = $v;
                break;
                case 'RA':
                    $arr_resgata[$x] = $v;
                break;
            }
        }

        $indicadores = ($arr_receitas + $arr_despesas + $arr_aplica + $arr_resgata);

        $this->view->data['indicadores'] = $indicadores;
        $this->view->data['orcamentos'] = $orcamentos;

        $this->renderPage(
            conteudo: 'indicadores_index'
        );
    }
}