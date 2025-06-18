<?php 

namespace src\Models\Familia;

use MF\Entity\Entity;

class FamiliaEntity extends Entity {

    public const main_table = 'familias';

    public int $idFamilia;
    public string $nomeFamilia;
}