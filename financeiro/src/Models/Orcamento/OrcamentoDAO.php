<?php

namespace src\Models\Orcamento;

use MF\Model\Model;
use MF\Model\SQLActions;

class OrcamentoDAO extends Model {
    public function orcamentos($year = '', $month = '')
    {
        $where = 'WHERE (MONTH(orcamentos.dataOrcamento) = MONTH(CURRENT_DATE()))';
        if ($month != '') {
            if ($month == 'Todos') {
                $where = 'WHERE orcamentos.dataOrcamento IS NOT NULL';
            } else {
                $where = "WHERE DATE_FORMAT(orcamentos.dataOrcamento, '%Y%b') = '$year$month'";
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

    public function buscarMediasDespesas($year, $month)
    {
        $where = "DATE_FORMAT(movimentos.dataMovimento, '%Y') = '$year'";
        $media = "(SUM(movimentos.valor) / MONTH(NOW())) AS valorOrcamento";
        if (!is_null($month) && $month != '') {
            $where = "DATE_FORMAT(movimentos.dataMovimento, '%Y-%m') = '$year-$month'";
            $media = "SUM(movimentos.valor) AS valorOrcamento";
        }

        $query = "SELECT movimentos.idCategoria,
                    $media,
                    categorias.categoria,
                    categorias.sinal,
                    movimentos.idProprietario,
                    proprietarios.proprietario
                    FROM movimentos
                    INNER JOIN categorias ON movimentos.idCategoria = categorias.idCategoria
                    LEFT JOIN proprietarios ON proprietarios.idProprietario = movimentos.idProprietario
                    WHERE $where
                    GROUP BY movimentos.idProprietario, movimentos.idCategoria";

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        return $result;
    }

    public function orcamentosPorProprietario($year = '', $month = '')
    {
        $where = 'WHERE (MONTH(orcamentos.dataOrcamento) = MONTH(CURRENT_DATE()))';
        if ($month != '') {
            if ($month == 'Todos') {
                $where = 'WHERE orcamentos.dataOrcamento IS NOT NULL';
            } else {
                $where = "WHERE DATE_FORMAT(orcamentos.dataOrcamento, '%Y%b') = '$year$month'";
            }
        }

        $query = "SELECT SUM(orcamentos.valor) AS totalOrcado, 
                            orcamentos.idProprietario,
                            categorias.idCategoria, 
                            categorias.categoria, 
                            categorias.tipo, 
                            MONTH(orcamentos.dataOrcamento) AS mesOrcado,
                            GROUP_CONCAT(orcamentos.idOrcamento SEPARATOR ',') AS idOrcamento,
                            proprietarios.proprietario
                    FROM orcamentos 
                    INNER JOIN categorias ON categorias.idCategoria = orcamentos.idCategoria
                    LEFT JOIN proprietarios ON proprietarios.idProprietario = orcamentos.idProprietario
                    $where
                    GROUP BY orcamentos.idProprietario, orcamentos.idCategoria
                    ORDER BY totalOrcado DESC";

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        $ret = [];
        foreach ($result as $val) {
            $ret[] = $val;
        }

        return $ret;
    }
}