<?php

namespace src;

require __DIR__ . '/../../conn/ConfigConnection.php';

use ConfigConnection;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\BrowserConsoleHandler;

class Conexao {

	public function getDb()
	{
		try {
			$con = (new ConfigConnection())->getConnection();
			return $con;
		} catch(\PDOException $e) {
			$logger = new Logger('log_conexao.txt');
			$logger->pushHandler(new BrowserConsoleHandler(Logger::ERROR));
			$logger->pushHandler(new StreamHandler(__DIR__ . '/log_conexao.txt', Logger::ERROR));
			$logger->error($e->getMessage());

			echo 'Atenção! Erro ao conectar-se ao Banco de Dados. Por favor, entrar em contato com o suporte';

			exit;
		}
	}
}