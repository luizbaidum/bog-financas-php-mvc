<?php
namespace src\Controllers;

use MF\Controller\Controller;
use src\Models\Usuarios\UsuariosDAO;

class UsuariosController extends Controller {
    private $idFamilia;

    function __construct() 
    {
        $this->getIdFamiliaActualUser();
        parent::__construct();
    }

    private function getIdFamiliaActualUser()
    {
        $id_usuario = $_SESSION['user'];

        if (empty($_SESSION['user'])) {
            $this->renderNullPage();
            exit;
        }

        $select_id_familia = (new UsuariosDAO())->buscarIdFamiliaUsuarioSemSeguranca($id_usuario);

        if ($select_id_familia == $_SESSION['id_familia']) {
            $this->idFamilia = $select_id_familia;
        } else {
            $this->renderNullPage();
            exit;
        }
    }

    public function index()
    {
        var_dump($this->idFamilia);
        $this->renderPage(
            conteudo: 'usuarios'
        );
    }
}