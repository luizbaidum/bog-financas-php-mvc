<?php

namespace src\Controllers;

use MF\Controller\Controller;

class ManutencaoController extends Controller {
    public function index()
    {
        $this->view->settings = [
            'action' => $this->index_route . '/cadastrar-manutencao',
            'title'  => 'Cadastro de Manutenção'
        ];

        $this->renderPage(conteudo: 'cad_manutencao', base_interna: 'base_cruds');
    }

    public function cadastrarManutencao()
    {
        $this->view->settings = [
            'action' => $this->index_route . '/cadastrar-manutencao',
            'title'  => 'Cadastro de Manutenção'
        ];

        $this->renderPage(conteudo: 'cad_manutencao', base_interna: 'base_cruds');
    }
}