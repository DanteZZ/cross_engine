<?php
	class PAGE {

		public $info; //Массив параметров страницы
		public function __construct() { //Функция конструктора
			
			$this->params = Array(); // Массив информации о странице
			$this->info['domain'] = $_SERVER['SERVER_NAME'];
			$this->info['link'] = $_SERVER['REQUEST_URI'];
			$this->info['parser'] = parse_url($this->info['link']);
			$this->info['path'] = $this->info['parser']['path'];
			$this->info['url'] =  explode('/',$this->info['parser']['path']);
			$this->info['page'] = $this->info['url'][1];
			$this->info['id'] = $this->info['url'][2];
			unset($this->info['parser']);
			
		}

		public function info($param=false){ //Возвращает распределённую ссылку
			if ($param) {
				return $this->info[$param];
			} else {
				return $this->info;
			};
		}

		public function is($link) { //Проверка на существование страницы
			GLOBAL $TMPL;
			$tmp = $TMPL->info();
			return is_file($_SERVER['DOCUMENT_ROOT'].$tmp['path'].$tmp['elements_folder']."/".$link);
		}

		public function isHome() { //Проверяет, является ли страница главной
			if ($this->info('page') == "") {return true;} else {return false;}
		}

		public function error($code) { //Переводит
			header("Location: /error/".$code);
			exit;
		}
	};

	$PAGE = new PAGE; //Внешний
?>