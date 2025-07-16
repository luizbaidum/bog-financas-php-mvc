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
            'action'     => $this->index_route . '/cad-usuario',
            'redirect'   => $this->index_route . '/usuarios',
            'title'      => 'Família/Usuários',
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
        $model_usuario = new UsuariosDAO();

        $obj_usuario->nome = $_POST['nome'];
        $obj_usuario->login = $_POST['login'];
        $obj_usuario->senha = $_POST['senha'];
        $senha_confirmar = $_POST['confirmaSenha'];

        $ret_validacao = $this->validacoesPreInsercao($model_usuario, $obj_usuario, $senha_confirmar);

        if ($ret_validacao['result'] == false) {
            $array_retorno = array(
                'result'   => false,
                'mensagem' => $ret_validacao['mensagem']
            );

            echo json_encode($array_retorno);
            exit;
        }

        try {
            $ret = $model_usuario->cadastrar(new UsuariosEntity, $obj_usuario);

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

    private function validacoesPreInsercao(object $model, object $obj_usuario, string|int $senha_confirmar) : array
    {
        $status = true;
        $mensagem = '';

        if ($obj_usuario->senha != $senha_confirmar) {
            $status = false;
            $mensagem = 'As senhas não conferem.';
        }

        if (!empty($model->consultarUsuarioPorLogin($obj_usuario->login))) {
            $status = false;
            $mensagem = 'Por favor, escolher outro login.';
        }

        return ['result' => $status, 'mensagem' => $mensagem];
    }
}