<?
/*
    Ядро системы
*/
    session_start();
	require("./config.php");
	require("./core/load.php");
	$DB->connect($se_dbhost,$se_dbuser,$se_dbpass,$se_dbname);
	require("./system.php");
?>