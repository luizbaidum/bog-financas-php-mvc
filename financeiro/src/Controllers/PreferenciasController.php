<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use src\Models\Preferencias\PreferenciasDAO;
use src\Models\Preferencias\PreferenciasEntity;

class PreferenciasController extends Controller {
    public function index() {
        $model_preferencias = new PreferenciasDAO();
        $prefs = $model_preferencias->selectAll(new PreferenciasEntity());

        $this->view->settings = [
            'action'   => $this->index_route . '/salvar_preferencias',
            'redirect' => $this->index_route . '/preferencias',
            'action_2' => $this->index_route . '/nova_preferencia',
            'title'    => 'Preferências',
        ];

        $this->view->data['prefs'] = $prefs;
        $this->renderPage(
            main_route: $this->index_route . '/preferencias', 
            conteudo: 'preferencias'
        );
    }

    public function editarPreferencia()
    {
        if ($this->isSetPost()) {
            $model_preferencias = new PreferenciasDAO();

            try {
                foreach ($_POST['idPreferencia'] as $id) {
                    $status = $_POST['status'][$id] ?? 'F';
                    $item['status'] = $status;
     
                    $ret = $model_preferencias->atualizar(new PreferenciasEntity, $item, ['idPreferencia' => $id]);

                    if (isset($ret['result']) && $ret['result'] > 0) {
                        $arr_atualizado[] = $id;
                    } else {
                        $arr_nao_atualizado[] = $id;
                    }
                }

                if (isset($arr_atualizado) && count($arr_atualizado) > 0) {
                    $msg = 'Preferências atualizadas: ' . implode(', ', $arr_atualizado);

                    if (count($arr_nao_atualizado) > 0) {
                        $msg .= '<br> Não atualizadas: ' . implode(', ', $arr_nao_atualizado);
                    }

                    $array_retorno = array(
                        'result'   => true,
                        'mensagem' => $msg,
                    );
    
                    echo json_encode($array_retorno);
				} else {
					throw new Exception('Nenhuma preferência foi atualizada.');
				}
            } catch (Exception $e) {
				$array_retorno = array(
					'result'   => false,
					'mensagem' => $e->getMessage(),
				);

				echo json_encode($array_retorno);
			}
        }
    }

    public function cadastrarPreferencia()
    {
        if ($this->isSetPost()) {
            $model_preferencias = new PreferenciasDAO();

            try {
                $item = $_POST;
                $item['status'] = 'T';
                $ret = $model_preferencias->cadastrar(new PreferenciasEntity, $item);

                if ($ret['result']) {
					$array_retorno = array(
						'result'   => $ret['result'],
						'mensagem' => $this->msg_retorno_sucesso
					);

					echo json_encode($array_retorno);
				} else {
					throw new Exception($this->msg_retorno_falha);
				}
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