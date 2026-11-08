<?php

namespace src\Controllers;

use MF\Controller\Controller;
use src\Models\Manutencao\ManutencaoDAO;
use src\Models\Proprietarios\ProprietariosDAO;
use src\Models\Proprietarios\ProprietariosEntity;

class ManutencaoController extends Controller {
    public function index()
    {
        $this->view->settings = [
            'action' => $this->index_route . '/cadastrar-manutencao',
            'title'  => 'Cadastro de Manutenção'
        ];

        $this->view->data['lista_proprietarios'] = (new ProprietariosDAO())->selectAll(new ProprietariosEntity(), [['status', '=', '"1"']], [], []);

        $this->renderPage(conteudo: 'cad_manutencao', base_interna: 'base_cruds');
    }

    public function cadastrarManutencao()
    {
        if ($this->isSetPost()) {
            $model_manutencao = new ManutencaoDAO();
            $model_manutencao->iniciarTransacao();
        }
    }

    public function relatorioManutencao()
    {

    }
}