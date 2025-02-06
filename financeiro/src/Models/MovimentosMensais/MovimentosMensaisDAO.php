<?php

namespace src\Models\MovimentosMensais;

use MF\Model\Model;
use MF\Model\SQLActions;

class MovimentosMensaisDAO extends Model {
    public function getMensais()
    {
        $query = 'SELECT movimentos_mensais.*, categorias.categoria, categorias.tipo, categorias.sinal FROM movimentos_mensais INNER JOIN categorias ON movimentos_mensais.idCategoria = categorias.idCategoria WHERE movimentos_mensais.idMovMensal > 0';

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }
}