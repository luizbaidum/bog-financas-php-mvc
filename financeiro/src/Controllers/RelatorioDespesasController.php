<?php
namespace src\Controllers;

use MF\Controller\Controller;
use src\Models\Categorias\CategoriasDAO;
use src\Models\Categorias\CategoriasEntity;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\Proprietarios\ProprietariosEntity;
use src\System\MonthAndYear;

class RelatorioDespesasController extends Controller {
    public function index()
    {
        $model_categorias = new CategoriasDAO();
        $model_movimentos = new MovimentosDAO();

        $this->view->settings = [
            'action'   => $this->index_route . '/relatorio-despesas',
            'redirect' => $this->index_route . '/relatorio-despesas',
            'title'    => 'Relatório de Despesas',
            'div'      => 'id-tabela-relatorio-despesas',
        ];

        if (isset($_POST) && !empty($_POST)) {
            if (empty($_POST['anoRelatorio'])) {
                $array_retorno = array(
					'result'   => false,
					'mensagem' => 'É obrigatório informar o ano do relatório.',
				);

				echo json_encode($array_retorno);
                exit;
            }

            $this->view->data['relatorio'] = $model_movimentos->relatorioDespesas($_POST);
            $this->renderSimple('tabela_relat_despesas');
        }

        $this->view->data['lista_categoria'] = $model_categorias->selectAll(new CategoriasEntity(), [['status', '=', '"1"'], ['tipo', '=', '"D"']], [], ['categoria' => 'ASC']);
        $this->view->data['months'] = MonthAndYear::getMonths();
        $this->view->data['years'] = MonthAndYear::getYears();
        $this->view->data['lista_regularidade'] = ['F', 'V'];
        $this->view->data['lista_proprietarios'] = $model_categorias->selectAll(new ProprietariosEntity, [], [], []);

        $this->renderPage(
            conteudo: 'relatorio_despesas'
        );
    }
}