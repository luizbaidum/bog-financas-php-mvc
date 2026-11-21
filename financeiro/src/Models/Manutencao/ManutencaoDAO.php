<?php

namespace src\Models\Manutencao;

use MF\Model\Model;

class ManutencaoDAO extends Model {
    public function getManutencoesByYear($year) {
        $query = "
            SELECT manutencoes.*,
            SUM(manutencoes_pecas.valorPeca) AS total_pecas,
            proprietarios.proprietario
            FROM manutencoes
            INNER JOIN manutencoes_pecas ON manutencoes_pecas.idManut = manutencoes.idManut
            INNER JOIN proprietarios ON proprietarios.idProprietario = manutencoes.idProprietario
            WHERE YEAR(manutencoes.dataManut) = ?
            GROUP BY manutencoes.idManut
            ORDER BY manutencoes.dataManut ASC;
        ";

        $result = $this->sql_actions->executarQuery($query, [$year]);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }
}