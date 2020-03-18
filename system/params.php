<?php
	class PARAMS {

		public function is($name){ //Проверяет существует ли параметр в БД
			GLOBAL $DB; //Класс работы с базами данных
			$res = $DB->query("SELECT * FROM `parameters` WHERE `name` = ".$DB->connection->quote($name),true);
			if ($res) {return true;} else {return false;};
		}

		public function set($name,$value,$multilang=-1,$locale=false){ //Устанавливает параметр в БД
			GLOBAL $DB, $se_multilocal, $se_localization, $LNG, $USR;
			
			if ($se_multilocal) {
				if ($multilang === -1) {
					$multilang = true;
				} else {
					$multilang = false;
				};
			} else {
				$multilang = false;
			};

			if (!$locale) {
				$locale = $LNG->locale;
			};

			$user = $USR->info();
			if ($user) {$user = $user['id'];} else {$user = 0;}

			switch (gettype($value)) { //Проверка типа данных
				case "boolean":
					$type = "bool";
					if ($value) {$value = "true";} else {$value = "false";};
				break;
				case "integer":
					$type = "int";
				break;
				case "double":
					$type = "float";
				break;
				case "string":
					$type = "string";
				break;
				case "array":
					$type = "array";
					$value = json_encode($value,JSON_UNESCAPED_UNICODE);
				break;
			};

			if ($this->is($name)) { //Если существует параметр
				$par = $DB->query("SELECT * FROM `parameters` WHERE `name` = ".$DB->connection->quote($name),true);
				if ($par['multilang'] OR $multilang) {
					$multilang = true;

					if ($type == "array") {$value = json_decode($value,JSON_UNESCAPED_UNICODE);};
					if ($type == "bool") {if ($value == "false") {$value = false;} else {$value = true;};};
					
					if (!$par['multilang']) {$par["value"] = "{}";};

					$par["value"] = json_decode($par['value'],true);
					$par["value"][$locale] = $value;

					$value = json_encode($par['value']);
				} else {
					$multilang = false;
				};

				$DB->exec("
					UPDATE `parameters` SET
					`type`='".$type."',
					`value`=".$DB->connection->quote($value).",
					`edit_time`=CURRENT_TIMESTAMP,
					`editor`=$user,
					`multilang`=".intval($multilang)."
					WHERE `name` = ".$DB->connection->quote($name));
			} else {

				if ($multilang) {
					if ($type == "array") {$value = json_decode($value,JSON_UNESCAPED_UNICODE);};
					if ($type == "bool") {if ($value == "false") {$value = false;} else {$value = true;};};
					$value = json_encode(Array("".$locale.""=>$value));
				};

				$DB->exec("
					INSERT INTO `parameters`
					(`name`,
					`type`, 
					`value`, 
					`edit_time`, 
					`editor`,
					`multilang`
					) 
					VALUES 
					(".$DB->connection->quote($name).",
					'".$type."',
					".$DB->connection->quote($value).",
					CURRENT_TIMESTAMP,
					$user,
					".intval($multilang)."
					)");
			}
		}

		public function get($name,$locale = false){ //Возвращает параметр из БД

			GLOBAL $DB, $se_multilocal, $se_localization, $LNG; //Класс работы с базами данных

			if (!$locale) {
				$locale = $LNG->locale;
			};

			if ($this->is($name)) { //Если параметр существует
				$res = $DB->query("SELECT * FROM `parameters` WHERE `name` = ".$DB->connection->quote($name),true);

				if ($res['multilang']) {
					$res['value'] = json_decode($res['value'],true);
					if ($res['value'][$locale]) {
						$res['value'] = $res['value'][$locale];
					} else {
						$res['value'] = $res['value'][$se_localization];
					};
					$value = $res['value'];
				} else {
					switch ($res['type']) { //Проверка типа данных
						case "bool":
							if ($res['value'] == "true") {$value = true;} else {$value = false;};
						break;
						case "int":
							$value = parseint($res['value']);
						break;
						case "float":
							$value = floatval($res['value']);
						break;
						case "string":
							$value = $res['value'];
						break;
						
						case "array":
							$value = json_decode($res['value'],true);
						break;
					};
				};
				return $value;
			} else {
				return null;
			}
		}

		public function info($name){ //Возвращает всю информацию о параметре из БД
			GLOBAL $DB; //Класс работы с базами данных
			if ($this->is($name)) { //Если параметр существует
				$res = $DB->query("SELECT * FROM `parameters` WHERE `name` = ".$DB->connection->quote($name),true);
				return $res;
			} else {
				return null;
			}
		}

		public function remove($name){ //Удаляет параметр из БД
			GLOBAL $DB; //Класс работы с базами данных
			if ($this->is($name)) { //Если параметр существует
				$DB->exec("DELETE FROM `parameters` WHERE `name` = ".$DB->connection->quote($name));
				return true;
			} else {
				return false;
			}
		}
	};

	$PARAMS = new PARAMS; //Внешний
?>