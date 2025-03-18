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
        // $crud = new Crud();

        // $id_movimento = $_POST['idMovimento'];
        // $id_conta_invest = $_POST['idContaInvest'];
        // $id_objetivo = $_POST['idObjetivo'] ?? '';
        // $rendimento = $crud->selectAll('rendimento', [['idMovimento', '=', $_POST['idMovimento']]], [], []);

        // $where = array(
        //     'idMovimento' => $_POST['idMovimento']
        // );

        // $arr_cat = explode(' - sinal: ' , $_POST['idCategoria']);
        // $_POST['idCategoria'] = $arr_cat[0];
        // $sinal = $arr_cat[1];

        // if ($sinal == '-' && $_POST['valor'] > 0) {
        //     $_POST['valor'] = $_POST['valor'] * -1;
        // } elseif ($sinal == '+' && $_POST['valor'] < 0) {
        //     $_POST['valor'] = $_POST['valor'] * -1;
        // }

        // unset($_POST['idMovimento']);
        // unset($_POST['idObjetivo']);

        // $values = $_POST;

        // $crud->update('movimento', $values, $where);

        // if (isset($rendimento[0]['idRendimento'])) {
        //     $old_id = $rendimento[0]['idRendimento'];
        //     $old_invest = $rendimento[0]['idContaInvest'];
        //     $old_valor = $rendimento[0]['valorRendimento'];
        //     $old_tipo = $rendimento[0]['tipo'];
        //     $old_data = $rendimento[0]['dataRendimento'];
        //     $old_movimento = $rendimento[0]['idMovimento'];

        //     $crud->delete([
        //         'action'       => 'rendimento',
        //         'idRendimento' => $old_id
        //     ]);

        //     $conta_invest = $crud->selectAll('conta_investimento', [['idContaInvest', '=', $old_invest]], [], [])[0];

        //     if ($old_tipo == '4' || $old_tipo == '2') {
        //         $saldo = $conta_invest['saldoAtual'] - $old_valor;
        //     } elseif ($old_tipo == '3' || $old_tipo == '1') {
        //         $saldo = $conta_invest['saldoAtual'] + abs($old_valor);
        //     }

        //     $crud->update(
        //         'conta_investimento', 
        //         ['saldoAtual' => $saldo], 
        //         ['idContaInvest' => $old_invest]
        //     );

        //     $objetivos = $crud->selectAll('obj', [['idContaInvest', '=', $old_invest]], [], []);
    
        //     foreach ($objetivos as $value) {
        //         $item = [
        //             'saldoAtual' => ($saldo * ($value['percentObjContaInvest'] / 100))
        //         ];
        //         $item_where = ['idObj' => $value['idObj']];
        //         $crud->update('obj', $item, $item_where);
        //     }
        // }

        // if ($id_conta_invest != '') {
        //     switch ($_POST['idCategoria']) {
        //         case $this->APLICACAO:
        //             $new_tipo = 4;
        //             $valor_aplicado = ($_POST['valor'] * -1); 
    
        //             $objetivos = $crud->selectAll('obj', [['idContaInvest', '=', $id_conta_invest]], [], []);
    
        //             foreach ($objetivos as $value) {
        //                 $item = [
        //                     'saldoAtual' => $value['saldoAtual'] + ($valor_aplicado * ($value['percentObjContaInvest'] / 100))
        //                 ];
        //                 $item_where = ['idObj' => $value['idObj']];
        //                 $crud->update('obj', $item, $item_where);
        //             }
    
        //             break;
        //         case $this->RESGATE:
        //             $new_tipo = 3;
        //             $valor_aplicado = ($_POST['valor'] * -1); 
    
        //             $saldo_atual = $crud->getSaldoAtual('obj', $id_objetivo);
        //             $item = [
        //                 'saldoAtual' => ($saldo_atual + $valor_aplicado)
        //             ];
        //             $item_where = [
        //                 'idObj' => $id_objetivo
        //             ];
        //             $crud->update('obj', $item, $item_where);
    
        //             break;
        //         default:
        //             $new_tipo = '';
        //             $valor_aplicado = 0;
        //     }

        //     $item = [
        //         'idContaInvest'   => $id_conta_invest,
        //         'valorRendimento' => $valor_aplicado,
        //         'dataRendimento'  => $_POST['dataMovimento'],
        //         'tipo'            => $new_tipo,
        //         'idMovimento'     => $id_movimento
        //     ];

        //     $crud->insert('rendimento', $item);

        //     $saldo_atual = $crud->getSaldoAtual('conta_investimento', $id_conta_invest);
        //     $item = [
        //         'saldoAtual' => ($saldo_atual + $valor_aplicado)
        //     ];
        //     $item_where = [
        //         'idContaInvest' => $id_conta_invest
        //     ];
        //     $crud->update('conta_investimento', $item, $item_where);
        //}
    }
}