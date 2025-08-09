<?php

namespace src\Controllers;

use MF\API\GitHub;
use MF\Controller\Controller;
use src\Models\Usuarios\UsuariosDAO;

class LoginController extends Controller {
    public function telaLogin()
    {
        $this->view->settings = [
            'action'              => $this->index_route . '/login',
            'title'               => 'Login - Bog Finanças',
            'url_primeiro_acesso' => $this->index_route . '/primeiro-acesso',
        ];

        $git_hub_infos = $this->getGitHubInfos();

        $this->view->settings['sys_version'] = $git_hub_infos['sys_version'];
        $this->view->settings['sys_version_msg'] = $git_hub_infos['release_name'];

        $this->renderLoginPage();
    }

    public function executarLogin()
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

        $this->view->settings['action'] = '/login';
        $this->view->settings['url_primeiro_acesso'] = $this->index_route . '/primeiro-acesso';

        $git_hub_infos = $this->getGitHubInfos();

        $this->view->settings['sys_version'] = $git_hub_infos['sys_version'];
        $this->view->settings['sys_version_msg'] = $git_hub_infos['release_name'];

        $this->renderLoginPage();
        exit;
    }

    private function getGitHubInfos()
    {
        $ret = [];

        $get_tag = (new GitHub())->getRepoRelease();

        $ret['sys_version'] = '>= v2.07';
        $ret['release_name'] = '';
        if ($get_tag['status'] == '200' || $get_tag['status'] == '201') {
            $ret['sys_version'] = $get_tag['tag'];
            $ret['release_name'] = $get_tag['name'];
        }

        return $ret;
    }
}