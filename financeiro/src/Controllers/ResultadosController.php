<?php
namespace src\Controllers\ResultadosController;

use MF\Controller\Controller;
use src\Models\Movimentos\MovimentosDAO;

class ResultadosController extends Controller {
    public function index() {
        $ano_filtro = $_GET['anoFiltro'];
        $mes_filtro = $_GET['mesFiltro'];

        $model_movimentos = new MovimentosDAO();
        $ret = $model_movimentos->getResultado($ano_filtro, $mes_filtro);

        $data = [];
        if ($ret) {
            foreach ($ret as $val) {
                if ($val['tipo'] == 'R') {
                    if (strpos($val['categoria'], 'Resgate') !== false) {
                        if (isset($total_resgate[$val['proprietario']])) {
                            $total_resgate[$val['proprietario']] += $val['total'];
                        } else {
                            $total_resgate[$val['proprietario']] = $val['total'];
                        }

                        if (isset($data[$val['proprietario']]['Resgate'])) {
                            $data[$val['proprietario']]['Resgate'] += $val['total'];
                        } else {
                            $data[$val['proprietario']]['Resgate'] = $val['total'];
                        }
                    } else {
                        if (isset($total_receita[$val['proprietario']])) {
                            $total_receita[$val['proprietario']] += $val['total'];
                        } else {
                            $total_receita[$val['proprietario']] = $val['total'];
                        }

                        if (isset($data[$val['proprietario']]['Receitas'])) {
                            $data[$val['proprietario']]['Receitas'] += $val['total'];
                        } else {
                            $data[$val['proprietario']]['Receitas'] = $val['total'];
                        }
                    }
                } elseif ($val['tipo'] == 'D') {
                    if (isset($total_despesa[$val['proprietario']])) {
                        $total_despesa[$val['proprietario']] += $val['total'];
                    } else {
                        $total_despesa[$val['proprietario']] = $val['total'];
                    }

                    if (isset($data[$val['proprietario']]['Despesas'])) {
                        $data[$val['proprietario']]['Despesas'] += $val['total'];
                    } else {
                        $data[$val['proprietario']]['Despesas'] = $val['total'];
                    }
                } elseif ($val['tipo'] == 'A') {
                    if (isset($total_aplicacao[$val['proprietario']])) {
                        $total_aplicacao[$val['proprietario']] += $val['total'];
                    } else {
                        $total_aplicacao[$val['proprietario']] = $val['total'];
                    }

                    if (isset($data[$val['proprietario']]['Aplicação'])) {
                        $data[$val['proprietario']]['Aplicação'] += $val['total'];
                    } else {
                        $data[$val['proprietario']]['Aplicação'] = $val['total'];
                    }
                }
            }
        }
        
        $this->view->data['data'] = $data;
        $this->view->data['total_resgate'] = $total_resgate ?? 0;
        $this->view->data['total_receita'] = $total_receita;
        $this->view->data['total_despesa'] = $total_despesa;
        $this->view->data['total_aplicacao'] = $total_aplicacao;

        $this->renderInModal(
            titulo: 'Demonstrativo', 
            conteudo: 'exibir_resultado'
        );
    }
}