<?php

namespace src\Models\Movimentos;

use MF\Helpers\DateHelper;
use MF\Model\Model;

class MovimentosDAO extends Model {
    public function indexTable($pesquisa, $year = '', $month = '')
    {
        $where = 'WHERE (DATE_FORMAT(movimentos.dataMovimento, "%Y%m") = DATE_FORMAT(CURRENT_DATE(), "%Y%m"))';

        if ($month != '' && $month == 'Todos') {
            $where = 'WHERE movimentos.dataMovimento IS NOT NULL';
        } elseif ($month != '' && $month != 'Todos') {
            $where = "WHERE DATE_FORMAT(movimentos.dataMovimento, '%Y%m') = '$year$month'";
        }

        if ($pesquisa != '') {
            $where .= ' AND (categorias.categoria LIKE "%' . $pesquisa . '%" OR movimentos.nomeMovimento LIKE "%' . $pesquisa . '%" OR proprietarios.proprietario = "' . $pesquisa . '")';
        }

        $query = "SELECT movimentos.*, categorias.categoria, categorias.tipo, proprietarios.proprietario FROM movimentos INNER JOIN categorias ON categorias.idCategoria = movimentos.idCategoria INNER JOIN proprietarios ON proprietarios.idProprietario = movimentos.idProprietario $where ORDER BY dataMovimento DESC, valor DESC";

		$result = $this->sql_actions->executarQuery($query);

        return $result;
    }

    public function getSaldoPassado(int $times = 2)
    {
        $mes_atual = date('m');

        if ($mes_atual != 01) {
            if ($mes_atual == 02) {
                $times = 1;
            }

            $where = 'MONTH(movimentos.dataMovimento) BETWEEN "'. ($mes_atual - $times).'" AND "'. ($mes_atual - 1).'"';

            $query = "SELECT SUM(movimentos.valor) AS valor, MONTH(movimentos.dataMovimento) AS MES
                        FROM movimentos 
                        WHERE $where
                        GROUP BY MES";

            $result = $this->sql_actions->executarQuery($query);

            return $result;
        }

        return [];
    }

    public function getResultado($year = '', $month = '')
    {
        $where = '(DATE_FORMAT(movimentos.dataMovimento, "%Y%m") = DATE_FORMAT(CURRENT_DATE(), "%Y%m"))';
        if (!empty($month)) {
            $where = "DATE_FORMAT(movimentos.dataMovimento, '%Y%m') = '$year$month'";
        }

        $query = "SELECT SUM(movimentos.valor) AS total, movimentos.idProprietario, categorias.tipo, proprietarios.proprietario FROM movimentos INNER JOIN categorias ON movimentos.idCategoria = categorias.idCategoria INNER JOIN proprietarios ON proprietarios.idProprietario = movimentos.idProprietario WHERE $where GROUP BY movimentos.idProprietario, categorias.idCategoria";

        $result = $this->sql_actions->executarQuery($query);

        if (count($result) > 0) {
            return $result;
        }

        return false;
    }

    public function detalharMovimento($id)
    {
        $query = "SELECT movimentos.*, 
            objetivos_invest.nomeObj, 
            objetivos_invest.idObj,
            categorias.categoria, 
            categorias.tipo, 
            movprop.proprietario AS proprietarioMov,
            investprop.proprietario AS proprietarioContaInvest,
            contas_investimentos.tituloInvest,
            contas_investimentos.nomeBanco
            FROM movimentos 
            LEFT JOIN rendimentos ON movimentos.idMovimento = rendimentos.idMovimento 
            LEFT JOIN objetivos_invest ON rendimentos.idObj = objetivos_invest.idObj 
            INNER JOIN categorias ON categorias.idCategoria = movimentos.idCategoria 
            INNER JOIN proprietarios movprop ON movprop.idProprietario = movimentos.idProprietario 
            LEFT JOIN contas_investimentos ON movimentos.idContaInvest = contas_investimentos.idContaInvest 
            LEFT JOIN proprietarios investprop ON investprop.idProprietario = contas_investimentos.idProprietario 
            WHERE movimentos.idMovimento = ?";

		$result = $this->sql_actions->executarQuery($query, [$id]);

        return $result;
    }

    public function consultarMovimento($id)
    {
        $query = "SELECT movimentos.*, rendimentos.idRendimento, rendimentos.idObj, objetivos_invest.nomeObj FROM movimentos LEFT JOIN rendimentos ON movimentos.idMovimento = rendimentos.idMovimento LEFT JOIN objetivos_invest ON rendimentos.idObj = objetivos_invest.idObj WHERE movimentos.idMovimento = ?";

		$result = $this->sql_actions->executarQuery($query, [$id]);

        return $result;
    }

    public function indexTableInvestimentos($pesquisa = '', $year = '', $month = '')
    {
        $where = '(DATE_FORMAT(movimentos.dataMovimento, "%Y%m") = DATE_FORMAT(CURRENT_DATE(), "%Y%m"))';

        if ($month != '' && $month == 'Todos') {
            $where = 'movimentos.dataMovimento IS NOT NULL';
        } elseif ($month != '' && $month != 'Todos') {
            $where = "DATE_FORMAT(movimentos.dataMovimento, '%Y%m') = '$year$month'";
        }

        if ($pesquisa != '') {
            //$where .= ' AND (categorias.categoria LIKE "%' . $pesquisa . '%" OR movimentos.nomeMovimento LIKE "%' . $pesquisa . '%" OR proprietarios.proprietario = "' . $pesquisa . '")';
            $where .= ' AND (proprietarios.proprietario = "' . $pesquisa . '")';
        }

        $query = "SELECT movimentos.*, categorias.categoria, CONCAT(contas_investimentos.nomeBanco, ' - ', contas_investimentos.tituloInvest) AS invest FROM movimentos INNER JOIN categorias ON categorias.idCategoria = movimentos.idCategoria INNER JOIN contas_investimentos ON contas_investimentos.idContaInvest = movimentos.idContaInvest INNER JOIN proprietarios ON proprietarios.idProprietario = movimentos.idProprietario WHERE movimentos.idContaInvest > 0 AND categorias.idCategoria IN (12, 10) AND $where ORDER BY dataMovimento DESC";

		$result = $this->sql_actions->executarQuery($query);

        return $result;
    }

    public function consultarObservacao($id): string
    {
        $params = [$id];

        $query = 'SELECT movimentos.observacao FROM movimentos WHERE movimentos.idMovimento = ?';

        $result = $this->sql_actions->executarQuery($query, $params);

        if (count($result) > 0) {
            return $result[0]['observacao'];
        }

        return '';
    }

    public function extratoProprietarios(array $filtros): array
    {
        $where = '';
        $params = array();
        if (!empty($filtros['idCategoria'])) {
            $where .= ' AND movimentos.idCategoria = ? ';
            $params[] = $filtros['idCategoria'];
        }

        if (!empty($filtros['idProprietario'])) {
            $where .= ' AND movimentos.idProprietario = ? ';
            $params[] = $filtros['idProprietario'];
        }

        if ($filtros['data_inicio'] != '' && $filtros['data_fim'] != '') {
            $where .= ' AND movimentos.dataMovimento >= ? AND movimentos.dataMovimento <= ? ';
            $params[] = $filtros['data_inicio'];
            $params[] = $filtros['data_fim'];
        }

        $query = "SELECT movimentos.*, proprietarios.proprietario, categorias.categoria FROM movimentos INNER JOIN proprietarios ON proprietarios.idProprietario = movimentos.idProprietario INNER JOIN categorias ON categorias.idCategoria = movimentos.idCategoria WHERE movimentos.idMovimento > 0 $where ORDER BY dataMovimento DESC";

		$result = $this->sql_actions->executarQuery($query, $params);

        return $result;
    }

    public function gerarRelatorioIndicadoresMensal($id_familia, $year = '', $month = '')
    {
        $where_realizado = ' (DATE_FORMAT(movimentos.dataMovimento, "%Y%m") = DATE_FORMAT(CURRENT_DATE(), "%Y%m"))';
        if ($month != '') {
            if ($month == 'Todos') {
                $where_realizado = ' movimentos.dataMovimento IS NOT NULL';
            } else {
                $where_realizado = " DATE_FORMAT(movimentos.dataMovimento, '%Y%m') = '$year$month'";
            }
        }

        $where_orcado = ' (MONTH(orcamentos.dataOrcamento) = MONTH(CURRENT_DATE()))';
        if ($month != '') {
            if ($month == 'Todos') {
                $where_orcado = ' orcamentos.dataOrcamento IS NOT NULL';
            } else {
                $where_orcado = " DATE_FORMAT(orcamentos.dataOrcamento, '%Y%m') = '$year$month'";
            }
        }

        $query  = "SELECT 
                    categorias.idCategoria,
                    categorias.categoria,
                    categorias.tipo,
                    (
                        SELECT IFNULL(SUM(movimentos.valor), 0)
                        FROM movimentos
                        WHERE movimentos.idCategoria = categorias.idCategoria AND movimentos.idFamilia = $id_familia
                        AND $where_realizado
                    ) AS totalRealizado,
                    (
                        SELECT IFNULL(SUM(orcamentos.valor), 0)
                        FROM orcamentos
                        WHERE orcamentos.idCategoria = categorias.idCategoria AND orcamentos.idFamilia = $id_familia
                        AND $where_orcado
                    ) AS totalOrcado
                    FROM categorias 
                    WHERE categorias.idCategoria > 0 AND categorias.idFamilia = $id_familia
                    HAVING (totalRealizado <> 0 OR totalOrcado <> 0)
                    ORDER BY categorias.tipo DESC, totalRealizado DESC";

        $result = $this->sql_actions->executarQuery($query, [], false);

        $ret = [];
        foreach ($result as $val) {
            $ret[$val['idCategoria']] = $val;
        }

        return $ret;
    }

    public function gerarRelatorioIndicadoresAnual($id_familia, $year = '')
    {
        $where_realizado = ' (DATE_FORMAT(movimentos.dataMovimento, "%Y") = DATE_FORMAT(CURRENT_DATE(), "%Y"))';
        if ($year != '') {
            $where_realizado = " DATE_FORMAT(movimentos.dataMovimento, '%Y') = '$year'";
        }

        $where_orcado = ' (YEAR(orcamentos.dataOrcamento) = YEAR(CURRENT_DATE()))';
        if ($year != '') {
            $where_orcado = " DATE_FORMAT(orcamentos.dataOrcamento, '%Y') = '$year'";
        }

        $query  = "SELECT SUM(movimentos.valor) AS total,
                        MONTH(movimentos.dataMovimento) AS mes,
                        categorias.idCategoria,
                        categorias.categoria,
                        categorias.tipo,
                        'T' AS isRealizado,
                        'F' AS isOrcado
                        FROM movimentos
                        INNER JOIN categorias ON movimentos.idCategoria = categorias.idCategoria
                        WHERE movimentos.idFamilia = $id_familia AND $where_realizado
                        GROUP BY movimentos.idCategoria, MONTH(movimentos.dataMovimento)
                    UNION ALL
                    SELECT SUM(orcamentos.valor) AS total,
                        MONTH(orcamentos.dataOrcamento) AS mes,
                        categorias.idCategoria,
                        categorias.categoria,
                        categorias.tipo,
                        'F' AS isRealizado,
                        'T' AS isOrcado
                        FROM orcamentos
                        INNER JOIN categorias ON orcamentos.idCategoria = categorias.idCategoria
                        WHERE orcamentos.idFamilia = $id_familia AND $where_orcado
                        GROUP BY orcamentos.idCategoria, MONTH(orcamentos.dataOrcamento)";

        $result = $this->sql_actions->executarQuery($query, [], false);

        $realizado = [];
        $orcado = [];
        foreach ($result as $val) {
            if ($val['isRealizado'] == 'T') {
                $realizado[$val['mes']][$val['idCategoria']] = $val;
            } elseif ($val['isOrcado'] == 'T') {
                $orcado[$val['mes']][$val['idCategoria']] = $val;
            }
        }

        return [$realizado, $orcado];
    }

    public function consultarAplicacoesPorMes(string|null $id_proprietario, string|int $ano): array
    {
        if (is_null($id_proprietario)) {
            return [];
        }

        $query = 'SELECT MONTH(movimentos.dataMovimento) AS mes, SUM(IF(categorias.tipo = "A" OR categorias.tipo = "RA", movimentos.valor, 0)) AS vlrEconomiaRealizado, proprietarios.proprietario, SUM(IF(categorias.tipo = "R", movimentos.valor, 0)) AS totalReceitasRealizado
        FROM movimentos 
        INNER JOIN proprietarios ON proprietarios.idProprietario = movimentos.idProprietario 
        INNER JOIN categorias ON movimentos.idCategoria = categorias.idCategoria
        WHERE movimentos.idProprietario = ? AND YEAR(movimentos.dataMovimento) = ? AND (categorias.tipo = "A" OR categorias.tipo = "RA" OR categorias.tipo = "R")
        GROUP BY MONTH(movimentos.dataMovimento) ASC';

        $params = [$id_proprietario, $ano];

		$result = $this->sql_actions->executarQuery($query, $params);

        return $result ?? [];
    }

    public function definirMovimentoMensalBaixado(string $id_movimento_mensal, string $nome_movimento)
    {
        $query = 'UPDATE movimentos SET idMovMensal = ? WHERE MONTH(dataMovimento) = ' . date('n') . ' AND YEAR(dataMovimento) = ' . date('Y') . ' AND idMovMensal = 0 AND nomeMovimento = ?';

        $params = [$id_movimento_mensal, $nome_movimento];

		$result = $this->sql_actions->executarQuery($query, $params);

        return $result ?? [];
    }

    public function getSaldoReceitas(string $id_proprietario = '', string $mes = '', string $ano = '')
    {
        $params = [];

        $where_clause = 'WHERE categorias.tipo = ? ';
        $params[] = 'R';

        if ($id_proprietario != '') {
            $where_clause .= "AND movimentos.idProprietario = ? ";
            $params[] = $id_proprietario;
        }

        if ($mes != 'Todos') {
            if ($mes != '' && $ano != '') {
                $where_clause .= "AND (DATE_FORMAT(movimentos.dataMovimento, '%Y%m') = ? ";
                $params[] = "$ano$mes";
            } else {
                $where_clause .= 'AND (MONTH(dataMovimento) = ? AND YEAR(dataMovimento) = ? ';
                $params[] = date('n');
                $params[] = date('Y');
            }

            if ($mes == '' && date('m') != 01) {
                $mes_anterior = DateHelper::calcMonth(date('m'), '-', 1);
                $where_clause .= 'OR DATE_FORMAT(movimentos.dataMovimento, "%Y%m") = ? ';

                $params[] = "$ano$mes_anterior";
            } elseif ($mes != '' && $mes != '01') {
                $mes_anterior = DateHelper::calcMonth($mes, '-', 1);;
                $where_clause .= 'OR DATE_FORMAT(movimentos.dataMovimento, "%Y%m") = ?';

                $params[] = "$ano$mes_anterior";
            }

            $where_clause .= ')';
        }

        $query = 'SELECT SUM(movimentos.valor) AS totalReceitas, MONTH(movimentos.dataMovimento) AS mes FROM movimentos INNER JOIN categorias ON categorias.idCategoria = movimentos.idCategoria ' . $where_clause . ' GROUP BY MONTH(movimentos.dataMovimento) ORDER BY MONTH(movimentos.dataMovimento) DESC';

        $result = $this->sql_actions->executarQuery($query, $params);

        foreach ($result as $value) {
            $ret[$value['mes']] = $value['totalReceitas'];
        }

        return $ret ?? [];
    }

    public function getSaldoDespesas(string $id_proprietario = '', string $mes = '', string $ano = '')
    {
        $params = [];

        $where_clause = 'WHERE categorias.tipo = ? ';
        $params[] = 'D';

        if ($id_proprietario != '') {
            $where_clause .= "AND movimentos.idProprietario = ? ";
            $params[] = $id_proprietario;
        }

        if ($mes != 'Todos') {
            if ($mes != '' && $ano != '') {
                $where_clause .= "AND (DATE_FORMAT(movimentos.dataMovimento, '%Y%m') = ? ";
                $params[] = "$ano$mes";
            } else {
                $where_clause .= 'AND (MONTH(dataMovimento) = ? AND YEAR(dataMovimento) = ? ';
                $params[] = date('n');
                $params[] = date('Y');
            }

            if ($mes == '' && date('m') != 01) {
                $mes_anterior = DateHelper::calcMonth(date('m'), '-', 1);
                $where_clause .= 'OR DATE_FORMAT(movimentos.dataMovimento, "%Y%m") = ? ';

                $params[] = "$ano$mes_anterior";
            } elseif ($mes != '' && $mes != '01') {
                $mes_anterior = DateHelper::calcMonth($mes, '-', 1);;
                $where_clause .= 'OR DATE_FORMAT(movimentos.dataMovimento, "%Y%m") = ?';

                $params[] = "$ano$mes_anterior";
            }

            $where_clause .= ')';
        }

        $query = 'SELECT SUM(movimentos.valor) AS totalDespesas, MONTH(movimentos.dataMovimento) AS mes FROM movimentos INNER JOIN categorias ON categorias.idCategoria = movimentos.idCategoria ' . $where_clause . ' GROUP BY MONTH(movimentos.dataMovimento) ORDER BY MONTH(movimentos.dataMovimento) DESC';

        $result = $this->sql_actions->executarQuery($query, $params);

        foreach ($result as $value) {
            $ret[$value['mes']] = $value['totalDespesas'];
        }

        return $ret ?? [];
    }

    public function getSaldoInvestimentos(string $id_proprietario = '', string $mes = '', string $ano = '')
    {
        $params = [];

        $where_clause = 'WHERE (categorias.tipo = ? OR categorias.tipo = ?)';
        $params[] = 'A';
        $params[] = 'RA';

        if ($id_proprietario != '') {
            $where_clause .= "AND movimentos.idProprietario = ? ";
            $params[] = $id_proprietario;
        }

        if ($mes != 'Todos') {
            if ($mes != '' && $ano != '') {
                $where_clause .= "AND (DATE_FORMAT(movimentos.dataMovimento, '%Y%m') = ? ";
                $params[] = "$ano$mes";
            } else {
                $where_clause .= 'AND (MONTH(dataMovimento) = ? AND YEAR(dataMovimento) = ? ';
                $params[] = date('n');
                $params[] = date('Y');
            }

            // if ($mes == '' && date('m') != 01) {
            //     $mes_anterior = DateHelper::calcMonth(date('m'), '-', 1);
            //     $where_clause .= 'OR DATE_FORMAT(movimentos.dataMovimento, "%Y%m") = ? ';

            //     $params[] = "$ano$mes_anterior";
            // } elseif ($mes != '' && $mes != '01') {
            //     $mes_anterior = DateHelper::calcMonth($mes, '-', 1);;
            //     $where_clause .= 'OR DATE_FORMAT(movimentos.dataMovimento, "%Y%m") = ?';

            //     $params[] = "$ano$mes_anterior";
            // }

            $where_clause .= ')';
        }

        $query = 'SELECT SUM(IF(categorias.tipo = "A", movimentos.valor, 0)) AS totalAplicacoes, SUM(IF(categorias.tipo = "RA", movimentos.valor, 0)) AS totalResgates, MONTH(movimentos.dataMovimento) AS mes FROM movimentos INNER JOIN categorias ON categorias.idCategoria = movimentos.idCategoria ' . $where_clause . ' GROUP BY MONTH(movimentos.dataMovimento) ORDER BY MONTH(movimentos.dataMovimento) DESC';

        $result = $this->sql_actions->executarQuery($query, $params);

        foreach ($result as $value) {
            $ret['aplic'] = $value['totalAplicacoes'];
            $ret['resg'] = $value['totalResgates'];
        }

        return $ret ?? [];
    }
}