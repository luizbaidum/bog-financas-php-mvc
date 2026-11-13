<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\NumbersHelper;
use NumberFormatter;
use src\Models\Manutencao\ManutencaoDAO;
use src\Models\Manutencao\ManutencaoEntity;
use src\Models\Manutencao\ManutencoesPecasDAO;
use src\Models\Manutencao\ManutencoesPecasEntity;
use src\Models\Manutencao\PecasDAO;
use src\Models\Manutencao\PecasEntity;
use src\Models\Proprietarios\ProprietariosDAO;
use src\Models\Proprietarios\ProprietariosEntity;

class ManutencaoController extends Controller {
    public function index()
    {
        $this->view->settings = [
            'action' => $this->index_route . '/cadastrar-manutencao',
            'title'  => 'Cadastro de Manutenção',
        ];

        $this->view->data['lista_proprietarios'] = (new ProprietariosDAO())->selectAll(new ProprietariosEntity(), [['status', '=', '"1"']], [], []);
        $this->view->data['url_buscar_peca'] = $this->index_route . '/buscar-peca?buscar=';
        $this->view->data['div_buscar_peca'] = 'teste';

        $this->renderPage(conteudo: 'cad_manutencao', base_interna: 'base_cruds');
    }

    public function buscarPeca()
    {
        $buscar = $_GET['buscar'];

        $ret = (new PecasDAO())->buscar($buscar);
        $this->view->data['ret'] = $ret;

        $this->renderSimple('ret_pecas');
    }

    public function cadastrarManutencao()
    {
        if ($this->isSetPost()) {
            $model_manutencao = new ManutencaoDAO();
            $model_pecas = new PecasDAO();
            $model_manutencoes_pecas = new ManutencoesPecasDAO();
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

                $item_principal = [
                    'dataManut'      => $_POST['dataManut'],
                    'oficina'        => $_POST['oficina'],
                    'vlrMO'          => NumbersHelper::formatBRtoUS($_POST['vlrMO']),
                    'idProprietario' => $_POST['idProprietario']
                ];

                $total_pecas = count($_POST['nomePeca']);

                for ($i = 0; $i <= $total_pecas; $i++) {
                    if (! empty($_POST['idPeca'][$i])) {
                        $id_pecas[$i] = $_POST['idPeca'][$i];
                    } else {
                        $item_peca = [
                            'nomePeca' => $_POST['nomePeca'][$i]
                        ];

                        $id_pecas[$i] = $model_pecas->cadastrar(new PecasEntity, $item_peca);
                    }
                }

                if (empty($id_pecas)) {
                    throw new Exception('Atenção: Informar peças usadas na manutenção.');
                }

                $id_manut = $model_manutencao->cadastrar(new ManutencaoEntity, $item_principal);

                foreach ($id_pecas as $k => $id_p) {
                    $id_peca = $id_pecas[$k];

                    $item_manut = [
                        'idManut'   => $id_manut,
                        'idPeca'    => $id_peca,
                        'valorPeca' => NumbersHelper::formatBRtoUS($_POST['valorPeca'][$k]),
                        'qtdPeca'   => NumbersHelper::formatBRtoUS($_POST['qtdPeca'][$k])
                    ];

                    $ret = $model_manutencoes_pecas->cadastrar(new ManutencoesPecasEntity, $item_manut);
                }

                if ($ret['result']) {
                    $array_retorno = array(
                        'result'   => $ret['result'],
                        'mensagem' => $this->msg_retorno_sucesso
                    );

                    $model_manutencao->finalizarTransacao();

                    echo json_encode($array_retorno);
                } else {
                    throw new Exception($this->msg_retorno_falha);
                }
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

    }
}