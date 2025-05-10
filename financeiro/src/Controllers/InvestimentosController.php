<?php
namespace src\Controllers;

use MF\Controller\Controller;
use MF\Model\Model;
use src\Models\Categorias\CategoriasEntity;
use src\Models\Investimentos\InvestimentosDAO;
use src\Models\Investimentos\InvestimentosEntity;
use src\Models\Objetivos\ObjetivosDAO;
use src\Models\Objetivos\ObjetivosEntity;

class InvestimentosController extends Controller {
    public function index() {
        $model_investimentos = new InvestimentosDAO();

        $contas = $model_investimentos->selectAll(new InvestimentosEntity, [], [], []);
        $invests = $model_investimentos->selectAll(new InvestimentosEntity, [], [], ['nomeBanco' => 'ASC']);
        $objs = $model_investimentos->selectAll(new ObjetivosEntity, [], [], ['saldoAtual' => 'DESC']);

        $this->view->settings = [
            'action'   => $this->index_route . '/cadastrar_rendimento',
            'redirect' => $this->index_route . '/contas_investimentos_index',
            'title'    => 'Indicadores',
            'url_obj'  => $this->index_route . '/consultar_objetivos?idContaInvest=',
        ];

        $this->view->data['contas'] = $contas;
        $this->view->data['invests'] = $invests;
        $this->view->data['objs'] = $objs;

        $this->renderPage(
            main_route: $this->index_route . '/contas_investimentos_index', 
            conteudo: 'contas_investimentos_index'
        );
    }

    public function definirMovimentoDoInvestimento()
    {
        $model = new Model();

        $this->view->data['tipo_movimento'] = $_GET['action'];
        $this->view->data['invests'] = $model->selectAll(new InvestimentosEntity, [], [], ['nomeBanco' => 'ASC', 'tituloInvest' => 'ASC']);
        $this->view->data['options_list'] = json_encode($model->selectAll(new ObjetivosEntity, [], [], []));
        $this->view->data['categorias'] = $model->selectAll(new CategoriasEntity, [], [], ['tipo' => 'ASC', 'categoria' => 'ASC']);
        $this->view->data['url_buscar_mov_mensal'] = $this->index_route . '/buscaMovMensal?buscar=';
        $this->view->data['div_buscar_mov_mensal'] = 'id-content-return';

        $this->renderSimple('definido_movimento_investimento');
    }

    public function consultarObjetivos()
    {
        $id_invest = $_GET['idContaInvest'];

        $model_objetivos = new ObjetivosDAO();

        $lista_objetivos = $model_objetivos->consultarObjetivosPorInvestimento($id_invest);

        $this->view->settings = [
            'action'   => $this->index_route . '/editar_objetivo',
            'redirect' => $this->index_route . '/extrato_investimentos',
        ];

        $this->view->data['lista_objetivos'] = $lista_objetivos;

        $this->renderInModal(titulo: 'Objetivos do investimento conta', conteudo: 'objetivos');
    }
}