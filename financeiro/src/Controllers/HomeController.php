<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\DateHelper;
use src\Models\Movimentos\MovimentosDAO;
use MF\View\SetButtons;

class HomeController extends Controller {

	public function home()
	{
		try {
			$ano_filtro = $_GET['anoFiltro'] ?? '';
			$mes_filtro = $_GET['mesFiltro'] ?? '';
			$pesquisa = $_GET['pesquisa'] ?? '';
			if ($pesquisa != '') {
				$pesquisa = trim($pesquisa, ' ');
			}

			$model_movimentos = new MovimentosDAO();
			$buttons = new SetButtons();
            $resultado = 0;

			$saldos_anteriores = array();

			if ($mes_filtro != '' || $pesquisa != '') {
				$movimentos = $model_movimentos->indexTable($pesquisa, $ano_filtro, $mes_filtro);
                $mov_investimentos = $model_movimentos->indexTableInvestimentos($pesquisa, $ano_filtro, $mes_filtro);
			} else {
				$movimentos = $model_movimentos->indexTable('');
				$saldos_anteriores = $model_movimentos->getSaldoPassado();
                $mov_investimentos = $model_movimentos->indexTableInvestimentos();
			}

            $movimentos_agrupados = [];
            $result_por_prop = [];
            foreach ($movimentos as $mov) {
                $dataBR = DateHelper::convertUStoBR($mov['dataMovimento']);
                $movimentos_agrupados[$dataBR][] = $mov;

                $resultado += $mov['valor'];
                $prop[$mov['idProprietario']] = $mov['proprietario'];

                if (isset($result_por_prop[$mov['idProprietario']])) {
                    $result_por_prop[$mov['idProprietario']] += $mov['valor'];
                } else {
                    $result_por_prop[$mov['idProprietario']] = $mov['valor'];
                }
            }

			$this->view->settings = [
				'url_edit'   => $this->index_route . '/movimentos?action=edit&idMovimento=',
				'redirect'   => $this->index_route . '/home',
				'url_search' => $this->index_route . '/home',
			];

			$buttons->setButton(
				'Apagar',
				$this->index_route . '/delete_movimentos',
				'px-2 btn btn-danger action-delete',
                'right'
			);

			$buttons->setButton(
				'Ver demonstrativo',
				$this->index_route . '/exibir_resultado?anoFiltro=' . $ano_filtro . '&mesFiltro=' . $mes_filtro,
				'px-2 btn btn-info render-ajax',
                'left',
                'true'
			);

			$this->view->buttons = $buttons->getButtons();
			$this->view->data['movimentos_agrupados'] = $movimentos_agrupados;
			$this->view->data['saldos_anteriores'] = $saldos_anteriores;
            $this->view->data['mov_investimentos'] = $mov_investimentos;
            $this->view->data['url_detail'] = $this->index_route . '/exibir-detalhes?idMovimento=';
            $this->view->data['result_por_prop'] = $result_por_prop;
            $this->view->data['resultado'] = $resultado;
            $this->view->data['prop'] = $prop ?? '';

			$this->renderPage(conteudo: 'home');
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
}