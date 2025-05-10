<?php
namespace src\Controllers;

use MF\Controller\Controller;
use src\Models\MovimentosMensais\MovimentosMensaisDAO;

class MovimentosMensaisController extends Controller {
    public function index() {
        $model_movimentos_mensais = new MovimentosMensaisDAO();

        $this->view->settings = [
            'action'   => $this->index_route . '/cad_mov_mensal',
            'redirect' => $this->index_route . '/movimentos_mensais_index',
            'title'    => 'Movimentos Mensais',
        ];

        $this->view->data['arr_mensais'] = $model_movimentos_mensais->getMensais();
        
        $this->renderPage(
            main_route: $this->index_route . '/movimentos_mensais_index', 
            conteudo: 'movimentos_mensais_index', 
            base_interna: 'base_cruds'
        );
    }

    public function buscarMovMensal() {
        $buscar = $_GET['buscar'];

        $model_movimentos_mensais = new MovimentosMensaisDAO();

        $ret = $model_movimentos_mensais->buscar($buscar);
        $this->view->data['ret'] = $ret;

        $this->renderSimple('ret_mov_mensais');
    }
}