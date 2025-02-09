<?php

namespace src\Models\Investimentos;

use MF\Model\Model;
use MF\Model\SQLActions;

class InvestimentosDAO extends Model {

    public function getSaldosIniciais()
    {
        $query = "SELECT contas_investimentos.saldoInicial, contas_investimentos.dataInicio, contas_investimentos.idContaInvest FROM contas_investimentos WHERE dataInicio IS NOT NULL GROUP BY idContaInvest ORDER BY idContaInvest ASC, dataInicio ASC";

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }
}