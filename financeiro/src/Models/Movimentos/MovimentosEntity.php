<?php

namespace src\Models\Movimentos;

use MF\Entity\Entity;

class MovimentosEntity extends Entity {
    public const main_table = 'movimentos';

    public int $idMovimento;
    public string $nomeMovimento;
    public string $dataMovimento;
    public int $idCategoria;
    public float $valor;
    public int $cartao;
    public int $idProprietario;
    public int $idFamilia;
    public int $idContaInvest;
    public string $observacao;
    public int $idMovMensal;
}