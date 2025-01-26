<?php

namespace src;

abstract class Diretorio {

    /**
     * Alterar diretório
     */
    const diretorio = '\\Users\\luizb\\Desktop\\github\\meu-financeiro\\htdocs-renovations';

    public static function getDiretorio()
    {
        return __DIR__;
    }
}

