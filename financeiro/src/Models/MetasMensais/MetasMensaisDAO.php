<?php

namespace src\Models\MetasMensais;

use MF\Model\Model;
use MF\Model\SQLActions;

class MetasMensaisDAO extends Model {
    public function listarMetasMensais(): array
    {
        $query = 'SELECT metas_mensais.*, proprietarios.proprietario 
        FROM metas_mensais 
        INNER JOIN proprietarios ON proprietarios.idProprietario = metas_mensais.idProprietario 
        WHERE 0=0
        ORDER BY metas_mensais.data ASC';

        $new_sql = new SQLActions();
		$result = $new_sql->executarQuery($query);

        return $result ?? [];
    }
}