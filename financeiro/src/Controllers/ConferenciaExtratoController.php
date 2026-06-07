<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use src\Models\Proprietarios\ProprietariosEntity;
use MF\Model\Model;
use src\Models\Movimentos\MovimentosDAO;
use MF\API\ConferenciaExtratoService;

class ConferenciaExtratoController extends Controller {

    public function index()
    {
        $model = new Model();

        $this->view->settings = [
            'action'   => $this->index_route . '/processar-conferencia-extrato',
            'redirect' => $this->index_route . '/conferencia-extrato',
            'title'    => 'Conferência de Extrato',
            'div'      => 'id-tabela-conferencia'
        ];

        $this->view->data['lista_proprietarios'] = $model->selectAll(new ProprietariosEntity, [['status', '=', '"1"']], [], []);

        $this->renderPage(
            conteudo: 'conferencia_extrato'
        );
    }

    public function processarConferenciaExtrato()
    {
        $dados_formulario = [
            'proprietario' => $_POST['proprietario'],
            'mes_ano' => $_POST['mes_ano'],
            'tipo_arquivo' => $_POST['tipo_arquivo'],
            'banco' => $_POST['banco']
        ];

        $xpl = explode('-', $_POST['mes_ano']);
        $ano_filtro = $xpl[0] ?? '';
        $mes_filtro = $xpl[1] ?? '';

        $arquivo = $_FILES['arquivo'];

        $model_movimentos = new MovimentosDAO();
        $movimentos = $model_movimentos->indexTable('', $ano_filtro, $mes_filtro);

        $service = new ConferenciaExtratoService();
        $resultado = $service->processarConferencia($dados_formulario, $movimentos, $arquivo);

        if ($resultado['sucesso']) {
            $this->view->data['resultado'] = $resultado['dados'];
            $this->view->data['movimentos'] = $movimentos;
            $this->renderSimple('resultado');
        } else {
            echo json_encode(['erro' => $resultado['erro']]);
        }
    }
}