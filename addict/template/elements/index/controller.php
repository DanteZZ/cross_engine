<?

class Controller_Index extends Controller
{
	function __construct() {
		$this->view = new View();
		$this->model = new Model_Index;
	}

	function action() {
		$data = $this->model->get_data();
		$this->load("parts/header");
		$this->view->generate('pages/index',$data);
		$this->load("parts/footer");
	}
}

?>