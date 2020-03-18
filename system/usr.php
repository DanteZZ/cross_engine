<?php
	class USR {

		public $cols; //Список штатных параметров

		public function __construct() { //Функция конструктора
			$this->cols = [
				'username',
				'password',
				'priority',
				'email',
				'avatar'
			];
		}

		public function is($username){ //Проверяет существует ли пользователь в БД
			GLOBAL $DB; //Класс работы с базами данных
			$res = $DB->query("SELECT * FROM `users` WHERE `username` = ".$DB->connection->quote($username));
			if ($res) {return true;} else {return false;};
		}

		public function setLang($locale){ //Устанавливает локальный язык
			GLOBAL $LNG; //Класс рыботы с языками
			if ($LNG->is($locale)) {$this->setParameter("lang",$locale);};
		}

		public function list($priority=false) { //Возвращает список существующих пользователей
			GLOBAL $DB;

			if (!$priority) {
				$list = $DB->query("SELECT * FROM `users`");
			} else {
				$list = $DB->query("SELECT * FROM `users` WHERE `priority` = ".$DB->connection->quote($priority));
			};

			if ($list) {
				foreach ($list as $key=>$user) {
					$user['parameters']  = json_decode($user['parameters'],true);
					$user['allow_rules'] = json_decode($user['allow_rules'],true);
					$user['deny_rules']  = json_decode($user['deny_rules'],true);
					if ($user['avatar'] == "") {$user['avatar'] = false;};
					unset($user['password']);
					$list[$key] = $user;
				};
				return $list;
			} else {
				return $list;
			}
			
		}

		public function info($username=false,$pass=false){ //Возвращает информацию о пользователе
			GLOBAL $DB; //Класс работы с базами данных
			GLOBAL $AUTH; //Класс работы с сессиями

			if (!$username) { $username = $AUTH->get('username'); $self = true; };

			if ($this->is($username)) { //Если такой пользователь вообще существует
				$user = $DB->query("SELECT * FROM `users` WHERE `username` = ".$DB->connection->quote($username),1);
				$user['parameters']  = json_decode($user['parameters'],true);
				$user['allow_rules'] = json_decode($user['allow_rules'],true);
				$user['deny_rules']  = json_decode($user['deny_rules'],true);
				if ($user['avatar'] == "") {$user['avatar'] = false;};

				if (!$pass) {unset($user['password']);};

				if ($self) { //Если запрашивается собственный аккаунт, а он не авторизован
					$codes = $this->getParameter("authCode",$AUTH->get('username'));
					if (!isset($codes[$AUTH->get('authCode')])) {
						$this->logout();
						return false;
					};
				};
				

				return $user;
			} else {return false;}
		}

		public function infoById($id){ //Возвращает информацию о пользователе
			GLOBAL $DB; //Класс работы с базами данных
			GLOBAL $AUTH; //Класс работы с сессиями

			$user = $DB->query("SELECT * FROM `users` WHERE `id` = ".$id,true);

			if ($user) { //Если такой пользователь вообще существует
				$user['parameters']  = json_decode($user['parameters'],true);
				$user['allow_rules'] = json_decode($user['allow_rules'],true);
				$user['deny_rules']  = json_decode($user['deny_rules'],true);
				return $user;
			} else {return false;}
		}

		public function getParameter($parameter,$username=false){ //Возвращает параметр пользователя
			GLOBAL $DB; //Класс работы с базами данных

			$user = $this->info($username);

			if ($user) { //Если пользователь существует
				if (in_array($parameter,$this->cols)) { //Если это штатный параметр
					return $user[$parameter];
				} else {
					return $user['parameters'][$parameter];
				}
				
			} else {return false;}
		}

		public function setParameter($parameter,$value,$username=false){ //Устанавливает параметр пользователя
			GLOBAL $DB; //Класс работы с базами данных
			GLOBAL $PRIORITY; //Класс работы с приоритетами

			$user = $this->info($username);
			if ($user) { //Если пользователь существует
				if (in_array($parameter,$this->cols)) { //Если это штатный параметр
					switch ($parameter) {
						case "username": if ($this->is($value)) {return 1001;}; break; //Если пользователь с таким username существует
						case "priority": if (!$PRIORITY->is($value)) {return 1002;}; break; //Проверка валидности приоритета 
						case "email": //if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {return 1003;}; 
						break; //Проверка валидности E-Mail'а
						case "password": $value = password_hash($value, PASSWORD_DEFAULT); break; //Хеширование пароля
					};

					if ($value == "true") {$value = true;};
					if ($value == "false") {$value = false;};

					$DB->exec("
					UPDATE `users` SET
					`$parameter`=".$DB->connection->quote($value)."
					WHERE `id` = ".$DB->connection->quote($user['id']));
					return true;
				} else {
					$user['parameters'][$parameter] = $value;

					$user['parameters'] = json_encode($user['parameters'],JSON_UNESCAPED_UNICODE);
					$user['parameters'] = str_replace('"true"', "true", $user['parameters']);
					$user['parameters'] = str_replace('"false"', "false", $user['parameters']);

					$DB->exec("
					UPDATE `users` SET
					`parameters`=".$DB->connection->quote($user['parameters'])."
					WHERE `id` = ".$DB->connection->quote($user['id']));
					return true;
				}
				
			} else {return false;}
		}

		public function removeParameter($parameter,$username=false){ //Удаляет параметр пользователя
			GLOBAL $DB; //Класс работы с базами данных
			$user = $this->info($username);
			if ($user) { //Если пользователь существует
				$params = $user['parameters'];
				$user['parameters'] = Array();
				foreach ($params as $key=>$val) { //Отсеиваем оставшиеся правила
					if ($val !== $parameter) {$user['parameters'][] = $val;};
				};
				$DB->exec("
					UPDATE `users` SET
					`parameters`=".$DB->connection->quote(json_encode($user['parameters'],JSON_UNESCAPED_UNICODE))."
					WHERE `id` = ".$DB->connection->quote($user['id'])
				);
				return true;
			} else {return false;}
		}

		public function isAuth(){ //Проверяет авторизован ли пользователь
			GLOBAL $DB; //Класс работы с базами данных
			GLOBAL $AUTH; //Класс работы с сессиями
			
			$user = $this->info();
			if ($user) { //Если имя пользователя есть в сессии и он существует [1 Проверка]
				$codes = $this->getParameter('authCode');
				if (isset($codes[$AUTH->get('authCode')])) { //Если код авторизации совпадает [2 Проверка]
					return true;
				} else {
					return false;
				}
			} else {return false;}
		}

		public function register($username,$password,$priority,$email,$parameters=[],$avatar=false,$allow_rules=[],$deny_rules=[]){ //Регистрирует пользователя
			GLOBAL $DB; //Класс работы с базами данных
			GLOBAL $PRIORITY; //Класс работы с приоритетами
			GLOBAL $EVENT; //Класс работы с событиями

			$user = $this->info($username);
			if (!$user) { //Если пользователь с таким именем не существует
				$password = password_hash($password, PASSWORD_DEFAULT);
				if (!$PRIORITY->is($priority)) {return 1002;}; //Проверка валидности приоритета
				//if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {return 1003;}; //Проверка валидности E-Mail'а
				$EVENT->init("on_user_register");

				$parameters = json_encode($parameters,JSON_UNESCAPED_UNICODE);
				$parameters = str_replace('"true"', "true", $parameters);
				$parameters = str_replace('"false"', "false", $parameters);

				$DB->exec("INSERT INTO `users`(
				    `username`, 
				    `password`, 
				    `priority`, 
				    `email`, 
				    `avatar`, 
				    `parameters`,
				    `allow_rules`,
				    `deny_rules`) VALUES (
				    ".$DB->connection->quote($username).",
				    ".$DB->connection->quote($password).",
				    ".$DB->connection->quote($priority).",
				    ".$DB->connection->quote($email).",
				    ".$DB->connection->quote($avatar).",
				    ".$DB->connection->quote($parameters).",
				    ".$DB->connection->quote(json_encode($allow_rules,JSON_UNESCAPED_UNICODE)).",
				    ".$DB->connection->quote(json_encode($deny_rules,JSON_UNESCAPED_UNICODE))."
				)");
				return true;
			} else {return 1001;}
		}

		public function remove($username){ //Удаляет пользователя из БД
			GLOBAL $DB; //Класс работы с базами данных
			GLOBAL $EVENT; //Класс работы с событиями

			if ($this->is($username)) { //Если пользователь существует
				$DB->exec("DELETE FROM `users` WHERE `username` = ".$DB->connection->quote($username));
				$EVENT->init("on_user_remove");
				return true;
			} else {
				return false;
			}
		}

		public function allowRule($rule,$username=false){ //Добавляет правило пользователю
			GLOBAL $DB; //Класс работы с базами данных

			$user = $this->info($username);

			if ($user) { //Если пользователь существует
				if (!in_array($rule,$user['allow_rules'])) {$user['allow_rules'][] = $rule;};
				unset($user['deny_rules'][array_search($rule,$user['deny_rules'])]);
				$DB->exec("
				UPDATE `users` SET
				`allow_rules`=".$DB->connection->quote(json_encode($user['allow_rules'],JSON_UNESCAPED_UNICODE)).",
				`deny_rules`=".$DB->connection->quote(json_encode($user['deny_rules'],JSON_UNESCAPED_UNICODE))."
				WHERE `id` = ".$DB->connection->quote($user['id']));
				return true;
			} else {
				return false;
			}
		}

		public function denyRule($rule,$username=false){ //Удаляет правило пользователя
			GLOBAL $DB; //Класс работы с базами данных

			$user = $this->info($username);

			if ($user) { //Если пользователь существует
				if (!in_array($rule,$user['deny_rules'])) {$user['deny_rules'][] = $rule;};
				unset($user['allow_rules'][array_search($rule,$user['allow_rules'])]);
				$DB->exec("
				UPDATE `users` SET
				`allow_rules`=".$DB->connection->quote(json_encode($user['allow_rules'],JSON_UNESCAPED_UNICODE)).",
				`deny_rules`=".$DB->connection->quote(json_encode($user['deny_rules'],JSON_UNESCAPED_UNICODE))."
				WHERE `id` = ".$DB->connection->quote($user['id']));
				return true;
			} else {
				return false;
			}
		}

		public function login($username,$password){ //Авторизует пользователя
			GLOBAL $DB; //Класс работы с базами данных
			GLOBAL $AUTH; //Класс работы с сессиями
			GLOBAL $EVENT; //Класс работы с событиями

			$user = $this->info($username,true);
			if ($user) { //Если пользователь с таким USERNAME существует
				if (password_verify($password, $user['password'])) { //Если пароль совпадает
					$authCode = md5(time());
					$codes = $this->getParameter("authCode",$username);
					if (!is_array($codes)) {$codes = Array();};
					$info = json_decode(file_get_contents("http://ipinfo.io/".$_SERVER['REMOTE_ADDR']."?token=d8eb7be1ebc231&callback=callback"));
					$codes[$authCode] = Array(
						"time"=>time(),
						"city"=>$info["city"],
						"region"=>$info['region'],
						"ip"=>$_SERVER['REMOTE_ADDR']
					);

					$this->setParameter("authCode",$codes,$username);
					$AUTH->set(["username"=>$username,"authCode"=>$authCode]);
					$EVENT->init("on_login");
					return true;
				} else {
					return false;
				}
			} else {return false;}
		}

		public function logout(){ //Очищает информацию о текущем пользователе
			GLOBAL $AUTH; //Класс работы с сессиями
			GLOBAL $EVENT; //Класс работы с событиями

			$codes = $this->getParameter("authCode",$AUTH->get('username'));

			unset($codes[$AUTH->get("authCode")]);
			$this->setParameter("authCode",$codes,$AUTH->get('username'));

			$AUTH->remove('username','authCode');
			$EVENT->init("on_logout");
		}
	};

	$USR = new USR; //Внешний
?>