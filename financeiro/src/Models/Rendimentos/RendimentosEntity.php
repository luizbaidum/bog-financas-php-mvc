<?php

namespace src\Models\Rendimentos;

use MF\Entity\Entity;

class RendimentosEntity extends Entity {
    const main_table = 'rendimentos';

    public int $idRendimento;
    public int $idContaInvest;
    public float $valorRendimento;
    public int $tipo;
    public string $dataRendimento;
    public int $idMovimento;
    public int $idFamilia; 
    public int $idObj;
}