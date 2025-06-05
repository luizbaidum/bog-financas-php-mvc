<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use src\Models\Movimentos\MovimentosDAO;
use MF\View\SetButtons;

class HomeController extends Controller {

	public function home()
	{
		try {
			$ano_filtro = $_GET['anoFiltro'] ?? '';
			$mes_filtro = $_GET['mesFiltro'] ?? '';
			$pesquisa = $_GET['pesquisa'] ?? '';
			if ($pesquisa != '') {
				$pesquisa = trim($pesquisa, ' ');
			}

			$model_movimentos = new MovimentosDAO();
			$buttons = new SetButtons();

			$saldos_anteriores = array();

			if ($mes_filtro != '' || $pesquisa != '') {
				$movimentos = $model_movimentos->indexTable($pesquisa, $ano_filtro, $mes_filtro);
                $mov_investimentos = $model_movimentos->indexTableInvestimentos($pesquisa, $ano_filtro, $mes_filtro);
			} else {
				$movimentos = $model_movimentos->indexTable('');
				$saldos_anteriores = $model_movimentos->getSaldoPassado();
                $mov_investimentos = $model_movimentos->indexTableInvestimentos();
			}

			$this->view->settings = [
				'url_edit'   => $this->index_route . '/movimentos?action=edit&idMovimento=',
				'redirect'   => $this->index_route . '/home',
				'url_search' => $this->index_route . '/home',
			];

			$buttons->setButton(
				'Del',
				$this->index_route . '/delete_movimentos',
				'px-2 btn btn-danger action-delete'
			);

			$buttons->setButton(
				'Ver demonstrativo',
				$this->index_route . '/exibir_resultado?anoFiltro=' . $ano_filtro . '&mesFiltro=' . $mes_filtro,
				'px-2 btn btn-info render-ajax',
                'true'
			);

			$this->view->buttons = $buttons->getButtons();
			$this->view->data['movimentos'] = $movimentos;
			$this->view->data['saldos_anteriores'] = $saldos_anteriores;
            $this->view->data['mov_investimentos'] = $mov_investimentos;
            $this->view->data['url_obs'] = $this->index_route . '/exibir_observacao?idMovimento=';

			$this->renderPage(main_route: $this->index_route, conteudo: 'home');
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
}