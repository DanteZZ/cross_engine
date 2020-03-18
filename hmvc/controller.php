<?

class Controller {
	
	public $model;
	public $view;
	
	function __construct()
	{
		$this->view = new View();
	}


	function load($element,$action=null,$data=null) {
		GLOBAL $TMPL;
		$tmpl = $TMPL->template;

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
}

?>