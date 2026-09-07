<?php

namespace src\Models\Rendimentos;

use MF\Model\Model;

class RendimentosDAO extends Model {
    public function getEvolucaoRendimentos()
    {
        $data = date('Y') . '-' . date('m') . '-01';
        $periodo = date('Y-m-d', strtotime('-1 year', strtotime($data)));

        $query = "SELECT
                    contas.idContaInvest,
                    COALESCE(SUM(rendimentos.valorRendimento), 0) AS valor,
                    meses.mesAno,
                    CONCAT(contas.nomeBanco, ' - ', contas.tituloInvest) AS nome,
                    contas.idProprietario,
                    proprietarios.proprietario AS proprietarioNome
                FROM
                    (SELECT DISTINCT idContaInvest, tituloInvest, idProprietario, nomeBanco
                        FROM contas_investimentos
                        WHERE contas_investimentos.idFamilia = $_SESSION[id_familia]
                        AND contas_investimentos.status = '1'
                    ) contas
                CROSS JOIN
                    (SELECT DISTINCT DATE_FORMAT(dataRendimento, '%Y%m') AS mesAno
                        FROM rendimentos
                        WHERE rendimentos.idFamilia = $_SESSION[id_familia]
                        AND rendimentos.dataRendimento <= CURDATE()
                        AND rendimentos.dataRendimento >= '$periodo'
                    ) meses
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

        $result = $this->sql_actions->executarQuery(query: $query, apply_security: false);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }

    public function getTotalizadorRendimentosAteData()
    {
        $data = date('Y') . '-' . date('m') . '-01';
        $periodo = date('Y-m-d', strtotime('-1 year', strtotime($data)));

        $query = "SELECT
                    contas_investimentos.idContaInvest,
                    (contas_investimentos.saldoInicial +
                        (
                            SELECT COALESCE(SUM(rendimentos.valorRendimento), 0)
                            FROM rendimentos
                            WHERE rendimentos.idContaInvest = contas_investimentos.idContaInvest
                            AND rendimentos.dataRendimento < '$periodo'
                        )
                    ) AS valor
                FROM contas_investimentos
                WHERE contas_investimentos.status = '1'
                GROUP BY contas_investimentos.idContaInvest
                ORDER BY contas_investimentos.idContaInvest ASC";

        $result = $this->sql_actions->executarQuery(query: $query);

        if (count($result) > 0) {
            foreach ($result as $row) {
                $ret[$row['idContaInvest']] = $row['valor'];
            }
        }

        return $ret ?? [];
    }

    public function selecionarDoisUltimosRendimentos(string $id_conta_invest, string $data_rend): array
    {
        $query = "SELECT idRendimento, idContaInvest, valorRendimento, tipo, dataRendimento FROM rendimentos WHERE (tipo = '1' OR tipo = '2') AND idContaInvest = ? AND dataRendimento <= ? ORDER BY idRendimento DESC LIMIT 0, 2";

        $params[] = $id_conta_invest;
        $params[] = $data_rend;

        $result = $this->sql_actions->executarQuery(query: $query, arr_values: $params);

        return $result;
    }

    public function buscarProjecao($ano)
    {
        $sql = "SELECT
                    MONTH(rendimentos.dataRendimento) AS mes,
                    SUM(rendimentos.valorRendimento) AS total
                FROM rendimentos
                INNER JOIN contas_investimentos ON contas_investimentos.idContaInvest = rendimentos.idContaInvest
                WHERE rendimentos.dataRendimento >= ?
                AND rendimentos.dataRendimento <= ? AND contas_investimentos.status = '1'
                GROUP BY MONTH(rendimentos.dataRendimento)
                ORDER BY MONTH(rendimentos.dataRendimento) ASC";

        $params[] = $ano . '-01-01';
        $params[] = $ano . '-12-31';

        $result = $this->sql_actions->executarQuery(query: $sql, arr_values: $params);

        $ret = array();
        foreach ($result as $value) {
            $ret[$value['mes']] = $value['total'];
        }

        return $ret;
    }

    public function buscarPosicaoInicial($ano)
    {
        $sql = "SELECT
                    SUM(contas_investimentos.saldoInicial) +
                    (SELECT
                        COALESCE(SUM(rendimentos.valorRendimento), 0)
                        FROM rendimentos
                        INNER JOIN contas_investimentos ON contas_investimentos.idContaInvest = rendimentos.idContaInvest
                        WHERE YEAR(rendimentos.dataRendimento) < ? AND contas_investimentos.status = '1'
                    ) AS total
                FROM contas_investimentos
                WHERE contas_investimentos.status = '1'";

        $params[] = $ano;

        $result = $this->sql_actions->executarQuery(query: $sql, arr_values: $params);

        return $result[0]['total'] ?? 0;
    }
}