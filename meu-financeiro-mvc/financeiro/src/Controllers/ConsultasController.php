<?php

namespace src\Controllers;

use MF\Controller\Controller;
use MF\Model\Model;
use src\Models\Categorias\CategoriasEntity;
use src\Models\Investimentos\InvestimentosDAO;
use src\Models\Investimentos\InvestimentosEntity;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\Objetivos\ObjetivosDAO;
use src\Models\Objetivos\ObjetivosEntity;
use src\Models\Orcamento\OrcamentoDAO;

class ConsultasController extends Controller {
    public function indicadores()
    {
        $model_movimentos = new MovimentosDAO();
        $model_orcamento = new OrcamentoDAO();

        $this->view->settings = [
            'action'   => '',
            'redirect' => $this->index_route . '/indicadores_index',
            'title'    => 'Indicadores',
        ];

        $indicadores = $model_movimentos->indicadores(); 
        $orcamentos = $model_orcamento->orcamentos();

        // if (isset($_POST['mesFiltro']) && !empty($_POST['mesFiltro'])) {
        //     $indicadores = $crud->indicadores($_POST['mesFiltro']); 
        //     $orcamentos = $crud->orcamentos($_POST['mesFiltro']);
        // } else {
        //     $indicadores = $crud->indicadores(); 
        //     $orcamentos = $crud->orcamentos();
        // }

        $receitas = 0;
        $aplicado = 0;
        foreach ($indicadores as $value) {
            if ($value['tipo'] == 'R' && $value['idCategoria'] != 10) //'Devolução de Aplicação'
                $receitas += $value['total'];
            
            if ($value['idCategoria'] == 12 || $value['idCategoria'] == 10) //'Aplicação' //'Devolução de Aplicação'
                $aplicado += $value['total'];
        }

        $this->view->data['indicadores'] = $indicadores;
        $this->view->data['orcamentos'] = $orcamentos;
        $this->view->data['receitas'] = $receitas;
        $this->view->data['aplicado'] = $aplicado;

        $this->renderPage(main_route: $this->index_route . '/indicadores_index', conteudo: 'indicadores_index');
    }

    public function investimentos()
    {
        $model_investimentos = new InvestimentosDAO();

        $contas = $model_investimentos->selectAll(new InvestimentosEntity, [], [], []);
        $invests = $model_investimentos->selectAll(new InvestimentosEntity, [], [], ['nomeBanco' => 'ASC']);
        $objs = $model_investimentos->selectAll(new ObjetivosEntity, [], [], ['saldoAtual' => 'DESC']);

        $this->view->settings = [
            'action'   => '',
            'redirect' => $this->index_route . '/contas_investimentos_index',
            'title'    => 'Indicadores',
        ];

        $this->view->data['contas'] = $contas;
        $this->view->data['invests'] = $invests;
        $this->view->data['objs'] = $objs;

        $this->renderPage(main_route: $this->index_route . '/contas_investimentos_index', conteudo: 'contas_investimentos_index');
    }

    public function orcamento()
    {
        $model_orcamento = new OrcamentoDAO();

        $orcamentos = $model_orcamento->orcamentos($_POST['mesFiltro'] ?? '');

        $this->view->settings = [
            'action'   => '',
            'redirect' => $this->index_route . '/orcamento_index',
            'title'    => 'Orçamento',
        ];

        $this->view->data['orcamentos'] = $orcamentos;

        $this->renderPage(main_route: $this->index_route . '/orcamento_index', conteudo: 'orcamento_index');
    }
}
?>