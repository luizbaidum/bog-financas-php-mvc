<?php
namespace src\Controllers;

use MF\Controller\Controller;
use src\Models\Rendimentos\RendimentosDAO;
use src\Models\Investimentos\InvestimentosDAO;

class RendimentosController extends Controller {
    public function index() {
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
        $this->renderPage(
            main_route: $this->index_route . '/evolucao_rendimentos', 
            conteudo: 'evolucao_rendimentos'
        );
    }
}