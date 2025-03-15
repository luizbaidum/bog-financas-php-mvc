<?php

namespace src;

abstract class Diretorio {

    /**
     * Alterar diretório
     */
    const diretorio = 'C:\Users\luizb\Desktop\github\web-financas-mvc\\';

    public static function getDiretorio()
    {
        return __DIR__;
    }

    public static function getBaseUrl()
    {
        $base = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https://' : 'http://';
        $base .= $_SERVER['SERVER_NAME'];
        if ($_SERVER['SERVER_PORT'] != '80') {
            $base .= ':' . $_SERVER['SERVER_PORT'];
        }

        return $base;
    }
}

