<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use src\Models\Usuarios\UsuariosDAO;
use src\Models\Usuarios\UsuariosEntity;

class PrimeiroAcessoController extends Controller {
    public function cadastrarPrimeiroAcesso()
    {
        $obj_usuario = new UsuariosEntity();
        $model_usuario = new UsuariosDAO();

        $obj_usuario->nome = $_POST['nome'];
        $obj_usuario->login = $_POST['login'];
        $obj_usuario->senha = $_POST['senha'];
        $senha_confirmar = $_POST['confirmaSenha'];

        $ret_validacao = $this->validacoesPreInsercao($model_usuario, $obj_usuario, $senha_confirmar);

        if ($ret_validacao['result'] == false) {
            $array_retorno = array(
                'result'   => false,
                'mensagem' => $ret_validacao['mensagem']
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

            echo json_encode($array_retorno);
        } catch (Exception $e) {
            $array_retorno = array(
                'result'   => false,
                'mensagem' => $e->getMessage()
            );

            echo json_encode($array_retorno);
        }
    }

    private function validacoesPreInsercao(object $model, object $obj_usuario, string|int $senha_confirmar) : array
    {
        $status = true;
        $mensagem = '';

        if ($obj_usuario->senha != $senha_confirmar) {
            $status = false;
            $mensagem = 'As senhas não conferem.';
        }

        if (!empty($model->consultarUsuarioPorLogin($obj_usuario->login))) {
            $status = false;
            $mensagem = 'Por favor, escolher outro login.';
        }

        return ['result' => $status, 'mensagem' => $mensagem];
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

    public function primeiraFamilia($id_usuario)
    {
        $this->view->settings = [
            'action'     => $this->index_route . '/cad-primeira-familia',
            'redirect'   => $this->index_route,
            'title'      => 'Família'
        ];

        $this->renderPage(
            conteudo: 'primeira_familia'
        );
    }
}