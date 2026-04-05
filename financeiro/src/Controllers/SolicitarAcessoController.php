<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use src\Models\SolicitarAcesso\SolicitarAcessoEntity;
use src\Models\SolicitarAcesso\SolicitarAcessoDAO;
use src\Models\Usuarios\UsuariosDAO;
use src\Services\AcessoService;

class SolicitarAcessoController extends Controller {
    public function solicitarPrimeiroAcesso()
    {
        $obj_solicitar_acesso = new SolicitarAcessoEntity();
        $model_solicitar_acesso = new SolicitarAcessoDAO();
        $model_solicitar_acesso->iniciarTransacao();

        $obj_solicitar_acesso->nome = $_POST['nome'];
        $obj_solicitar_acesso->login = $_POST['login'];

        $senha_post = $_POST['senha'];
        $obj_solicitar_acesso->senha = md5($senha_post);

        $ret_validacao = (new AcessoService())->validacoesPreInsercao((new UsuariosDAO()), $obj_solicitar_acesso, null);

        if ($ret_validacao['result'] == false) {
            $array_retorno = array(
                'result'   => false,
                'mensagem' => $ret_validacao['mensagem']
            );

            echo json_encode($array_retorno);
            exit;
        }

        $data_hora_atual = date('Y-m-d H:i:s');

        $hash_in_conteudo = $obj_solicitar_acesso->nome . $obj_solicitar_acesso->login . $senha_post . $data_hora_atual;
        $hash_in_md5 = md5($hash_in_conteudo);
        $obj_solicitar_acesso->hashIn = $hash_in_md5;
        $obj_solicitar_acesso->dataHoraSolicitacao = $data_hora_atual;

        try {
            $ret = $model_solicitar_acesso->cadastrarSolicitarAcesso($obj_solicitar_acesso);

            $novo_id = $ret['result'];

            if ($novo_id < 1) {
                throw new Exception($this->msg_retorno_falha);
            }

            $hash_out_conteudo = $novo_id . $hash_in_md5;
            $hash_out_md5 = md5($hash_out_conteudo);

            $model_solicitar_acesso->atualizarHashOut($novo_id, $hash_out_md5);

             $array_retorno = array(
                'result'   => true,
                'mensagem' => 'Solicitação de acesso realizada com sucesso. Anote o código abaixo para a próxima etapa.',
                'codigo'   => $obj_solicitar_acesso->hashIn
            );

            if (empty($ret)) {
                throw new Exception($this->msg_retorno_falha);
            }

            $array_retorno = array(
                'result'   => $ret['result'],
                'mensagem' => $this->msg_retorno_sucesso
            );

            $model_solicitar_acesso->finalizarTransacao();

            echo json_encode($array_retorno);
            exit;
        } catch (Exception $e) {
            $array_retorno = array(
                'result'   => false,
                'mensagem' => $e->getMessage()
            );

            $$model_solicitar_acesso->cancelarTransacao();

            echo json_encode($array_retorno);
            exit;
        }
    }

    public function index()
    {
        $this->view->settings = [
            'action'     => $this->index_route . '/cad-solicitar-acesso',
            'redirect'   => $this->index_route,
            'title'      => 'Solicitar Acesso'
        ];

        $this->renderPage(
            conteudo: 'solicitar_acesso'
        );
    }
}