<?php
	class MDL {
		public function __construct() {
			$this->path = false;
			$this->current = false;
		}

		public function list(){ //Список доступных модулей
			$modules = scandir($_SERVER["DOCUMENT_ROOT"]."/addict/modules/");
			$res = Array();
			array_shift($modules);
			array_shift($modules);
			if ($modules) {
    			foreach ($modules as $key=>$val) {
    				if (is_file($_SERVER["DOCUMENT_ROOT"]."/addict/modules/$val/config.json")) {
    					$res[] = $val;
    				};
    			};
    			return $res;
			} else {
			    return false;
			};
		}

		public function load(){ //Загрузка модулей в ядро
			$modules = $this->list();
			foreach ($modules as $key=>$val) {
				$this->start($val);
			};
		}

		public function is($name){ //Проверка существования модуля
			if (is_file($_SERVER["DOCUMENT_ROOT"]."/addict/modules/$name/config.json")) {
				return true;
			} else {
				return false;
			};
		}

		public function info($name){ //Информация о модуле
			if (is_file($_SERVER["DOCUMENT_ROOT"]."/addict/modules/$name/config.json")) { //Если модуль существует
				$module = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/addict/modules/$name/config.json"),true);
				$module['id'] = $name;
				$module['mod_path'] = $_SERVER["DOCUMENT_ROOT"]."/addict/modules/$name/";
				$module['mod_link'] = "/addict/modules/$name/";
				return $module;
			} else {
				return false;
			}
		}

		public function link($name){ //Ссылка до модуля с http://
			GLOBAL $se_url;
			$module = $this->info($name);
			return $se_url."/".$module['link'];
		}

		public function path($name){ //Путь к модулю
			GLOBAL $se_url;
			$module = $this->info($name);
			return $module['link'];
		}

		public function activate($name) { //Активирует модуль
			if ($this->is($name)) { //Если такой модуль существует
				$module = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/addict/modules/$name/config.json"),true);
				$module['disabled'] = false;
				file_put_contents($_SERVER["DOCUMENT_ROOT"]."/addict/modules/$name/config.json", json_encode($module,JSON_UNESCAPED_UNICODE));
			} else {
				return false;
			}
		}

		public function deactivate($name) { //Отключает модуль
			if ($this->is($name)) { //Если такой модуль существует
				$module = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/addict/modules/$name/config.json"),true);
				$module['disabled'] = true;
				file_put_contents($_SERVER["DOCUMENT_ROOT"]."/addict/modules/$name/config.json", json_encode($module,JSON_UNESCAPED_UNICODE));
			} else {
				return false;
			}
		}

		public function start($name){ //Запуск модуля
			$module = $this->info($name);
			$this->current = $module;
			if ((!$module['disabled']) AND (is_file($_SERVER["DOCUMENT_ROOT"]."/addict/modules/$name/".$module['start']))) {
                $this->path = "/addict/modules/$name/";
                require_once($_SERVER["DOCUMENT_ROOT"]."/addict/modules/$name/".$module['start']);
			};
		}
	};

	$MDL = new MDL; //Внешний
?>
