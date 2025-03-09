<?php 

namespace src\Models\Preferencias;

use MF\Entity\Entity;

class PreferenciasEntity extends Entity {
    public const main_table = 'preferencias';

    public int $idPreferencia;
    public string $titulo;
    public string $status;
    public int $idFamilia;
}