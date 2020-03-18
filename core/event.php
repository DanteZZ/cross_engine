<?php
	class EVENT {
		public $list;

		public function __construct() { //Функция конструктора
			$this->list = [
				"before_draw"=>[],
				"after_draw"=>[],

				"on_head_start"=>[],
				"on_head_end"=>[],

				"on_page_start"=>[],
				"on_page_end"=>[],

				"on_login"=>[],
				"on_logout"=>[],

				"on_user_register"=>[],
				"on_user_remove"=>[]
			];

		}

		public function set($name,$functions,$class=false) { //Устанавливает функцию для события
			if (!isset($this->list[$name])) { //Создаём событие, если его не существует
				$this->list[$name] = Array();
			};

			if (gettype($functions) == "string") { //Если одна функция
				if ($class) { //Если прописан класс
					$this->list[$name][] = Array('GLOBAL '.$class.'; return '.$class.'->'.$functions.';');
				} else {
					$this->list[$name][] = Array("return ".$functions.';');
				}
			} else { //Если это массив функций
				if ($class) { //Если прописан класс
					foreach ($functions as $key=>$val) {
						$functions[$key] = 'GLOBAL '.$class.'; return '.$class.'->'.$functions[$key].';';
					};
				} else {
					foreach ($functions as $key=>$val) {
						$functions[$key] = "return ".$functions[$key].";";
					};

				}
				$this->list[$name][] = $functions;
			}
			
		}

		public function init($name,$multiple=false) { //Вызывает событие
			if ((isset($this->list[$name])) AND (count($this->list[$name])>0)) { //Если событие и функции в нём существуют
				$res = Array(); //Массив возвращаемых значений
				foreach ($this->list[$name] as $key=>$val) {
					foreach ($val as $id=>$func) {
						$res[] = eval($func);
					};
				};
				if ($multiple) {
					return $res; //Возврат нескольких значений
				} else {
					return end($res);
				}
			};
		}

		public function count($name) { //Возвращает количество функций в событии
			if (isset($this->list[$name])) { //Если событие существует
				return count($this->list[$name]);
			};
		}
	};

	$EVENT = new EVENT; //Внешний
?>
