<?php

namespace src\Models\Rendimentos;

use MF\Entity\Entity;

class RendimentosDeletadosEntity extends Entity {
    const main_table = 'rendimentos_deletados';

    public int $idRendimentoDeletado;
    public int $idRendimentoOriginal;
    public int $idMovimento;
    public int $idContaInvest;
    public float $valorRendimento;
    public int $tipo;
    public string $dataRendimento;
    public int $idObj;
    public int $idUsuarioExclusao;
    public string $dataExclusao;
    public int $idFamilia;
}
