<?php

namespace src\Models\Familia;

use MF\Model\Model;
use MF\Model\SQLActions;

class FamiliaDAO extends Model {
    public function consultarNomeFamilia($id_familia)
    {
        $result = '';
        if (!empty($id_familia)) {
            $query = 'SELECT familias.nomeFamilia FROM familias WHERE familias.idFamilia = ?';

            $new_sql = new SQLActions();
		    $result = $new_sql->executarQuery($query, [$id_familia])[0]['nomeFamilia'];
        }

        return $result;
    }
}