<?php 

namespace src\Models\MetasMensais;

use MF\Entity\Entity;

class MetasMensaisEntity extends Entity {

    public const main_table = 'metas_mensais';

    public int $idMetaMensal;
    public string $data;
    public float $totalReceitas;
    public int $idProprietario;
    public int $idFamilia;
    public float $vlrEconomia;
    public string $atualizado;
}