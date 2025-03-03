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
}