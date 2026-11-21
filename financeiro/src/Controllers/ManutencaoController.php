<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\DateHelper;
use MF\Helpers\NumbersHelper;
use src\Models\Manutencao\ManutencaoDAO;
use src\Models\Manutencao\ManutencaoEntity;
use src\Models\Manutencao\ManutencoesPecasEntity;
use src\Models\Manutencao\PecasDAO;
use src\Models\Manutencao\PecasEntity;
use src\Models\Proprietarios\ProprietariosDAO;
use src\Models\Proprietarios\ProprietariosEntity;
use src\System\MonthAndYear;

class ManutencaoController extends Controller {
    public function index()
    {
        $this->view->settings = [
            'action'   => $this->index_route . '/cadastrar-manutencao',
            'title'    => 'Cadastro de Manutenção',
            'redirect' => $this->index_route . '/manutencao-index'
        ];

        $this->view->data['lista_proprietarios'] = (new ProprietariosDAO())->selectAll(new ProprietariosEntity(), [['status', '=', '"1"']], [], []);
        $this->view->data['url_buscar_peca'] = $this->index_route . '/buscar-peca?buscar=';
        $this->view->data['div_buscar_peca'] = 'render-ret-pecas-0';

        $this->renderPage(conteudo: 'cad_manutencao', base_interna: 'base_cruds');
    }

    public function buscarPeca()
    {
        $buscar = $_GET['buscar'];

        $this->view->data['settings']['redirect'] = '';

        $ret = (new PecasDAO())->buscar($buscar);
        $this->view->data['ret'] = $ret;

        $this->renderSimple('ret_pecas');
    }

    public function cadastrarManutencao()
    {
        if ($this->isSetPost()) {
            $model_manutencao = new ManutencaoDAO();

            $model_manutencao->iniciarTransacao();
            try {
                if ($_POST['dataManut'] == '') {
                    throw new Exception('Atenção: Escolher data da manutenção.');
                }

                if ($_POST['oficina'] == '') {
                    throw new Exception('Atenção: Preencher oficina da manutenção.');
                }

                $pecas_repetidas = array_count_values($_POST['nomePeca']);
                foreach ($pecas_repetidas as $v) {
                    if ($v > 1) {
                        throw new Exception('Atenção: Existem peças idênticas digitadas mais de uma vez na mesma manutenção.');
                    }
                }

                $total_pecas = count($_POST['nomePeca']);
                for ($i = 0; $i < $total_pecas; $i++) {

                    $id_pecas[$i] = $_POST['idPeca'][$i];
                    if ($id_pecas[$i] == '') {
                        $item_peca = [
                            'nomePeca' => $_POST['nomePeca'][$i]
                        ];

                        $id_pecas[$i] = $model_manutencao->cadastrar(new PecasEntity, $item_peca, false)['result'] ?? 0;
                        if ($id_pecas[$i] <= 0) {
                            throw new Exception('Atenção: Erro ao cadastrar peça.');
                        }
                    }
                }

                if (empty($id_pecas)) {
                    throw new Exception('Atenção: Informar peças usadas na manutenção.');
                }

                $item_principal = [
                    'dataManut'      => $_POST['dataManut'],
                    'oficina'        => $_POST['oficina'],
                    'vlrMO'          => NumbersHelper::formatBRtoUS($_POST['vlrMO']),
                    'idProprietario' => $_POST['idProprietario'],
                    'km'             => NumbersHelper::formatBRtoUS($_POST['km']),
                ];

                $id_manut = $model_manutencao->cadastrar(new ManutencaoEntity, $item_principal)['result'] ?? 0;

                if ($id_manut <= 0) {
                    throw new Exception('Atenção: Erro ao cadastrar manutenção.');
                }

                foreach ($id_pecas as $k => $id_p) {
                    $item_manut = [
                        'idManut'   => $id_manut,
                        'idPeca'    => $id_p,
                        'valorPeca' => NumbersHelper::formatBRtoUS($_POST['valorPeca'][$k]),
                        'qtdPeca'   => NumbersHelper::formatBRtoUS($_POST['qtdPeca'][$k])
                    ];

                    $ret = $model_manutencao->cadastrar(new ManutencoesPecasEntity, $item_manut, false);
                    if (empty($ret['result'])) {
                        throw new Exception($this->msg_retorno_falha);
                    }
                }

                $model_manutencao->finalizarTransacao();

                $array_retorno = array(
                    'result'   => true,
                    'mensagem' => $this->msg_retorno_sucesso
                );

                echo json_encode($array_retorno);
            } catch (Exception $e) {
                $array_retorno = array(
                    'result'   => false,
                    'mensagem' => $e->getMessage(),
                );

                $model_manutencao->cancelarTransacao();

                echo json_encode($array_retorno);
            }
        }
    }

    public function relatorioManutencao()
    {
        $model_manutencao = new ManutencaoDAO();

        $this->view->settings = [
            'action'   => $this->index_route . '/processar-relatorio-manutencao',
            'title'    => 'Manutenções',
            'div'      => 'content-relatorio-manutencao'
        ];

        $this->view->data['years'] = MonthAndYear::getYears();

        $this->renderPage(
            conteudo: 'relatorio_manutencao'
        );
    }

    public function processarRelatorioManutencao()
    {
        $model_manutencao = new ManutencaoDAO();

        $ano = $_POST['anoManut'];

        $this->view->data['dados'] = $model_manutencao->getManutencoesByYear($ano);
        $this->view->data['url_detail'] = $this->index_route . '/exibir-detalhes-manutencao?idManut=';

        $this->renderSimple('ret_relatorio_manutencao');
    }
}