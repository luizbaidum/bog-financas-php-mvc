<?php

namespace MF\Helpers;

class NumbersHelper {
    public static function onlyNumbers($str)
    {
        $str = preg_replace('/\D/', '', $str);
        return $str;
    }

    public static function formatBRtoUS($num)
	{
        if ($num == null || $num == '')
            return '';

        $numero = str_replace('.', '', $num);
        $formated = str_replace(',', '.', $numero);

        return $formated;
    }

    public static function formatUStoBR($num)
	{
        if ($num == null || $num == '')
            return '0,00';

        $num = str_replace(',', '', $num);
        $formated = number_format($num, 2, ',', '.');

        return $formated;
    }
}