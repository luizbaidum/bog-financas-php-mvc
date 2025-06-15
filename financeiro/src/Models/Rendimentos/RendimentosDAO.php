<?php

namespace src\Models\Rendimentos;

use MF\Model\Model;
use MF\Model\SQLActions;

class RendimentosDAO extends Model {
    public function getEvolucaoRendimentos()
    {
        $query = "SELECT 
                    contas.idContaInvest,
                    COALESCE(SUM(rendimentos.valorRendimento), 0) AS valor,
                    meses.mesAno,
                    CONCAT(contas.idContaInvest, ' - ', contas.tituloInvest) AS nome,
                    contas.idProprietario,
                    proprietarios.proprietario AS proprietarioNome
                FROM 
                    (SELECT DISTINCT idContaInvest, tituloInvest, idProprietario FROM contas_investimentos WHERE contas_investimentos.idFamilia = $_SESSION[id_familia]) contas
                CROSS JOIN 
                    (SELECT DISTINCT DATE_FORMAT(dataRendimento, '%Y%m') AS mesAno FROM rendimentos WHERE rendimentos.idFamilia = $_SESSION[id_familia]) meses
                LEFT JOIN 
                    rendimentos 
                    ON rendimentos.idContaInvest = contas.idContaInvest
                    AND DATE_FORMAT(rendimentos.dataRendimento, '%Y%m') = meses.mesAno AND rendimentos.idFamilia = $_SESSION[id_familia]
                LEFT JOIN 
                    proprietarios
                    ON proprietarios.idProprietario = contas.idProprietario
                GROUP BY 
                    contas.idContaInvest, meses.mesAno
                ORDER BY 
                    contas.idContaInvest ASC, meses.mesAno ASC";

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery(query: $query, apply_security: false);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }
}