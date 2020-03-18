<?php

	/*
	     Файл ядра CrossEngine
	*/

	require("system/load.php"); //Подключаем функции работы с сайтом
	require("hmvc/load.php"); //Подключаем MVC функции

	$MDL->load(); //Подгружаем модули
	$EVENT->init("modules-load");
	
	$stopList = $EVENT->init("stop-load",true);
	if (gettype($stopList) == "array") {
		$stop = false;
        foreach($stopList as $key=>$val) {
            if ($val == true) {$stop = true; break;}
        };
	};

	if (!$stop) { //Проверка отмены подгрузки страницы
		require_once("router.php");
	};
?>
