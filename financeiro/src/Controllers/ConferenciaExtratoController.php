<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use src\Models\Proprietarios\ProprietariosEntity;
use MF\Model\Model;

class ConferenciaExtratoController extends Controller {

    public function index()
    {
        $model = new Model();

        $this->view->settings = [
            'action'   => $this->index_route . '/extrato-investimentos',
            'redirect' => $this->index_route . '/extrato-investimentos',
            'title'    => 'Conferência de Extrato',
            'div'      => 'id-tabela-extrato'
        ];

        $this->view->data['lista_proprietarios'] = $model->selectAll(new ProprietariosEntity, [['status', '=', '"1"']], [], []);

        $this->renderPage(
            conteudo: 'conferencia_extrato'
        );
    }

    public function processarConferenciaExtrato()
    {
        $dadosFormulario = [
            'proprietario' => $_POST['proprietario'],
            'mes_ano' => $_POST['mes_ano'],
            'tipo_arquivo' => $_POST['tipo_arquivo']
        ];

        $arquivo = $_FILES['arquivo']; // IMPORTANTE: corrigir o name no form

        $service = new ConferenciaExtratoService($this->conexao);
        $resultado = $service->processarConferencia($dadosFormulario, $arquivo);

        if ($resultado['sucesso']) {
            $this->view->resultado = $resultado['dados'];
            $this->render('resultado', 'layout_ajax');
        } else {
            echo json_encode(['erro' => $resultado['erro']]);
        }
    }
}