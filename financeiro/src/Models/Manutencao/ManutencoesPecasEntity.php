<?php

namespace src\Models\Manutencao;

use MF\Entity\Entity;

class ManutencoesPecasEntity extends Entity {
    public const main_table = 'manutencoes_pecas';

    public int $idManutPeca;
    public int $idManut;
    public int $idPeca;
    public float $valorPeca;
    public float $qtdPeca;
}