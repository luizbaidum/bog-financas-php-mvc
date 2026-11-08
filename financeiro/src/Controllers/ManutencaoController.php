<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\NumbersHelper;
use NumberFormatter;
use src\Models\Manutencao\ManutencaoDAO;
use src\Models\Manutencao\ManutencaoEntity;
use src\Models\Proprietarios\ProprietariosDAO;
use src\Models\Proprietarios\ProprietariosEntity;

class ManutencaoController extends Controller {
    public function index()
    {
        $this->view->settings = [
            'action' => $this->index_route . '/cadastrar-manutencao',
            'title'  => 'Cadastro de Manutenção'
        ];

        $this->view->data['lista_proprietarios'] = (new ProprietariosDAO())->selectAll(new ProprietariosEntity(), [['status', '=', '"1"']], [], []);

        $this->renderPage(conteudo: 'cad_manutencao', base_interna: 'base_cruds');
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

                $item_principal = [
                    'dataManut'      => $_POST['dataManut'],
                    'oficina'        => $_POST['oficina'],
                    'vlrMO'          => NumbersHelper::formatBRtoUS($_POST['vlrMO']),
                    'idProprietario' => $_POST['idProprietario']
                ];

                // fazer cadastro dos nomes das peças primeiro.
                // recuperar ids e cadastrar esses ids no id da manut
                // fazer o js de ajax para puxar nome peça e nome oficina

                $id_manut = $model_manutencao->cadastrar(new ManutencaoEntity, $item_principal);

                foreach ($_POST['nomePeca'] as $k => $peca) {
                    $item_peca = [

                    ];


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