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

	/*
	public function readFromDataFile(){

		se

		$dir = 'app/data/';

		$files = scandir($dir);


		foreach ($files as $file) {

			if($file == '.' || $file == '..') continue;

			$text = file_get_contents($dir.$file);

			$lines = explode("\n", $text);

			foreach ($lines as $line) {

				$strings = preg_split ('/ /', $line, 3);
				$json = array_pop($strings);
				$date = implode(" ", $strings);

				echo $date."<br><br>";
				var_dump(json_decode($json, true));
				echo "<br><br>";

			}

		}

		var_dump($files);

	}
	*/

	public function addAWS(){
		$this->render('addaws');
	}


}
