<?php 

namespace src\Services;

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

    public function gerarHashOut(): string
    {
        $ret = '';

        return $ret;
    }
}