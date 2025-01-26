<?php

namespace src\Models\MovimentosMensais;

use MF\Entity\Entity;

class MovimentosMensaisEntity extends Entity {
    public const main_table = 'movimentos_mensais';

    public int $idMovMensal;
    public string $dataRepete;
    public float $valorDespesa;
    public int $idCategoria;
    public string $nomeMovimento;
    public int $proprietario;
    public int $idFamilia;
}