<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\Movimentos\MovimentosEntity;
use src\Models\Orcamento\OrcamentoDAO;
use src\Models\Orcamento\OrcamentoEntity;
use src\Models\Rendimentos\RendimentosDAO;
use src\Models\Rendimentos\RendimentosEntity;

class DelecoesController extends Controller {
    public function deletarMovimento()
    {
        if ($this->isSetPost()) {

            $model_rendimentos = new RendimentosDAO();
            $model_movimentos = new MovimentosDAO();

            try {
                foreach ($_POST['itens'] as $id) {
                    $rend = $model_rendimentos->selectAll(new RendimentosEntity, [['idMovimento', '=', $id]], [], []);

                    if (!empty($rend)) {
                        $model_movimentos->arr_nao_afetados[] = $id;
                    } else {
                        $ret = $model_movimentos->delete(new MovimentosEntity, 'idMovimento', $id);
                        if ($ret != false) {
                            $model_movimentos->arr_afetados[] = $id;
                        } else {
                            $model_movimentos->arr_nao_afetados[] = $id;
                        }
                    }
                }

                $array_retorno = array(
					'result'   => true,
					'mensagem' => 'Movimentos excluídos: ' . implode(', ', $model_movimentos->arr_afetados) . '. Movimentos não excluídos: ' . implode(', ', $model_movimentos->arr_nao_afetados),
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

    public function deletarItensOrcamento()
    {
        if ($this->isSetPost()) {

            $model_orcamento = new OrcamentoDAO();

            try {
                foreach ($_POST['itens'] as $id) {
                    $ret = $model_orcamento->delete(new OrcamentoEntity(), 'idOrcamento', $id);

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