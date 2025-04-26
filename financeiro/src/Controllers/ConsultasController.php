<?php

namespace src\Controllers;

use MF\Controller\Controller;
use MF\Model\Model;
use src\Models\Categorias\CategoriasEntity;
use src\Models\Investimentos\InvestimentosDAO;
use src\Models\Investimentos\InvestimentosEntity;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\MovimentosMensais\MovimentosMensaisDAO;
use src\Models\Objetivos\ObjetivosDAO;
use src\Models\Objetivos\ObjetivosEntity;
use src\Models\Orcamento\OrcamentoDAO;
use src\Models\Preferencias\PreferenciasDAO;
use src\Models\Preferencias\PreferenciasEntity;
use src\Models\Rendimentos\RendimentosDAO;
use src\Models\Rendimentos\RendimentosEntity;

class ConsultasController extends Controller {
    public function indicadores()
    {
        $model_movimentos = new MovimentosDAO();
        $model_orcamento = new OrcamentoDAO();

        $ano_filtro = $_GET['anoFiltro'] ?? '';
		$mes_filtro = $_GET['mesFiltro'] ?? '';

        $this->view->settings = [
            'action'     => '',
            'redirect'   => $this->index_route . '/indicadores_index',
            'title'      => 'Indicadores',
            'url_search' => $this->index_route . '/indicadores_index'
        ];

        $indicadores = $model_movimentos->indicadores(); 
        $orcamentos = $model_orcamento->orcamentos();

        if ($mes_filtro != '') {
            $indicadores = $model_movimentos->indicadores($ano_filtro, $mes_filtro); 
            $orcamentos = $model_orcamento->orcamentos($ano_filtro, $mes_filtro);
        }

        $receitas = 0;
        $aplicado = 0;
        foreach ($indicadores as $value) {
            if ($value['tipo'] == 'R' && $value['idCategoria'] != 10) //'Devolução de Aplicação'
                $receitas += $value['total'];
            
            if ($value['idCategoria'] == 12 || $value['idCategoria'] == 10) //'Aplicação' //'Devolução de Aplicação'
                $aplicado += $value['total'];
        }

        $this->view->data['indicadores'] = $indicadores;
        $this->view->data['orcamentos'] = $orcamentos;
        $this->view->data['receitas'] = $receitas;
        $this->view->data['aplicado'] = $aplicado;

        $this->renderPage(main_route: $this->index_route . '/indicadores_index', conteudo: 'indicadores_index');
    }

    public function investimentos()
    {
        $model_investimentos = new InvestimentosDAO();

        $contas = $model_investimentos->selectAll(new InvestimentosEntity, [], [], []);
        $invests = $model_investimentos->selectAll(new InvestimentosEntity, [], [], ['nomeBanco' => 'ASC']);
        $objs = $model_investimentos->selectAll(new ObjetivosEntity, [], [], ['saldoAtual' => 'DESC']);

        $this->view->settings = [
            'action'   => $this->index_route . '/cadastrar_rendimento',
            'redirect' => $this->index_route . '/contas_investimentos_index',
            'title'    => 'Indicadores',
            'url_obj'  => $this->index_route . '/consultar_objetivos?idContaInvest=',
        ];

        $this->view->data['contas'] = $contas;
        $this->view->data['invests'] = $invests;
        $this->view->data['objs'] = $objs;

        $this->renderPage(main_route: $this->index_route . '/contas_investimentos_index', conteudo: 'contas_investimentos_index');
    }

    public function orcamento()
    {
        $model_orcamento = new OrcamentoDAO();

        $ano_filtro = $_GET['anoFiltro'] ?? '';
		$mes_filtro = $_GET['mesFiltro'] ?? '';

        if ($mes_filtro != '') {
            $orcamentos = $model_orcamento->orcamentos($ano_filtro, $mes_filtro);
        } else {
            $orcamentos = $model_orcamento->orcamentos();
        }

        $this->view->settings = [
            'action'     => '',
            'redirect'   => $this->index_route . '/orcamento_index',
            'title'      => 'Orçamento',
            'url_search' => $this->index_route . '/orcamento_index'
        ];

        $this->view->data['orcamentos'] = $orcamentos;

        $this->renderPage(main_route: $this->index_route . '/orcamento_index', conteudo: 'orcamento_index');
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

    public function movimentosMensais()
    {
        $model_movimentos_mensais = new MovimentosMensaisDAO();

        $this->view->settings = [
            'action'   => $this->index_route . '/cad_mov_mensal',
            'redirect' => $this->index_route . '/movimentos_mensais_index',
            'title'    => 'Movimentos Mensais',
        ];

        $this->view->data['arr_mensais'] = $model_movimentos_mensais->getMensais();

        $this->renderPage(main_route: $this->index_route . '/movimentos_mensais_index', conteudo: 'movimentos_mensais_index', base_interna: 'base_cruds');
    }

    public function evolucaoRendimentos()
    {
        $model_rendimentos = new RendimentosDAO();
        $model_investimentos = new InvestimentosDAO();

        $saldos = $model_investimentos->getSaldosIniciais();
        $rendi = $model_rendimentos->getEvolucaoRendimentos();

        foreach ($saldos as $s) {
            foreach ($rendi as $k => $r) {
                if ($r['idContaInvest'] == $s['idContaInvest']) {
                    $rendi[$k]['valor'] = $r['valor'] + $s['saldoInicial'];
                    break;
                }
            }
        }

        foreach ($rendi as $k => $r) {
            $id = $rendi[($k - 1)]['idContaInvest'] ?? 0;
            $valor = $rendi[($k - 1)]['valor'] ?? 0;

            if ($id > 0 && $id == $r['idContaInvest']) {
                $rendi[$k]['valor'] = $r['valor'] + $valor;
            }
        }

        $this->view->data['ret'] = json_encode($rendi);

        $this->renderPage(main_route: $this->index_route . '/evolucao_rendimentos', conteudo: 'evolucao_rendimentos');
    }

    public function extratoInvestimentos()
    {
        $model_investimentos = new InvestimentosDAO();

        $this->view->settings = [
            'action'   => $this->index_route . '/extrato_investimentos',
            'redirect' => $this->index_route . '/extrato_investimentos',
            'title'    => 'Extrato Investimentos',
            'div'      => 'id-tabela-extrato'
        ];

        if (isset($_POST) && !empty($_POST)) {
            $this->view->data['extrato'] = $model_investimentos->consultarExtrato($_POST);
            $this->renderSimple('tabela_extrato');
        }

        $this->view->data['extrato'] = $model_investimentos->consultarExtrato([]);;
        $this->view->data['lista_invest'] = $model_investimentos->selectAll(new InvestimentosEntity, [], [], []);
        $this->view->data['months'] = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Todos');
        $this->view->data['lista_acao'] = $model_investimentos->selectAll(new RendimentosEntity, [], ['rendimentos', 'tipo'], []);

        $this->renderPage(main_route: $this->index_route . '/extrato_investimentos', conteudo: 'extrato_investimentos');
    }

    public function consultarObjetivos()
    {
        $id_invest = $_GET['idContaInvest'];

        $model_objetivos = new ObjetivosDAO();

        $lista_objetivos = $model_objetivos->consultarObjetivosPorInvestimento($id_invest);

        $this->view->settings = [
            'action'   => $this->index_route . '/editar_objetivo',
            'redirect' => $this->index_route . '/extrato_investimentos',
        ];

        $this->view->data['lista_objetivos'] = $lista_objetivos;

        $this->renderInModal(titulo: 'Objetivos do investimento conta', conteudo: 'objetivos');
    }

    public function buscarOrcamentoDoRealizado()
    {
        $model_orcamento = new OrcamentoDAO();

        list($ano_origem, $mes_origem) = explode('-', $_POST['mesAnoOrigem']);

        $lista = $model_orcamento->buscarMediasDespesas($ano_origem, $mes_origem);

        $this->view->data['lista'] = $lista;

        $this->renderSimple('tabela_orcamento_importado');
    }

    public function preferencias()
    {
        $model_preferencias = new PreferenciasDAO();

        $prefs = $model_preferencias->selectAll(new PreferenciasEntity());

        $this->view->settings = [
            'action'   => $this->index_route . '/salvar_preferencias',
            'redirect' => $this->index_route . '/preferencias',
            'action_2' => $this->index_route . '/nova_preferencia',
            'title'    => 'Preferências',
        ];

        $this->view->data['prefs'] = $prefs;

        $this->renderPage(main_route: $this->index_route . '/preferencias', conteudo: 'preferencias');
    }

    public function movimentos()
    {
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

        $this->renderPage(main_route: $this->index_route . '/movimentos', conteudo: 'movimentos', base_interna: 'base_cruds');
    }

    public function buscarMovMensal()
    {
        $buscar = $_GET['buscar'];

        $model_movimentos_mensais = new MovimentosMensaisDAO();

        $ret = $model_movimentos_mensais->buscar($buscar);

        $this->view->data['ret'] = $ret;

        $this->renderSimple('ret_mov_mensais');
    }
    
    public function definirMovimentoDoInvestimento()
    {
        $model = new Model();

        $this->view->data['tipo_movimento'] = $_GET['action'];
        $this->view->data['invests'] = $model->selectAll(new InvestimentosEntity, [], [], ['nomeBanco' => 'ASC', 'tituloInvest' => 'ASC']);

        $this->renderSimple('definido_movimento_investimento');
    }
}
?>