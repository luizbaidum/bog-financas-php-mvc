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

    public static function calcMonth(string|int $month_in, string $operador, string|int $count)
    {
        switch ($operador) {
            case '+':
                $result = $month_in + $count;
                break;
            case '-':
                $result = $month_in - $count;
                break;
            case '*':
                $result = $month_in * $count;
                break;
            case '/':
                if ($count != 0) {
                    $result = $month_in / $count;
                } else {
                    $result = 0;
                }
                break;
            default:
                $result = $month_in;
                break;
        }

        if ($result >= 1 && $result <= 9 && substr($result, 0, 1) != '0') {
            $result = '0' . $result;
        }

        return $result;
    }
}