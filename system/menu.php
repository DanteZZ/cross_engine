<?php
	class MENU {

		public function is($name){ //Проверяет существует ли меню в БД
			GLOBAL $DB; //Класс работы с базами данных
			$res = $DB->query("SELECT * FROM `menus` WHERE `name` = ".$DB->connection->quote($name));
			if ($res) {return true;} else {return false;};
		}

		public function set($name,$items,$locale=false){ //Устанавливает меню в БД
			GLOBAL $DB, $se_multilocal, $se_localization, $LNG;

			if (!$locale) {
				$locale = $LNG->locale;
			};

			if ($this->is($name)) { //Если существует меню

				$menu = $this->get($name);

				if ($se_multilocal) { //Если используются несколько языков
					$menu['items'][$locale] = $items;
					$items = $menu['items'];
				};

				$DB->exec("
					UPDATE `menus` SET
					`items`=".$DB->connection->quote(json_encode($items,JSON_UNESCAPED_UNICODE)).",
					`edit_time`=CURRENT_TIMESTAMP
					WHERE `name` = ".$DB->connection->quote($name));
			} else {

				if ($se_multilocal) { //Если используются несколько языков
					$items = Array($locale=>$items);
				};

				$DB->exec("
					INSERT INTO `menus`
					(`name`,
					`items`, 
					`edit_time`) 
					VALUES 
					(".$DB->connection->quote($name).",
					".$DB->connection->quote(json_encode($items,JSON_UNESCAPED_UNICODE)).",
					CURRENT_TIMESTAMP)");
			}
		}

		public function get($name,$locale=false){ //Возвращает меню из БД
			GLOBAL $DB, $se_multilocal, $se_localization, $LNG;

			if (!$locale) {
				$locale = $LNG->locale;
			};

			if ($this->is($name)) { //Если меню существует
				$res = $DB->query("SELECT * FROM `menus` WHERE `name` = ".$DB->connection->quote($name),true);
				$res['items'] = json_decode($res['items'],true);

				if ($se_multilocal) { //Если используются несколько языков
					$res['items'] = $res['items'][$locale];
				};
				return $res;
			} else {
				return false;
			}
		}

		public function remove($name){ //Удаляет меню из БД
			GLOBAL $DB; //Класс работы с базами данных
			if ($this->is($name)) { //Если меню существует
				$DB->exec("DELETE FROM `menus` WHERE `name` = ".$DB->connection->quote($name));
				return true;
			} else {
				return false;
			}
		}
	};

	$MENU = new MENU; //Внешний
?>