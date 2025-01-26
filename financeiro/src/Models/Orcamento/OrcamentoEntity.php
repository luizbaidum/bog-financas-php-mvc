<?php 

namespace src\Models\Orcamento;

use MF\Entity\Entity;

class OrcamentoEntity extends Entity {
    public const main_table = 'orcamentos';

    public int $idOrcamento;
    public int $idCategoria;
    public string $dataOrcamento;
    public float $valor;
    public int $cartao;
    public int $idFamilia;
}