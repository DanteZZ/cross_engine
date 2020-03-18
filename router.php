<?php
/*
	Файл генератора страниц
*/
	$tmpl = $TMPL->template; //Информация о теме



	$part = str_replace("?".$_SERVER['QUERY_STRING'], "", $_SERVER['REQUEST_URI']);

	$uri = substr($part, 1);
	if (substr($uri,-1,1) == "/") {$uri = substr($uri,0,strlen($uri)-1);};
	$uriAr = explode("/",$uri); //Массив ссылки

	if ($uri) {

		if ($uriAr[0] == "error") { //Если страница запрашивает ошибку
			
			if ($PAGE->is($uriAr[1])) { //Если файл ошибки существует
				$PAGE->get($uriAr[1]);
			} else { //Если не существует
				echo "<h1>Error ".$uriAr[1]."</h1>";
			}

		} else {

			$pages = $tmpl['pages']; //Страницы
			$pageVals = Array(); //Переменные из ссылки

			foreach ($uriAr as $key => $path) {
				$list = Array();
				if ($pages[urldecode($path)]) { //Если страница существует

					$page = $pages[urldecode($path)];
					$pages = $page['subs'];

					if ($page['rules']) { //Если страница запрашивает права
						if (gettype($page['rules']) == "string") { //Если это одно право
							if (!$PRIORITY->isRule($page['rules'])) {
								$PAGE->error(403);
							};
						} else { //Если это массив правил
							foreach ($page['rules'] as $rkey => $rule) {
								if (!$PRIORITY->isRule($rule)) {
									$PAGE->error(403);
								};
							}
						}
					};

				} else { //Если страница не существует
					$has = 0; //Существование переменной
					foreach ($pages as $name=>$pg) {
						if ((substr($name,0,1) == "{") AND (substr($name,-1,1) == "}")) { //Если страница может получать переменные
							$has = 1;
							$page = $pg;
							$pages = $page['subs'];
							$valname = substr($name,1,strlen($name)-2);
							 

							if (isset($page['get-array'])) { //Если переменная привязана к массиву
								switch ($page['get-array']) {
									case "DS": 	
										$pageVals[$valname] = $DS->get($path);
										if (!$pageVals[$valname]) {
											$PAGE->error(404);
										};
									break;
									case "USER":
										$pageVals[$valname] = $USR->info($path);
										if (!$pageVals[$valname]) {
											$PAGE->error(404);
										};
									break;
								};
							} else { //Если не привязана, просто отправляем её 
								$pageVals[$valname] = $path;
							}


							if ($page['rules']) { //Если страница запрашивает права
								if (gettype($page['rules']) == "string") { //Если это одно право
									if (!$PRIORITY->isRule($page['rules'])) {
										$PAGE->error(403);
									};
								} else { //Если это массив правил
									foreach ($page['rules'] as $rkey => $rule) {
										if (!$PRIORITY->isRule($rule)) {
											$PAGE->error(403);
										};
									}
								}
							};

							break;
						} else {
							continue;
						};
					};
					if (!$has) { //Если переменной не нашлось
						$PAGE->error(404);
					};
				};
			};


			unset($page['subs']);
			$page = array_merge($page,$pageVals);
			$PAGE->params = $page;
			
			if ($page['action']) {
				$action = "action_".$page['action'];
			} else {
				$action = "action";
			};
			
			$element_path = $_SERVER['DOCUMENT_ROOT']."/".$tmpl['path'].$tmpl['elements_folder']."/".strtolower($page['element'])."/";
			if (is_file($element_path."controller.php")) { //Если контроллер страницы существует
				$EVENT->init("on_page_start");

				if(file_exists($element_path."model.php"))
				{
					include_once($element_path."model.php");
				}

				include_once($element_path."controller.php");

				
				$controller_name = "Controller_".str_replace(" ","_", ucwords( str_replace("/", " ", $page['element']) ) );
				// создаем контроллер
				$controller = new $controller_name;
				if(method_exists($controller, $action))
				{
					$controller->$action();
				}
				else
				{
					// здесь также разумнее было бы кинуть исключение
					$PAGE->error(404);
				}

				$EVENT->init("on_page_end");

			} else { //Если не существует
				$PAGE->error(404);
			}
				
		};
	} else {
		$page = $tmpl['pages'][''];
		$page['link'] = $uri;
		$PAGE->params = $page;
		if ($page['action']) {
			$action = "action_".$page['action'];
		} else {
			$action = "action";
		};


		$element_path = $_SERVER['DOCUMENT_ROOT']."/".$tmpl['path'].$tmpl['elements_folder']."/".strtolower($page['element'])."/";
		if (is_file($element_path."controller.php")) { //Если контроллер страницы существует
			$EVENT->init("on_page_start");

			if(file_exists($element_path."model.php"))
			{
				include $element_path."model.php";
			}

			include $element_path."controller.php";

			
			$controller_name = "Controller_".str_replace(" ","_", ucwords( str_replace("/", " ", $page['element']) ) );
			// создаем контроллер
			$controller = new $controller_name;

			if(method_exists($controller, $action))
			{
				// вызываем действие контроллера
				$controller->$action();
			}
			else
			{
				// здесь также разумнее было бы кинуть исключение
				$PAGE->error(404);
			}

			$EVENT->init("on_page_end");

		} else { //Если не существует
			echo $element_path."controller.php";
			$PAGE->error(404);
		}
	}
	
?>