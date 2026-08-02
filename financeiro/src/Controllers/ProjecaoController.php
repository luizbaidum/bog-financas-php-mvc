<?php
namespace src\Controllers;

use MF\Controller\Controller;
use src\System\MonthAndYear;

class ProjecaoController extends Controller {
    public function index()
    {
        $this->view->settings = [
            'action'   => $this->index_route . '/projecao',
            'redirect' => $this->index_route . '/projecao',
            'title'    => 'Projeção',
            'div'      => 'id-tabela-projecao'
        ];

        if (isset($_POST) && !empty($_POST)) {
            // Placeholder: processar dados de projeção quando implementado
            $this->view->data['resultado'] = [];
            $this->renderSimple('tabela_projecao');
        }

        $this->view->data['months'] = MonthAndYear::getMonths();
        $this->view->data['years'] = MonthAndYear::getYears();

        $this->renderPage(
            conteudo: 'projecao_index'
        );
    }
}
