<?php

namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use src\Models\Categorias\CategoriasDAO;
use src\Models\Categorias\CategoriasEntity;

class CategoriasController extends Controller {

    public function consultarCategoriasInvestimentos()
    {
        $categorias = (new CategoriasDAO())->selecionarCategoriasTipoAeRA();
        echo json_encode($categorias);
    }

    public function categorias()
    {
        $this->view->settings = [
            'action'   => $this->index_route . '/cad_categorias',
            'redirect' => $this->index_route . '/categorias',
            'title'    => 'Cadastro de Categoria',
        ];

        $this->renderPage(main_route: $this->index_route . '/categorias', conteudo: 'categorias', base_interna: 'base_cruds');
    }

    public function cadastrarCategorias()
    {
        if ($this->isSetPost()) {
            try {
                $_POST['tipo'] = strtoupper($_POST['tipo']);

                if ($_POST['tipo'] != 'R' && $_POST['tipo'] != 'D' && $_POST['tipo'] != 'A' && $_POST['tipo'] != 'RA') {
                    throw new Exception('Atenção: Definir tipo como R, D, A ou RA.');
                }

                if ($_POST['sinal'] != '+' && $_POST['sinal'] != '-') {
                    throw new Exception('Atenção: Definir sinal como + ou -.');
                }

                if ($_POST['tipo'] == 'R' || $_POST['tipo'] == 'RA') {
                    if ($_POST['sinal'] != '+') {
                        throw new Exception("Atenção: Definir sinal como '+', pois 'Receita' ou 'Resgate de Aplicação' são entradas.");
                    }
                } else {
                   if ($_POST['sinal'] != '-') {
                        throw new Exception("Atenção: Definir sinal como '-', pois 'Aplicação' ou 'Despesa' são saídas.");
                    } 
                }

                if ($_POST['regularidade'] == '') {
                    throw new Exception("Atenção: Escolher uma opção para 'Regularidade'");
                }

                $ret = (new CategoriasDAO())->cadastrar(new CategoriasEntity, $_POST);

                if ($ret['result']) {
                    $array_retorno = array(
                        'result'   => $ret['result'],
                        'mensagem' => $this->msg_retorno_sucesso
                    );

                    echo json_encode($array_retorno);
                } else {
                    throw new Exception($this->msg_retorno_falha);
                }
            } catch (Exception $e) {
                $array_retorno = array(
                    'result'   => false,
                    'mensagem' => $e->getMessage(),
                );

                echo json_encode($array_retorno);
            }
        }
    }
}