<?php

namespace src\Models\Orcamento;

use MF\Model\Model;
use MF\Model\SQLActions;

class OrcamentoDAO extends Model {
    public function orcamentos($month = '')
    {
        $where = 'WHERE (MONTH(orcamentos.dataOrcamento) = MONTH(CURRENT_DATE()))';
        if (!empty($month)) {
            if ($month == 'Todos') {
                $where = 'WHERE orcamentos.dataOrcamento IS NOT NULL';
            } else {
                $where = "WHERE DATE_FORMAT(orcamentos.dataOrcamento, '%b') = '$month'";
            }
        }

        $query = "SELECT SUM(orcamentos.valor) AS totalOrcado, 
                            categorias.idCategoria, 
                            categorias.categoria, 
                            categorias.tipo, 
                            MONTH(orcamentos.dataOrcamento) AS mesOrcado,
                            GROUP_CONCAT(orcamentos.idOrcamento SEPARATOR ',') AS idOrcamento
                    FROM orcamentos 
                    INNER JOIN categorias ON categorias.idCategoria = orcamentos.idCategoria
                    $where
                    GROUP BY orcamentos.idCategoria
                    ORDER BY totalOrcado DESC";

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        $ret = [];
        foreach ($result as $val) {
            $ret[$val['idCategoria']] = $val;
        }

        return $ret;
    }
}