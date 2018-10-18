<?php
class Controller {
	protected $f3;
	protected $db;
	public function __construct() {
		if ((float)PCRE_VERSION<7.9) trigger_error('PCRE version is out of date');
		$this->f3 = Base::instance();
		date_default_timezone_set($this->f3->get('TIMEZONE'));

		/*$this->f3->set('DB',new DB\SQL(
			'mysql:host='.$this->f3->get('DB_HOST').';port=3306;dbname='.$this->f3->get('DB_NAME'),
			$this->f3->get('DB_USER'),
			$this->f3->get('DB_PASS'),
			array(\PDO::ATTR_PERSISTENT => TRUE))
		);

		$this->db = $this->f3->get('DB');
		new \DB\SQL\Session($this->db,'sessiondata',TRUE,function($session){
			return false; // destroy session
		});*/

	}

	public function beforeroute() {

	}

	public function afterroute() {

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

}
