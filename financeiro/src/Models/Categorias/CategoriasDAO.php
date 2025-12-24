<?php

namespace src\Models\Categorias;

use MF\Model\Model;

class CategoriasDAO extends Model {
    public function selecionarCategoriasTipoAeRA()
    {
        $query = 'SELECT categorias.tipo, categorias.idCategoria FROM categorias WHERE categorias.tipo IN ("A", "RA")';

        $result = $this->sql_actions->executarQuery($query);

        $array = [];
        foreach ($result as $v) {
            $array[$v['tipo']] = $v['idCategoria'];
        }

        return $array;
    }
}