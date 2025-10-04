<?php
namespace src\Controllers;

use MF\Controller\Controller;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\Orcamento\OrcamentoDAO;

class IndicadoresController extends Controller {
    public function index() {
        $model_movimentos = new MovimentosDAO();

        $ano_filtro = $_GET['anoFiltro'] ?? '';
        $mes_filtro = $_GET['mesFiltro'] ?? '';

        $this->view->settings = [
            'action'     => '',
            'redirect'   => $this->index_route . '/indicadores-index',
            'title'      => 'Indicadores',
            'url_search' => $this->index_route . '/indicadores-index'
        ];

        $arr_totais_por_tipo = [];
        $arr_totais_por_tipo_orcado = [];

        if ($mes_filtro != '') {
            $indicadores = $model_movimentos->gerarRelatorioIndicadores($ano_filtro, $mes_filtro);
        } else {
            $indicadores = $model_movimentos->gerarRelatorioIndicadores();
        }

        foreach ($indicadores as $v) {
            isset($arr_totais_por_tipo[$v['tipo']]) ? $arr_totais_por_tipo[$v['tipo']] += $v['totalRealizado'] : $arr_totais_por_tipo[$v['tipo']] = $v['totalRealizado'];
            isset($arr_totais_por_tipo_orcado[$v['tipo']]) ? $arr_totais_por_tipo_orcado[$v['tipo']] += $v['totalOrcado'] : $arr_totais_por_tipo_orcado[$v['tipo']] = $v['totalOrcado'];
        }

        $this->view->data['indicadores'] = $indicadores;
        $this->view->data['arr_totais_por_tipo'] = $arr_totais_por_tipo;
        $this->view->data['arr_totais_por_tipo_orcado'] = $arr_totais_por_tipo_orcado;

        $this->renderPage(
            conteudo: 'indicadores_index'
        );
    }
}