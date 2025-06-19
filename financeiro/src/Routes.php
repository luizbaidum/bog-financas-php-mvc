<?php

namespace src;

use MF\Init\Bootstrap;

class Routes extends Bootstrap {

	public function initRoutes()
	{
        $routes[] = array('route' => '/home', 'controller' => 'HomeController', 'action' => 'home');

        $routes[] = array('route' => '/', 'controller' => 'LoginController', 'action' => 'login');
        $routes[] = array('route' => '/logout', 'controller' => 'LoginController', 'action' => 'logout');

        $routes[] = array('route' => '/cad-mov-mensal', 'controller' => 'MovimentosMensaisController', 'action' => 'lancarMovimentoMensalComoMovimento');
        $routes[] = array('route' => '/buscaMovMensal', 'controller' => 'MovimentosMensaisController', 'action' => 'buscarMovMensal');
        $routes[] = array('route' => '/movimentos-mensais-index', 'controller' => 'MovimentosMensaisController', 'action' => 'index');
        $routes[] = array('route' => '/movimentos-mensais', 'controller' => 'MovimentosMensaisController', 'action' => 'movimentosMensais');
        $routes[] = array('route' => '/cad_movimentos_mensais', 'controller' => 'MovimentosMensaisController', 'action' => 'cadastrarMovimentosMensais');
        $routes[] = array('route' => '/edit-mov-mensal', 'controller' => 'MovimentosMensaisController', 'action' => 'editarMovimentoMensal');

        $routes[] = array('route' => '/consultar_objetivos', 'controller' => 'InvestimentosController', 'action' => 'consultarObjetivos');
        $routes[] = array('route' => '/contas_investimentos_index', 'controller' => 'InvestimentosController', 'action' => 'index');
        $routes[] = array('route' => '/definir_movimento_investimento', 'controller' => 'InvestimentosController', 'action' => 'definirMovimentoDoInvestimento');
        $routes[] = array('route' => '/editar_objetivo', 'controller' => 'InvestimentosController', 'action' => 'editarObjetivo');
        $routes[] = array('route' => '/investimentos', 'controller' => 'InvestimentosController', 'action' => 'investimentos');
        $routes[] = array('route' => '/cad_investimentos', 'controller' => 'InvestimentosController', 'action' => 'cadastrarInvestimentos');
        $routes[] = array('route' => '/objetivos', 'controller' => 'InvestimentosController', 'action' => 'objetivos');
        $routes[] = array('route' => '/cad_objetivos', 'controller' => 'InvestimentosController', 'action' => 'cadastrarObjetivos');
        $routes[] = array('route' => '/investimentos_movimentar', 'controller' => 'InvestimentosController', 'action' => 'movimentarInvestimentos');

        $routes[] = array('route' => '/exibir_resultado', 'controller' => 'MovimentosController', 'action' => 'exibirResultados');
        $routes[] = array('route' => '/movimentos', 'controller' => 'MovimentosController', 'action' => 'index');
        $routes[] = array('route' => '/edit_movimento', 'controller' => 'MovimentosController', 'action' => 'editarMovimento');
        $routes[] = array('route' => '/delete_movimentos', 'controller' => 'MovimentosController', 'action' => 'deletarMovimento');
        $routes[] = array('route' => '/cad_movimentos', 'controller' => 'MovimentosController', 'action' => 'cadastrarMovimentos');
        $routes[] = array('route' => '/exibir_observacao', 'controller' => 'MovimentosController', 'action' => 'exibirObs');

        $routes[] = array('route' => '/buscar_orcamento_do_realizado', 'controller' => 'OrcamentoController', 'action' => 'buscarOrcamentoDoRealizado');
        $routes[] = array('route' => '/orcamento_index', 'controller' => 'OrcamentoController', 'action' => 'index');
        $routes[] = array('route' => '/delete_itens_orcamento', 'controller' => 'OrcamentoController', 'action' => 'deletarItensOrcamento');
        $routes[] = array('route' => '/orcamento', 'controller' => 'OrcamentoController', 'action' => 'orcamento');
        $routes[] = array('route' => '/orcamento_do_realizado', 'controller' => 'OrcamentoController', 'action' => 'orcamentoDoRealizado');
        $routes[] = array('route' => '/cad_orcamento', 'controller' => 'OrcamentoController', 'action' => 'cadastrarOrcamento');
        $routes[] = array('route' => '/cad_orcamento_do_realizado', 'controller' => 'OrcamentoController', 'action' => 'cadastrarOrcamentoDoRealizado');

        $routes[] = array('route' => '/preferencias', 'controller' => 'PreferenciasController', 'action' => 'index');
        $routes[] = array('route' => '/salvar_preferencias', 'controller' => 'PreferenciasController', 'action' => 'editarPreferencia');
        $routes[] = array('route' => '/nova_preferencia', 'controller' => 'PreferenciasController', 'action' => 'cadastrarPreferencia');

        $routes[] = array('route' => '/evolucao_rendimentos', 'controller' => 'RendimentosController', 'action' => 'index');
        $routes[] = array('route' => '/cadastrar_rendimento', 'controller' => 'RendimentosController', 'action' => 'cadastrarRendimento');

        $routes[] = array('route' => '/extrato_investimentos', 'controller' => 'ExtratoInvestimentosController', 'action' => 'index');

        $routes[] = array('route' => '/indicadores_index', 'controller' => 'IndicadoresController', 'action' => 'index');

        $routes[] = array('route' => '/categorias', 'controller' => 'CategoriasController', 'action' => 'categorias');
        $routes[] = array('route' => '/cad_categorias', 'controller' => 'CategoriasController', 'action' => 'cadastrarCategorias');

        $routes[] = array('route' => '/proprietarios', 'controller' => 'ProprietariosController', 'action' => 'proprietarios');
        $routes[] = array('route' => '/cad_proprietarios', 'controller' => 'ProprietariosController', 'action' => 'cadastrarProprietarios');

		$this->setRoutes($routes);
	}
}