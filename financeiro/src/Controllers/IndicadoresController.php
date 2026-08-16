<?php
namespace src\Controllers;

use MF\Controller\Controller;
use src\Models\Movimentos\MovimentosDAO;

class IndicadoresController extends Controller {
    public function index() {
        $model_movimentos = new MovimentosDAO();

        $ano_filtro = $_GET['anoFiltro'] ?? '';
        $mes_filtro = $_GET['mesFiltro'] ?? '';

        $this->view->settings = [
            'action'     => '',
            'redirect'   => $this->index_route . '/indicadores-index',
            'title'      => 'Indicadores',
            'url_search' => $this->index_route . '/indicadores-index'
        ];

        $arr_totais_por_tipo = [];
        $arr_totais_por_tipo_orcado = [];

        if ($mes_filtro != '') {
            $indicadores = $model_movimentos->gerarRelatorioIndicadoresMensal($_SESSION['id_familia'], $ano_filtro, $mes_filtro);
        } else {
            $indicadores = $model_movimentos->gerarRelatorioIndicadoresMensal($_SESSION['id_familia']);
        }

        list($realizado, $orcado) = $model_movimentos->gerarRelatorioIndicadoresAnual($_SESSION['id_familia'], $ano_filtro);

        foreach ($indicadores as $v) {
            isset($arr_totais_por_tipo[$v['tipo']]) ? $arr_totais_por_tipo[$v['tipo']] += $v['totalRealizado'] : $arr_totais_por_tipo[$v['tipo']] = $v['totalRealizado'];
            isset($arr_totais_por_tipo_orcado[$v['tipo']]) ? $arr_totais_por_tipo_orcado[$v['tipo']] += $v['totalOrcado'] : $arr_totais_por_tipo_orcado[$v['tipo']] = $v['totalOrcado'];
        }

        $this->view->data['indicadores'] = $indicadores;
        list($this->view->data['arr_cat'], $this->view->data['indicadores_anuais']) = $this->prepararDadosAnual($realizado, $orcado);
        $this->view->data['arr_totais_por_tipo'] = $arr_totais_por_tipo;
        $this->view->data['arr_totais_por_tipo_orcado'] = $arr_totais_por_tipo_orcado;

        $this->renderPage(
            conteudo: 'indicadores_index'
        );
    }

    private function prepararDadosAnual($realizado, $orcado): array
    {
        $indicadores_anuais = [];
        $arr_cat = [];

        foreach ($realizado as $item) {
            foreach ($item as $it) {
                $categoria_id = $it['idCategoria'];
                $categoria_nome = $it['categoria'];
                $total_realizado = $it['total'];
                $mes = $it['mes'];
                $tipo = $it['tipo'];

                $arr_cat[$categoria_id]['nome'] = $categoria_nome;
                $arr_cat[$categoria_id]['tipo'] = $tipo;

                $indicadores_anuais[$mes][$categoria_id]['realizado'] = $total_realizado;
            }
        }

        foreach ($orcado as $item) {
            foreach ($item as $it) {
                $categoria_id = $it['idCategoria'];
                $categoria_nome = $it['categoria'];
                $total_orcado = $it['total'];
                $mes = $it['mes'];
                $tipo = $it['tipo'];

                $arr_cat[$categoria_id]['nome'] = $categoria_nome;
                $arr_cat[$categoria_id]['tipo'] = $tipo;

                $indicadores_anuais[$mes][$categoria_id]['orcado'] = $total_orcado;
            }
        }

        // Ordenar o $arr_cat pelo tipo: Receita (R) , Despesa (D) e outras...
        uasort($arr_cat, function($a, $b) {
            $order = ['R' => 1, 'D' => 2, 'A' => 3, 'RA' => 4];
            $pesoA = $order[$a['tipo']] ?? 5;
            $pesoB = $order[$b['tipo']] ?? 5;

            if ($pesoA == $pesoB) {
                return strcmp($a['nome'], $b['nome']);
            }
            return $pesoA <=> $pesoB;
        });


        return [$arr_cat, $indicadores_anuais];
    }
}