<?php

namespace src\Models\Categorias;

use MF\Model\Model;
use MF\Model\SQLActions;

class CategoriasDAO extends Model {
    public function selecionarCategoriasTipoAeRA()
    {
        $query = 'SELECT categorias.tipo, categorias.idCategoria FROM categorias WHERE categorias.tipo IN ("A", "RA")';

        $new_sql = new SQLActions();
        $result = $new_sql->executarQuery($query);

        if (count($result) > 0) {
            return $result;
        }

        return [];
    }
}