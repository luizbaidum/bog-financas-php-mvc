<?php

header('Content-type: text/html; charset=utf-8');    
setlocale(LC_ALL, NULL); //limpa com defaults
setlocale(LC_ALL, 'pt_BR.utf-8');

session_start();

/**
 * Alterar diretório
 */
include ("C:\\Users\\luizb\\Desktop\\github\\web-financas-mvc\\financeiro\\vendor\\MF\\Security\\ErrorTreatment.php");

require_once ("C:\Users\luizb\Desktop\github\web-financas-mvc\\financeiro\\vendor\\autoload.php");

set_error_handler('errorHandler'); 
set_exception_handler('exceptionHandler'); 

$route = new \src\Routes;