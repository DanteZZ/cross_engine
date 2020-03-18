<?php
	class PRIORITY {

		public function is($tag){ //Проверяет существует ли приоритет в БД
			GLOBAL $DB; //Класс работы с базами данных
			$res = $DB->query("SELECT * FROM `priorities` WHERE `tag` = ".$DB->connection->quote($tag));
			if ($res) {return true;} else {return false;};
		}

		public function isRule($rule,$tag=false){ //Проверяет существует ли приоритет в БД
			GLOBAL $DB; //Класс работы с базами данных
			GLOBAL $USR; //Класс работы с пользователями

			if (!$tag) { //Если тег не указан
				if ($USR->isAuth()) { //Проверяем авторизован ли пользователь
					$inf = $USR->info();
					if (in_array($rule,$inf['deny_rules'])) { return false; };
					$tag = $inf['priority'];
					$prior = $this->get($tag);
					$rules = $prior['rules'];
					$rules = array_merge($rules,$inf['allow_rules']);
					if (in_array($rule,$rules)) { return true;} else {return false;};

				} else {
					return false;
				};
			};
			if ($this->is($tag)) { //Если приоритет существует
				$priority = $DB->query("SELECT * FROM `priorities` WHERE `tag` = ".$DB->connection->quote($tag),1);
				$priority['rules'] = json_decode($priority['rules'],true);
				if (in_array($rule,$priority['rules'])) {
					return true;
				} else {
					return false;
				};
			} else {
				return false;
			}

		}

		public function add($tag,$name,$rules){ //Добавляет новый приоритет в БД
			GLOBAL $DB; //Класс работы с базами данных
			if (!$this->is($tag)) { //Если приоритета с таким TAG не существует
				$DB->exec("
					INSERT INTO `priorities`
					(`tag`,
					`name`,
					`rules`)
					VALUES
					(".$DB->connection->quote($tag).",
					".$DB->connection->quote($name).",
					".$DB->connection->quote(json_encode($rules,JSON_UNESCAPED_UNICODE)).")");
				return true;
			} else {
				return false;
			}
		}

		public function setRules($tag,$rules){ //Добавляет правило в приоритет
			GLOBAL $DB; //Класс работы с базами данных

			if ($this->is($tag)) { //Если приоритет существует
				$priority = $DB->query("SELECT * FROM `priorities` WHERE `tag` = ".$DB->connection->quote($tag),1);
				$DB->exec("
					UPDATE `priorities` SET
					`rules`=".$DB->connection->quote(json_encode($rules,JSON_UNESCAPED_UNICODE))."
					WHERE `tag` = ".$DB->connection->quote($tag));
				return true;
			} else {
				return false;
			}
		}

		public function addRule($tag,$rule){ //Добавляет правило в приоритет
			GLOBAL $DB; //Класс работы с базами данных

			if ($this->is($tag)) { //Если приоритет существует
				$priority = $DB->query("SELECT * FROM `priorities` WHERE `tag` = ".$DB->connection->quote($tag),1);
				$rules = json_decode($priority['rules'],true);
				if (!in_array($rule,$rules)) { //Добавляем правило если оно ещё не существует
					$rules[] = $rule;
				};

				$DB->exec("
					UPDATE `priorities` SET
					`rules`=".$DB->connection->quote(json_encode($rules,JSON_UNESCAPED_UNICODE))."
					WHERE `tag` = ".$DB->connection->quote($tag));
				return true;
			} else {
				return false;
			}
		}

		public function removeRule($tag,$rule){ //Добавляет правило из приоритета
			GLOBAL $DB; //Класс работы с базами данных

			if ($this->is($tag)) { //Если приоритет существует
				$priority = $DB->query("SELECT * FROM `priorities` WHERE `tag` = ".$DB->connection->quote($tag),1);
				$orules = json_decode($priority['rules'],true);
				$rules = Array();
				foreach ($orules as $key=>$val) { //Отсеиваем оставшиеся правила
					if ($val !== $rule) {$rules[] = $val;};
				};

				$DB->exec("
					UPDATE `priorities` SET
					`rules`=".$DB->connection->quote(json_encode($rules,JSON_UNESCAPED_UNICODE))."
					WHERE `tag` = ".$DB->connection->quote($tag));
				return true;
			} else {
				return false;
			}
		}

		public function setName($tag,$name){ //Устанавливаем название приоритету
			GLOBAL $DB; //Класс работы с базами данных

			if ($this->is($tag)) { //Если приоритет существует
				$DB->exec("
					UPDATE `priorities` SET
					`name`=".$DB->connection->quote($name)."
					WHERE `tag` = ".$DB->connection->quote($tag));
				return true;
			} else {
				return false;
			}
		}

		public function get($tag){ //Возвращает приоритет
			GLOBAL $DB; //Класс работы с базами данных

			if ($this->is($tag)) { //Если приоритет существует
				$priority = $DB->query("SELECT * FROM `priorities` WHERE `tag` = ".$DB->connection->quote($tag),true);
				$priority['rules'] = json_decode($priority['rules'],true);
				return $priority;
			} else {
				return false;
			}
		}

		public function remove($tag,$user=false){ //Удаляет приоритет из БД, и изменяет приоритет пользователя (Если false, то удаляет)
			GLOBAL $DB; //Класс работы с базами данных
			if ($this->is($tag)) { //Если приоритет существует
				
				if ($user) {
					if (!$this->get($user)) {
						return false;
					}
				};

				$DB->exec("DELETE FROM `priorities` WHERE `tag` = ".$DB->connection->quote($tag));

				if (!$user) {
					$DB->exec("DELETE FROM `users` WHERE `priority` = ".$DB->connection->quote($user));
				} else {
					$DB->exec("
					UPDATE `users` SET
					`priority`=".$DB->connection->quote($user)."
					WHERE `priority` = ".$DB->connection->quote($tag));
				};

				return true;
			} else {
				return false;
			};
		}

		public function list() { //Возвращает список существующих пользователей
			GLOBAL $DB;
			$list = $DB->query("SELECT * FROM `priorities`");
			foreach ($list as $key=>$prior) {
				$list[$key]['rules'] = json_decode($prior['rules'],true);
			};
			return $list;
		}
	};

	$PRIORITY = new PRIORITY; //Внешний
?>
