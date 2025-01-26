<?php

namespace src\Controllers;

use MF\Controller\Controller;
use src\Models\Usuarios\UsuariosDAO;

class LoginController extends Controller {

    public function login()
    {
        if ($this->isSetPost()) {
            try {
                if (!empty($_POST['login']) && !empty($_POST['senha'])) {
                    $_SESSION['logado'] = false;
    
                    $model_usuarios = new UsuariosDAO();
                    $logon = $model_usuarios->idUsuarioPorLoginSenha($_POST);

                    if (!$logon) {
                        $_GET['incorrect'] = 'true';
                        $this->logout();
                    }
    
                    if (count($logon) > 1) {
                        $this->view->data['mensagem'] = 'Erro ao realizar login. Favor entrar em contato com suporte.';
                        $this->renderNullPage();
                    }
    
                    if (!empty($logon) && count($logon) == 1) {
                        $_SESSION['user'] = $logon[0]['idUsuario'];
                        $_SESSION['nivel'] = $logon[0]['nivel'];
                        $_SESSION['id_familia'] = $logon[0]['idFamilia'];
                        $_SESSION['logado'] = true;
    
                        header('Location: /home');
                    } else {
                        $this->renderizarModalAlerta('Atenção!', 'Usuário ou senha incorretos.');
                    }
                } else {
                    $this->renderizarModalAlerta('Atenção!', 'Usuário ou senha incorretos.');
                }
            } catch (\Exception $e) {
                $array_retorno = array(
					'result' => false,
					'mensagem' => $e->getMessage(),
				);

				echo json_encode($array_retorno);
            }
        }

        $this->view->settings = [
            'action' => $this->index_route,
            'title'  => 'Teste - Entre'
        ];

        $this->renderLoginPage();
    }

    public function logout()
    {
        session_destroy();

        if (isset($_GET['erro']))
            $this->view->data['mensagem'] = 'Faça login primeiro.';

        if (isset($_GET['logout']))
            $this->view->data['mensagem'] = 'Você está desconectado.';

        if (isset($_GET['incorrect']))
            $this->view->data['mensagem'] = 'Usuário e/ou senha incorreto(s).';

        $this->view->settings['action'] = '/';

        $this->renderLoginPage();
    }
}