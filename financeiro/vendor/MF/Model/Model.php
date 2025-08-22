<?php 

namespace MF\Model;

use DateTime;
use Exception;
use src\Models\Investimentos\InvestimentosEntity;
use src\Models\Objetivos\ObjetivosEntity;

class Model {

	protected DateTime $current_time;
    public array $arr_afetados = array();
    public array $arr_nao_afetados = array();

	public function cadastrar(object $entity, $data)
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
			$result = $new_sql->executarQuery($query, $arr_values);

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

	public function atualizar(object $entity, array|object $values, array $where_condition)
	{
        $arr_values = array();

		try {
			$table = $entity::main_table;

			$query = "UPDATE $table SET ";

			foreach ($values as $k => $v) {
				$query .= "$k = ?, ";
				$arr_values[] = $v;
			}

			$query = rtrim($query, ', ');

			$where_column = array_key_first($where_condition);
			$where_value = $where_condition[$where_column];

			$query .= " WHERE $where_column = $where_value";

			$new_sql = new SQLActions();
			$result = $new_sql->executarQuery($query, $arr_values);

			if ($result) {
				return array(
					'result' => $result
				);
			} else {
				throw new Exception('O processo de atualização foi realizado, mas não existem mudanças para atualizar.');
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

	public function getAllColumns($entity_name)
    {
		$entity_ns = "src\Models\\" . $entity_name . "\\" . $entity_name . "Entity";
		$entity = new $entity_ns;

        $query = "SHOW COLUMNS FROM " . $entity::main_table;

        $new_sql = new SQLActions();

        $result = $new_sql->executarQuery($query);

        if (!empty($result)) {
            foreach ($result as $columns)
                $ret[] = $columns->Field;
        } else {
			throw new Exception("Erro ao buscar as infos da página. Entre em contato com o suporte.");
		}

        return $ret ?? [];
    }

	 //selectAll(action: "movimento", where_conditions: [['valor', '>', '15000']], group_conditions: ['tabela', 'coluna', 'tabela2', 'coluna2'], order_conditions: ['dataMovimento' => 'DESC']);
	public function selectAll(object $entity, array $where_conditions = [], array $group_conditions = [], array $order_conditions = [])
	{
		$where = 'WHERE 0 = 0';
		$group = '';
		$order = '';

		$table = $entity::main_table;

		if (!empty($where_conditions)) {
			$where = 'WHERE ';
			foreach ($where_conditions as $part)
				$where .= "$part[0] $part[1] $part[2]";
				//column, condition, value
				//Ex.: coluna > 1
		}

		if (!empty($group_conditions)) {
			$group = 'GROUP BY ';

			$total = count($group_conditions);

			for ($i = 0; $i < $total; $i += 2)
				$group .= $group_conditions[$i] . '.' . $group_conditions[$i + 1] . ', ';

			$group = rtrim($group, ', ');
		}

		if (!empty($order_conditions)) {
			$order = 'ORDER BY ';
			foreach ($order_conditions as $column => $cond)
				$order .= "$column $cond,";

			$order = rtrim($order, ',');
		}

		$query = "SELECT $table.* FROM $table $where $group $order";

		$new_sql = new SQLActions();
        return $new_sql->executarQuery($query, []);
	}

	public function getSaldoAtual(object $entity, $id_where)
    {
        $table = $entity::main_table;

        switch (true) {
            case $entity instanceof InvestimentosEntity:
                $column = 'idContaInvest';
                break;
            case $entity instanceof ObjetivosEntity:
                $column = 'idObj';
                break;
        }

        $arr_values = array();

        $query = "SELECT $table.saldoAtual FROM $table WHERE $table.$column = ?";

        $arr_values[] = $id_where;

        $new_sql = new SQLActions();
		$result = $new_sql->executarQuery($query, $arr_values);

        return $result[0]['saldoAtual'] ?? 0;
    }

    public function delete(object $entity, string $field, string | int $id)
    {
        $arr_values = array();
        $table = $table = $entity::main_table;

        $query = "DELETE FROM `$table` WHERE `$field` = ?"; 
        $arr_values[] = $id;

        $new_sql = new SQLActions();
		$result = $new_sql->executarQuery($query, $arr_values);

        return $result;
    }
}