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

        $indicadores = $model_movimentos->indicadoresRelatorio();
        $orcamentos = $model_orcamento->orcamentosIndicadores();
        if ($mes_filtro != '') {
            $indicadores = $model_movimentos->indicadoresRelatorio($ano_filtro, $mes_filtro);
            $orcamentos = $model_orcamento->orcamentosIndicadores($ano_filtro, $mes_filtro);
        }

        $this->view->data['indicadores'] = $indicadores;
        $this->view->data['orcamentos'] = $orcamentos;

        $this->renderPage(
            conteudo: 'indicadores_index'
        );
    }
}