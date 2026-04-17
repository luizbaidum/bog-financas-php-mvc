<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use src\Models\Usuarios\UsuariosDAO;
use src\Models\Usuarios\UsuariosEntity;
use src\Services\AcessoService;

class PrimeiroAcessoController extends Controller {
    public function cadastrarPrimeiroAcesso()
    {
        $obj_usuario = new UsuariosEntity();
        $model_usuario = new UsuariosDAO();
        $service_acesso = new AcessoService();

        $model_usuario->iniciarTransacao();

        $obj_usuario->nome = $_POST['nome'];
        $obj_usuario->login = $_POST['login'];
        $obj_usuario->senha = md5($_POST['senha']);
        $obj_usuario->hash = $_POST['hash'];
        $senha_confirmar = md5($_POST['confirmaSenha']);

        if ($obj_usuario->hash == '') {
            $array_retorno = array(
                'result'   => false,
                'mensagem' => 'Hash de acesso é obrigatório.'
            );

            echo json_encode($array_retorno);
            exit;
        }

        $ret_validacao = $service_acesso->validacoesPreInsercao($model_usuario, $obj_usuario, $senha_confirmar);

        if ($ret_validacao['result'] == false) {
            $array_retorno = array(
                'result'   => false,
                'mensagem' => $ret_validacao['mensagem']
            );

            echo json_encode($array_retorno);
            exit;
        }

        $ret_valida_hash = $service_acesso->validarHashAcesso($model_usuario, $obj_usuario);

        if ($ret_valida_hash['result'] == false) {
            $array_retorno = array(
                'result'   => false,
                'mensagem' => $ret_valida_hash['mensagem']
            );

            echo json_encode($array_retorno);
            exit;
        }

        try {
            $ret = $model_usuario->cadastrarUsuarioSemFamilia($obj_usuario);

            if (empty($ret)) {
                throw new Exception($this->msg_retorno_falha);
            }

            $array_retorno = array(
                'result'   => $ret['result'],
                'mensagem' => $this->msg_retorno_sucesso
            );

            $model_usuario->finalizarTransacao();

            echo json_encode($array_retorno);
            exit;
        } catch (Exception $e) {
            $array_retorno = array(
                'result'   => false,
                'mensagem' => $e->getMessage()
            );

            $model_usuario->cancelarTransacao();

            echo json_encode($array_retorno);
            exit;
        }
    }

    public function primeiroAcesso()
    {
        $this->view->settings = [
            'action'     => $this->index_route . '/cad-primeiro-acesso',
            'redirect'   => $this->index_route,
            'title'      => 'Primeiro Acesso'
        ];

        $this->renderPage(
            conteudo: 'primeiro_acesso'
        );
    }

    public function primeiraFamilia()
    {
        $this->renderPrimeiraFamiliaPage();
        exit;
    }
}