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
			// $pesquisa = '';
			// if (!empty($_POST['pesquisa']) && trim($_POST['pesquisa'], ' ') != '') {
			// 	$pesquisa = trim($_POST['pesquisa'], ' ');
			// }

			$model_movimentos = new MovimentosDAO();
			$buttons = new SetButtons();

			$saldos_anteriores = array();

			if (isset($_POST['mesFiltro']) && !empty($_POST['mesFiltro'])) {
				$movimentos = $model_movimentos->indexTable('', $_POST['mesFiltro']);
			} else {
				$movimentos = $model_movimentos->indexTable('');
				$saldos_anteriores = $model_movimentos->getSaldoPassado();
			}

			$this->view->settings = [
				'url_edit'   => $this->index_route . '/edit_movimentos?idMovimento=',
				'redirect'   => $this->index_route . '/home'
			];

			// if ($pesquisa != '') {
			// 	$saldos_anteriores = array();
			// }

			$buttons->setButton(
				'Del',
				$this->index_route . '/delete_movimentos',
				'px-2 btn btn-danger'
			);

			$this->view->buttons = $buttons->getButtons();
			$this->view->data['movimentos'] = $movimentos;
			$this->view->data['saldos_anteriores'] = $saldos_anteriores;

			$this->renderPage(main_route: $this->index_route, conteudo: 'home');
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
}