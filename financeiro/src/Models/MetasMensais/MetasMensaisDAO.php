<?php

namespace src\Models\MetasMensais;

use MF\Model\Model;
use MF\Model\SQLActions;

class MetasMensaisDAO extends Model {
    public function listarMetasMensais(string|null $id_proprietario, string|int $ano): array
    {
        if (is_null($id_proprietario)) {
            return [];
        }

        $query = 'SELECT metas_mensais.*, proprietarios.proprietario 
        FROM metas_mensais 
        INNER JOIN proprietarios ON proprietarios.idProprietario = metas_mensais.idProprietario 
        WHERE metas_mensais.idProprietario = ? AND YEAR(metas_mensais.data) = ?
        ORDER BY metas_mensais.data ASC';

        $params = [$id_proprietario, $ano];

        $new_sql = new SQLActions();
		$result = $new_sql->executarQuery($query, $params);

        return $result ?? [];
    }
}