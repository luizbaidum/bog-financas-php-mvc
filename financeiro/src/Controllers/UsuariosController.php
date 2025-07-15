<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use src\Models\Usuarios\UsuariosDAO;
use src\Models\Usuarios\UsuariosEntity;

class UsuariosController extends Controller {
    private $idFamilia;
    private $isGestor = false;

    function __construct() 
    {
        $this->getIdFamiliaActualUser();
        parent::__construct();
    }

    private function getIdFamiliaActualUser()
    {
        $model_usuarios = new UsuariosDAO();
        $id_usuario = $_SESSION['user'];

        if (empty($_SESSION['user'])) {
            $this->renderNullPage();
            exit;
        }

        $select_id_familia = $model_usuarios->buscarIdFamiliaUsuarioSemSeguranca($id_usuario);
        $is_gestor = $model_usuarios->detalhar($id_usuario)[0]['gestor'] == 'T' ? true : false;

        if ($select_id_familia == $_SESSION['id_familia']) {
            $this->idFamilia = $select_id_familia;
            $this->isGestor = $is_gestor;
        } else {
            $this->renderNullPage();
            exit;
        }
    }

    public function index()
    {
        $this->view->settings = [
            'action'     => '',
            'redirect'   => $this->index_route . '/indicadores_index',
            'title'      => 'Família/Usuários',
            'url_search' => $this->index_route . '/indicadores_index',
            'is_gestor'  => $this->isGestor
        ];

        $usuarios = (new UsuariosDAO())->selectAll(new UsuariosEntity, [], [], []);

        $this->view->data['lista_usuarios'] = $usuarios;

        $this->renderPage(
            conteudo: 'usuarios'
        );
    }

    public function cadastrarUsuario()
    {
        $obj_usuario = new UsuariosEntity();

        $obj_usuario->nome = $_POST['nome'];
        $obj_usuario->login = $_POST['login'];
        $obj_usuario->senha = $_POST['senha'];
        $senha_confirmar = $_POST['confirmaSenha'];

        if ($obj_usuario->senha != $senha_confirmar) {
            $array_retorno = array(
                'result'   => false,
                'mensagem' => 'As senhas não conferem.'
            );

            echo json_encode($array_retorno);
        }

        try {
            if (empty($ret)) {
                throw new Exception($this->msg_retorno_falha);
            }

            $array_retorno = array(
                'result'   => $ret['result'],
                'mensagem' => $this->msg_retorno_sucesso
            );

            echo json_encode($array_retorno);
        } catch (Exception $e) {
            $array_retorno = array(
                'result'   => false,
                'mensagem' => $e->getMessage()
            );

            echo json_encode($array_retorno);
        }
    }
}