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
        'Cadastros', 'Consultas'
    ];

    public array $titles = [
        'Cadastros' => [
            'Categorias', 'Movimentos', 'Contas Invest', 'Movimentos Mensais', 'Objetivos', 'Orçamento', 'Importar Orçamento'
        ],
        'Consultas' => [
            'Indicadores', 'Lista Contas Invest', 'Extrato Contas Invest', 'Movimentos Mensais', 'Orçamento', 'Evolução Rendimentos'
        ]
    ];

    public array $routes = [
        'Cadastros' => [
            'categorias', 'movimentos', 'investimentos', 'movimentos_mensais', 'objetivos', 'orcamento', 'importar_orcamento'
        ],
        'Consultas' => [
            'indicadores_index', 'contas_investimentos_index', 'contas_investimentos_extrato', 'movimentos_mensais_index', 'orcamento_index', 'evolucao_rendimentos'
        ]
    ];

    public array $niveis = [
        'Cadastros' => [],
        'Consultas' => []
    ];
}
?>