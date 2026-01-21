<?php

namespace src\Models\DespesasLembrar;

use MF\Model\Model;

class DespesasLembrarDAO extends Model {
    public function indexTable($pesquisa, $year = '', $month = '')
    {
        $where = 'WHERE (DATE_FORMAT(despesas_lembrar.data, "%Y%m") = DATE_FORMAT(CURRENT_DATE(), "%Y%m"))';

        if ($month != '' && $month == 'Todos') {
            $where = 'WHERE despesas_lembrar.data IS NOT NULL';
        } elseif ($month != '' && $month != 'Todos') {
            $where = "WHERE DATE_FORMAT(despesas_lembrar.data, '%Y%m') = '$year$month'";
        }

        if ($pesquisa != '') {
            $where .= ' AND (categorias.categoria LIKE "%' . $pesquisa . '%" OR despesas_lembrar.nomeMovimento LIKE "%' . $pesquisa . '%" OR pagante.proprietario = "' . $pesquisa . '" OR verdadeiro.proprietario = "' . $pesquisa . '")';
        }

        $query = "SELECT despesas_lembrar.*, categorias.categoria, categorias.tipo, pagante.proprietario AS propPagante, verdadeiro.proprietario as propReal FROM despesas_lembrar LEFT JOIN proprietarios pagante ON pagante.idProprietario = despesas_lembrar.idProprietarioPagante LEFT JOIN proprietarios verdadeiro ON verdadeiro.idProprietario = despesas_lembrar.idProprietarioReal LEFT JOIN movimentos ON despesas_lembrar.idMovimento = movimentos.idMovimento LEFT JOIN categorias ON categorias.idCategoria = movimentos.idCategoria $where ORDER BY data DESC, valor DESC";

		$result = $this->sql_actions->executarQuery($query);

        return $result;
    }

    public function verificarMovReal(string $idDespLembrar)
    {
        $query = "SELECT movimentos.idMovimento FROM despesas_lembrar INNER JOIN movimentos ON movimentos.idMovimento = despesas_lembrar.idMovimento WHERE despesas_lembrar.idDespLembrar = ?";

        $result = $this->sql_actions->executarQuery($query, array($idDespLembrar));
        return $result[0] ?? null;
    }
}