<?php

class AWSController extends Controller{

	public function main(){

		//insert
		// $am = new AWSMapper($this->db);
		// $am->name = 'CJ2';
		// $am->username = 'sjdhsjd';
		// $am->password = 'asdsadd';
		// $am->save();

		//retrieve
		// $am->load();
		// while(!$am->dry()){
		// 	echo $am->name . "<br>";
		// 	echo $am->password . "<br>";
		// 	$am->next();
		// }

		//update
		// $am->load(array('name = ?', 'CJ'));
		// $am->name = 'hahaha';
		// $am->save();

		//delete
		// $am->load();
		// while(!$am->dry()){
		// 	$am->erase();
		// 	$am->next();
		// }

		//manual query
		// $arr = $this->db->exec('select * from aws where name = :name and id = :id', [
		// 	':name'=>'haha',
		// 	':id'=>$id,
		// ]);
		// var_dump($arr);


		$this->render('main');

	}

	public function map(){
		$this->render('awsmap');
	}

	
	public function readFromDataFile(){

		die('Please do not use this.');

		set_time_limit(0);

		$dir = 'app/data/';

		$files = scandir($dir);

		$aws_array = array(
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
		);

		$this->db->exec('TRUNCATE aws;');
		$this->db->exec('TRUNCATE reading;');


		foreach ($files as $file) {

			if($file == '.' || $file == '..') continue;

			$text = file_get_contents($dir.$file);

			$name = explode('.', $file)[0];

			$aws = $aws_array[$name];

			$lines = explode("\n", $text);

			$this->db->exec('INSERT INTO aws (name, username, password) VALUES (:name, :username, :password)', [
				':name'=>$name,
				':username'=>$aws[0],
				':password'=>$aws[1],
			]);

			echo $name."\n";


			$aws = $this->db->exec('SELECT * FROM aws WHERE name = ?', $name)[0]; 

			foreach ($lines as $line) {

				$strings = preg_split ('/ /', $line, 3);
				$json = array_pop($strings);
				$date = implode(" ", $strings);

				if (isJson($json)) {

					echo $date."\n";
					$json = json_decode($json, true);
					//echo "<br><br>";

					$values = [];
					$values[':aws_id'] = $aws['aws_id'];
					$values[':observation_time'] = isset($json['observation_time_rfc822'])?date('Y-m-d H:i:s',strtotime($json['observation_time_rfc822'])):null;
					$values[':location'] = $json['location']?: null;
					$values[':latitude'] = $json['latitude']?: null;
					$values[':longitude'] = $json['longitude']?: null;
					$values[':date_recorded'] = $date;
					$values[':station_id'] = $json['station_id']?: null;
					$values[':station_name'] = $json['davis_current_observation']['station_name']?: null;
					$values[':temperature'] = $json['temp_c']?: null;
					$values[':wind_speed'] = $json['wind_mph']?: null;
					$values[':wind_direction'] = $json['wind_dir']?: null;
					$values[':solar_radiation'] = $json['davis_current_observation']['solar_radiation']?: null;
					$values[':rain'] = $json['davis_current_observation']['rain_day_in']?: null;

					$this->db->exec('INSERT INTO reading (aws_id, observation_time, location, latitude, longitude, date_recorded, station_id, station_name, temperature, wind_speed, wind_direction, solar_radiation, rain) VALUES (:aws_id, :observation_time, :location, :latitude, :longitude, :date_recorded, :station_id, :station_name, :temperature, :wind_speed, :wind_direction, :solar_radiation, :rain)', $values);

				}

				ob_end_clean();

			}

		}

		var_dump($files);

	}

	function generateXMLdata(){

		$symbols = file_get_contents('app/resources/weather-symbols.txt');
		$symbols = explode("\n", $symbols);

		$weatherdata = new SimpleXMLElement('<weatherdata/>');

		$location = $weatherdata->addChild('location');
		$location->addChild('name', 'LB');
		$location->addChild('type', 'Town');
		$location->addChild('country', 'Philippines');

		$credit = $weatherdata->addChild('credit');
		$link = $credit->addChild('link');
		$link->addAttribute('text', 'Weather forecast from Yr, delivered by the Norwegian Meteorological Institute and the NRK" url="http://www.yr.no/place/United_Kingdom/England/London/');
		
		$timezone = $location->addChild('timezone');
		$timezone->addAttribute('id', 'Europe/London');
		$timezone->addAttribute('utcoffsetMinutes', '60');

		$forecast = $weatherdata->addChild('forecast');
		$tabular = $forecast->addChild('tabular');
		for ($i=0; $i<30; $i++) {
			$time = $tabular->addChild('time');

			$time->addAttribute('from', date("Y-m-d\TH:i:s", strtotime("+".$i." hours")));
			$time->addAttribute('to', date("Y-m-d\TH:i:s", strtotime("+".($i+1)." hours")));

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
			$precipitation->addAttribute('value', rand(0,3).'.'.rand(0,99));

			$windDirection = $time->addChild('windDirection');
			$windDirection->addAttribute('deg', rand(0,360).'.'.rand(0,9));
			$windDirection->addAttribute('code', 'SSE');
			$windDirection->addAttribute('name', 'South-southeast');

			
			$windSpeed = $time->addChild('windSpeed');
			$windSpeed->addAttribute('mps', rand(0,10).'.'.rand(0,9));
			$windSpeed->addAttribute('name', 'Gentle breeze');


			$temperature = $time->addChild('temperature');
			$temperature->addAttribute('unit', 'celsius');
			$temperature->addAttribute('value', rand(12,35));


			$pressure = $time->addChild('pressure');
			$pressure->addAttribute('unit', 'hPa');
			$pressure->addAttribute('value', rand(900,1200).'.'.rand(0,9));

		}


		echo $weatherdata->asXML();

		


	}
	

	public function addAWS(){
		$this->render('addaws');
	}

	public function manageAWS(){
		$aws = $this->db->exec('SELECT * FROM aws'); 

		$this->render('manageaws');
	}

	public function addNewAWS($f3){
		$am = new AWSMapper($this->db);
		$am->name = $f3->get('POST.name');
		$am->username = $f3->get('POST.username');
		$am->password = $f3->get('POST.password');
		$am->save();

		$this->f3->reroute('/manageAWS');
	}

	public function editAWS($f3){
		
	}

	public function compare(){

		$results = $this->db->exec('SELECT aws_id, name from aws;');
		$aws = [];
		foreach ($results as $result) {
			$aws[] = array(
				'id'=>$result['aws_id'],
				'name'=>$result['name']
			);
		}

		$this->f3->set('aws', $aws);
		$this->f3->set('aws2', $aws);
		$this->render('compare');
	
	}

}
