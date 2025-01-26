<?php 

namespace src\Models\System;

use MF\Entity\Entity;

class LembretesEntity extends Entity {

    public const main_table = 'lembretes';

    public int $idLembrete;
    public string $lembrete;
    public string $dataLembrete;
    public int $idFamilia;
}