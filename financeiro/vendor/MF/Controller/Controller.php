<?php

namespace MF\Controller;

use src\Diretorio;
use src\Models\System\LembretesDAO;
use src\Models\System\LembretesEntity;

class Controller {

	public string $index_route = '';

	public function __construct(
		public $view =  new \stdClass() /*conteudo, base_interna, route, titulo, text*/, 
		public string $empresa = 'Bog Finanças'
	) {
		if (isset($_SERVER)) {
			$this->index_route = 'http://' . $_SERVER['HTTP_HOST'];
		}
	}

	protected function renderInModal($titulo, $conteudo)
	{
		$this->view->conteudo = $conteudo;
		$this->view->titulo = $titulo;

		ob_start();
			$this->carregarConteudo();
			$resultado = ob_get_contents();
		ob_end_clean();

		echo $resultado;
		exit;
	}

	protected function renderPage($main_route, $conteudo, $base_interna = '')
	{	
		$lembretes = (new LembretesDAO())->selectAll(new LembretesEntity);
		$this->view->lembretes = $lembretes;

		$this->view->conteudo = $conteudo;
		$this->view->base_interna = $base_interna;
		$this->view->route = $main_route;

		if ($this->isAjaxRequest()) {
			ob_start();

			include (Diretorio::diretorio . 'financeiro/vendor/MF/View/DataExtract.php');
			
			if ($base_interna == '') {
				$this->carregarConteudo();
				$resultado = ob_get_contents();
			} elseif ($base_interna != '' && file_exists(Diretorio::getDiretorio() . "/Views/Principais/$base_interna.phtml")) {
				require_once (Diretorio::getDiretorio() . "/Views/Principais/$base_interna.phtml");
				$resultado = ob_get_contents();
			} else {
				$this->renderNullPage();
				$resultado = ob_get_contents();
			}

			ob_end_clean();
			echo json_encode($resultado);
			exit;
		} else {
			require_once (Diretorio::getDiretorio() . '/Views/Principais/base_externa.phtml');
		}
	}

	protected function renderLoginPage()
	{
		require_once (Diretorio::getDiretorio() . '/Views/Principais/base_login.phtml');
	}

	public function renderNullPage()
	{
		require_once (Diretorio::getDiretorio() . '/Views/Principais/null_page.phtml');
	}

	protected function isSetPost()
	{
		if (isset($_POST) && !empty($_POST))
			return true;

		return false;
	}

	protected function isAjaxRequest()
	{
		if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest')
			return true;

		return false;
	}

	protected function renderizarModalAlerta($titulo = '', $texto = '')
	{
		$this->view->titulo = $titulo;

		$this->view->texto = $texto;

		$this->contentModal();

		echo "<script>
				window.addEventListener('load', abrirModalAlerta);
				function abrirModalAlerta() {
					$('#id-modal-alerta').modal('show');
				}
			</script>";
	}
	
	protected function carregarConteudo()
	{
		$classe_atual = get_class($this);
		$classe_atual = str_replace('src\Controllers\\', '', $classe_atual);
		$classe_atual = str_replace('Controller', '', $classe_atual);
		$classe_atual = lcfirst($classe_atual);

		include (Diretorio::diretorio . 'financeiro/vendor/MF/View/DataExtract.php');
		require_once (Diretorio::getDiretorio() . '/Views/' . $classe_atual . '/' . $this->view->conteudo . '.phtml');
	}

	protected function carregarBaseInterna()
	{
		include (Diretorio::diretorio . 'financeiro/vendor/MF/View/DataExtract.php');
		require_once (Diretorio::getDiretorio() . '/Views/Principais/' . $this->view->base_interna . '.phtml');
	}

	protected function contentModal()
	{
		include (Diretorio::diretorio . 'financeiro/vendor/MF/View/DataExtract.php');
		require_once (Diretorio::getDiretorio() . '/Views/Principais/modal_alerta.phtml');
	}

    protected function renderSimple($conteudo)
	{
		$this->view->conteudo = $conteudo;

        ob_start();
            $this->carregarConteudo();
            $resultado = ob_get_contents();
        ob_end_clean();
        echo $resultado;
        exit;
	}
}