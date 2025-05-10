<?php
namespace src\Controllers;

use MF\Controller\Controller;
use MF\Model\Model;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\Categorias\CategoriasEntity;
use src\Models\Investimentos\InvestimentosEntity;
use src\Models\MovimentosMensais\MovimentosMensaisDAO;
use src\Models\Objetivos\ObjetivosEntity;

class MovimentosController extends Controller {
    public function index() {
        $model = new Model();
        $model_movimentos = new MovimentosDAO();

        $action = $_GET['action'] ?? null;
        $id = $_GET['idMovimento'] ?? null;

        $title = 'Cadastro de Movimento';
        $url_action = '/cad_movimentos';
        if ($id != '') {
            $url_action = '/edit_movimento';
            $title = 'Edição de Movimento';
            $mov = $model_movimentos->consultarMovimento($id);
        }

        $this->view->settings = [
            'action'   => $this->index_route . $url_action,
            'redirect' => $this->index_route . '/home',
            'title'    => $title,
        ];

        $this->view->data['options_list'] = json_encode($model->selectAll(new ObjetivosEntity, [], [], []));
        $this->view->data['categorias'] = $model->selectAll(new CategoriasEntity, [], [], ['tipo' => 'ASC', 'categoria' => 'ASC']);
        $this->view->data['invests'] = $model->selectAll(new InvestimentosEntity, [], [], ['nomeBanco' => 'ASC']);
        $this->view->data['movimento'] = $mov[0] ?? null;
        $this->view->data['url_buscar_mov_mensal'] = $this->index_route . '/buscaMovMensal?buscar=';
        $this->view->data['div_buscar_mov_mensal'] = 'id-content-return';

        $this->renderPage(
            main_route: $this->index_route . '/movimentos', 
            conteudo: 'movimentos', 
            base_interna: 'base_cruds'
        );
    }

    public function exibirResultados()
    {
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

        $this->renderInModal(titulo: 'Demonstrativo', conteudo: 'exibir_resultado');
    }
}