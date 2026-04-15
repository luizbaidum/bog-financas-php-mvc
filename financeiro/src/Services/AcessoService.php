<?php 

namespace src\Services;

use src\Models\SolicitarAcesso\SolicitarAcessoDAO;

class AcessoService {
    public function validacoesPreInsercao(object $model, object $obj_usuario, string|int|null $senha_confirmar): array
    {
        $status = true;
        $mensagem = '';

        if ($senha_confirmar !== null && $obj_usuario->senha != $senha_confirmar) {
            $status = false;
            $mensagem = 'As senhas não conferem.';
        }

        if (! empty($model->consultarUsuarioPorLogin($obj_usuario->login))) {
            $status = false;
            $mensagem = 'Por favor, escolher outro login.';
        }

        return ['result' => $status, 'mensagem' => $mensagem];
    }

    public function validarHashAcesso(object $model_usuario, object $obj_usuario): array
    {
        $status = true;
        $mensagem = '';

        $model_primeiro_acesso = new SolicitarAcessoDAO();

        $ret = $model_usuario->consultarSolicitarAcessoPorHash($obj_usuario->hash);

        if (! empty($ret)) {
            $status   = false;
            $mensagem = 'Hash de acesso inválido. Por favor, verificar o código informado.';
        }

        $ret = $model_primeiro_acesso->consultarSolicitarAcessoPorHash($obj_usuario->hash);

        $nome = strtolower($ret[0]['nome']);
        $login = strtolower($ret[0]['login']);
        $senha = $ret[0]['senha'];

        if ($nome != strtolower($obj_usuario->nome) || $login != strtolower($obj_usuario->login) || $senha != $obj_usuario->senha) {
            $status   = false;
            $mensagem = 'Hash de acesso inválido. Por favor, verificar o código informado.';
        }

        return ['result' => $status, 'mensagem' => $mensagem];
    }
}