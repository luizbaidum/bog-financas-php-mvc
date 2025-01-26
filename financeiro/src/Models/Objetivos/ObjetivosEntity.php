<?php 

namespace src\Models\Objetivos;

use MF\Entity\Entity;

class ObjetivosEntity extends Entity {

    public const main_table = 'objetivos_invest';

    public int $idObj;
    public string $nomeObj;
    public float $vlrObj;
    public float $percentObjContaInvest;
    public float $saldoAtual;
    public int $idContaInvest;
    public string $dataCriacao;
    public int $idFamilia;
}