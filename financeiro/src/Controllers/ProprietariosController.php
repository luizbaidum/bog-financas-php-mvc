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
            'extra_url' => $this->index_route . '/edit-status-proprietario?id='
        ];

        $this->view->data['todos_prop'] = (new ProprietariosDAO())->selectAll(new ProprietariosEntity, [], [], ['proprietario' => 'ASC']);

        $this->renderPage(conteudo: 'proprietarios', base_interna: 'base_cruds', extra: 'listagem_proprietarios');
    }

    public function cadastrarProprietarios()
    {
        if ($this->isSetPost()) {
            $model_proprietarios = new ProprietariosDAO();
            $model_proprietarios->iniciarTransacao();
            try {

                $ret = $model_proprietarios->cadastrar(new ProprietariosEntity, $_POST);

                if ($ret['result']) {
                    $array_retorno = array(
                        'result'   => $ret['result'],
                        'mensagem' => $this->msg_retorno_sucesso
                    );

                    $model_proprietarios->finalizarTransacao();

                    echo json_encode($array_retorno);
                    exit;
                } else {
                    throw new Exception($this->msg_retorno_falha);
                }
            } catch (Exception $e) {
                $array_retorno = array(
                    'result'   => false,
                    'mensagem' => $e->getMessage(),
                );

                $model_proprietarios->cancelarTransacao();

                echo json_encode($array_retorno);
                exit;
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
        $this->view->data['lista_categorias'] = $model_movimentos->selectAll(new CategoriasEntity, [['status', '=', '"1"']], [], ['tipo' => 'ASC', 'categoria' => 'ASC']);

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

     public function editarStatus()
    {
        $id_proprietario = $_GET['id'];
        $status = $_GET['status'];

        if (! empty($id_proprietario) && $status != '') {

            $model_proprietarios = new ProprietariosDAO();
            $model_proprietarios->iniciarTransacao();

            try {
                $ret = $model_proprietarios->atualizar(new ProprietariosEntity,
                            ['status' => $status],
                            ['idProprietario' =>  $id_proprietario]
                        );

                if ($ret['result']) {
                    $array_retorno = array(
                        'result'   => $ret['result'],
                        'mensagem' => 'idProprietario ' . $id_proprietario . ' alterado com sucesso.'
                    );

                    $model_proprietarios->finalizarTransacao();

                    echo json_encode($array_retorno);
                } else {
                    throw new Exception('Erro ao alterar idProprietario ' . $id_proprietario . '.');
                }
            } catch (Exception $e) {
                $array_retorno = array(
                    'result'   => false,
                    'mensagem' => $e->getMessage(),
                );

                $model_proprietarios->cancelarTransacao();

                echo json_encode($array_retorno);
            }
        }
    }
}