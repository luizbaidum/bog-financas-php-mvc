<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
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

        $this->renderPage(main_route: $this->index_route . '/proprietarios', conteudo: 'proprietarios', base_interna: 'base_cruds');
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
}