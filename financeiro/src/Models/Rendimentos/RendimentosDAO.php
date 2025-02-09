<?php

namespace src\Models\Rendimentos;

use MF\Model\Model;
use MF\Model\SQLActions;

class RendimentosDAO extends Model {
    public function getEvolucaoRendimentos()
    {
        $query = "SELECT rendimentos.idContaInvest, SUM(rendimentos.valorRendimento) AS valor, DATE_FORMAT(rendimentos.dataRendimento, '%Y%m') AS mesAno, CONCAT(rendimentos.idContaInvest, ' - ', contas_investimentos.tituloInvest) AS nome FROM rendimentos INNER JOIN contas_investimentos ON rendimentos.idContaInvest = contas_investimentos.idContaInvest WHERE 0 = 0 GROUP BY rendimentos.idContaInvest, mesAno ORDER BY rendimentos.idContaInvest ASC, mesAno ASC";

        //DATE_FORMAT(rendimentos.dataRendimento, '%Y%m') BETWEEN DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -6 MONTH), '%Y%m') AND DATE_FORMAT(CURDATE(), '%Y%m')

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }
}