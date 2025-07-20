<?php

namespace src\Models\Usuarios;

use MF\Model\Model;
use MF\Model\SQLActions;

class UsuariosDAO extends Model {

    public function idUsuarioPorLoginSenha($params)
    {
        $obj = new UsuariosEntity($params);

        $query_params = array();

        $query = 'SELECT usuarios.idUsuario, usuarios.idFamilia, usuarios.nivel FROM usuarios INNER JOIN familias ON usuarios.idFamilia = familias.idFamilia WHERE usuarios.login = ? AND usuarios.senha = ?';

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
        $query = 'SELECT usuarios.* FROM usuarios WHERE usuarios.idUsuario = ?';

        $query_params[] = $id;

        $new_sql = new SQLActions();

        $result = $new_sql->executarQuery($query, $query_params);

        return $result;
    }

    public function buscarIdFamiliaUsuarioSemSeguranca(string|int $id_usuario) : string
    {
        $query = 'SELECT usuarios.idFamilia FROM usuarios INNER JOIN familias ON usuarios.idFamilia = familias.idFamilia WHERE usuarios.idUsuario = ?';

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query, [$id_usuario], false);

        return (string) isset($result[0]) ? $result[0]['idFamilia'] : '';
    }

    public function consultarUsuarioPorLogin(string $login) : array
    {
        $query = 'SELECT usuarios.* FROM usuarios WHERE usuarios.login = ?';

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query, [$login], false);

        return $result;
    }

    public function cadastrarUsuarioSemFamilia($data)
    {
        $arr_values = array();

		try {
			$table = UsuariosEntity::main_table;
			$query = "INSERT INTO $table (";

			foreach ($data as $k => $v)
				$query .= "$k, ";

			$query = rtrim($query, ', ') . ', idFamilia) ';

			$query .= 'VALUES (';

			foreach ($data as $k => $v) {
				$query .= '?, ';
				$arr_values[] = $v;
			}

            $query .= '?, ';
            $arr_values[] = 0;

			$query = rtrim($query, ', ') . ')';

			$new_sql = new SQLActions();
			$result = $new_sql->executarQuery($query, $arr_values, false);

			if ($result) {
				return array(
					'result' => $result
				);
			} else {
				throw new \Exception('Erro ao cadastrar.');
			}
		} catch (\Exception $e) {
			errorHandler(
				1, 
				$e->getMessage(),
				$e->getFile(),
				$e->getLine()
			);

			return array(
				'result'   => false,
				'mensagem' => $e->getMessage()
			);
		}
    }
};