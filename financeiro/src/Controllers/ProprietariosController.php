<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use src\Models\Categorias\CategoriasEntity;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\Proprietarios\ProprietariosEntity;
use src\Models\Proprietarios\ProprietariosDAO;

class ProprietariosController extends Controller {
    public function proprietarios()
    {
        $this->view->settings = [
            'action'   => $this->index_route . '/cad_proprietarios',
            'redirect' => $this->index_route . '/proprietarios',
            'title'    => 'Cadastro de Proprietario',
        ];

        $this->renderPage(conteudo: 'proprietarios', base_interna: 'base_cruds');
    }

    public function cadastrarProprietarios()
    {
        if ($this->isSetPost()) {
            try {

                $ret = (new ProprietariosDAO())->cadastrar(new ProprietariosEntity, $_POST);

                if ($ret['result']) {
                    $array_retorno = array(
                        'result'   => $ret['result'],
                        'mensagem' => $this->msg_retorno_sucesso
                    );

                    echo json_encode($array_retorno);
                } else {
                    throw new Exception($this->msg_retorno_falha);
                }
            } catch (Exception $e) {
                $array_retorno = array(
                    'result'   => false,
                    'mensagem' => $e->getMessage(),
                );

                echo json_encode($array_retorno);
            }
        }
    }

    public function extratoProprietarios()
    {
        $model_movimentos = new MovimentosDAO();

        $this->view->settings = [
            'action'   => $this->index_route . '/processar-extrato-proprietarios',
            'title'    => 'Extrato por Proprietário',
            'div'      => 'content-extrato-proprietarios'
        ];

        $this->view->data['lista_proprietarios'] = $model_movimentos->selectAll(new ProprietariosEntity, [], [], []);
        $this->view->data['lista_categorias'] = $model_movimentos->selectAll(new CategoriasEntity, [['status', '=', '1']], [], ['tipo' => 'ASC', 'categoria' => 'ASC']);

        $this->renderPage(
            conteudo: 'extrato_proprietarios_form'
        );
    }

    public function processarExtratoProprietarios()
    {
        $model_movimentos = new MovimentosDAO();

        $filtros = [
            'data_inicio'    => $_POST['data_inicio'],
            'data_fim'       => $_POST['data_fim'],
            'idCategoria'    => $_POST['idCategoria'],
            'idProprietario' => $_POST['idProprietario'],
        ];

        $this->view->data['dados'] = $model_movimentos->extratoProprietarios($filtros);

        $this->renderSimple('extrato_proprietarios');
    }
}