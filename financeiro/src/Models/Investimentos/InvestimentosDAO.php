<?php

namespace src\Models\Investimentos;

use MF\Model\Model;

class InvestimentosDAO extends Model {

    public function getSaldosIniciais()
    {
        $query = "SELECT contas_investimentos.saldoInicial, contas_investimentos.dataInicio, contas_investimentos.idContaInvest FROM contas_investimentos WHERE dataInicio IS NOT NULL GROUP BY idContaInvest ORDER BY idContaInvest ASC, dataInicio ASC";

        $result = $this->sql_actions->executarQuery($query);

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
        $proprietario = $filtro['idProprietario'] ?? '';
        $desconsiderar = $filtro['investNaoConsiderar'] ?? [];

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

        if ($proprietario != '') {
            $where .= "AND `contas_investimentos`.`idProprietario` = '$proprietario'";
        }

        if (! empty($desconsiderar)) {

            foreach ($desconsiderar as $id) {
                $where .= " AND `rendimentos`.`idContaInvest` != '$id'";
            }
        }

        $query = "SELECT `rendimentos`.*, CONCAT(`contas_investimentos`.`nomeBanco`, ' - ', `contas_investimentos`.`tituloInvest`) AS conta, CONCAT(objetivos_invest.idObj, ' - ', nomeObj) AS objetivo FROM `rendimentos` INNER JOIN `contas_investimentos` ON `rendimentos`.`idContaInvest` = `contas_investimentos`.`idContaInvest` INNER JOIN `proprietarios` ON `proprietarios`.`idProprietario` = `contas_investimentos`.`idProprietario` LEFT JOIN objetivos_invest ON objetivos_invest.idObj = rendimentos.idObj WHERE `rendimentos`.`idRendimento` > 0 $where ORDER BY `rendimentos`.`dataRendimento` DESC";

        $result = $this->sql_actions->executarQuery($query);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }

    public function getAllContas($only_actives = false)
    {
        $where = '';
        if ($only_actives) {
            $where = ' AND contas_investimentos.status = "1" ';
        }

        $query = "SELECT contas_investimentos.*, proprietarios.proprietario FROM contas_investimentos INNER JOIN proprietarios ON proprietarios.idProprietario = contas_investimentos.idProprietario WHERE contas_investimentos.idContaInvest > 0 $where ORDER BY contas_investimentos.nomeBanco, contas_investimentos.tituloInvest, contas_investimentos.idProprietario";

        $result = $this->sql_actions->executarQuery($query);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }
}