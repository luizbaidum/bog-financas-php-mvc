<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\NumbersHelper;
use MF\Model\Model;
use src\Models\MetasMensais\MetasMensaisDAO;
use src\Models\MetasMensais\MetasMensaisEntity;
use src\Models\Movimentos\MovimentosDAO;
use src\Models\Proprietarios\ProprietariosEntity;

class MetasMensaisController extends Controller {
    public function lancarMetasMensais() 
    {
        $model = new Model();

        $this->view->settings = [
            'action'   => $this->index_route . '/cad-metas-mensais',
            'redirect' => $this->index_route . '/metas-mensais',
            'title'    => 'Metas Mensais',
        ];

        $this->view->data['lista_proprietarios'] = $model->selectAll(new ProprietariosEntity, [], [], []);

        $this->renderPage(
            conteudo: 'metas_mensais',
            base_interna: 'base_cruds'
        );
    }

    public function cadastrarMetasMensais()
    {
        if ($this->isSetPost()) {
            try {
                $objeto = new MetasMensaisEntity();

                $objeto->data = $_POST['data'];
                $objeto->totalReceitas = NumbersHelper::formatBRtoUS($_POST['totalReceitas']);
                $objeto->vlrEconomia = NumbersHelper::formatBRtoUS($_POST['vlrEconomizar']);
                $objeto->idProprietario = $_POST['idProprietario'];
                $objeto->atualizado = 'F';

                $ret = (new MetasMensaisDAO())->cadastrar(new MetasMensaisEntity, $objeto);

                if (!$ret['result']) {
                    throw new Exception($this->msg_retorno_falha);
                }

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

    public function metasMensaisIndex() 
    {
        $model_metas_mensais = new MetasMensaisDAO();

        $this->view->settings = [
            'url' => $this->index_route . '/obter-metas-mensais',
            'div' => 'metas-content'
        ];

        $this->view->data['lista_proprietarios'] = $model_metas_mensais->selectAll(new ProprietariosEntity, [], [], []);

        $this->renderPage(
            conteudo: 'metas_mensais_index'
        );
    }

    public function obterMetas(): void
    {
        $this->view->data['lista_mm'] = $this->tratarMetas();

        $this->renderSimple('conteudo_metas');
    }

    private function tratarMetas(): array
    {
        $lista_mm = (new MetasMensaisDAO())->listarMetasMensais($_GET['idProprietario'], date('Y'));
        $realizado = (new MovimentosDAO())->consultarAplicacoesPorMes($_GET['idProprietario'], date('Y'));

        $meses = array(
            '01' => 'Janeiro',
            '02' => 'Fevereiro',
            '03' => 'Março',
            '04' => 'Abril',
            '05' => 'Maio',
            '06' => 'Junho',
            '07' => 'Julho',
            '08' => 'Agosto',
            '09' => 'Setembro',
            '10' => 'Outubro',
            '11' => 'Novembro',
            '12' => 'Dezembro'
        );

        foreach ($meses as $num => $str) {
            foreach ($lista_mm as $k => $v_mm)  {
                $tmp_xpl = explode('-', $v_mm['data']);
                $mes = $tmp_xpl[1];
                $ano = $tmp_xpl[0];

                if ($num == $mes) {
                    $lista_mm_ret[$k] = [
                        'mesAno'                 => $str . ' de ' . $ano,
                        'totalReceitas'          => $v_mm['totalReceitas'],
                        'vlrEconomia'            => $v_mm['vlrEconomia'],
                        'proprietario'           => $v_mm['proprietario'],
                        'vlrEconomiaRealizado'   => 1,
                        'totalReceitasRealizado' => 1
                    ];

                    foreach ($realizado as $r) {
                        if ($r['mes'] > 0 && $r['mes'] < 10) {
                            $r['mes'] = '0' . $r['mes'];
                        }

                        if ($num == $r['mes']) {
                            $lista_mm_ret[$k]['vlrEconomiaRealizado'] = $r['vlrEconomiaRealizado'];
                            $lista_mm_ret[$k]['totalReceitasRealizado'] = $r['totalReceitasRealizado'];
                        }
                    }
                }
            }
        }

        return $lista_mm_ret ?? [];
    }
}