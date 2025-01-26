<?php 

namespace MF\Init;

use MF\View\Buttons;

abstract class Bootstrap {

	private $routes;

	abstract protected function initRoutes();

	public function __construct()
	{
		$this->initRoutes();
		$this->run($this->getUrlPatch());
	}

	public function getRoutes()
	{
		return $this->routes;
	}

	public function setRoutes(array $routes)
	{
		$this->routes = $routes;
	}

	protected function run($url)
	{
		foreach ($this->getRoutes() as $value) {
			if ($url == $value['route'] || $url == $value['route'] . '/') {

				$class = 'src\\Controllers\\' . ucfirst($value['controller']);
				$controller = new $class;

				$nivel_usuario = $_SESSION['nivel'] ?? NULL;
				$index_route = $this->getIndexRoute($value['controller']);
				//$acesso_pagina = $this->getPageSecurityByRoute($index_route);

				$acesso_pagina = true;

				if ($nivel_usuario != "a" && $acesso_pagina !== true) {
					if ($acesso_pagina != $nivel_usuario) {
						$controller->view->data['mensagem'] = 'Acesso negado.';
						$controller->renderNullPage();
						exit;
					}
				}

				$action = $value['action'];
				$controller->$action();
			}
		}
	}

	protected function getUrlPatch()
	{
		return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	}

	protected function getIndexRoute($controller)
	{
		foreach ($this->getRoutes() as $value) {
			if ($controller == $value['controller'] && "index" == $value['action'])
				return $value["route"];
		}
	}

	protected function getPageSecurityByRoute($route)
	{
		if ($route == NULL || $route == "")
			return true;

		$route = ltrim($route, "/");
	}
}