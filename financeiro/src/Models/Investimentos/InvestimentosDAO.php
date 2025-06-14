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

    public function consultarExtrato($filtro)
    {
        $ano = $filtro['extratoAno'] ?? '';
        $mes = $filtro['extratoMes'] ?? '';
        $invest = $filtro['extratoInvest'] ?? '';
        $acao = $filtro['acaoInvest'] ?? '';

        if ($mes == '') {
            if ($ano == date('Y') || $ano == '') {
                $hoje = date('Y-m-d');
                $data_create = date_create($hoje);
            } else {
                $hoje = "$ano-12-31";
                $data_create = date_create($hoje);
            }

            date_sub($data_create, date_interval_create_from_date_string('90 days'));
    
            $where = "AND `rendimentos`.`dataRendimento` BETWEEN '" . date_format($data_create, 'Y-m-01') . "' AND '$hoje'";
        } elseif ($mes == 'Todos') {
            $where = "AND DATE_FORMAT(`rendimentos`.`dataRendimento`, '%Y') = '$ano'";
        } else {
            $where = "AND DATE_FORMAT(`rendimentos`.`dataRendimento`, '%Y%b') = '$ano$mes'";
        }

        if ($invest != '') {
            $where .= "AND `rendimentos`.`idContaInvest` = '$invest'";
        }

        if ($acao != '') {
            $where .= "AND `rendimentos`.`tipo` = '$acao'";
        }

        $query = "SELECT `rendimentos`.*, CONCAT(`contas_investimentos`.`nomeBanco`, ' - ', `contas_investimentos`.`tituloInvest`) AS conta FROM `rendimentos` INNER JOIN `contas_investimentos` ON `rendimentos`.`idContaInvest` = `contas_investimentos`.`idContaInvest` WHERE `rendimentos`.`idRendimento` > 0 $where ORDER BY `rendimentos`.`dataRendimento` DESC";

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }

    public function getAllContas()
    {
        $query = "SELECT contas_investimentos.*, proprietarios.proprietario FROM contas_investimentos LEFT JOIN proprietarios ON proprietarios.idProprietario = contas_investimentos.idProprietario WHERE contas_investimentos.idContaInvest > 0";

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }
}