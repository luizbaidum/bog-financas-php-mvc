<?php

use Monolog\Logger;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;

function errorHandler($error_level, $error_message, $error_file, $error_line, $error_context = "")
{
    switch ($error_level) {
        case 1:
            $type_error = 'ERROR';
            break;
        case 2:
            $type_error = 'WARNING';
            break;
        case 8:
            $type_error = 'NOTICE';
            break;
        default:
            $type_error = 'OTHER';
    }

    $logger = new Logger('log_php.txt');

    $logger->pushHandler(new BrowserConsoleHandler(Logger::ERROR));
    $logger->pushHandler(new StreamHandler(__DIR__ . '/log_php.txt', Logger::ERROR));

    $logger->error($error_message, ['local' => $error_file, 'linha' => $error_line, 'tipo' => $type_error]);

    //echo "<br><h4>Aconteceu um erro fatal. Entre em contato com o suporte.</h4><br>";
    /**
     * Para visualizar, descomente.
     */
   //print_r(traceError($exception));
}

function traceError($exception)
{
    $trace = $exception->getTrace();

    return $trace;
}

//Erro fatal não capturado pelo try catch. A função que chama a exceptionHandler interrompe o sistema.
function exceptionHandler($exception)
{
    $logger = new Logger('log_php.txt');

    $logger->pushHandler(new BrowserConsoleHandler(Logger::ERROR));
    $logger->pushHandler(new StreamHandler(__DIR__ . '/log_php.txt', Logger::ERROR));

    $logger->error($exception->getMessage(), ['local' => $exception->getFile(), 'linha' => $exception->getLine(), 'type' => 'ERROR']);

    $array_retorno = array(
        'result'   => false,
        'mensagem' => '<div style="margin: 20px;">Aconteceu um erro inesperado. Por favor entrar em contato com o desenvolvedor.</div>',
    );

    echo json_encode($array_retorno);

   /**
    * Para visualizar, descomente.
    */
   //print_r(traceError($exception));
}