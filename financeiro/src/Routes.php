<?php

namespace src;

use MF\Init\Bootstrap;

class Routes extends Bootstrap {

	public function initRoutes()
	{
		$routes[] = array('route' => '/cad_categorias', 'controller' => 'CadastrosController', 'action' => 'cadastrarCategorias');
		$routes[] = array('route' => '/cad_investimentos', 'controller' => 'CadastrosController', 'action' => 'cadastrarInvestimentos');
		$routes[] = array('route' => '/cad_mov_mensal', 'controller' => 'CadastrosController', 'action' => 'lancarMovimentoMensal');
		$routes[] = array('route' => '/cad_movimentos', 'controller' => 'CadastrosController', 'action' => 'cadastrarMovimentos');
		$routes[] = array('route' => '/cad_movimentos_mensais', 'controller' => 'CadastrosController', 'action' => 'cadastrarMovimentosMensais');
		$routes[] = array('route' => '/cad_objetivos', 'controller' => 'CadastrosController', 'action' => 'cadastrarObjetivos');
		$routes[] = array('route' => '/cad_orcamento', 'controller' => 'CadastrosController', 'action' => 'cadastrarOrcamento');
		$routes[] = array('route' => '/cad_orcamento_do_realizado', 'controller' => 'CadastrosController', 'action' => 'cadastrarOrcamentoDoRealizado');
		$routes[] = array('route' => '/cadastrar_rendimento', 'controller' => 'CadastrosController', 'action' => 'cadastrarRendimento');
		$routes[] = array('route' => '/categorias', 'controller' => 'CadastrosController', 'action' => 'categorias');
		$routes[] = array('route' => '/investimentos', 'controller' => 'CadastrosController', 'action' => 'investimentos');
		$routes[] = array('route' => '/movimentos_mensais', 'controller' => 'CadastrosController', 'action' => 'movimentosMensais');
		$routes[] = array('route' => '/nova_preferencia', 'controller' => 'CadastrosController', 'action' => 'cadastrarPreferencia');
		$routes[] = array('route' => '/objetivos', 'controller' => 'CadastrosController', 'action' => 'objetivos');
		$routes[] = array('route' => '/orcamento', 'controller' => 'CadastrosController', 'action' => 'orcamento');
		$routes[] = array('route' => '/orcamento_do_realizado', 'controller' => 'CadastrosController', 'action' => 'orcamentoDoRealizado');
        $routes[] = array('route' => '/investimentos_movimentar', 'controller' => 'CadastrosController', 'action' => 'movimentarInvestimentos');

		$routes[] = array('route' => '/home', 'controller' => 'HomeController', 'action' => 'home');

		$routes[] = array('route' => '/', 'controller' => 'LoginController', 'action' => 'login');
		$routes[] = array('route' => '/logout', 'controller' => 'LoginController', 'action' => 'logout');

        $routes[] = array('route' => '/buscar_orcamento_do_realizado', 'controller' => 'OrcamentoController', 'action' => 'buscarOrcamentoDoRealizado');
        $routes[] = array('route' => '/buscaMovMensal', 'controller' => 'MovimentosMensaisController', 'action' => 'buscarMovMensal');
        $routes[] = array('route' => '/consultar_objetivos', 'controller' => 'InvestimentosController', 'action' => 'consultarObjetivos');
        $routes[] = array('route' => '/contas_investimentos_index', 'controller' => 'InvestimentosController', 'action' => 'index');
        $routes[] = array('route' => '/evolucao_rendimentos', 'controller' => 'RendimentosController', 'action' => 'index');
        $routes[] = array('route' => '/exibir_resultado', 'controller' => 'MovimentosController', 'action' => 'exibirResultados');
        $routes[] = array('route' => '/extrato_investimentos', 'controller' => 'ExtratoInvestimentosController', 'action' => 'index');
        $routes[] = array('route' => '/indicadores_index', 'controller' => 'IndicadoresController', 'action' => 'index');
        $routes[] = array('route' => '/movimentos', 'controller' => 'MovimentosController', 'action' => 'index');
        $routes[] = array('route' => '/movimentos_mensais_index', 'controller' => 'MovimentosMensaisController', 'action' => 'index');
        $routes[] = array('route' => '/orcamento_index', 'controller' => 'OrcamentoController', 'action' => 'index');
        $routes[] = array('route' => '/preferencias', 'controller' => 'PreferenciasController', 'action' => 'index');
        $routes[] = array('route' => '/definir_movimento_investimento', 'controller' => 'InvestimentosController', 'action' => 'definirMovimentoDoInvestimento');
        $routes[] = array('route' => '/editar_objetivo', 'controller' => 'InvestimentosController', 'action' => 'editarObjetivo');
        $routes[] = array('route' => '/salvar_preferencias', 'controller' => 'PreferenciasController', 'action' => 'editarPreferencia');
        $routes[] = array('route' => '/edit_movimento', 'controller' => 'MovimentosController', 'action' => 'editarMovimento');
        $routes[] = array('route' => '/delete_movimentos', 'controller' => 'MovimentosController', 'action' => 'deletarMovimento');
        $routes[] = array('route' => '/delete_itens_orcamento', 'controller' => 'OrcamentoController', 'action' => 'deletarItensOrcamento');

		$this->setRoutes($routes);
	}
}