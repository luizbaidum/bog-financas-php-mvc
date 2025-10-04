<?php 

namespace MF\Init;

use MF\Controller\Controller;
use src\Controllers\PrimeiroAcessoController;

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
        $this->validarLogado();
        $this->validarExisteFamilia();

        $route = $this->findMatchingRoute($url);

        if (!$route) {
            $this->renderAcessoNegado('Acesso negado.');
            return;
        }

        $this->executeRoute($route);
    }

    private function findMatchingRoute($url)
    {
        foreach ($this->getRoutes() as $value) {
            if ($url == $value['route'] || $url == ($value['route'] . '/')) {
                return $value;
            }
        }

        return null;
    }

    private function executeRoute($route)
    {
        $class = 'src\\Controllers\\' . ucfirst($route['controller']);

        if (!class_exists($class)) {
            $this->renderAcessoNegado('Controller não encontrado.');
            return;
        }

        $controller = new $class;

        if (!$this->hasAccess($route, $controller)) {
            $this->renderAcessoNegado('Acesso negado.');
            return;
        }

        $action = $route['action'];

        if (!method_exists($controller, $action)) {
            $this->renderAcessoNegado('Ação não encontrada.');
            return;
        }

        $controller->$action();
    }

    private function hasAccess($route)
    {
        $nivel_usuario = $_SESSION['nivel'] ?? null;
        $index_route = $this->getIndexRoute($route['controller']);
        $acesso_pagina = true;

        if ($nivel_usuario != "a" && $acesso_pagina !== true) {
            return $acesso_pagina == $nivel_usuario;
        }

        return true;
    }

    private function renderAcessoNegado($mensagem)
    {
        $class = 'MF\\Controller\\Controller';
        $controller = new $class;
        $controller->view->data['mensagem'] = $mensagem;
        $controller->renderNullPage();
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

    private function validarLogado()
    {
        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != '/primeiro-acesso' && 
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != '/cad-primeiro-acesso' && 
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != '/logout' && 
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != '/' && 
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != '/login' && 
            (empty($_SESSION) || !isset($_SESSION['logado']) || !$_SESSION['logado'])) {
                header ('location: logout?erro=true');
        }
    }

    private function validarExisteFamilia()
    {
        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != '/logout' 
            && !Controller::isAjaxRequest() 
            && !empty($_SESSION['logado']) 
            && empty($_SESSION['id_familia'])
        ) {
            (new PrimeiroAcessoController())->primeiraFamilia();
        }
    }
}