<?php

namespace src\Models\Movimentos;

use MF\Model\Model;
use MF\Model\SQLActions;

class MovimentosDAO extends Model {
    public function indexTable($pesquisa, $month = '')
    {
        $where = 'WHERE (MONTH(movimentos.dataMovimento) = MONTH(CURRENT_DATE()))';

        if ($month != '' && $month == 'Todos') {
            $where = 'WHERE movimentos.dataMovimento IS NOT NULL';
        } elseif ($month != '' && $month != 'Todos') {
            $where = "WHERE DATE_FORMAT(movimentos.dataMovimento, '%b') = '$month'";
        }

        if ($pesquisa != '') {
            $where .= ' AND (categoria_movimentos.categoria LIKE "%' . $pesquisa . '%" OR movimentos.nomeMovimento LIKE "%' . $pesquisa . '%")';
        }

        $query = "SELECT movimentos.*, categoria_movimentos.categoria, categoria_movimentos.tipo FROM movimentos INNER JOIN categoria_movimentos ON categoria_movimentos.idCategoria = movimentos.idCategoria $where ORDER BY dataMovimento DESC";

        $new_sql = new SQLActions();
		$result = $new_sql->executarQuery($query);

        return $result;
    }

    public function getSaldoPassado(int $times = 2)
    {
        $mes_atual = date('m');
        $where = 'MONTH(movimentos.dataMovimento) BETWEEN "'. ($mes_atual - $times).'" AND "'. ($mes_atual - 1).'"';

        $query = "SELECT SUM(movimentos.valor) AS valor, MONTH(movimentos.dataMovimento) AS MES
                    FROM movimentos 
                    WHERE $where
                    GROUP BY MES";

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        return $result;
    }

    public function indicadores($month = '')
    {
        $where = 'WHERE (MONTH(movimentos.dataMovimento) = MONTH(CURRENT_DATE()))';
        if (!empty($month)) {
            if ($month == 'Todos') {
                $where = 'WHERE movimentos.dataMovimento IS NOT NULL';
            } else {
                $where = "WHERE DATE_FORMAT(movimentos.dataMovimento, '%b') = '$month'";
            }
        }

        $query = "SELECT SUM(movimentos.valor) AS total, categoria_movimentos.idCategoria, categoria_movimentos.categoria, categoria_movimentos.tipo
                    FROM movimentos 
                    INNER JOIN categoria_movimentos ON categoria_movimentos.idCategoria = movimentos.idCategoria
                    $where
                    GROUP BY movimentos.idCategoria
                    ORDER BY total DESC";

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        $ret = [];
        foreach ($result as $val) {
            $ret[$val['idCategoria']] = $val;
        }

        return $ret;
    }
}