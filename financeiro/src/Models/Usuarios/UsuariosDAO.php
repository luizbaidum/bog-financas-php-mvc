<?php

namespace src\Models\Usuarios;

use MF\Model\Model;
use MF\Model\SQLActions;

class UsuariosDAO extends Model {

    public function idUsuarioPorLoginSenha($params)
    {
        $obj = new UsuariosEntity($params);

        $query_params = array();

        $query = 'SELECT idUsuario, idFamilia, nivel FROM usuarios WHERE usuarios.login = ? AND usuarios.senha = ?';

        $query_params[] = $obj->login;
        $query_params[] = $obj->senha;

		$new_sql = new SQLActions();
		$dados = $new_sql->executarQuery($query, $query_params, false);

        if (count($dados) > 0) {
            return $dados;
        }

        return false;
    }

    public function detalhar($id)
    {
        $query = 'SELECT usuarios.* FROM usuarios WHERE usuarios.id = ?';

        $query_params[] = $id;

        $new_sql = new SQLActions();

        $result = $new_sql->executarQuery($query, $query_params);

        return $result;
    }
};