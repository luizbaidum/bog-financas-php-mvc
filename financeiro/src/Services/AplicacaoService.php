<?php 

namespace src\Services;

use MF\Model\Model;
use src\Models\Categorias\CategoriasDAO;
use src\Models\Investimentos\InvestimentosEntity;
use src\Models\Objetivos\ObjetivosEntity;
use src\Models\Rendimentos\RendimentosEntity;

class AplicacaoService {
    private $categoria_A;
    private $categoria_RA;

    private CategoriasDAO $model_categorias;
    private Model $model;

    public function __construct(
        ?CategoriasDAO $model_categorias = null,
        ?Model $model = null
    ) {
        $this->model_categorias = $model_categorias ?? new CategoriasDAO();
        $this->model = $model ?? new Model();

        $categorias = $this->model_categorias->selecionarCategoriasTipoAeRA();

        $this->categoria_A  = $categorias['A'];
        $this->categoria_RA = $categorias['RA'];
    }

    public function inserirMovimentacaodeAplicacao(
        $id_conta_invest,
        $id_objetivo,
        $id_movimento,
        $id_categoria,
        $valor,
        $data_rend
    ) {
        switch ($id_categoria) {
            case $this->categoria_A:
                $tipo = 4;

                $valor_aplicado = $valor;
                if ($valor_aplicado < 0) {
                    $valor_aplicado *= -1;
                }

                $this->aplicarObjetivo($id_objetivo, $valor_aplicado, $id_conta_invest);
                break;

            case $this->categoria_RA:
                $tipo = 3;

                $valor_aplicado = $valor;
                if ($valor_aplicado > 0) {
                    $valor_aplicado *= -1;
                }

                $this->aplicarObjetivo($id_objetivo, $valor_aplicado, $id_conta_invest);
                break;

            default:
                $tipo = '';
                $valor_aplicado = 0;
        }

        $obj_rendimento = new RendimentosEntity();
        $obj_rendimento->idContaInvest   = $id_conta_invest;
        $obj_rendimento->valorRendimento = $valor_aplicado;
        $obj_rendimento->tipo            = $tipo;
        $obj_rendimento->dataRendimento  = $data_rend;
        $obj_rendimento->idMovimento     = $id_movimento;
        $obj_rendimento->idObj           = (empty($id_objetivo) ? 0 : $id_objetivo);

        $this->model->cadastrar($obj_rendimento, $obj_rendimento);

        $saldo_atual = $this->model->getSaldoAtual(
            new InvestimentosEntity(),
            $id_conta_invest
        );

        $item = [
            'saldoAtual' => $saldo_atual + $valor_aplicado
        ];

        $item_where = [
            'idContaInvest' => $id_conta_invest
        ];

        $this->model->atualizar(
            new InvestimentosEntity(),
            $item,
            $item_where
        );
    }

    public function aplicarObjetivo(
        string|null $id_objetivo,
        float $valor_aplicado,
        string $id_conta_invest
    ): void {
        if (!empty($id_objetivo)) {
            $saldo_atual = $this->model->getSaldoAtual(
                new ObjetivosEntity(),
                $id_objetivo
            );

            $item = [
                'saldoAtual' => $saldo_atual + $valor_aplicado
            ];

            $item_where = [
                'idObj' => $id_objetivo
            ];

            $this->model->atualizar(
                new ObjetivosEntity(),
                $item,
                $item_where
            );
        } else {
            $objetivos = $this->model->selectAll(
                new ObjetivosEntity(),
                [
                    ['idContaInvest', '=', $id_conta_invest],
                    ['finalizado', '=', '"F"']
                ],
                [],
                []
            );

            if (!empty($objetivos)) {
                foreach ($objetivos as $value) {
                    $item = [
                        'saldoAtual' => $value['saldoAtual'] +
                            ($valor_aplicado * ($value['percentObjContaInvest'] / 100))
                    ];

                    $item_where = [
                        'idObj' => $value['idObj']
                    ];

                    $this->model->atualizar(
                        new ObjetivosEntity(),
                        $item,
                        $item_where
                    );
                }
            }
        }
    }
}