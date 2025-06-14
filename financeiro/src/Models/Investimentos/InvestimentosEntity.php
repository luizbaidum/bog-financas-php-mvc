<?php 

namespace src\Models\Investimentos;

use MF\Entity\Entity;

class InvestimentosEntity extends Entity {

    public const main_table = 'contas_investimentos';

    public int $idContaInvest;
    public string $nomeBanco;
    public string $tituloInvest;
    public float $saldoInicial;
    public float $saldoAtual;
    public string $dataInicio;
    public float $saldoAnterior;
    public string $dataAnterior;
    public int $idProprietario;
    public int $idFamilia;
}