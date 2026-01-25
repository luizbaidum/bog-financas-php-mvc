<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\DateHelper;
use MF\Helpers\NumbersHelper;
use src\Models\Movimentos\MovimentosDAO;
use MF\View\SetButtons;
use src\Models\Preferencias\PreferenciasEntity;

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
                'card'       => [
                    'div' => 'div-cards',
                    'url' => $this->index_route . 'render-card-receitas'
                ]
			];

			$buttons->setButton(
				'Apagar',
				$this->index_route . '/delete-movimentos',
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

            $preferencia_cards = $model_movimentos->selectAll(
                                    new PreferenciasEntity(), 
                                    [['idPreferencia', '=', '"1"']]
                                );

            $preferencia_investimentos = $model_movimentos->selectAll(
                                    new PreferenciasEntity(), 
                                    [['idPreferencia', '=', '"2"']]
                                );

            $this->view->data['exibir_cards'] = false;
            if ($preferencia_cards[0]['status'] == 'T') {
                $this->view->data['exibir_cards'] = true;
                $this->getDataCardsResumo();
            }

            $this->view->data['exibir_resumo_investimentos'] = false;
            if ($preferencia_investimentos[0]['status'] == 'T') {
                $this->view->data['exibir_resumo_investimentos'] = true;
            }

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

    private function getDataCardsResumo(): void
    {
        $idProprietario = $_POST['idProprietario'] ?? '';
        $mesFiltro = $_GET['mesFiltro'] ?? '';
        $anoFiltro = $_GET['anoFiltro'] ?? '';

        $receita = (new MovimentosDAO())->getSaldoReceitas(
            $idProprietario,
            $mesFiltro,
            $anoFiltro
        );

        $data_receita = $this->prepararDadosCardReceita($receita, $_GET['mesFiltro'] ?? date('m'));
        $this->view->data['data_receita'] = $data_receita;

        $despesa = (new MovimentosDAO())->getSaldodespesas(
            $idProprietario,
            $mesFiltro,
            $anoFiltro
        );

        $data_despesa = $this->prepararDadosCardDespesa($despesa, $_GET['mesFiltro'] ?? date('m'));
        $this->view->data['data_despesa'] = $data_despesa;

        $investimentos = (new MovimentosDAO())->getSaldoInvestimentos(
            $idProprietario,
            $mesFiltro,
            $anoFiltro
        );

        $data_investimentos = $this->prepararDadosCardInvestimentos($investimentos, $_GET['mesFiltro'] ?? date('m'));
        $this->view->data['data_investimentos'] = $data_investimentos;

        $saldo = $data_receita['receita_iso'] - abs($data_despesa['despesa_iso']) - abs($data_investimentos['aplica_iso']) + $data_investimentos['resgata_uso'];
        $this->view->data['data_saldo'] = [
            'saldo'    => NumbersHelper::formatUStoBR($saldo),
            'bg-color' => $saldo > 0 ? 'info' : 'warning'
        ];
    }

    private function prepararDadosCardInvestimentos(array $dados_in, string|int $mes_principal): array
    {
        $dados_out = [];

        $aplica = $dados_in['aplic'] ?? '0';
        $resgata = $dados_in['resg'] ?? '0';
        $diferenca = $aplica - abs($resgata);

        $dados_out = [
            'aplica'      => NumbersHelper::formatUStoBR($aplica),
            'resgata'     => NumbersHelper::formatUStoBR($resgata),
            'bg-color'    => $diferenca > 0 ? 'success' : 'danger',
            'aplica_iso'  => $aplica,
            'resgata_uso' => $resgata
        ];

        return $dados_out;
    }

    private function prepararDadosCardReceita(array $dados_in, string|int $mes_principal): array
    {
        $dados_out = [];

        $mes_principal = ltrim($mes_principal, '0');
        $mes_anterior = ltrim($mes_principal - 1, '0');

        $receita_principal = $dados_in[$mes_principal] ?? '0';
        $receita_anterior = $dados_in[$mes_anterior] ?? '0';
        $diferenca = $receita_anterior != 0 ? (($receita_principal / $receita_anterior) - 1) * 100 : 0;

        $dados_out = [
            'receita'     => NumbersHelper::formatUStoBR($receita_principal),
            'diferenca'   => $diferenca > 0 ? '+' . NumbersHelper::formatUStoBR($diferenca) : NumbersHelper::formatUStoBR($diferenca),
            'bg-color'    => $diferenca > 0 ? 'success' : 'danger',
            'receita_iso' => $receita_principal
        ];

        return $dados_out;
    }

    private function prepararDadosCardDespesa(array $dados_in, string|int $mes_principal): array
    {
        $dados_out = [];

        $mes_principal = ltrim($mes_principal, '0');
        $mes_anterior = ltrim($mes_principal - 1, '0');

        $despesa_principal = $dados_in[$mes_principal] ?? '0';
        $despesa_anterior = $dados_in[$mes_anterior] ?? '0';
        $diferenca = $despesa_anterior != 0 ? (($despesa_principal / $despesa_anterior) - 1) * 100 : 0;

        $dados_out = [
            'despesa'     => NumbersHelper::formatUStoBR($despesa_principal),
            'diferenca'   => $diferenca > 0 ? '+' . NumbersHelper::formatUStoBR($diferenca) : NumbersHelper::formatUStoBR($diferenca),
            'bg-color'    => $diferenca < 0 ? 'success' : 'danger',
            'despesa_iso' => $despesa_principal
        ];

        return $dados_out;
    }
}