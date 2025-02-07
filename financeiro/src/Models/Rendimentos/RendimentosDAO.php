<?php

namespace src\Models\Rendimentos;

use MF\Model\Model;
use MF\Model\SQLActions;

class RendimentosDAO extends Model {
    public function getEvolucaoRendimentos()
    {
        $query = "SELECT rendimentos.idContaInvest, SUM(rendimentos.valorRendimento) AS valor, DATE_FORMAT(rendimentos.dataRendimento, '%Y%m') AS mesAno, CONCAT(rendimentos.idContaInvest, ' - ', contas_investimentos.tituloInvest) AS nome FROM rendimentos INNER JOIN contas_investimentos ON rendimentos.idContaInvest = contas_investimentos.idContaInvest WHERE DATE_FORMAT(rendimentos.dataRendimento, '%Y') = DATE_FORMAT(CURDATE(), '%Y') GROUP BY rendimentos.idContaInvest, mesAno ORDER BY mesAno ASC";

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }

    /**
     * select contas_investimentos.idContaInvest, (saldoAnterior + sum(valorRendimento)) as saldo from contas_investimentos inner join rendimentos on contas_investimentos.idContaInvest = rendimentos.idContaInvest where DATE_FORMAT(dataRendimento, '%m%Y') >= DATE_FORMAT(dataAnterior, '%m%Y') GROUP BY contas_investimentos.idContaInvest;
     */
}