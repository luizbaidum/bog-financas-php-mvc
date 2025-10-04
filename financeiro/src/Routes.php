<?php

namespace src;

use MF\Init\Bootstrap;

class Routes extends Bootstrap {

	public function initRoutes()
	{
        $routes[] = array('route' => '/home', 'controller' => 'HomeController', 'action' => 'home');

        $routes[] = array('route' => '/', 'controller' => 'LoginController', 'action' => 'telaLogin');
        $routes[] = array('route' => '/login', 'controller' => 'LoginController', 'action' => 'executarLogin');
        $routes[] = array('route' => '/logout', 'controller' => 'LoginController', 'action' => 'logout');

        $routes[] = array('route' => '/cad-mov-mensal', 'controller' => 'MovimentosMensaisController', 'action' => 'lancarMovimentoMensalComoMovimento');
        $routes[] = array('route' => '/buscaMovMensal', 'controller' => 'MovimentosMensaisController', 'action' => 'buscarMovMensal');
        $routes[] = array('route' => '/movimentos-mensais-index', 'controller' => 'MovimentosMensaisController', 'action' => 'index');
        $routes[] = array('route' => '/movimentos-mensais', 'controller' => 'MovimentosMensaisController', 'action' => 'movimentosMensais');
        $routes[] = array('route' => '/cad-movimentos-mensais', 'controller' => 'MovimentosMensaisController', 'action' => 'cadastrarMovimentosMensais');
        $routes[] = array('route' => '/edit-mov-mensal', 'controller' => 'MovimentosMensaisController', 'action' => 'editarMovimentoMensal');

        $routes[] = array('route' => '/consultar_objetivos', 'controller' => 'InvestimentosController', 'action' => 'consultarObjetivos');
        $routes[] = array('route' => '/contas-investimentos-index', 'controller' => 'InvestimentosController', 'action' => 'index');
        $routes[] = array('route' => '/definir_movimento_investimento', 'controller' => 'InvestimentosController', 'action' => 'definirMovimentoDoInvestimento');
        $routes[] = array('route' => '/editar_objetivo', 'controller' => 'InvestimentosController', 'action' => 'editarObjetivo');
        $routes[] = array('route' => '/investimentos', 'controller' => 'InvestimentosController', 'action' => 'investimentos');
        $routes[] = array('route' => '/cad_investimentos', 'controller' => 'InvestimentosController', 'action' => 'cadastrarInvestimentos');
        $routes[] = array('route' => '/objetivos', 'controller' => 'InvestimentosController', 'action' => 'objetivos');
        $routes[] = array('route' => '/cad-objetivos', 'controller' => 'InvestimentosController', 'action' => 'cadastrarObjetivos');
        $routes[] = array('route' => '/investimentos-movimentar', 'controller' => 'InvestimentosController', 'action' => 'movimentarInvestimentos');
        $routes[] = array('route' => '/edit-status-investimento', 'controller' => 'InvestimentosController', 'action' => 'editarStatus');
        $routes[] = array('route' => '/edit-status-objetivo', 'controller' => 'InvestimentosController', 'action' => 'editarStatusObjetivo');
        $routes[] = array('route' => '/validar-percentual-uso-json', 'controller' => 'InvestimentosController', 'action' => 'validarPercentualUsoJson');

        $routes[] = array('route' => '/exibir_resultado', 'controller' => 'MovimentosController', 'action' => 'exibirResultados');
        $routes[] = array('route' => '/movimentos', 'controller' => 'MovimentosController', 'action' => 'index');
        $routes[] = array('route' => '/edit_movimento', 'controller' => 'MovimentosController', 'action' => 'editarMovimento');
        $routes[] = array('route' => '/delete_movimentos', 'controller' => 'MovimentosController', 'action' => 'deletarMovimento');
        $routes[] = array('route' => '/cad_movimentos', 'controller' => 'MovimentosController', 'action' => 'cadastrarMovimentos');
        $routes[] = array('route' => '/exibir-detalhes', 'controller' => 'MovimentosController', 'action' => 'exibirDetalhes');

        $routes[] = array('route' => '/buscar-orcamento-do-realizado', 'controller' => 'OrcamentoController', 'action' => 'buscarOrcamentoDoRealizado');
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
        $routes[] = array('route' => '/cadastrar-rendimento', 'controller' => 'RendimentosController', 'action' => 'cadastrarRendimento');

        $routes[] = array('route' => '/extrato_investimentos', 'controller' => 'ExtratoInvestimentosController', 'action' => 'index');

        $routes[] = array('route' => '/indicadores-index', 'controller' => 'IndicadoresController', 'action' => 'index');

        $routes[] = array('route' => '/categorias', 'controller' => 'CategoriasController', 'action' => 'categorias');
        $routes[] = array('route' => '/cad_categorias', 'controller' => 'CategoriasController', 'action' => 'cadastrarCategorias');
        $routes[] = array('route' => '/consultar-categorias-investimentos', 'controller' => 'CategoriasController', 'action' => 'consultarCategoriasInvestimentos');
        $routes[] = array('route' => '/edit-status-categoria', 'controller' => 'CategoriasController', 'action' => 'editarStatus');

        $routes[] = array('route' => '/proprietarios', 'controller' => 'ProprietariosController', 'action' => 'proprietarios');
        $routes[] = array('route' => '/cad_proprietarios', 'controller' => 'ProprietariosController', 'action' => 'cadastrarProprietarios');
        $routes[] = array('route' => '/extrato-proprietarios', 'controller' => 'ProprietariosController', 'action' => 'extratoProprietarios');
        $routes[] = array('route' => '/processar-extrato-proprietarios', 'controller' => 'ProprietariosController', 'action' => 'processarExtratoProprietarios');

        $routes[] = array('route' => '/usuarios', 'controller' => 'FamiliaUsuariosController', 'action' => 'index');
        $routes[] = array('route' => '/cad-usuario', 'controller' => 'FamiliaUsuariosController', 'action' => 'cadastrarUsuario');
        $routes[] = array('route' => '/cad-primeira-familia', 'controller' => 'FamiliaUsuariosController', 'action' => 'cadastrarFamilia');

        $routes[] = array('route' => '/primeiro-acesso', 'controller' => 'PrimeiroAcessoController', 'action' => 'primeiroAcesso');
        $routes[] = array('route' => '/cad-primeiro-acesso', 'controller' => 'PrimeiroAcessoController', 'action' => 'cadastrarPrimeiroAcesso');

		$this->setRoutes($routes);
	}
}