<?php

namespace src\Models\Manutencao;

use MF\Entity\Entity;

class ManutencaoEntity extends Entity {
    public const main_table = 'manutencoes';

    public int $idManut;
    public string $dataManut;
    public string $oficina;
    public float $vlrMO;
    public int $idFamilia;
    public int $idProprietario;
}