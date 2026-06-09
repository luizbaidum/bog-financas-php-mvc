<?php 

namespace src\Models\ConferenciaExtrato;

use MF\Entity\Entity;

class ConferenciaExtratoEntity extends Entity {

    public const main_table = 'conferencia_extrato';

    public int $idConferencia;
    public string $dataExtrato;
    public string $descricao;
    public float $credito;
    public float $debito;
    public int $idMovimento;
    public int $idFamilia;
}