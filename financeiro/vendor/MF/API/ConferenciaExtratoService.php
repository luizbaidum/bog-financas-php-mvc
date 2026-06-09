<?php

namespace MF\API;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;
use Exception;
use MF\Helpers\DateHelper;

class ConferenciaExtratoService
{
    /**
     * Processa a conferência do extrato bancário
     *
     * @param array $dadosFormulario ['proprietario', 'mes_ano', 'tipo_arquivo']
     * @param array $arquivo $_FILES['arquivo']
     * @return array Resultado da conferência
     */
    public function processarConferencia($dadosFormulario, $movimentos, $arquivo)
    {
        try {
            // 1. Validar dados recebidos
            $this->validarDados($dadosFormulario, $arquivo);

            // 2. Buscar movimentos lançados no sistema
            $movimentosSistema = $movimentos;

            // 3. Ler e processar arquivo do extrato
            $movimentosExtrato = $this->lerArquivoExtrato(
                $arquivo,
                $dadosFormulario['tipo_arquivo'],
                $dadosFormulario['banco']
            );

            // 4. Fazer conferência/match entre os dados
            $resultado = $this->conferirMovimentos($movimentosSistema, $movimentosExtrato);

            // echo '<pre>';
            // print_r($resultado);
            // echo '</pre>';
            // exit;

            return [
                'sucesso' => true,
                'dados' => $resultado
            ];

        } catch (Exception $e) {
            return [
                'sucesso' => false,
                'erro' => $e->getMessage()
            ];
        }
    }

    /**
     * Valida os dados recebidos
     */
    private function validarDados($dados, $arquivo)
    {
        if (empty($dados['mes_ano'])) {
            throw new Exception('Mês/Ano não informado');
        }

        if (empty($dados['banco'])) {
            throw new Exception('Banco não informado');
        }

        if (empty($dados['tipo_arquivo'])) {
            throw new Exception('Tipo de arquivo não informado');
        }

        if (!isset($arquivo) || $arquivo['error'] != UPLOAD_ERR_OK) {
            throw new Exception('Arquivo não enviado ou erro no upload');
        }

        // Validar se o tipo do arquivo enviado corresponde ao tipo declarado no formulário
        $this->validarTipoArquivo($arquivo, $dados['tipo_arquivo']);

        // Validação de tipos permitidos por banco
        $banco = strtolower($dados['banco']);
        $tipoArquivo = strtolower($dados['tipo_arquivo']);

        if ($tipoArquivo == 'xlsx') {
            throw new Exception('Formato XLSX ainda não é permitido. Use CSV ou PDF conforme o banco.');
        }

        // Validações específicas por banco
        switch ($banco) {
            case 'bradesco':
            case 'bb':
                // Bradesco e BB aceitam apenas CSV
                if ($tipoArquivo != 'csv') {
                    throw new Exception('Para ' . strtoupper($banco) . ', apenas arquivos CSV são permitidos');
                }
                break;

            case 'cef':
                // CEF aceita apenas PDF
                if ($tipoArquivo != 'pdf') {
                    throw new Exception('Para CEF, apenas arquivos PDF são permitidos');
                }
                break;

            default:
                throw new Exception('Banco não reconhecido');
        }
    }

    private function validarTipoArquivo($arquivo, $tipoDeclarado)
    {
        // Obter a extensão real do arquivo enviado
        $nomeArquivo = $arquivo['name'];
        $extensaoReal = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
        $tipoDeclarado = strtolower($tipoDeclarado);

        // Verificar correspondência
        if ($extensaoReal !== $tipoDeclarado) {
            throw new Exception(
                "Incompatibilidade de arquivo: você selecionou '{$tipoDeclarado}' no formulário, " .
                "mas o arquivo enviado é do tipo '{$extensaoReal}'. " .
                "Por favor, envie um arquivo {$tipoDeclarado} ou selecione o tipo correto no formulário."
            );
        }

        // Validação adicional com MIME type para maior segurança
        $mimeType = mime_content_type($arquivo['tmp_name']);
        $mimeTypeEsperado = $this->getMimeTypeEsperado($tipoDeclarado);

        if (!in_array($mimeType, $mimeTypeEsperado)) {
            throw new Exception(
                "O arquivo enviado não parece ser um {$tipoDeclarado} válido. " .
                "Tipo detectado: {$mimeType}"
            );
        }
    }

    /**
     * Retorna os MIME types esperados para cada tipo de arquivo
     */
    private function getMimeTypeEsperado($tipo)
    {
        $mimeTypes = [
            'pdf' => ['application/pdf'],
            'csv' => ['text/csv', 'text/plain', 'application/csv', 'text/comma-separated-values'],
            'xlsx' => [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel'
            ]
        ];

        return $mimeTypes[$tipo] ?? [];
    }

    /**
     * Lê e processa o arquivo de extrato
     */
    private function lerArquivoExtrato($arquivo, $tipoArquivo, $banco)
    {
        switch ($tipoArquivo) {
            case 'pdf':
                return $this->lerPDF($arquivo['tmp_name']);
            case 'xlsx':
                return $this->lerExcel($arquivo['tmp_name']);
            case 'csv':
                if (strtolower($banco) == 'bradesco') {
                    return $this->lerCSVBradesco($arquivo['tmp_name']);
                } else {
                    return $this->lerCSVBB($arquivo['tmp_name']);
                }
            default:
                throw new Exception('Tipo de arquivo não suportado');
        }
    }

    private function lerPDF($caminhoArquivo)
    {
        // Requer: composer require smalot/pdfparser
        $parser = new PdfParser();
        $pdf = $parser->parseFile($caminhoArquivo);
        $texto = $pdf->getText();

        // Processar o texto do PDF e extrair movimentos
        // IMPORTANTE: O padrão varia por banco - ajustar conforme necessário
        return $this->extrairMovimentosDeTexto($texto);
    }

    private function lerExcel($caminhoArquivo)
    {
        // Requer: composer require phpoffice/phpspreadsheet
        $spreadsheet = IOFactory::load($caminhoArquivo);
        $sheet = $spreadsheet->getActiveSheet();
        $movimentos = [];

        // Assumindo estrutura: Data | Descrição | Valor | Tipo
        // Ajustar conforme layout do banco
        foreach ($sheet->getRowIterator(2) as $row) { // Pula cabeçalho
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $dados = [];
            foreach ($cellIterator as $cell) {
                $dados[] = $cell->getValue();
            }

            if (! empty($dados[0])) {
                $movimentos[] = [
                    'data' => $this->converterData($dados[0]),
                    'descricao' => $this->normalizarTexto($dados[1] ?? ''),
                    'valor' => floatval($dados[2] ?? 0)
                ];
            }
        }

        return $movimentos;
    }

    private function lerCSVBradesco($caminhoArquivo)
    {
        $movimentos = [];

        if (($handle = fopen($caminhoArquivo, 'r')) !== false) {
            // Pular cabeçalho
            fgetcsv($handle, 1000, ';');

            while (($dados = fgetcsv($handle, 1000, ';')) !== false) {
                if (! empty($dados[0])) {
                    $movimentos[] = [
                        'data' => $this->converterData($dados[0]),
                        'descricao' => $this->normalizarTexto($dados[1] ?? ''),
                        'credito' => floatval(str_replace(',', '.', $dados[3] ?? 0)),
                        'debito' => floatval(str_replace(',', '.', $dados[4] ?? 0))
                    ];
                }
            }
            fclose($handle);
        }

        return $movimentos;
    }

    private function lerCSVBB($caminhoArquivo)
    {
        // Função genérica para BB
        $movimentos = [];

        if (($handle = fopen($caminhoArquivo, 'r')) !== false) {
            // Pular cabeçalho
            fgetcsv($handle, 1000, ';');

            while (($dados = fgetcsv($handle, 1000, ',')) !== false) {
                if (!empty($dados[0])) {

                    $valor = floatval(str_replace(',', '.', $dados[4] ?? 0));

                    $movimentos[] = [
                        'data' => $this->converterData($dados[0]),
                        'descricao' => $this->normalizarTexto($dados[1] ?? ''),
                        'credito' => $valor >= 0 ? $valor : 0,
                        'debito' => $valor < 0 ? abs($valor) : 0
                    ];
                }
            }
            fclose($handle);
        }

        // echo '<pre>';
        // print_r($movimentos);
        // echo '</pre>';
        // exit;

        return $movimentos;
    }

    /**
     * Extrai movimentos de texto (para PDF)
     */
    private function extrairMovimentosDeTexto($texto)
    {
        $movimentos = [];
        $linhas = explode("\n", $texto);

        // Padrão genérico - AJUSTAR conforme formato do banco
        // Exemplo: "01/01/2024 COMPRA LOJA X 100,00"
        $pattern = '/(\d{2}\/\d{2}\/\d{4})\s+(.+?)\s+([\d.,]+)/';

        foreach ($linhas as $linha) {
            if (preg_match($pattern, $linha, $matches)) {
                $movimentos[] = [
                    'data' => $this->converterData($matches[1]),
                    'descricao' => $this->normalizarTexto($matches[2]),
                    'valor' => floatval(str_replace(',', '.', str_replace('.', '', $matches[3]))),
                ];
            }
        }

        echo '<pre>';
        print_r($movimentos);
        echo '</pre>';
        exit;

        return $movimentos;
    }

    /**
     * Faz a conferência entre movimentos do sistema e do extrato
     */
    private function conferirMovimentos($movimentosSistema, $movimentosExtrato)
    {
        // echo '<pre>';
        // print_r($movimentosSistema);
        // print_r($movimentosExtrato);
        // echo '</pre>';
        // exit;
        $conferidos = [];
        $naoConciliados_sistema = [];
        $naoConciliados_extrato = [];

        // Criar cópia para marcar items conferidos
        $extratoRestante = $movimentosExtrato;

        foreach ($movimentosSistema as $movSistema) {
            $encontrado = false;

            $valor_sistema = $movSistema['valor'];
            $data_sistema = $movSistema['dataMovimento'];

            foreach ($extratoRestante as $key => $movExtrato) {
                $valor_extrato = $movExtrato['credito'] > 0 ? $movExtrato['credito'] : (abs($movExtrato['debito']) * -1);
                $data_extrato = $movExtrato['data'];

                // echo '<pre>';
                // echo "Comparando:\n";
                // echo "Sistema: Data={$data_sistema}, Valor={$valor_sistema}, Descrição={$movSistema['nomeMovimento']}\n";
                // echo "Extrato: Data={$data_extrato}, Valor={$valor_extrato}, Descrição={$movExtrato['descricao']}\n";
                // echo '</pre>';

                $matchData = $data_sistema == $movExtrato['data'];
                $matchValor = abs($valor_sistema - $valor_extrato) < 0.01;
                $matchDescricao = $this->calcularSimilaridade(
                    $movSistema['nomeMovimento'],
                    $movExtrato['descricao']
                ) > 0.7; // 70% de similaridade

                if ($matchData && $matchValor /*&& $matchDescricao*/) {
                    $conferidos[] = [
                        'sistema' => $movSistema,
                        'extrato' => $movExtrato,
                        'status' => 'OK'
                    ];

                    unset($extratoRestante[$key]);
                    $encontrado = true;
                    break;
                }
            }

            if (! $encontrado) {
                $naoConciliados_sistema[] = $movSistema;
            }
        }

        $naoConciliados_extrato = array_values($extratoRestante);

        return [
            'conferidos' => $conferidos,
            'sistema_nao_encontrado' => $naoConciliados_sistema,
            'extrato_nao_encontrado' => $naoConciliados_extrato,
            'resumo' => [
                'total_conferidos' => count($conferidos),
                'total_sistema' => count($movimentosSistema),
                'total_extrato' => count($movimentosExtrato),
                'percentual_conferencia' => count($movimentosSistema) > 0 
                    ? round((count($conferidos) / count($movimentosSistema)) * 100, 2) 
                    : 0
            ]
        ];
    }

    /**
     * Normaliza texto para comparação
     */
    private function normalizarTexto($texto)
    {
        $texto = mb_strtoupper($texto, 'UTF-8');
        $texto = preg_replace('/[^A-Z0-9\s]/', '', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        return trim($texto);
    }

    /**
     * Calcula similaridade entre textos
     */
    private function calcularSimilaridade($texto1, $texto2)
    {
        similar_text($texto1, $texto2, $percentual);
        return $percentual / 100;
    }

    /**
     * Converte data para formato padrão
     */
    private function converterData($data)
    {
        // Tenta diferentes formatos
        $formatos = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y'];

        foreach ($formatos as $formato) {
            $dataObj = \DateTime::createFromFormat($formato, $data);
            if ($dataObj) {
                return $dataObj->format('Y-m-d');
            }
        }

        return $data;
    }
}