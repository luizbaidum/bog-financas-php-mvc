<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use src\Models\Proprietarios\ProprietariosEntity;
use MF\Model\Model;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\ConferenciaExtrato\ConferenciaExtratoDAO;
use src\Models\ConferenciaExtrato\ConferenciaExtratoEntity;
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
            'mes_ano'         => $_POST['mes_ano'],
            'tipo_arquivo'    => $_POST['tipo_arquivo'],
            'banco'           => $_POST['banco'],
            'match_data'      => isset($_POST['match_data']) && $_POST['match_data'] == '1',
            'match_valor'     => isset($_POST['match_valor']) && $_POST['match_valor'] == '1',
            'match_descricao' => isset($_POST['match_descricao']) && $_POST['match_descricao'] == '1',
        ];

        $xpl = explode('-', $_POST['mes_ano']);
        $ano_filtro = $xpl[0] ?? '';
        $mes_filtro = $xpl[1] ?? '';

        $arquivo = $_FILES['arquivo'];

        $model_movimentos = new MovimentosDAO();
        $model_conferencia = new ConferenciaExtratoDAO();

        $movimentos = $model_movimentos->indexTable('', $ano_filtro, $mes_filtro);
        $registros_conferidos = $model_conferencia->selectAll(new ConferenciaExtratoEntity, [['dataExtrato', '>=', $_POST['mes_ano'], ['dataExtrato', '<=', $_POST['mes_ano']]], ], [], []);

        $service = new ConferenciaExtratoService();
        $resultado = $service->processarConferencia($dados_formulario, $movimentos, $arquivo);

        if ($resultado['sucesso']) {
            $this->view->data['resultado'] = $resultado['dados'];
            $this->view->data['movimentos'] = $movimentos;
            $this->view->data['registros_conferidos'] = $registros_conferidos;
            $this->renderSimple('resultado');
        } else {
            echo json_encode(['erro' => $resultado['erro']]);
        }
    }
    
    public function salvarConferenciaExtrato()
    {
        if ($this->isSetPost()) {
            try {
                $conferidos = $_POST['conferidos'] ?? [];

                if (empty($conferidos)) {
                    throw new Exception('Nenhum movimento conferido recebido.');
                }

                $model_conferencia = new ConferenciaExtratoDAO();
                $obj_conferencia = new ConferenciaExtratoEntity();
                $model_conferencia->iniciarTransacao();

                $dados = [];
                foreach ($conferidos as $id_movimento => $json_extrato) {
                    $extrato = json_decode($json_extrato, true);

                    if ($extrato == null) {
                        throw new Exception("Dado inválido para o movimento ID {$id_movimento}.");
                    }

                    $obj_conferencia->dataExtrato = $extrato['data'];
                    $obj_conferencia->descricao   = $extrato['descricao'];
                    $obj_conferencia->credito     = $extrato['credito'];
                    $obj_conferencia->debito      = $extrato['debito'];
                    $obj_conferencia->idMovimento = $id_movimento;

                    $ret = $model_conferencia->cadastrar(new ConferenciaExtratoEntity, $obj_conferencia);

                    if (! $ret['result']) {
                        throw new Exception($this->msg_retorno_falha);
                    }
                }

                if ($ret['result']) {
					$array_retorno = array(
						'result'   => $ret['result'],
						'mensagem' => $this->msg_retorno_sucesso
					);

                    $model_conferencia->finalizarTransacao();

					echo json_encode($array_retorno);
				} else {
					throw new Exception($this->msg_retorno_falha);
				}
            } catch (Exception $e) {
               $array_retorno = array(
					'result'   => false,
					'mensagem' => $e->getMessage(),
				);

                $model_conferencia->cancelarTransacao();

				echo json_encode($array_retorno);
            }
        }
    }

    public function consultarConferenciaExtrato()
    {
        $model = new Model();

        $this->view->settings = [
            'action'   => $this->index_route . '/processar-consulta-conferencia-extrato',
            'redirect' => $this->index_route . '/conferencia-extrato',
            'title'    => 'Conferência de Extrato',
            'div'      => 'id-tabela-conferencia'
        ];

        $this->view->data['lista_proprietarios'] = $model->selectAll(new ProprietariosEntity, [['status', '=', '"1"']], [], []);

        $this->renderPage(
            conteudo: 'conferencia_extrato_consulta'
        );
    }

    public function processarConsultarConferenciaExtrato()
    {
        $model_conferencia = new ConferenciaExtratoDAO();
        $model_movimentos = new MovimentosDAO();

        $movimentos = $model_movimentos->indexTable('', $ano_filtro, $mes_filtro);
        $registros_conferidos = $model_conferencia->selectAll(new ConferenciaExtratoEntity, [['dataExtrato', '>=', $_POST['mes_ano'], ['dataExtrato', '<=', $_POST['mes_ano']]], ], [], []);

        $this->view->data['movimentos'] = $movimentos;
        $this->view->data['registros_conferidos'] = $registros_conferidos;

        $this->renderSimple(
            conteudo: 'resultado_consulta'
        );
    }
}