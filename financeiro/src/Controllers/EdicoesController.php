<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\NumbersHelper;
use src\Models\Investimentos\InvestimentosEntity;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\Movimentos\MovimentosEntity;
use src\Models\Objetivos\ObjetivosDAO;
use src\Models\Objetivos\ObjetivosEntity;
use src\Models\Preferencias\PreferenciasDAO;
use src\Models\Preferencias\PreferenciasEntity;
use src\Models\Rendimentos\RendimentosDAO;
use src\Models\Rendimentos\RendimentosEntity;

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
        $model_movimentos = new MovimentosDAO();
        $model_rendimentos = new RendimentosDAO();
        $model_objetivos = new ObjetivosDAO();

        try {
            $id_movimento = $_POST['idMovimento'];
            $id_conta_invest = $_POST['idContaInvest'];
            $id_objetivo = $_POST['idObjetivo'] ?? '';
            $id_objetivo_old = $_POST['idObjOld'] ?? '0';

            unset($_POST['idMovimento']);
            unset($_POST['idObjetivo']);
            unset($_POST['idObjOld']);

            $arr_cat = explode(' - sinal: ' , $_POST['idCategoria']);
            $_POST['idCategoria'] = $arr_cat[0];
            $sinal = $arr_cat[1];

            if ($sinal == '-' && $_POST['valor'] > 0) {
                $_POST['valor'] = $_POST['valor'] * -1;
            } elseif ($sinal == '+' && $_POST['valor'] < 0) {
                $_POST['valor'] = $_POST['valor'] * -1;
            }

            $values = $_POST;
            $where = array(
                'idMovimento' => $id_movimento
            );

            $ret = $model_movimentos->atualizar(new MovimentosEntity, $values, $where);

            $rendimento = $model_rendimentos->selectAll(new RendimentosEntity, [['idMovimento', '=', $id_movimento]], [], []);

            if (isset($rendimento[0]['idRendimento'])) {
                $rendimento = $rendimento[0];
                $old_id = $rendimento['idRendimento'];
                $old_invest = $rendimento['idContaInvest'];
                $old_valor = $rendimento['valorRendimento'];
                $old_tipo = $rendimento['tipo'];
                $old_data = $rendimento['dataRendimento'];
                $old_movimento = $rendimento['idMovimento'];

                $conta_invest = $model_rendimentos->selectAll(new InvestimentosEntity, [['idContaInvest', '=', $old_invest]], [], [])[0];

                if ($old_tipo == '4' || $old_tipo == '2') {
                    $saldo = $conta_invest['saldoAtual'] - $old_valor;
                } elseif ($old_tipo == '3' || $old_tipo == '1') {
                    $saldo = $conta_invest['saldoAtual'] + abs($old_valor);
                }

                $model_rendimentos->atualizar(
                    new InvestimentosEntity, 
                    ['saldoAtual' => $saldo], 
                    ['idContaInvest' => $old_invest]
                );

                if (empty($id_objetivo_old)) {
                    $objetivos = $model_objetivos->selectAll(new ObjetivosEntity, [['idContaInvest', '=', $old_invest]], [], []);

                    foreach ($objetivos as $value) {
                        $item = [
                            'saldoAtual' => ($saldo * ($value['percentObjContaInvest'] / 100))
                        ];
                        $item_where = ['idObj' => $value['idObj']];
                        $model_objetivos->atualizar(new ObjetivosEntity, $item, $item_where);
                    }
                } else {
                    $objetivo = $model_objetivos->selectAll(new ObjetivosEntity, [['idObj', '=', $id_objetivo_old]], [], [])[0];

                    if ($old_tipo == '4' || $old_tipo == '2') {
                        $saldo_obj = $objetivo['saldoAtual'] - $old_valor;
                    } elseif ($old_tipo == '3' || $old_tipo == '1') {
                        $saldo_obj = $objetivo['saldoAtual'] + abs($old_valor);
                    }

                    $item = [
                        'saldoAtual' => $saldo_obj
                    ];
                    $item_where = ['idObj' => $id_objetivo_old];
                    $model_objetivos->atualizar(new ObjetivosEntity, $item, $item_where);
                }

                $model_rendimentos->delete(new RendimentosEntity, 'idRendimento', $old_id);
            }

            if (!empty($id_conta_invest)) {
                (new CadastrosController())->inserirMovimentacaodeAplicacao($id_conta_invest, $id_objetivo, $id_movimento);
            }

            if (!isset($ret['result']) || empty($ret['result'])) {
                throw new Exception('O Movimento não foi atualizado.');
            }

            $array_retorno = array(
                'result'   => true,
                'mensagem' => 'Movimento id ' . $id_movimento . ' atualizado com sucesso.',
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