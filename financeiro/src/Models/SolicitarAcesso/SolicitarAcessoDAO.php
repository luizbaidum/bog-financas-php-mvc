<?php

namespace src\Models\SolicitarAcesso;

use MF\Model\Model;

class SolicitarAcessoDAO extends Model {
    public function cadastrarSolicitarAcesso($data)
    {
        $arr_values = array();

		try {
			$table = SolicitarAcessoEntity::main_table;
			$query = "INSERT INTO $table (";

			foreach ($data as $k => $v)
				$query .= "$k, ";

			$query = rtrim($query, ', ') . ') ';

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
}