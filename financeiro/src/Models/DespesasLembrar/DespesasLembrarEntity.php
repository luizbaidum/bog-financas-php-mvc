<?php 

namespace src\Models\DespesasLembrar;

use MF\Entity\Entity;

class DespesasLembrarEntity extends Entity {

    public const main_table = 'despesas_lembrar';

    public int $idDespLembrar;
    public string $data;
    public float $valor;
    public string $descricao;
    public int $idProprietarioPagante;
    public int $idProprietarioReal;
    public string $metodoPgto;
    public int $idMovimento;
    public int $idFamilia;
}