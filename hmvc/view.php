<?

class View
{
	function generate($element_name, $data = null,$ext=true)
	{
		GLOBAL $TMPL;
		
		if(is_array($data)) {
			// преобразуем элементы массива в переменные
			if ($ext) {extract($data);};
		}
		
		$tmpl = $TMPL->template;
		include $_SERVER['DOCUMENT_ROOT']."/".$tmpl['path'].$tmpl['elements_folder']."/".strtolower($element_name)."/view.php";
	}
}

?>