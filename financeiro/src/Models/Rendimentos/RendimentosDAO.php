<?php

namespace src\Models\Rendimentos;

use MF\Model\Model;
use MF\Model\SQLActions;

class RendimentosDAO extends Model {
    public function getEvolucaoRendimentos()
    {
        $query = "SELECT rendimentos.idContaInvest, SUM(rendimentos.valorRendimento) AS valor, DATE_FORMAT(rendimentos.dataRendimento, '%Y%m') AS mesAno, CONCAT(rendimentos.idContaInvest, ' - ', contas_investimentos.tituloInvest) AS nome FROM rendimentos INNER JOIN contas_investimentos ON rendimentos.idContaInvest = contas_investimentos.idContaInvest WHERE DATE_FORMAT(rendimentos.dataRendimento, '%Y%m') BETWEEN DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -6 MONTH), '%Y%m') AND DATE_FORMAT(CURDATE(), '%Y%m') GROUP BY rendimentos.idContaInvest, mesAno ORDER BY mesAno ASC";

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        echo '<pre>';
        print_r($result);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }
}