<?php
	class DS {

		public $cols; //Список штатных параметров

		public function __construct() { //Функция конструктора
			$this->cols = [
				'id',
				'edit_time',
				'create_time',
				'creator',
				'editor',
				'type'
			];
		}

		public function extend($ds) { //Соединяет параметры DataSet'a
			if (count($ds['parameters']) > 0) {
				$pars = $ds['parameters'];
				unset($ds['parameters']);
				$res = array_merge($ds,$pars);
				return $res;
			} else {
				return $ds;
			};
		}

		public function is($id) { //Проверка существования DataSet'а
			GLOBAL $DB; //Класс работы с базами данных
			$res = $DB->query("SELECT * FROM `datasets` WHERE `id` = ".$DB->connection->quote($id));
			if ($res) {return true;} else {return false;};
		}

		public function get($id=false,$ext = true,$locale=false) { //Возвращает DataSet в виде массива
			GLOBAL $DB, $se_multilocal, $se_localization, $LNG; //Класс работы с базами данных

			if (!$locale) {
				$locale = $LNG->locale;
			};

			if (!$id) {
				GLOBAL $PAGE;
				$inf = $PAGE->info();
				$id = $inf['dataset']['id'];
			};
			if ($this->is($id)){ //Если такой пост существует
				$ds = $DB->query("SELECT * FROM `datasets` WHERE `id` = ".$DB->connection->quote($id),1);

				$ds['parameters'] = json_decode($ds['parameters'],true);

				if ($se_multilocal AND $ext) { //Если используются несколько языков
					if (isset($ds['parameters'][$locale])) {
						$ds['parameters'] = $ds['parameters'][$locale];
					} else {
						$ds['parameters'] = $ds['parameters'][$se_localization];
					}
					
				};

				$type = $this->getTypeById($ds['type']);
                $ds['type'] = $type['name'];
                if ($ext) { $ds = $this->extend($ds); };
				return $ds;
			} else {
				return false;
			}
		}

		private function getPar($list,$pars) {
			foreach ($list as $key=>$par) {
				if ($pars[$par]) {
					$pars = $pars[$par];
				} else {
					return false;
				};
			};
			return $pars;
		}

		public function search($type,$string=false,$parameters=false,$locale=false) {
			return $this->list($type,$parameters,$locale,$string);
		}

		public function list($type,$parameters=false,$locale=false,$search=false) { //Выводит список всех DataSet’ов определённого типа, с проверкой параметров
			GLOBAL $DB, $se_multilocal, $LNG, $se_localization; //Класс работы с базами данных
			$type =$this->getType($type);
			
			if (!$locale) {
				$locale = $LNG->locale;
			};

			if (!$parameters) {
				if ($type) { //Если данный тип существует
					if ($search) {
						$list = $DB->query(
							"SELECT * FROM `datasets` WHERE `type`= ".$DB->connection->quote($type['id'])." AND `parameters` LIKE ".$DB->connection->quote("%".$search."%")
						);
					} else {
						$list = $DB->query("SELECT * FROM `datasets` WHERE `type` = ".$DB->connection->quote($type['id']));
					};
					
					if ($list) {
	    				foreach ($list as $key=>$val) {
	    					$list[$key]['parameters'] = json_decode($list[$key]['parameters'],true);
	    					if ($se_multilocal) { //Если используются несколько языков
	    						if (isset($list[$key]['parameters'][$locale])) {
	    							$list[$key]['parameters'] = $list[$key]['parameters'][$locale];
	    						} else {
	    							$list[$key]['parameters'] = $list[$key]['parameters'][$se_localization];
	    						};
								
							};
	    					$list[$key] = $this->extend($list[$key]);
	    				};
	    				return $list;
					} else {
					    return false;
					}
				} else {
					return false;
				};
			} else {
				if ($type) { //Если данный тип существует
					if ($search) {
						$query = "SELECT * FROM `datasets` WHERE `type`= ".$DB->connection->quote($type['id'])." AND `parameters` LIKE ".$DB->connection->quote("%".$search."%"); //Запрос
					} else {
						$query = "SELECT * FROM `datasets` WHERE `type` = ".$DB->connection->quote($type['id']); //Запрос
					};

					if (isset($parameters['orderby'])) {// Высвобождаем массив параметров
						$orderby = $parameters['orderby'];
						unset($parameters['orderby']);
					};

					if (isset($parameters['ordertype'])) {// Высвобождаем массив параметров
						$ordertype = $parameters['ordertype'];
						unset($parameters['ordertype']);
					};


					$conditions = [ //Список продвинутых условий
						'$<'=>[],
						'$>'=>[],
						'$!'=>[],
						'$&'=>[],
						'$|'=>[],
						'#='=>[],
						'#<'=>[],
						'#>'=>[],
						'#&'=>[]
					];

					foreach ($parameters as $key=>$val) {
						if (in_array(substr($val, 0,2), ['$<','$>','$!','$&','$|','#>','#<','#&','#='])) { //Если параметр с продвинутым условием
							$conditions[substr($val, 0,2)][$key] = substr($val, 2); //Добавляем в массив с условиями
							unset($parameters[$key]);
						};
					};

					$list = Array(); //Будущий отсортированный список
					$olist = $DB->query($query); //Список без проверки параметров

					if ($olist) { //Если удалось найти DataSet'ы
						foreach ($olist as $key=>$val) { //Выбираем DataSet

							$olist[$key]['parameters'] = json_decode($olist[$key]['parameters'],true);

							if ($se_multilocal) { //Если используются несколько языков
	    						if (isset($olist[$key]['parameters'][$locale])) {
	    							$olist[$key]['parameters'] = $olist[$key]['parameters'][$locale];
	    						} else {
	    							$olist[$key]['parameters'] = $olist[$key]['parameters'][$se_localization];
	    						};
								
							};

							$olist[$key] = $this->extend($olist[$key]);
							$pars = $olist[$key];




							$right = true; //До проверки все DataSet'ы правильные
							foreach ($parameters as $par=>$to) { //Проверяем условие равенства
								$pval = $this->getPar(explode("->",$par),$pars);
								if ($pval!==$to) {$right=false;};
							};
							foreach ($conditions["$<"] as $par=>$to) { //Проверяем условие меньшенства
								$pval = $this->getPar(explode("->",$par),$pars);
								if ($pval>=floatval($to)) {$right=false;};
							};
							foreach ($conditions["$>"] as $par=>$to) { //Проверяем условие большенства
								$pval = $this->getPar(explode("->",$par),$pars);
								if ($pval<=floatval($to)) {$right=false;};
							};
							foreach ($conditions["$&"] as $par=>$to) { //Проверяем условие диапозона
								$pval = $this->getPar(explode("->",$par),$pars);
								$betw = explode("&", $to);
								if (($pval<=floatval($betw[0])) OR ($pval>=floatval($betw[1]))) {$right=false;};
							};
							foreach ($conditions["$!"] as $par=>$to) { //Проверяем условие неравенства
								$pval = $this->getPar(explode("->",$par),$pars);
								if ($pval==$to) {$right=false;};
							};

							foreach ($conditions["$|"] as $par=>$to) { //Проверяем условие ИЛИ
								$pval = $this->getPar(explode("->",$par),$pars);
								$orl = explode("|", $to);
								$rok = false;
								foreach ($orl as $betk=>$orp) {
									if ($orp == 'false') {$orp = false;};
									if ($orp == 'true')  {$orp = true;};
									if ($orp == 'null')  {$orp = null;};
									if (intval($orp) == strval(intval($orp)))  {$orp = intval($orp);};
									if (floatval($orp) == strval(floatval($orp)))  {$orp = floatval($orp);};
									if ($orp == $pval) {$rok=true; break;}
								};
								if (!$rok) {$right = false;}; 
							};

							//COUNT
							foreach ($conditions['#='] as $par=>$to) { //Проверяем условие равенства
								$pval = $this->getPar(explode("->",$par),$pars);
								if (gettype($pval) !== "array") {$right=false;};
								if (count($pval)!==intval($to)) {$right=false;};
							};
							foreach ($conditions['#<'] as $par=>$to) { //Проверяем условие меньшенства
								$pval = $this->getPar(explode("->",$par),$pars);
								if (gettype($pval) !== "array") {$right=false;};
								if (count($pval)>=intval($to)) {$right=false;};
							};
							foreach ($conditions['#>'] as $par=>$to) { //Проверяем условие большенства
								$pval = $this->getPar(explode("->",$par),$pars);
								if (gettype($pval) !== "array") {$right=false;};
								if (count($pval)<=intval($to)) {$right=false;};
							};
							foreach ($conditions['#&'] as $par=>$to) { //Проверяем условие диапозона
								$pval = $this->getPar(explode("->",$par),$pars);
								if (gettype($pval) !== "array") {$right=false;};
								$betw = explode("&", $to);
								if ((count($pval)<=intval($betw[0])) OR (count($pval)>=intval($betw[1]))) {$right=false;};
							};

							if (!$right) {continue;}; //Отсеиваем ненужный DataSet
							$list[] = $olist[$key];
						};

						if (isset($orderby)) { //Если нужна сортировка по параметру
							if (count($list)>1){ //Если количество DataSet'ов больше 1

								if (isset($list[0][$orderby])) { //Если сортировочный параметр существует

									$mask = Array(); //Сортировочная маска
									$arrRes = Array(); //Результат

									foreach ($list as $key=>$val) { //Перебираем гавно
										$mask[$key] = $val[$orderby];
									};

									if (mb_strtoupper($ordertype) == "DESC") { //Если сортировка по убыванию
										arsort($mask);
									} else { //Иначе
										asort($mask);
									};

									foreach ($mask as $key=>$val) {
										$arrRes[] = $list[$key];
									};
									$list = $arrRes;
								};
							};
						};
						return $list;

					} else {
						return false;
					}
				} else {
					return false;
				};
			};
		}

		public function add($type,$parameters,$locale=false) { //Добавляет новый DataSet с указанными параметрами
			GLOBAL $DB,$USR, $se_multilocal, $LNG; //Класс работы с базами данных

			$type = $this->getType($type);
			$user = $USR->info();

			if (!$locale) {
				$locale = $LNG->locale;
			};

			if ($type) { //Если такой тип существует
				if ($se_multilocal) { //Если используются несколько языков
					$parameters = Array("".$locale.""=>$parameters);
				};
				$ok = $DB->exec("INSERT INTO `datasets`(
				    `type`,
				    `creator`,
				    `editor`,
				    `parameters`
				    ) VALUES (
				    ".$DB->connection->quote($type['id']).",
				    ".$DB->connection->quote($user['id']).",
				    ".$DB->connection->quote($user['id']).",
				    ".$DB->connection->quote(json_encode($parameters,JSON_UNESCAPED_UNICODE))."
				)");
				if ($ok) { //Если удалось добавить DataSet
					$id = $DB->query("SELECT LAST_INSERT_ID();");
					return intval($id[0]["LAST_INSERT_ID()"]); //Возвращаем ID
				} else {
					return false;
				}
			}
		}

		public function change($id,$type,$parameters,$full=false,$locale=false) { //Изменяет весь DataSet
			GLOBAL $DB,$USR, $se_multilocal, $LNG;

			if (!$locale) {
				$locale = $LNG->locale;
			};

			$type = $this->getType($type);
			$user = $USR->info();
			if (!$type) { return false; } //Проверка существования типа
			if ($ds = $this->get($id,false,$locale)) { //Если DataSet существует
				
				if ($full) {
					if ($se_multilocal) { //Если используются несколько языков
						$ds['parameters'][$locale] = $parameters;
					} else {
						$ds['parameters'] = $parameters;
					};
				} else {
					foreach ($parameters as $pname=>$val) {
						if ($se_multilocal) { //Если используются несколько языков
							$ds['parameters'][$locale][$pname] = $val;
						} else {
							$ds['parameters'][$pname] = $val;
						};
					};
				};

				$ds['parameters'] = json_encode($ds['parameters'],JSON_UNESCAPED_UNICODE);
				$ds['parameters'] = str_replace('"true"', "true", $ds['parameters']);
				$ds['parameters'] = str_replace('"false"', "false", $ds['parameters']);

				$DB->exec("
					UPDATE `datasets` SET
				    `type`=".$DB->connection->quote($type['id']).",
				    `editor`=".$DB->connection->quote($user['id']).",
				    `edit_time`=CURRENT_TIMESTAMP,
					`parameters`=".$DB->connection->quote($ds['parameters'])."
					WHERE `id` = ".$id);
				return true;
			} else {
				return false;
			}
		}

		public function getParameter($id,$parameter){
			if ($ds = $this->get($id)) {
				if (isset($ds[$parameter])) {
					return $ds[$parameter];
				} else {
					return null;
				};
			};
		}

		public function setParameter($id,$parameter,$value,$locale=false){ //Устанавливает параметр ДатаСета
			GLOBAL $DB,$USR, $se_multilocal, $LNG;

			if (!$locale) {
				$locale = $LNG->locale;
			};

			$user = $USR->info();
			if ($this->get($id)) { //Если DataSet существует
				if (in_array($parameter,$this->cols)) { //Если это штатный параметр

					if ($parameter == 'type') { //Если редактируется тип
						$type = $this->getType($value); //Проверка не существование типа
						if (!$type) { return false; }
						$value = $type['id'];
					};


					return $DB->exec("
					UPDATE `datasets` SET
					`$parameter`=".$DB->connection->quote($value).",
					`editor`=".$DB->connection->quote($user['id']).",
				    `edit_time`=CURRENT_TIMESTAMP
					WHERE `id` = ".$DB->connection->quote($id));
				} else {
					$ds = $DB->query("SELECT * FROM `datasets` WHERE `id` = ".$DB->connection->quote($id),1);
					$ds['parameters'] = json_decode($ds['parameters'],true);

					if ($se_multilocal) {
						$ds['parameters'][$locale][$parameter] = $value;
					} else {
						$ds['parameters'][$parameter] = $value;
					};
					
					$ds['parameters'] = json_encode($ds['parameters'],JSON_UNESCAPED_UNICODE);
					$ds['parameters'] = str_replace('"true"', "true", $ds['parameters']);
					$ds['parameters'] = str_replace('"false"', "false", $ds['parameters']);

					return $DB->exec("
					UPDATE `datasets` SET
					`parameters`=".$DB->connection->quote($ds['parameters']).",
					`editor`=".$DB->connection->quote($user['id']).",
				    `edit_time`=CURRENT_TIMESTAMP
					WHERE `id` = ".$DB->connection->quote($id));
				}

			} else {return false;}
		}

		public function remove($id){ //Удаляет DataSet
			GLOBAL $DB; //Класс работы с базами данных
			if ($this->is($id)) { //Если DataSet существует
				$DB->exec("DELETE FROM `datasets` WHERE `id` = ".$DB->connection->quote($id));
				return true;
			} else {
				return false;
			}
		}


		public function isType($name=false) { //Проверка существования типа DataSet'ов
			GLOBAL $DB; //Класс работы с базами данных
			
			if (!$name) {return false;};

			$res = $DB->query("SELECT * FROM `types` WHERE `name` = ".$DB->connection->quote($name),1);
			if ($res) {return true;} else {return false;};
		}

		public function getType($name) { //Возвращает тип DataSet'ов
			GLOBAL $DB; //Класс работы с базами данных
			if ($this->isType($name)) { //Если тип существует
				$type = $DB->query("SELECT * FROM `types` WHERE `name` = ".$DB->connection->quote($name),1);
				$type['parameters'] = json_decode($type['parameters'],true);
				return $type;
			} else {
				return false;
			}
		}

		public function getTypeById($id) { //Возвращает тип DataSet'ов по его ID
			GLOBAL $DB; //Класс работы с базами данных
			return $DB->query("SELECT * FROM `types` WHERE `id` = ".$DB->connection->quote($id),1);
		}

		public function addType($name,$params=Array()) { //Добавляет новый тип DataSet'ов
			GLOBAL $DB; //Класс работы с базами данных

			if (!$this->isType($name)) { //Если такого типа ещё не существует
				return $DB->exec("INSERT INTO `types`(
				    `name`,
				    `parameters`
				    ) VALUES (
				    ".$DB->connection->quote($name).",
				    ".$DB->connection->quote(json_encode($params,JSON_UNESCAPED_UNICODE))."
				)");
			} else {
				return false;
			}
		}

		public function changeType($name,$params) { //Редактирует параметры типа DataSet'ов
			GLOBAL $DB; //Класс работы с базами данных

			if ($this->isType($name)) { //Если тип существует существует
				return $DB->exec("
					UPDATE `types` SET
					`parameters`=".$DB->connection->quote(json_encode($params,JSON_UNESCAPED_UNICODE))."
					WHERE `name` = ".$DB->connection->quote($name));
			} else {
				return false;
			}
		}

		public function removeType($name){ //Удаляет тип DataSet'а
			GLOBAL $DB; //Класс работы с базами данных
			if ($this->isType($name)) { //Если DataSet существует
				$DB->exec("DELETE FROM `types` WHERE `name` = ".$DB->connection->quote($name));
				return true;
			} else {
				return false;
			}
		}

	};

	$DS = new DS; //Внешний
?>
