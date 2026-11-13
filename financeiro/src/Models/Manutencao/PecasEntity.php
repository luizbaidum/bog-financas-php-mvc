<?php

namespace src\Models\Manutencao;

use MF\Entity\Entity;

class PecasEntity extends Entity {
    public const main_table = 'pecas';

    public int $idPeca;
    public string $nomePeca;
}