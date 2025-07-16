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
        'Cadastros', 'Consultas', 'Preferencias'
    ];

    public array $titles = [
        'Cadastros' => [
            'Categorias', 'Movimentos', 'Contas Investimentos', 'Movimentos Mensais', 'Objetivos', 'Orçamento', 'Orçamento do Realizado', 'Movimento entre Investimentos', 'Proprietarios'
        ],
        'Consultas' => [
            'Indicadores', 'Contas Investimentos', 'Extrato Contas Invest', 'Orçamento', 'Evolução Rendimentos', 'Extrato por Proprietário'
        ],
        'Preferencias' => [
            'Preferencias'
        ]
    ];

    public array $routes = [
        'Cadastros' => [
            'categorias', 'movimentos', 'investimentos', 'movimentos-mensais-index', 'objetivos', 'orcamento', 'orcamento_do_realizado', 'investimentos-movimentar', 'proprietarios'
        ],
        'Consultas' => [
            'indicadores_index', 'contas_investimentos_index', 'extrato_investimentos', 'orcamento_index', 'evolucao_rendimentos', 'extrato-proprietarios'
        ],
        'Preferencias' => [
            'preferencias'
        ]
    ];

    public array $niveis = [
        'Cadastros'    => [],
        'Consultas'    => [],
        'Preferencias' => []
    ];
}
?>