<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class ConferenciaExtratoService
{
    private $conexao;

    public function __construct($conexao)
    {
        $this->conexao = $conexao;
    }

    /**
     * Processa a conferência do extrato bancário
     *
     * @param array $dadosFormulario ['proprietario', 'mes_ano', 'tipo_arquivo']
     * @param array $arquivo $_FILES['arquivo']
     * @return array Resultado da conferência
     */
    public function processarConferencia($dadosFormulario, $arquivo)
    {
        try {
            // 1. Validar dados recebidos
            $this->validarDados($dadosFormulario, $arquivo);

            // 2. Buscar movimentos lançados no sistema
            $movimentosSistema = $this->buscarMovimentosSistema(
                $dadosFormulario['proprietario'],
                $dadosFormulario['mes_ano']
            );

            // 3. Ler e processar arquivo do extrato
            $movimentosExtrato = $this->lerArquivoExtrato(
                $arquivo,
                $dadosFormulario['tipo_arquivo']
            );

            // 4. Fazer conferência/match entre os dados
            $resultado = $this->conferirMovimentos($movimentosSistema, $movimentosExtrato);

            return [
                'sucesso' => true,
                'dados' => $resultado
            ];

        } catch (\Exception $e) {
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
        if (empty($dados['proprietario'])) {
            throw new \Exception('Proprietário não informado');
        }

        if (empty($dados['mes_ano'])) {
            throw new \Exception('Mês/Ano não informado');
        }

        if (empty($dados['tipo_arquivo'])) {
            throw new \Exception('Tipo de arquivo não informado');
        }

        if (!isset($arquivo) || $arquivo['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Arquivo não enviado ou erro no upload');
        }

        $tiposPermitidos = ['pdf', 'xlsx', 'csv'];
        if (!in_array($dados['tipo_arquivo'], $tiposPermitidos)) {
            throw new \Exception('Tipo de arquivo inválido');
        }
    }

    /**
     * Busca movimentos lançados no sistema
     */
    private function buscarMovimentosSistema($idProprietario, $mesAno)
    {
        // Converter mes_ano (2024-01) para filtro SQL
        list($ano, $mes) = explode('-', $mesAno);

        $sql = "SELECT 
                    m.idMovimento,
                    m.data,
                    m.descricao,
                    m.valor,
                    m.tipo,
                    c.categoria
                FROM movimentos m
                LEFT JOIN categorias c ON m.idCategoria = c.idCategoria
                WHERE m.idProprietario = :proprietario
                AND YEAR(m.data) = :ano
                AND MONTH(m.data) = :mes
                ORDER BY m.data, m.idMovimento";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindParam(':proprietario', $idProprietario);
        $stmt->bindParam(':ano', $ano);
        $stmt->bindParam(':mes', $mes);
        $stmt->execute();

        $movimentos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Normalizar valores
        return array_map(function($mov) {
            return [
                'id' => $mov['idMovimento'],
                'data' => $mov['data'],
                'descricao' => $this->normalizarTexto($mov['descricao']),
                'valor' => floatval($mov['valor']),
                'tipo' => $mov['tipo'],
                'categoria' => $mov['categoria']
            ];
        }, $movimentos);
    }

    /**
     * Lê e processa o arquivo de extrato
     */
    private function lerArquivoExtrato($arquivo, $tipoArquivo)
    {
        switch ($tipoArquivo) {
            case 'pdf':
                return $this->lerPDF($arquivo['tmp_name']);
            case 'xlsx':
                return $this->lerExcel($arquivo['tmp_name']);
            case 'csv':
                return $this->lerCSV($arquivo['tmp_name']);
            default:
                throw new \Exception('Tipo de arquivo não suportado');
        }
    }

    /**
     * Lê arquivo PDF
     */
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

    /**
     * Lê arquivo Excel
     */
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

            if (!empty($dados[0])) {
                $movimentos[] = [
                    'data' => $this->converterData($dados[0]),
                    'descricao' => $this->normalizarTexto($dados[1] ?? ''),
                    'valor' => floatval($dados[2] ?? 0),
                    'tipo' => $this->identificarTipo($dados[2] ?? 0)
                ];
            }
        }

        return $movimentos;
    }

    /**
     * Lê arquivo CSV
     */
    private function lerCSV($caminhoArquivo)
    {
        $movimentos = [];

        if (($handle = fopen($caminhoArquivo, 'r')) !== false) {
            // Pular cabeçalho
            fgetcsv($handle, 1000, ';');

            while (($dados = fgetcsv($handle, 1000, ';')) !== false) {
                if (!empty($dados[0])) {
                    $movimentos[] = [
                        'data' => $this->converterData($dados[0]),
                        'descricao' => $this->normalizarTexto($dados[1] ?? ''),
                        'valor' => floatval(str_replace(',', '.', $dados[2] ?? 0)),
                        'tipo' => $this->identificarTipo($dados[2] ?? 0)
                    ];
                }
            }
            fclose($handle);
        }

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
                    'tipo' => $this->identificarTipo($matches[3])
                ];
            }
        }

        return $movimentos;
    }

    /**
     * Faz a conferência entre movimentos do sistema e do extrato
     */
    private function conferirMovimentos($movimentosSistema, $movimentosExtrato)
    {
        $conferidos = [];
        $naoConciliados_sistema = [];
        $naoConciliados_extrato = [];

        // Criar cópia para marcar items conferidos
        $extratoRestante = $movimentosExtrato;

        foreach ($movimentosSistema as $movSistema) {
            $encontrado = false;

            foreach ($extratoRestante as $key => $movExtrato) {
                // Critérios de match
                $matchData = $movSistema['data'] === $movExtrato['data'];
                $matchValor = abs($movSistema['valor'] - $movExtrato['valor']) < 0.01;
                $matchDescricao = $this->calcularSimilaridade(
                    $movSistema['descricao'],
                    $movExtrato['descricao']
                ) > 0.7; // 70% de similaridade

                if ($matchData && $matchValor && $matchDescricao) {
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

            if (!$encontrado) {
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

    /**
     * Identifica tipo de movimento (débito/crédito)
     */
    private function identificarTipo($valor)
    {
        return floatval($valor) < 0 ? 'debito' : 'credito';
    }
}