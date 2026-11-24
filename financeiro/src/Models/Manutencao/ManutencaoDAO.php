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

    public function detalharManutencao($id_manut)
    {

        $query = '
            SELECT manutencoes.*,
            proprietarios.proprietario,
            manutencoes_pecas.*,
            pecas.nomePeca,
            familias.nomeFamilia
            FROM manutencoes
            INNER JOIN proprietarios ON proprietarios.idProprietario = manutencoes.idProprietario
            INNER JOIN manutencoes_pecas ON manutencoes_pecas.idManut = manutencoes.idManut
            INNER JOIN pecas ON pecas.idPeca = manutencoes_pecas.idPeca
            INNER JOIN familias ON familias.idFamilia = manutencoes.idFamilia
            WHERE manutencoes.idManut = ?
            GROUP BY manutencoes_pecas.idManutPeca
        ';

        $result = $this->sql_actions->executarQuery($query, [$id_manut]);

        if (count($result) > 0) {
            return $result;
        }

        return [];

    }
}