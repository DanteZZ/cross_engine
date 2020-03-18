<?php
	class AUTH {

		public function set($params,$time=false){ //Устанавливает параметры в сессию
			GLOBAL $se_clear_time;
			if ($time) {$cleartime = $time;} else {$cleartime = $se_clear_time;}; //Проверка на индивидуальное время хранения
			if ($cleartime == -1) {$cleartime= time()+3600*24*365*24;} else {$cleartime .= time();} //Проверка на бесконечное время хранения
			foreach ($params as $key=>$val) {
				setcookie($key, $val, $cleartime,"/"); 
			};
		}

		public function get($params){ //Получение переменной-ных из сессии
			if (gettype($params) == 'string') {
				$res = $_COOKIE[$params];
			} else {
				$res = Array();
				foreach ($params as $key=>$val) {
					$res[] = $_COOKIE[$val];
				};
			}
			return $res;
		}

		public function remove($params){ //Удаление переменной-ных из сессии
			if (gettype($params) == 'string') {
				if (isset($_COOKIE[$params])) {setcookie($params, null,time()-3600,"/");};
			} else {
				foreach ($params as $key=>$val) {
					if (isset($_COOKIE[$val])) {setcookie($val, null,time()-3600,"/");};
				};
			}
		}

		public function clear(){ //Очистить сессию
			foreach($_COOKIE as $key => $value) {setcookie($key, null,time()-3600,"/");};
		}

		public function is($name){ //Проверка существования переменной в сессии
			return isset($_COOKIE[$name]);
		}

		public function setClearTime($time){ //Установить время хранения сессии в секундах
			$cfg = file_get_contents("config.php");
			$cfg = str_replace(" ","",$cfg);
			$cfg = str_replace("	","",$cfg);
			$cfg = str_replace('$se_clear_time="'.$this->theme['id'].'"','$se_clear_time="'.$theme.'"',$cfg);
			file_put_contents("config.php", $cfg);
		}
	};

	$AUTH = new AUTH; //Внешний
?>