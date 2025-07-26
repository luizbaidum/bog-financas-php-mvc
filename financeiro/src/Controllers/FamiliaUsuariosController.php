<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use src\Models\Familia\FamiliaDAO;
use src\Models\Familia\FamiliaEntity;
use src\Models\Usuarios\UsuariosDAO;
use src\Models\Usuarios\UsuariosEntity;

class FamiliaUsuariosController extends Controller {
    public function index()
    {
        $id_usuario = $_SESSION['user'];
        $id_familia = $_SESSION['id_familia'];

        $model_usuarios = new UsuariosDAO();

        $nome_familia = (new FamiliaDAO())->consultarNomeFamilia($id_familia);
        $is_gestor = $model_usuarios->detalhar($id_usuario)[0]['gestor'] == 'T' ? true : false;

        $select_id_familia = $model_usuarios->buscarIdFamiliaUsuarioSemSeguranca($id_usuario);

        if ($id_familia != $select_id_familia) {
            $this->renderNullPage();
            exit;
        }

        $this->view->settings = [
            'action'     => $this->index_route . '/cad-usuario',
            'redirect'   => $this->index_route . '/usuarios',
            'title'      => 'Família/Usuários',
            'is_gestor'  => $is_gestor
        ];

        $usuarios = (new UsuariosDAO())->selectAll(new UsuariosEntity, [], [], []);

        $this->view->data['lista_usuarios'] = $usuarios;
        $this->view->data['familia'] = $nome_familia;

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

    public function cadastrarFamilia()
    {
        try {
            $id_usuario = $_SESSION['user'];

            $familia_obj = new FamiliaEntity();
            $familia_obj->nomeFamilia = $_POST['nomeFamilia'];

            $novo_id_familia = (new FamiliaDAO())->cadastrarFamilia(new FamiliaEntity, $familia_obj)['result'] ?? 0;

            $_SESSION['id_familia'] = $novo_id_familia;

            $ret = (new UsuariosDAO())->atualizar(
                new UsuariosEntity, 
                [
                    'idFamilia' => $novo_id_familia,
                    'gestor'    => 'T'
                ],
                ['idUsuario' =>  $id_usuario]
            );

            if (empty($ret['result'])) {
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