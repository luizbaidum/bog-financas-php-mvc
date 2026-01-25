<?php
    namespace src\System;

    class MonthAndYear {
        private const MONTHS = array(
            '01' => 'Jan',
            '02' => 'Fev',
            '03' => 'Mar',
            '04' => 'Abr',
            '05' => 'Mai',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Ago',
            '09' => 'Set',
            '10' => 'Out',
            '11' => 'Nov',
            '12' => 'Dez',
            'Todos' => 'Todos'
        );

        private const MONTHSREVERSED = array(
            'Jan' => '01',
            'Feb' => '02',
            'Mar' => '03',
            'Apr' => '04',
            'May' => '05',
            'Jun' => '06',
            'Jul' => '07',
            'Aug' => '08',
            'Sep' => '09',
            'Oct' => '10',
            'Nov' => '11',
            'Dec' => '12',
            'Todos' => 'Todos'
        );

        private const YEARS = array('2024', '2025', '2026', '2027', '2028', '2029', '2030');

        public static function getMonths()
        {
            return self::MONTHS;
        }

        public static function getYears()
        {
            return self::YEARS;
        }
    }
?>