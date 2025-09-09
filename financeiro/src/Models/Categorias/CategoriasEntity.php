<?php 

namespace src\Models\Categorias;

use MF\Entity\Entity;

class CategoriasEntity extends Entity {

    public const main_table = 'categorias';

    public int $idCategoria;
    public string $categoria;
    public string $tipo;
    public string $sinal;
    public int $idFamilia;
    public string $regularidade;
    public string $status;
}