<?php

namespace src\Models\SolicitarAcesso;

use MF\Entity\Entity;

class SolicitarAcessoEntity extends Entity {
    const main_table = 'solicitar_acesso';

    public int $idSolicitarAcesso;
    public string $nome;
    public string $login;
    public string $senha;
    public string $hashIn;
    public string $dataHoraSolicitacao;
    public string $hashOut;
}