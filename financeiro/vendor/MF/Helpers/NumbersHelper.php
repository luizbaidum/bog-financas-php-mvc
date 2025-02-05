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

        $num = str_replace(',', '', $num);
        $formated = number_format($num, 2, ',', '.');

        return $formated;
    }
    
    public static function formatUStoBR($num)
	{
        if ($num == null || $num == '')
            return '';
            
        $num = str_replace(',', '', $num);
        $formated = number_format($num, 2, ',', '.');

        return $formated;
    }
}