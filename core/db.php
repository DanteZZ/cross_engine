<?php
	class DB {
		public $connection;
		public function connect($host,$user,$pass,$base){ //Функция подключения
			$this->connection = new PDO('mysql:host='.$host.';dbname='.$base, $user, $pass);
			$this->connection->exec("SET CHARACTER SET utf8");
		}

		public function query($query,$row=false) { //Выполнить запрос с возвратом
			$res = $this->connection->query($query);
			if (!$res) {
				return false;
			} else {
				if ($res->rowCount() > 0) {
					if (!$row) { //Если нужны все строки
						return $res->fetchAll(PDO::FETCH_ASSOC);
					} else { //Если только одна
						return $res->fetch(PDO::FETCH_ASSOC);
					}
				} else {
					return false;
				}
			}
		}

		public function exec($query) { //Выполнить запрос без возврата
			$res = $this->connection->exec($query);
			return $res;
		}
	};

	$DB = new DB; //Внешний
?>