<?php

namespace src\Models\Usuarios;

use MF\Entity\Entity;

class UsuariosEntity extends Entity {

    public const main_table = 'usuarios';

    public int $idUsuario;
    public string $login;
    public string $senha;
    public int $idFamilia;
    public string $gestor;
    public string $nome;
}