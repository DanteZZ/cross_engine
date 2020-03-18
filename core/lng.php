<?php
	class LNG {
		public $items;
		public $locale;

		public function __construct() { //Функция конструктора
			GLOBAL $se_localization, $USR, $_SESSION;
			$this->locale = $se_localization;
			$this->checkLocale();
			$this->items = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/addict/localizations/".$this->locale.".json"),true);
		}

		public function is($locale = false){ //Проверка существования языкового пакета
			if (!$locale) {
				$locale = $this->locale;
			};
			return is_file($_SERVER["DOCUMENT_ROOT"]."/addict/localizations/".$locale.".json");
		}

		public function checkLocale() {
			GLOBAL $se_localization, $USR, $_SESSION;
			$old = $this->locale;
			
			if ($USR) {
				if ($ulng = $USR->getParameter('lang')) { //Проверка на язык пользователя
					$this->locale = $ulng;
				};
			};

			if (@$_SESSION['lang']) { //Проверка на язык сессии
				$this->locale = $_SESSION['lang'];
			};
			if ($this->locale !== $old) {
				$this->items = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/addict/localizations/".$this->locale.".json"),true);
			};
		}

		public function isItem($item,$locale = false){ //Проверка существования элемента пакета
			$this->checkLocale();
			$curlng = $this->items;
			if ($locale) {
				if ($this->is($locale)) {
					$curlng = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/addict/localizations/".$locale.".json"),true);
				} else {
					return false;
				}
			}
			return isset($curlng[$item]);
		}

		public function info($locale = false){ //Вся информация о текущем языковом пакете
			$this->checkLocale();
			$curlng = $this->items;
			$inf = Array();

			if ($locale) {
				if ($this->is($locale)) {
					$curlng = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/addict/localizations/".$locale.".json"),true);
					$inf['locale'] = $locale;
				} else {
					return false;
				};
			} else {
				$inf['locale'] = $this->locale;
			};

			$inf['items'] = $curlng;
			return $inf;
		}

		public function get($item,$locale = false) { //Получение текста из языкового пакета
			$this->checkLocale();

			$curlng = $this->items;
			if ($locale) {
				if ($this->is($locale)) {
					$curlng = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/addict/localizations/".$locale.".json"),true);
				} else {
					return false;
				}
			};

			$item = str_replace("\+","&plus;",$item);
			if (strstr($item, '+')) { //Если элементов несколько
				$res = "";
				$items = explode("+", $item);
				foreach ($items as $key=>$val) {
					$val = str_replace("&plus;","+",$val);
					if (($this->isItem($val)) OR ($this->isItem($val,$locale))) {
							$res.=$curlng[$val];
						}
						else
						{
							$res.=$val;
						};
				};
				return $res;
			} else { //Если элемент 1
				return ($curlng[$item]);
			};
		}

		public function setFull($locale){ //Установить языковую локаль сайта
			if ($this->is($locale)) {
				$cfg = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/config.php");
				$cfg = str_replace(" ","",$cfg);
				$cfg = str_replace("	","",$cfg);
				$cfg = str_replace('$se_localization="'.$this->locale.'"','$se_localization="'.$locale.'"',$cfg);
				file_put_contents($_SERVER["DOCUMENT_ROOT"]."/config.php", $cfg);
				$this->items = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/addict/localizations/".$this->locale.".json"),true);
			};
		}

		public function setUsr($locale){ //Установить языковую локаль пользователя
			GLOBAL $USR;
			if ($this->is($locale)) {
				$USR->setParameter("lang",$locale);
				$this->locale = $locale;
				$this->items = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/addict/localizations/".$this->locale.".json"),true);
			} else {
				return false;
			};
		}

		public function setSes($locale){ //Установить языковую локаль сессии
			GLOBAL $USR, $_SESSION;
			if ($this->is($locale)) {
				$_SESSION['lang'] = $locale;
				$this->locale = $locale;
				$this->items = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/addict/localizations/".$this->locale.".json"),true);
			} else {
				return false;
			};
		}

	};

	$LNG = new LNG; //Внешний
?>
