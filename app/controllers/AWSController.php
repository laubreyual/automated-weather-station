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

		//die('Please do not use this.');

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
	



}
