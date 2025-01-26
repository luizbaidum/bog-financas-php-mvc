<?php
	class ConfigConnection {

		private $con;
		private $host = 'localhost';
		private $db   = 'meu_financeiro';
		private $user = 'root';
		private $pw   = '';

		private function doConnect()
		{
			$this->con = new \PDO("mysql:host=$this->host; dbname=$this->db", $this->user, $this->pw, array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, 
                \PDO::ATTR_PERSISTENT => false,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            ));
		}

		public function getConnection()
		{
			$this->doConnect();
			return $this->con;
		}
	}
?>