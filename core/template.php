<?php
	class TMPL {
		public $template;

		public function __construct() { //Функция конструктора
			$this->template = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/addict/template/config.json"),true);
			$this->template['link'] = "addict/template/";
			$this->template['path'] = "addict/template/";
		}

		public function loadElement($element,$action=null,$data=null) {
			$tmpl = $this->template;

			if ($action) {
				$action = "action_".$action;
			} else {
				$action = "action";
			};
			
			$element_path = $_SERVER['DOCUMENT_ROOT']."/".$tmpl['path'].$tmpl['elements_folder']."/".strtolower($element)."/";
			if (is_file($element_path."controller.php")) { //Если контроллер страницы существует
				if(file_exists($element_path."model.php"))
				{
					include_once($element_path."model.php");
				}

				include_once($element_path."controller.php");

				
				$controller_name = "Controller_".str_replace(" ","_", ucwords( str_replace("/", " ", $element) ) );
				// создаем контроллер
				$controller = new $controller_name;
				if(method_exists($controller, $action)) {
					$controller->$action($data);
					return true;
				} else {
					return false;
				}

			} 
		}

		public function link(){ //Ссылка до темы с http://
			GLOBAL $se_url;
			$thm = $this->info();
			return $se_url."/".$this->template['link'];
		}

		public function info($par = null) {
			if (!$par) {
				return $this->template[$par];
			} else {
				return $this->template;
			}
		}

		public function path(){ //Путь до темы
			GLOBAL $se_url;
			$thm = $this->info();
			return $_SERVER["DOCUMENT_ROOT"]."/".$this->template['link'];
		}
		
		public function setPar($par,$val){ //Устанавливает параметр у темы
			if (is_file($_SERVER["DOCUMENT_ROOT"]."/addict/template/config.json")) { //Если тема существует
				$template = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/addict/templates/config.json"),true);
				$template[$par] = $val;
				file_put_contents($_SERVER["DOCUMENT_ROOT"]."/addict/templates/config.json",json_encode($template,JSON_UNESCAPED_UNICODE));
				return true;
			} else {
				return false;
			}
		}

	};

	$TMPL = new TMPL; //Внешний
?>
