<?php
namespace src\Controllers;

use MF\Controller\Controller;
use src\Models\Familia\FamiliaDAO;

class FamiliaController extends Controller {

    public function index()
    {
        $model_familia = new FamiliaDAO();

        $this->view->settings = [
            'action'   => $this->index_route . '/cadastrar-familia',
            'redirect' => $this->index_route . '/familia',
            'title'    => 'Família'
        ];

        $this->renderPage(
            main_route: $this->index_route . '/familia', 
            conteudo: 'familia_index',
            base_interna: 'base_cruds'
        );
    }
}