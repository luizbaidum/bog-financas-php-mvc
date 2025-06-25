<?php

namespace src\Models\Movimentos;

use MF\Model\Model;
use MF\Model\SQLActions;

class MovimentosDAO extends Model {
    public function indexTable($pesquisa, $year = '', $month = '')
    {
        $where = 'WHERE (DATE_FORMAT(movimentos.dataMovimento, "%Y%m") = DATE_FORMAT(CURRENT_DATE(), "%Y%m"))';

        if ($month != '' && $month == 'Todos') {
            $where = 'WHERE movimentos.dataMovimento IS NOT NULL';
        } elseif ($month != '' && $month != 'Todos') {
            $where = "WHERE DATE_FORMAT(movimentos.dataMovimento, '%Y%b') = '$year$month'";
        }

        if ($pesquisa != '') {
            $where .= ' AND (categorias.categoria LIKE "%' . $pesquisa . '%" OR movimentos.nomeMovimento LIKE "%' . $pesquisa . '%" OR proprietarios.proprietario = "' . $pesquisa . '")';
        }

        $query = "SELECT movimentos.*, categorias.categoria, categorias.tipo, proprietarios.proprietario FROM movimentos INNER JOIN categorias ON categorias.idCategoria = movimentos.idCategoria LEFT JOIN proprietarios ON proprietarios.idProprietario = movimentos.idProprietario $where ORDER BY dataMovimento DESC";

        $new_sql = new SQLActions();
		$result = $new_sql->executarQuery($query);

        return $result;
    }

    public function getSaldoPassado(int $times = 2)
    {
        $mes_atual = date('m');
        $where = 'MONTH(movimentos.dataMovimento) BETWEEN "'. ($mes_atual - $times).'" AND "'. ($mes_atual - 1).'"';

        $query = "SELECT SUM(movimentos.valor) AS valor, MONTH(movimentos.dataMovimento) AS MES
                    FROM movimentos 
                    WHERE $where
                    GROUP BY MES";

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        return $result;
    }

    public function indicadores($year = '', $month = '')
    {
        $where = 'WHERE (DATE_FORMAT(movimentos.dataMovimento, "%Y%m") = DATE_FORMAT(CURRENT_DATE(), "%Y%m"))';
        if (!empty($month)) {
            if ($month == 'Todos') {
                $where = 'WHERE movimentos.dataMovimento IS NOT NULL';
            } else {
                $where = "WHERE DATE_FORMAT(movimentos.dataMovimento, '%Y%b') = '$year$month'";
            }
        }

        $query = "SELECT SUM(movimentos.valor) AS total, categorias.idCategoria, categorias.categoria, categorias.tipo
                    FROM movimentos 
                    INNER JOIN categorias ON categorias.idCategoria = movimentos.idCategoria
                    $where
                    GROUP BY movimentos.idCategoria
                    ORDER BY total DESC";

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        $ret = [];
        foreach ($result as $val) {
            $ret[$val['idCategoria']] = $val;
        }

        return $ret;
    }

    public function getResultado($year = '', $month = '')
    {
        $where = '(DATE_FORMAT(movimentos.dataMovimento, "%Y%m") = DATE_FORMAT(CURRENT_DATE(), "%Y%m"))';
        if (!empty($month)) {
            $where = "DATE_FORMAT(movimentos.dataMovimento, '%Y%b') = '$year$month'";
        }

        $query = "SELECT SUM(movimentos.valor) AS total, movimentos.idProprietario, categorias.tipo, categorias.categoria, proprietarios.proprietario FROM movimentos INNER JOIN categorias ON movimentos.idCategoria = categorias.idCategoria LEFT JOIN proprietarios ON proprietarios.idProprietario = movimentos.idProprietario WHERE $where GROUP BY movimentos.idProprietario, categorias.idCategoria";

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        if (count($result) > 0) {
            return $result;
        }

        return false;
    }

    public function consultarMovimento($id)
    {
        $query = "SELECT movimentos.*, rendimentos.idRendimento, rendimentos.idObj, objetivos_invest.nomeObj FROM movimentos LEFT JOIN rendimentos ON movimentos.idMovimento = rendimentos.idMovimento LEFT JOIN objetivos_invest ON rendimentos.idObj = objetivos_invest.idObj WHERE movimentos.idMovimento = ?";

        $new_sql = new SQLActions();
		$result = $new_sql->executarQuery($query, [$id]);

        return $result;
    }

    public function indexTableInvestimentos($pesquisa = '', $year = '', $month = '')
    {
        $where = '(DATE_FORMAT(movimentos.dataMovimento, "%Y%m") = DATE_FORMAT(CURRENT_DATE(), "%Y%m"))';

        if ($month != '' && $month == 'Todos') {
            $where = 'movimentos.dataMovimento IS NOT NULL';
        } elseif ($month != '' && $month != 'Todos') {
            $where = "DATE_FORMAT(movimentos.dataMovimento, '%Y%b') = '$year$month'";
        }

        if ($pesquisa != '') {
            //$where .= ' AND (categorias.categoria LIKE "%' . $pesquisa . '%" OR movimentos.nomeMovimento LIKE "%' . $pesquisa . '%" OR proprietarios.proprietario = "' . $pesquisa . '")';
            $where .= ' AND (proprietarios.proprietario = "' . $pesquisa . '")';
        }

        $query = "SELECT movimentos.*, categorias.categoria, CONCAT(contas_investimentos.nomeBanco, ' - ', contas_investimentos.tituloInvest) AS invest FROM movimentos INNER JOIN categorias ON categorias.idCategoria = movimentos.idCategoria INNER JOIN contas_investimentos ON contas_investimentos.idContaInvest = movimentos.idContaInvest INNER JOIN proprietarios ON proprietarios.idProprietario = movimentos.idProprietario WHERE movimentos.idContaInvest > 0 AND categorias.idCategoria IN (12, 10) AND $where ORDER BY dataMovimento DESC";

        $new_sql = new SQLActions();
		$result = $new_sql->executarQuery($query);

        return $result;
    }

    public function consultarObservacao($id) : string
    {
        $params = [$id];

        $query = 'SELECT movimentos.observacao FROM movimentos WHERE movimentos.idMovimento = ?';

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query, $params);

        if (count($result) > 0) {
            return $result[0]['observacao'];
        }

        return '';
    }

    public function extratoProprietarios(array $filtros) : array
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

        $query = "SELECT movimentos.*, proprietarios.proprietario, categorias.categoria FROM movimentos LEFT JOIN proprietarios ON proprietarios.idProprietario = movimentos.idProprietario INNER JOIN categorias ON categorias.idCategoria = movimentos.idCategoria WHERE movimentos.idMovimento > 0 $where ORDER BY dataMovimento DESC";

        $new_sql = new SQLActions();
		$result = $new_sql->executarQuery($query, $params);

        return $result;
    }
}