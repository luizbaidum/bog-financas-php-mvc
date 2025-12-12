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
        'Cadastros', 'Consultas', 'Configs', 'Lembrar'
    ];

    public array $titles = [
        'Cadastros' => [
            'Categorias', 'Movimentos', 'Contas Investimentos', 'Movimentos Mensais', 'Objetivos Invest.', 'Orçamento', 'Orçamento do Realizado', 'Movimento entre Investimentos', 'Metas Mensais'
        ],
        'Consultas' => [
            'Indicadores', 'Investimentos', 'Extrato Contas Invest', 'Orçamento', 'Evolução Rendimentos', 'Extrato por Proprietário', 'Metas Mensais'
        ],
        'Configs' => [
            'Preferencias', 'Proprietarios', 'Usuarios'
        ],
        'Lembrar' => [
            'Despesas'
        ]
    ];

    public array $routes = [
        'Cadastros' => [
            'categorias', 'movimentos', 'investimentos', 'movimentos-mensais-index', 'objetivos', 'orcamento', 'orcamento_do_realizado', 'investimentos-movimentar', 'metas-mensais'
        ],
        'Consultas' => [
            'indicadores-index', 'contas-investimentos-index', 'extrato-investimentos', 'orcamento_index', 'evolucao_rendimentos', 'extrato-proprietarios', 'metas-mensais-index'
        ],
        'Configs' => [
            'preferencias', 'proprietarios', 'usuarios'
        ],
        'Lembrar' => [
            'lembrar-despesas'
        ]
    ];

    public array $niveis = [
        'Cadastros' => [],
        'Consultas' => [],
        'Configs'   => []
    ];
}
?>