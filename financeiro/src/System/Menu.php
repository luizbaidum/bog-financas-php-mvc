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
        'Cadastros', 'Consultas', 'Configs'
    ];

    public array $titles = [
        'Cadastros' => [
            'Categorias', 'Movimentos', 'Contas Investimentos', 'Movimentos Mensais', 'Objetivos', 'Orçamento', 'Orçamento do Realizado', 'Movimento entre Investimentos'
        ],
        'Consultas' => [
            'Indicadores', 'Contas Investimentos', 'Extrato Contas Invest', 'Orçamento', 'Evolução Rendimentos', 'Extrato por Proprietário'
        ],
        'Configs' => [
            'Preferencias', 'Proprietarios', 'Usuarios'
        ]
    ];

    public array $routes = [
        'Cadastros' => [
            'categorias', 'movimentos', 'investimentos', 'movimentos-mensais-index', 'objetivos', 'orcamento', 'orcamento_do_realizado', 'investimentos-movimentar',
        ],
        'Consultas' => [
            'indicadores_index', 'contas_investimentos_index', 'extrato_investimentos', 'orcamento_index', 'evolucao_rendimentos', 'extrato-proprietarios'
        ],
        'Configs' => [
            'preferencias', 'proprietarios', 'usuarios'
        ]
    ];

    public array $niveis = [
        'Cadastros'    => [],
        'Consultas'    => [],
        'Configs' => []
    ];
}
?>