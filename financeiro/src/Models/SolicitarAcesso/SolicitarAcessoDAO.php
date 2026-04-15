<?php

namespace src\Models\SolicitarAcesso;

use Exception;
use MF\Model\Model;
use Throwable;

class SolicitarAcessoDAO extends Model {
    public function consultarSolicitarAcessoPorHash(string $hash) : array
    {
        $query = 'SELECT solicitar_acesso.* FROM solicitar_acesso WHERE solicitar_acesso.hash = ?';

        $result = $this->sql_actions->executarQuery($query, [$hash], false);

        return $result;
    }

    public function cadastrar(object $entity, $data)
	{
		$arr_values = array();

		try {
			$table = $entity::main_table;
			$query = "INSERT INTO $table (";

			foreach ($data as $k => $v) {
                $query .= "$k, ";
            }

			$query = rtrim($query, ', ') . ')';

			$query .= 'VALUES (';

			foreach ($data as $k => $v) {
				$query .= '?, ';
				$arr_values[] = $v;
			}

			$query = rtrim($query, ', ') . ')';

			$result = $this->sql_actions->executarQuery($query, $arr_values, false);

			if ($result) {
				return array(
					'result' => $result
				);
			} else {
				throw new Exception('Erro ao cadastrar.');
			}
		} catch (Throwable $e) {
			errorHandler(
				1,
				$e->getMessage(),
				$e->getFile(),
				$e->getLine()
			);

			return array(
                'result' => false
            );
		}
	}
}