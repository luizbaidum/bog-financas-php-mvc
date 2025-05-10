<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use src\Models\Orcamento\OrcamentoDAO;
use MF\View\SetButtons;
use src\Models\Orcamento\OrcamentoEntity;

class OrcamentoController extends Controller {
    public function index() {
        $model_orcamento = new OrcamentoDAO();
        $buttons = new SetButtons();

        $ano_filtro = $_GET['anoFiltro'] ?? '';
        $mes_filtro = $_GET['mesFiltro'] ?? '';

        if ($mes_filtro != '') {
            $orcamentos = $model_orcamento->orcamentosPorProprietario($ano_filtro, $mes_filtro);
        } else {
            $orcamentos = $model_orcamento->orcamentosPorProprietario();
        }

        $this->view->settings = [
            'action'     => '',
            'redirect'   => $this->index_route . '/orcamento_index',
            'title'      => 'Orçamento',
            'url_search' => $this->index_route . '/orcamento_index'
        ];

        $buttons->setButton(
            'Del',
            $this->index_route . '/delete_itens_orcamento',
            'px-2 btn btn-danger action-delete'
        );

        $this->view->buttons = $buttons->getButtons();
        $this->view->data['orcamentos'] = $orcamentos;

        $this->renderPage(
            main_route: $this->index_route . '/orcamento_index', 
            conteudo: 'orcamento_index'
        );
    }

    public function buscarOrcamentoDoRealizado()
    {
        $model_orcamento = new OrcamentoDAO();

        list($ano_origem, $mes_origem) = explode('-', $_POST['mesAnoOrigem']);

        $lista = $model_orcamento->buscarMediasDespesas($ano_origem, $mes_origem);

        $this->view->data['lista'] = $lista;

        $this->renderSimple('tabela_orcamento_importado');
    }

    public function deletarItensOrcamento()
    {
        if ($this->isSetPost()) {

            $model_orcamento = new OrcamentoDAO();

            try {
                foreach ($_POST['itens'] as $id) {
                    $ret = $model_orcamento->delete(new OrcamentoEntity, 'idOrcamento', $id);

                    if ($ret != false) {
                        $model_orcamento->arr_afetados[] = $id;
                    } else {
                        $model_orcamento->arr_nao_afetados[] = $id;
                    }
                }

                $array_retorno = array(
					'result'   => true,
					'mensagem' => 'Orçamentos excluídos: ' . implode(', ', $model_orcamento->arr_afetados) . '. Orçamentos não excluídos: ' . implode(', ', $model_orcamento->arr_nao_afetados),
				);

				echo json_encode($array_retorno);

            } catch (Exception $e) {
                $array_retorno = array(
					'result'   => false,
					'mensagem' => $e->getMessage(),
				);

				echo json_encode($array_retorno);
            }
        }
    }
}