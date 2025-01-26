<?php

namespace MF\Helpers;

use DateTime;

class DateHelper {
    public static function convertUStoBR($date)
    {
        if ($date == null || $date == '')
            return ''; 

        date_default_timezone_set('America/Sao_Paulo');
        $ob_data_atual = new DateTime($date);
        $data_formatada = $ob_data_atual->format('d/m/Y');

        return $data_formatada;
    }
}