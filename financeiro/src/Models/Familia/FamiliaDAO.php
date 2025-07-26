<?php

namespace src\Models\Familia;

use Exception;
use MF\Model\Model;
use MF\Model\SQLActions;

class FamiliaDAO extends Model {
    public function consultarNomeFamilia($id_familia)
    {
        $result = '';
        if (!empty($id_familia)) {
            $query = 'SELECT familias.nomeFamilia FROM familias WHERE familias.idFamilia = ?';

            $new_sql = new SQLActions();
		    $result = $new_sql->executarQuery($query, [$id_familia])[0]['nomeFamilia'];
        }

        return $result;
    }

    public function cadastrarFamilia(object $entity, $data)
	{
		$arr_values = array();

		try {
			$table = $entity::main_table;
			$query = "INSERT INTO $table (";

			foreach ($data as $k => $v)
				$query .= "$k, ";

			$query = rtrim($query, ', ') . ')';

			$query .= 'VALUES (';

			foreach ($data as $k => $v) {
				$query .= '?, ';
				$arr_values[] = $v;
			}

			$query = rtrim($query, ', ') . ')';

			$new_sql = new SQLActions();
			$result = $new_sql->executarQuery($query, $arr_values, false);

			if ($result) {
				return array(
					'result' => $result
				);
			} else {
				throw new Exception('Erro ao cadastrar.');
			}
		} catch (Exception $e) {
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
}