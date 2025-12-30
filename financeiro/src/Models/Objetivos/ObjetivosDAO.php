<?php

namespace src\Models\Objetivos;

use MF\Model\Model;

class ObjetivosDAO extends Model {
    public function consultarObjetivosPorInvestimento($id_invest)
    {
        $query = "SELECT objetivos_invest.* FROM objetivos_invest WHERE objetivos_invest.idContaInvest = ?";

        $arr_values[] = $id_invest;

        $result = $this->sql_actions->executarQuery($query, $arr_values);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }

    public function consultarPercentualDisponivel($id_conta_invest)
    {
        $query = 'SELECT SUM(objetivos_invest.percentObjContaInvest) AS totalUtilizado FROM objetivos_invest WHERE objetivos_invest.idContaInvest = ?';

        $arr_values[] = $id_conta_invest;

        $result = $this->sql_actions->executarQuery($query, $arr_values);

        if (count($result) > 0) {
            return $result[0]['totalUtilizado'];
        }

        return false;
    }
}