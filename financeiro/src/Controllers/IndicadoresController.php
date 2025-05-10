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

        $indicadores = $model_movimentos->indicadores(); 
        $orcamentos = $model_orcamento->orcamentos();

        if ($mes_filtro != '') {
            $indicadores = $model_movimentos->indicadores($ano_filtro, $mes_filtro); 
            $orcamentos = $model_orcamento->orcamentos($ano_filtro, $mes_filtro);
        }

        $receitas = 0;
        $aplicado = 0;
        foreach ($indicadores as $value) {
            if ($value['tipo'] == 'R' && $value['idCategoria'] != 10)
                $receitas += $value['total'];
            
            if ($value['idCategoria'] == 12 || $value['idCategoria'] == 10)
                $aplicado += $value['total'];
        }

        $this->view->data['indicadores'] = $indicadores;
        $this->view->data['orcamentos'] = $orcamentos;
        $this->view->data['receitas'] = $receitas;
        $this->view->data['aplicado'] = $aplicado;

        $this->renderPage(
            main_route: $this->index_route . '/indicadores_index', 
            conteudo: 'indicadores_index'
        );
    }
}