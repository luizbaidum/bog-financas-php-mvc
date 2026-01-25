<?php

namespace src\System;

use MF\Controller\Controller;
use src\Models\Usuarios\UsuariosDAO;

class Menu {
    public array $userInfo;

    public function __construct() 
    {
        $userInfo = (new UsuariosDAO())->detalhar($_SESSION['user']);

        $this->userInfo = [
            'nome'  => $userInfo[0]['nome'],
            'nivel' => $userInfo[0]['gestor'] == 'T' ? 'Gestor' : 'Membro'
        ];
    }

    public function getUrlLogout()
    {
        return (new Controller())->index_route . '/logout?logout=true';
    }

    public function getUrlHome()
    {
        return (new Controller())->index_route . '/home';
    }

    public array $grupos = [
        'Cadastros', 'Consultas', 'Config.', 'Lembrar'
    ];

    public array $titles = [
        'Cadastros' => [
            'Categorias', 'Movimentos', 'Investimentos', 'Movimentos Mensais', 'Objetivos Invest.', 'Orçamento', 'Orçamento do Realizado', 'Movimento entre Investimentos', 'Metas Mensais'
        ],
        'Consultas' => [
            'Indicadores', 'Investimentos', 'Extrato Investimentos', 'Orçamento', 'Evolução Investimentos', 'Extrato por Proprietário', 'Metas Mensais'
        ],
        'Config.' => [
            'Preferências', 'Proprietários', 'Usuários'
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
        'Config.' => [
            'preferencias', 'proprietarios', 'usuarios'
        ],
        'Lembrar' => [
            'lembrar-despesas'
        ]
    ];

    public array $niveis = [
        'Cadastros' => [],
        'Consultas' => [],
        'Config.'   => []
    ];
}
?>