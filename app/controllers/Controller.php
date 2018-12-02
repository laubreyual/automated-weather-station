<?php
class Controller {
	protected $f3;
	protected $db;
	public function __construct() {
		if ((float)PCRE_VERSION<7.9) trigger_error('PCRE version is out of date');
		$this->f3 = Base::instance();
		date_default_timezone_set($this->f3->get('TIMEZONE'));

		$this->f3->set('DB',new DB\SQL(
			'mysql:host='.$this->f3->get('DB_HOST').';port=3306;dbname='.$this->f3->get('DB_NAME'),
			$this->f3->get('DB_USER'),
			$this->f3->get('DB_PASS'),
			array(\PDO::ATTR_PERSISTENT => TRUE))
		);

		$this->db = $this->f3->get('DB');
		new \DB\SQL\Session($this->db,'sessiondata',TRUE,function($session){
			return false; // destroy session
		});

	}

	public function beforeroute() {

	}

	public function afterroute() {

	}

	public function render($page){

		$this->f3->set('template', $this->f3->get('VIEWS').$page.'.htm');
		
		echo Template::instance()->render($this->f3->get('VIEWS').'layout.htm');

	}

	public function expect($param) {
		$argnum = func_num_args();
		$args = func_get_args();

		if($argnum == 1 && is_array($param)){
			for ($i = 0; $i < sizeof($param); $i++) {
				if(!$this->f3->exists('REQUEST.'.$param[$i])){
					$this->error('Parameter expected: '.$param[$i]);
				}
		    }
		}
		else{
		    for ($i = 0; $i < $argnum; $i++) {
				if(!$this->f3->exists('REQUEST.'.$args[$i])){
					$this->error('Parameter expected: '.$args[$i]);
				}
		    }
		}
	}

	public function respond($array) {
		$json = json_encode(array('status'=>'OK','result'=>$array));
		die($json);
	}

	public function error($error) {
		$json = json_encode(array('status'=>'ERROR','error'=>$error));
		die($json);
	}

	public function createInsertQuery($arr, $tname, $tcols){

		$mysqli = new mysqli($this->f3->get('DB_HOST'), $this->f3->get('DB_USER'), $this->f3->get('DB_PASS'), $this->f3->get('DB_NAME'));

		$init_q = "INSERT INTO `" . mysqli_real_escape_string($mysqli, $tname) . "` (";

		for($i = 0; $i < sizeof($tcols); $i++){
			$init_q .= "`" . mysqli_real_escape_string($mysqli, $tcols[$i]) . "`";

			if($i != sizeof($tcols) - 1) $init_q .= ",";
			else $init_q .= ") VALUES ";
		}

		for($i = 0; $i < sizeof($arr); $i++){
			if(sizeof($arr[$i]) != sizeof($tcols))
				return false;

			$init_q .= '(';
			for($j = 0; $j < sizeof($tcols); $j++) {
				if(!isset($arr[$i][$tcols[$j]]) && $arr[$i][$tcols[$j]] != null)
					return false;
				else{
					if(is_string($arr[$i][$tcols[$j]])){
						$init_q .= '"'. mysqli_real_escape_string($mysqli, $arr[$i][$tcols[$j]]) . '"';
					} else if($arr[$i][$tcols[$j]] === null) {
						$init_q .= 'null';
						
					} else {
						$init_q .= mysqli_real_escape_string($mysqli, $arr[$i][$tcols[$j]]);
					}
				}

				if($j != sizeof($tcols) - 1)
					$init_q .= ', ';
				else
					$init_q .= ")";

			}

			if($i != sizeof($arr) - 1)
				$init_q .= ", ";
		}

		return $init_q;
	}

}
