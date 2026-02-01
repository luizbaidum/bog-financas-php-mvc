<?php
namespace src\Controllers;

use MF\Controller\Controller;
use src\Models\Investimentos\InvestimentosDAO;
use src\Models\Investimentos\InvestimentosEntity;
use src\Models\Proprietarios\ProprietariosEntity;
use src\Models\Rendimentos\RendimentosEntity;
use src\System\MonthAndYear;

class ExtratoInvestimentosController extends Controller {
    public function index() 
    {
        $model_investimentos = new InvestimentosDAO();

        $this->view->settings = [
            'action'   => $this->index_route . '/extrato-investimentos',
            'redirect' => $this->index_route . '/extrato-investimentos',
            'title'    => 'Extrato Investimentos',
            'div'      => 'id-tabela-extrato'
        ];

        if (isset($_POST) && !empty($_POST)) {
            $this->view->data['extrato'] = $model_investimentos->consultarExtrato($_POST);
            $this->renderSimple('tabela_extrato');
        }

        $this->view->data['extrato'] = $model_investimentos->consultarExtrato([]);
        $this->view->data['lista_invest'] = $model_investimentos->selectAll(new InvestimentosEntity, [['status', '=', '"1"']], [], ['nomeBanco' => 'ASC']);
        $this->view->data['months'] = MonthAndYear::getMonths();
        $this->view->data['years'] = MonthAndYear::getYears();
        $this->view->data['lista_acao'] = $model_investimentos->selectAll(new RendimentosEntity, [], ['rendimentos', 'tipo'], []);
        $this->view->data['lista_proprietarios'] = $model_investimentos->selectAll(new ProprietariosEntity, [], [], []);

        $this->renderPage(
            conteudo: 'extrato_investimentos'
        );
    }
}