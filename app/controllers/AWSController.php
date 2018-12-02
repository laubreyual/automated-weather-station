<?php

class AWSController extends Controller{

	public function __construct(){

        parent::__construct();

        $this->f3->set('AWS_ARRAY', [
        	"ICALABAR18"=>["001D0AF11A4F","uplbs4r41vc1"],//
        	"ICAGAYAN3"=>["001D0AF11A50","uplbs4r41vc2"],//
        	"ICAGAYAN2"=>["001D0AF11A51","uplbs4r41vc3"],//
        	"ICENTRAL91"=>["001D0AF11A52","uplbs4r41vc4"],//
        	"ICALABAR25"=>["001D0AF11A55","uplbs4r41vc5"],//
        	"IWESTERN635"=>["001D0AF11A77","uplbs4r41vc6"],//
        	"ICENTRAL94"=>["001D0AF11A57","uplbs4r41vc7"],//
        	"IBICOLGU2"=>["001D0AF11A7C","uplbs4r41vc8"],//
        	"IMIMAROP6"=>["001D0AF11A7E","uplbs4r41vc9"],//
        	"IMIMAROP7"=>["001D0AF11A7F","uplbs4r41vc10"],//
        	"IMIMAROP8"=>["001D0AF11A58","uplbs4r41vc11"],//
        	"IZAMBOAN4"=>["001D0AF11A81","uplbs4r41vc12"],//
        	"IDAVAORE19"=>["001D0AF11D7F","uplbs4r41vc13"],//
        	"IDAVAORE20"=>["001D0AF11A59","uplbs4r41vc14"],//
        	"INORTHER117"=>["001D0AF11D80","uplbs4r41vc15"],//
        	"INORTHER86"=>["001D0AF11A5D","uplbs4r41vc16"],//
        	"IREGIONX6"=>["001D0AF11A5A","uplbs4r41vc17"],//
        	"IWESTERN596"=>["001D0AF11D82","uplbs4r41vc18"],//
        	"ILOSBAOS2"=>["001D0AF11D84","uplbs4r41vc20"]//
        ]);

	}

	public function main(){

		$awses = $this->db->exec('select * from aws');

		if ($this->f3->exists('GET.id')) {
			$aws_id = $this->f3->get('GET.id');
		} else {
			$aws_id = $awses[0]['aws_id'];
		}

		$am = new AWSMapper($this->db);
		$am->load(['aws_id = ?', $aws_id]);

		if ($am->dry()) {
			die('Invalid id.');
		} 

		$this->f3->set('aws_id', $aws_id);
		$this->f3->set('aws', $am->cast());
		$this->f3->set('awses', $awses);

		$this->render('main');

	}

	public function map(){

		$this->render('awsmap');

	}

	
	public function readFromDataFile(){

		set_time_limit(0);

		$aws_array = $this->f3->get('AWS_ARRAY');

		$am = new AWSMapper($this->db);
		$rm = new ReadingMapper($this->db);
		
		$dir = 'app/data/';
		$files = scandir($dir);

		foreach ($files as $file) {

			if ($file == '.' || $file == '..') continue;

			$name = explode('.', $file)[0];
			$ext = explode('.', $file)[1];

			if ($ext != 'txt') continue;
			
			$aws = $aws_array[$name];

			$am->reset();
			$am->load(['name = ?', $name]);
			if ($am->dry()) {
				$this->db->exec('INSERT INTO aws (name, username, password) VALUES (:name, :username, :password)', [
					':name'=>$name,
					':username'=>$aws[0],
					':password'=>$aws[1],
				]);
			}

			echo $name."\n";

			$aws = $this->db->exec('SELECT * FROM aws WHERE name = ?', $name)[0]; 

			$last_update = $this->db->exec('SELECT observation_time FROM reading WHERE aws_id = ? ORDER BY observation_time DESC LIMIT 1', $aws['aws_id'])[0]; 

			if (!$last_update) {
				$last_update = '1990-01-01 00:00:00';
			}

			$existing = [];

			$buffer = [];

			echo "Reading $dir/$file\n";
			$handle = fopen($dir.$file, "r");
			if ($handle) {
			    while (($line = fgets($handle)) !== false) {
					$strings = preg_split ('/ /', $line, 3);
					$json = array_pop($strings);
					$date = implode(" ", $strings);

					if (isJson($json)) {

						$json = json_decode($json, true);

						$values = [];
						$values['observation_time'] = isset($json['observation_time_rfc822'])?date('Y-m-d H:i:s',strtotime($json['observation_time_rfc822'])):null;

						if($values['observation_time'] < $last_update) {
							echo "skipping\n";
							continue;
						} else if (in_array($values['observation_time'], $existing)) {
							echo "existing\n";
							continue;
						} else {
							$existing[] = $values['observation_time'];
						}
						
						$values['aws_id'] = $aws['aws_id'];
						$values['location'] = $json['location']?: null;
						$values['latitude'] = $json['latitude']?: null;
						$values['longitude'] = $json['longitude']?: null;
						$values['date_recorded'] = $date;
						$values['station_id'] = $json['station_id']?: null;
						$values['station_name'] = $json['davis_current_observation']['station_name']?: null;
						$values['temperature'] = $json['temp_c']?: null;
						$values['wind_speed'] = $json['wind_mph']?: null;
						$values['wind_direction'] = $json['wind_dir']?: null;
						$values['wind_degrees'] = (float) $json['wind_degrees']?: null;
						$values['solar_radiation'] = $json['davis_current_observation']['solar_radiation']?: null;
						$values['rain'] = $json['davis_current_observation']['rain_day_in']?: null;
						$values['pressure'] = (float) $json['pressure_mb']?: null;

						echo $date."\n";
						$buffer[] = $values;

						//$this->db->exec('INSERT INTO reading (aws_id, observation_time, location, latitude, longitude, date_recorded, station_id, station_name, temperature, wind_speed, wind_direction, solar_radiation, rain) VALUES (:aws_id, :observation_time, :location, :latitude, :longitude, :date_recorded, :station_id, :station_name, :temperature, :wind_speed, :wind_direction, :solar_radiation, :rain)', $values);

					}

					if (sizeof($buffer) >= 500) {

						$cols = array('aws_id','observation_time','location','latitude','longitude','date_recorded','station_id','station_name','temperature','wind_speed','wind_direction','wind_degrees','solar_radiation','rain','pressure');

						$query = $this->createInsertQuery($buffer, 'reading', $cols);

						$this->db->exec($query);

						$buffer = [];

					}

					ob_end_clean();
			        // process the line read.
			    }
			    fclose($handle);
			} else {
			    echo "Error opening $dir/$file\n";
			} 

			if (sizeof($buffer) > 0) {
				$cols = array('aws_id','observation_time','location','latitude','longitude','date_recorded','station_id','station_name','temperature','wind_speed','wind_direction','wind_degrees','solar_radiation','rain','pressure');

				$query = $this->createInsertQuery($buffer, 'reading', $cols);

				$this->db->exec($query);

				$buffer = [];

			}

		}

	}

	function generateXMLdata(){

		$aws_id = $this->f3->get('PARAMS.id');

		$limit = 30;


		$readings = $this->db->exec('select * from reading where aws_id = :aws_id order by observation_time desc limit '.$limit, [
			':aws_id'=>$aws_id,
		]);
		$readings = array_reverse($readings);

		$symbols = file_get_contents('app/resources/weather-symbols.txt');
		$symbols = explode("\n", $symbols);

		$weatherdata = new SimpleXMLElement('<weatherdata/>');

		$location = $weatherdata->addChild('location');
		$parts = preg_split('~,(?=[^,]*$)~', $readings[0]['location']);
		$location->addChild('name', $parts[0]);
		//$location->addChild('type', 'Town');
		$location->addChild('country', $parts[1]);

		$credit = $weatherdata->addChild('credit');
		$link = $credit->addChild('link');
		$link->addAttribute('text', 'Weather data from api.weatherlink.com');
		
		$timezone = $location->addChild('timezone');
		$timezone->addAttribute('id', 'Asia/Manila');
		$timezone->addAttribute('utcoffsetMinutes', '480');

		$forecast = $weatherdata->addChild('forecast');
		$tabular = $forecast->addChild('tabular');

		$lasttemp = 26;
		$lastpress = 1013;

		foreach ($readings as $i => $reading) {
			$time = $tabular->addChild('time');

			$time->addAttribute('from', date("Y-m-d\TH:i:s", strtotime($reading['observation_time'])));

			if ($i+1 == sizeof($readings)) {
				$time->addAttribute('to', date("Y-m-d\TH:i:s", strtotime($reading['observation_time'])+3600));

			} else {
				$time->addAttribute('to', date("Y-m-d\TH:i:s", strtotime($readings[$i+1]['observation_time'])));
			}

			$x = rand(0,sizeof($symbols)-1);
			$parts = explode(',', $symbols[$x]);
			$name = $parts[0];
			$var = $parts[1];

			$x = rand(0,sizeof($symbols)-1);
			$symbol = $time->addChild('symbol');
			$symbol->addAttribute('number', $x);
			$symbol->addAttribute('numberEx', $x);
			$symbol->addAttribute('name', $name);
			$symbol->addAttribute('var', $var);
			
			$precipitation = $time->addChild('precipitation');
			$precipitation->addAttribute('value', $reading['rain']);

			$windDirection = $time->addChild('windDirection');
			$windDirection->addAttribute('deg', $reading['wind_degrees']); 
			$windDirection->addAttribute('code', 'SSE');
			$windDirection->addAttribute('name', $reading['wind_direction']);
			
			$windSpeed = $time->addChild('windSpeed');
			$windSpeed->addAttribute('mps', $reading['wind_speed']);
			$windSpeed->addAttribute('name', 'Gentle breeze');

			$temperature = $time->addChild('temperature');
			$temperature->addAttribute('unit', 'celsius');
			$temperature->addAttribute('value', $reading['temperature']?:$lasttemp);
			$lasttemp = $reading['temperature']?:$lasttemp;

			$pressure = $time->addChild('pressure');
			$pressure->addAttribute('unit', 'hPa');
			$pressure->addAttribute('value', $reading['pressure']?:$lastpress);
			$lastpress = $reading['pressure']?:$lastpress;



		}

		echo $weatherdata->asXML();


	}
	

	public function addAWS(){
		$this->render('addaws');
	}

	public function loginPage() {
		if ($this->f3->exists('SESSION.user')) {
			$this->f3->reroute('/manageAWS');
		} else {
			$error = $this->f3->get('SESSION.error');
			$this->f3->clear('SESSION.error');

			$this->f3->set('PAGE.error',$error);
			$this->render('login');
		}
	}

	public function login() {
		$username = $this->f3->get('POST.username');
		$password = md5($this->f3->get('POST.password'));

		$um = new UserMapper($this->db);
		$um->load(array('username = :username AND password = :password', array(':username'=>$username,':password'=>$password)));

		if($um->dry()){
			$this->f3->set('SESSION.error', "Invalid username/password.");
			$this->f3->reroute('/login');
		} else {
			$temp = $um->cast();

			$user = array('user_id'=>$um->user_id,'first_name'=>$um->first_name,'last_name'=>$um->last_name,'username'=>$um->username);

			$user = array_merge($temp,$user);
			$this->f3->set('SESSION.user',$user);
			$this->f3->reroute('/manageAWS');
		}
	}

	public function logout() {
		$this->f3->clear('SESSION.user');
		$this->f3->reroute('/login');
	}

	public function manageAWS(){
		if ($this->f3->exists('SESSION.user')) {
			$stations = $this->db->exec('SELECT * FROM aws'); 

			$aws = [];
			foreach ($stations as $station) {
				$aws[] = array(
					'id' => $station['aws_id'],
					'name' => $station['name'],
					'username' => $station['username'],
					'password' => $station['password']
				);
			}

			$this->f3->set('aws', $aws);
			$this->render('manageaws');
		} else {
			$this->f3->reroute('/login');
		}
	}

	public function addNewAWS($f3){
		if ($this->f3->exists('SESSION.user')) {
			$am = new AWSMapper($this->db);
			$am->name = $f3->get('POST.name');
			$am->username = $f3->get('POST.username');
			$am->password = $f3->get('POST.password');
			$am->save();

			$this->f3->reroute('/manageAWS');
		} else {
			$this->f3->reroute('/login');
		}
	}

	public function editAWS($f3){
		if ($this->f3->exists('SESSION.user')) {
			$name = $this->f3->get('POST.name');
			$username = $this->f3->get('POST.username');
			$password = $this->f3->get('POST.password');
			$name = strtoupper($name);
			$name = trim($name);
			$username = strtoupper($username);
			$username = trim($username);
			$password = trim($password);

			$this->db->exec('UPDATE aws SET name = :name, username = :username, password = :password where aws_id = :id', array(
				':name'=>$name,
				':username'=>$username,
				':password'=>$password,
				':id'=>$this->f3->get('POST.aws_id')
			));
			$this->f3->reroute('/manageAWS');
		} else {
			$this->f3->reroute('/login');
		}
	}

	public function deleteAWS($f3){
		if ($this->f3->exists('SESSION.user')) {
			$aws_id = $this->f3->get('PARAMS.id');

			$this->db->exec('DELETE FROM aws WHERE aws_id = :id', array(
				':id'=>$aws_id
			));

			$this->f3->reroute('/manageAWS');
		} else {
			$this->f3->reroute('/login');
		}
	}

	public function compare(){

		$results = $this->db->exec('SELECT * from aws;');
		$aws = [];
		foreach ($results as $result) {
			$aws[] = array(
				'id'=>$result['aws_id'],
				'name'=>$result['name']
			);
		}


		if ($this->f3->exists('GET.id')) {
			$aws_id = $this->f3->get('GET.id');
		} else {
			$aws_id = $results[0]['aws_id'];
		}
		$this->f3->set('aws_id', $aws_id);

		if ($this->f3->exists('GET.id2')) {
			$aws_id2 = $this->f3->get('GET.id2');
		} else {
			$aws_id2 = $results[0]['aws_id'];
		}
		$this->f3->set('aws_id2', $aws_id2);
		

		$this->f3->set('awses', $results);
		$this->f3->set('aws', $aws);
		$this->f3->set('aws2', $aws);
		$this->render('compare');
	
	}

}
