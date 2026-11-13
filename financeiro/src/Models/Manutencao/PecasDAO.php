<?php

namespace src\Models\Manutencao;

use MF\Model\Model;

class PecasDAO extends Model {
    public function buscar($search)
    {
        $params = [
            '%' . $search . '%'
        ];

        $query = 'SELECT pecas.idPeca, pecas.nomePeca
                  FROM pecas
                  WHERE pecas.nomePeca LIKE (?)';

        $result = $this->sql_actions->executarQuery($query, $params, false);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }
}