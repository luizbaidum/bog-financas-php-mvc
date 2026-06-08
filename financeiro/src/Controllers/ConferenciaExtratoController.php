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
        $this->view->settings = [
            'action2'  => $this->index_route . '/salvar-conferencia-extrato',
            'redirect' => $this->index_route . '/conferencia-extrato',
        ];

        $dados_formulario = [
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
    
    public function salvarConferenciaExtrato()
    {
        try {
            $conferidos = $_POST['conferidos'] ?? [];

            echo '<pre>';
            print_r($conferidos);
            echo '</pre>';
            exit;

            if (empty($conferidos)) {
                throw new Exception('Nenhum movimento conferido recebido.');
            }

            $dados = [];
            foreach ($conferidos as $id_movimento => $json_extrato) {
                $extrato = json_decode($json_extrato, true);

                if ($extrato == null) {
                    throw new Exception("Dado inválido para o movimento ID {$id_movimento}.");
                }

                $dados[] = [
                    'id_movimento' => (int) $id_movimento,
                    'extrato'      => $extrato,
                ];
            }

            // TODO: persistir $dados no banco de dados

            echo json_encode([
                'result'   => true,
                'mensagem' => 'Conferência salva com sucesso.',
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'result'   => false,
                'mensagem' => $e->getMessage(),
            ]);
        }
    }

    public function consultarConferenciaExtrato()
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

    public function processarConsultarConferenciaExtrato()
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
}