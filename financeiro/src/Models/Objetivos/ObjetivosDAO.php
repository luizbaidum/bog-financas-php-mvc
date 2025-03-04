<?php

namespace src\Models\Objetivos;

use MF\Model\Model;
use MF\Model\SQLActions;

class ObjetivosDAO extends Model {
    public function consultarObjetivosPorInvestimento($id_invest)
    {
        $query = "SELECT objetivos_invest.* FROM objetivos_invest WHERE objetivos_invest.idContaInvest = ?";

        $arr_values[] = $id_invest;

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query, $arr_values);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }

    public function consultarPercentualDisponivel($id_conta_invest)
    {
        $query = 'SELECT SUM(objetivos_invest.percentObjContaInvest) AS totalUtilizado FROM objetivos_invest WHERE objetivos_invest.idContaInvest = ?';

        $arr_values[] = $id_conta_invest;

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query, $arr_values);

        if (count($result) > 0) {
            return $result[0]['totalUtilizado'];
        }

        return false;
    }
}