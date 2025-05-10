<?php
namespace src\Controllers;

use MF\Controller\Controller;
use src\Models\Preferencias\PreferenciasDAO;
use src\Models\Preferencias\PreferenciasEntity;

class PreferenciasController extends Controller {
    public function index() {
        $model_preferencias = new PreferenciasDAO();
        $prefs = $model_preferencias->selectAll(new PreferenciasEntity());

        $this->view->settings = [
            'action'   => $this->index_route . '/salvar_preferencias',
            'redirect' => $this->index_route . '/preferencias',
            'action_2' => $this->index_route . '/nova_preferencia',
            'title'    => 'Preferências',
        ];

        $this->view->data['prefs'] = $prefs;
        $this->renderPage(
            main_route: $this->index_route . '/preferencias', 
            conteudo: 'preferencias'
        );
    }
}