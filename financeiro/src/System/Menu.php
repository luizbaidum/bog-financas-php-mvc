<?php

namespace src\System;

use MF\Controller\Controller;

class Menu {
    public function getUrlLogout()
    {
        return (new Controller())->index_route . '/logout?logout=true';
    }

    public function getUrlHome()
    {
        return (new Controller())->index_route . '/home';
    }

    public array $grupos = [
        'Preferencias', 'Cadastros', 'Consultas'
    ];

    public array $titles = [
        'Preferencias' => [
            'Preferencias'
        ],
        'Cadastros' => [
            'Categorias', 'Movimentos', 'Contas Invest', 'Movimentos Mensais', 'Objetivos', 'Orçamento', 'Orçamento do Realizado', 'Movimento entre Investimentos', 'Proprietarios'
        ],
        'Consultas' => [
            'Indicadores', 'Lista Contas Invest', 'Extrato Contas Invest', 'Orçamento', 'Evolução Rendimentos'
        ]
    ];

    public array $routes = [
        'Preferencias' => [
            'preferencias'
        ],
        'Cadastros' => [
            'categorias', 'movimentos', 'investimentos', 'movimentos_mensais_index', 'objetivos', 'orcamento', 'orcamento_do_realizado', 'investimentos_movimentar', 'proprietarios'
        ],
        'Consultas' => [
            'indicadores_index', 'contas_investimentos_index', 'extrato_investimentos', 'orcamento_index', 'evolucao_rendimentos'
        ]
    ];

    public array $niveis = [
        'Preferencias' => [],
        'Cadastros'    => [],
        'Consultas'    => []
    ];
}
?>