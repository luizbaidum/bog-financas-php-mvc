<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\NumbersHelper;
use src\Models\Objetivos\ObjetivosDAO;
use src\Models\Objetivos\ObjetivosEntity;
use src\Models\Preferencias\PreferenciasDAO;
use src\Models\Preferencias\PreferenciasEntity;

class EdicoesController extends Controller {
    public function editarObjetivo()
    {
        $model_objetivos = new ObjetivosDAO();

        if ($this->isSetPost()) {
            try {
                $id = $_POST['idObj'];
                $_POST['vlrObj'] = NumbersHelper::formatBRtoUS($_POST['vlrObj']);
                $_POST['percentObjContaInvest'] = NumbersHelper::formatBRtoUS($_POST['percentObjContaInvest']);

                $conta_invest = $_POST['idContaInvest'];
                $percentual_old = $_POST['percentObjContaInvestOLD'];

                unset($_POST['idObj']);
                unset($_POST['idContaInvest']);
                unset($_POST['percentObjContaInvestOLD']);

                if ($_POST['percentObjContaInvest'] > $percentual_old) {
                    $validation = $this->validarPercentualUso($conta_invest, ($_POST['percentObjContaInvest'] - $percentual_old));

                    if (!$validation['status']) {
                        throw new Exception($validation['msg']);
                    }
                }

                $ret = $model_objetivos->atualizar(new ObjetivosEntity, $_POST, ['idObj' => $id]);

                if (!isset($ret['result']) || empty($ret['result'])) {
                    throw new Exception('O objetivo não foi atualizado.');
                }

                $array_retorno = array(
					'result'   => true,
					'mensagem' => 'Objetivo id ' . $id . ' atualizado com sucesso.',
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

    private function validarPercentualUso($id_conta_invest, $percentual)
    {
        $utilizado = (new ObjetivosDAO())->consultarPercentualDisponivel($id_conta_invest, $percentual);

        if ($utilizado !== false && ($percentual + $utilizado) > 100) {
            return [
                'status' => false,
                'msg'    => 'Atenção! A Conta Invest informada já está ' . $utilizado . '% comprometida.'
            ];
        }

        return ['status' => true];
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

    public function editarMovimento()
    {
        
    }
}