<?php
namespace src\Controllers;

use MF\Controller\Controller;
use MF\Model\Model;
use src\Models\Proprietarios\ProprietariosEntity;

class MetasMensaisController extends Controller {
    public function lancarMetasMensais() 
    {
        $model = new Model();

        $this->view->settings = [
            'action'   => $this->index_route . '/cad-metas-mensais',
            'redirect' => $this->index_route . '/metas-mensais',
            'title'    => 'Metas Mensais',
        ];

        $this->view->data['lista_proprietarios'] = $model->selectAll(new ProprietariosEntity, [], [], []);

        $this->renderPage(
            conteudo: 'metas_mensais',
            base_interna: 'base_cruds'
        );
    }

    public function metasMensaisIndex() 
    {
        $model = new Model();

        $this->view->data['lista_proprietarios'] = $model->selectAll(new ProprietariosEntity, [], [], []);

        $this->renderPage(
            conteudo: 'metas_mensais_index'
        );
    }
}