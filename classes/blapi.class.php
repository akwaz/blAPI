<?php

	class blAPI {
		protected $maps_map = array(
				//vanilla
			"key" => "value"
		);

		//config map
		protected $config_map = [
				"game" =>"bf4",
				"url" => "http://battlelog.battlefield.com/bf4/",
				"maps" => "shit",
				"smth" => 			
		];
		
		//stores data about maps (levels) in bf4 - every map is called in a different way than in-game
		//example - real name: 
		public function __contruct() {
			
			
		}
		
		public function test() {
			echo $config_map['smth'];

		}
		
	}