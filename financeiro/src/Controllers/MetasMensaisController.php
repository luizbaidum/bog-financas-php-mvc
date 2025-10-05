<?php
namespace src\Controllers;

use Exception;
use MF\Controller\Controller;
use MF\Helpers\NumbersHelper;
use MF\Model\Model;
use src\Models\MetasMensais\MetasMensaisDAO;
use src\Models\MetasMensais\MetasMensaisEntity;
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

        $this->view->data['lista_proprietarios'] = $model_metas_mensais->selectAll(new ProprietariosEntity, [], [], []);
        $this->view->data['lista_mm'] = $this->tratarMetas($model_metas_mensais);

        $this->renderPage(
            conteudo: 'metas_mensais_index'
        );
    }

    private function tratarMetas(MetasMensaisDAO $dao): array 
    {
        $lista_mm = $dao->listarMetasMensais(new MetasMensaisEntity, [], [], []);

        $meses = array(
            '01'  => "Janeiro",
            '02'  => "Fevereiro",
            '03'  => "Março",
            '04'  => "Abril",
            '05'  => "Maio",
            '06'  => "Junho",
            '07'  => "Julho",
            '08'  => "Agosto",
            '09'  => "Setembro",
            '10' => "Outubro",
            '11' => "Novembro",
            '12' => "Dezembro"
        );

        foreach ($lista_mm as $v) {
            $tmp_xpl = explode('-', $v['data']);
            $v['mesAno'] = $meses[$tmp_xpl[1]] . ' de ' . $tmp_xpl[0];

            $lista_mm_ret[] = $v;
        }

        return $lista_mm_ret ?? [];
    }
}