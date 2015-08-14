<?php

	class blAPI {
		
		protected  $settings_map = array();//dont know why does it exist, but it exists (at least for now...)
		
		//stores data about maps (levels) in bf4 - every map is called in a different way than in-game
		//example - real name: Golmud Railway, battlelog name: MP_Journey
		protected  $map_map;
		
		//preset map
		protected  $preset_map;
			
		//gamemodes map
		protected  $mode_map;
				
		//DLCs map
		protected  $dlc_map;
		
		//kit map
		protected $kit_map;
				
		//region map
		protected  $region_map;		
		
		//config map
		protected  $config_map;
				
		public function __construct($game) {
			if ($game != 'unknown'){ $this->loadCfg($game);}	
		}
		
		/* START OF PUBLIC API CALLS */
		
		//allows user to set the game after constructing the object
		public function setGame($game) {
			$this->loadCfg($game);
		}
		
		/* public function getServerData($server_url, $json = false, $human_friendly = false)
		 * 
		 * Purpose: Get data about particular server. 
		 * Args: 
		 * 		*$server_url* - URL of the server (*OBLIGATORY)
		 * 
		 *		$json - false by default, if you want recieve a JSON object, set this to TRUE (optional)
		 *
		 * 		$human_friendly - false by default. If true, output data will be converted into ready-to-use vals
		 * 						  like real name of map, correct preset name (optional)
		 * 
		 * Returns: array or JSON object
		 * Throws: nothing
		 */
		public function getServerData($server_url, $json = false, $human_friendly = false) {
			$url = $server_url . '?json=1';
			$server_data = $this->query($url);
	
			if ($human_friendly) {
				
				//pass $result to special method
				$server_data = $this->humanFriendlyServer($server_data);	
				
				//return json object or php array
				// if json = true, we have to encode it again, cause we've already change JSON to php array in humanFriendlyServer
				/*if ($json) {
					return json_encode($server_data);
				}
				else {
					return $server_data;
				}*/
				$server_data = $this->returnData($server_data, $json, false);
				return $server_data;
			}
			//if user doesn't want human-friendly response...
			else {
				/*//if json = true, return $result without decoding
				if ($json) {return $server_data;}
				//otherwise decode JSON objcet to assoc array
				else {return json_decode($server_data, true);}*/
				$server_data = $this->returnData($server_data, $json, true);
				return $server_data;
			}
		}
		
		
		/* public function getBattleReport($battle_report)
		 * 
		 * Purpose: Get data about match from battlereport
		 * Args:
		 * 		
		 * Returns:
		 * Throws:
		 */
		public function getBattleReport($report_id, $json = false, $human_friendly = false) {
			$url = $this->config_map['report_url_start'] . $report_id . $this->config_map['report_url_end'];
			$report = $this->query($url);		
			if ($human_friendly) {
				$report = $this->humanFriendlyReport($report);
				$report = $this->returnData($report, $json, false);
				return $report;
			}else {
				$report = $this->returnData($report, $json, true);
				return $report;
			}
		}
		
		
		/* public function getPlayerData ($player)
		 * 
		 * Purpose: Get data about one player
		 * Args:
		 * 		*$player_id* - player id, number can by found between "stats" and 
		 * 					   platform strings, e.g
		 * 					   soldier/ArekTheMLGPro/stats/887022216/pc/ (*OBLIGATORY)
		 *  	
		 * 		$json - false by default. If true, method will return JSON object (optional)
		 * 		
		 * 		$big - false by default, can be etiher true or false, 
		 *  		   if true method returns huge (about 2680 lines) array/JSON object. If false,
		 * 			   will return about 298 lines. (optional)
		 * 		
		 * 		$human_friendly - false by default. If true, output data will be converted into 
		 * 					   ready-to-use vals like correct preset name (optional)
		 * 
		 * Returns: array or JSON object
		 * Throws: nothing
		 */
		public function getPlayerData ($player_id, $json = false, $human_friendly = false, $big = false) {
			//big big data
			/*http://battlelog.battlefield.com/bf4/warsawoverviewpopulate/ID/1/
			
			//small data
			/*http://battlelog.battlefield.com/bf4/warsawdetailedstatspopulate/ID/1/
			 * */
			
			if ($big) {
				$url = $this->config_map['big_user_url_start'] . $player_id . $this->config_map['user_url_end'];
			}
			else {
				$url = $this->config_map['small_user_url_start'] . $player_id . $this->config_map['user_url_end'];
			}
			$player_data = $this->query($url);
			
			
			//spaghetti incoming...
			if ($human_friendly) { //if user wants user-friendly output, we will send this var to special method
				$player_data = $this->humanFriendlyPlayer($player_data, $big);
				
				//as below, we have to check if user wants JSON, or array. Keep in mind that by using humanFriendlyPlayer method,
				//we've decoded our JSON. It's assoc array right now. That's why behaviour is reversed
				/*if ($json) {
					return json_encode($player_data);
				}
				else {
					return $player_data;
				}*/
				$player_data = $this->returnData($player_data, $json, false);
				return $player_data;
			}
			else { //if he doesn't want this, we can return the data...
				/*if ($json) { //... but we also have to check if user wants JSON or assoc array
					//if json, simply return stuff - we haven't decoded it
					return $player_data;
				}
				else {
					//if assoc array, we have to decode it
					return json_decode($player_data, true);
				}*/
				$player_data = $this->returnData($player_data, $json, true);
				return $player_data;
			}
			//whoa, that was a lot of spaghetti!
		}
		
		/* END OF PUBLIC API CALLS */
		
		protected function query($url) {
			//will use either cURL or simply file_get_contents
			//$url var must me ready to use when passed to this method
			return $result = file_get_contents($url);			
		}
	
		
		protected function loadCfg($game) {
			
			//load universal assets
			$this->region_map = require_once "cfg/region_map.php";
			//$this->settings_map = require_once"cfg/settings_map.php";
			$this->preset_map = require_once 'cfg/preset_map.php';
			if ($game == 'bf4') {
				
				//load game-specify assets 
				$this->kit_map = require_once 'cfg/bf4/bf4_kit_map.php';
				$this->map_map = require_once "cfg/bf4/bf4_map_map.php";
				$this->dlc_map = require_once 'cfg/bf4/bf4_dlc_map.php';
				$this->mode_map = require_once "classes/cfg/bf4/bf4_mode_map.php";
				$this->config_map = require_once 'classes/cfg/bf4/bf4_config_map.php';
			}
			elseif ($game == 'bfh') {
				$this->kit_map = require_once 'cfg/bfh/bfh_kit_map.php';
				$this->map_map = require_once "cfg/bfh/bfh_map_map.php";
				$this->dlc_map = require_once 'cfg/bfh/bfh_dlc_map.php';
				$this->mode_map = require_once "classes/cfg/bfh/bfh_mode_map.php";
				$this->config_map = require_once 'classes/cfg/bfh/bfh_config_map.php';
				
			}
		}
		
		protected function humanFriendlyServer ($result) {
			//we have to decode json here anyway, cause we want to change some values to more user-friendly
			$result = json_decode($result, true);
		
			//would you like some spaghetti?
			//set correct name of CURRENT MAP
			$map = $result['message']['SERVER_INFO']['map'];
			$this->map_map[$map] = $this->checkOffset($map, $this->map_map);
			$result['message']['SERVER_INFO']['map'] = $this->map_map[$map];
		
			//set name of current mode
			$mode = $result['message']['SERVER_INFO']['mapMode'];
			$this->mode_map[$mode] = $this->checkOffset($mode, $this->mode_map);
			$result['message']['SERVER_INFO']['mapMode'] = $this->mode_map[$mode];
			
			//set correct names of maps IN ROTATION
			//side note: I could actually do a method (like in humanFriendlySmallPlayer), so I wouldn't have to copy this foreach 1 million times
			//but dang it.
			//Je n'ai pas d'envie.
			$i = 0;
			foreach ($result['message']['SERVER_INFO']['maps']['maps'] as &$row) {
				$name =  $row['map'];
				$this->map_map[$name] = $this->checkOffset($name, $this->map_map);
				
				$maps[$i] = $this->map_map[$name]; //to set next map later
				$row['map'] = $this->map_map[$name];
				$i++; //it's important var, don't delete this!
			}
			unset($row); //unset to break the reference caused by &
			
			//set next map
			$nextIndex = $result['message']['SERVER_INFO']['maps']['nextMapIndex'];
			$this->map_map[$nextIndex] = $this->checkOffset($nextIndex, $this->map_map);
			$result['message']['SERVER_INFO']['maps']['nextMapIndex'] = $maps[$nextIndex];
			//set correct names of modes in rotation
			foreach ($result['message']['SERVER_INFO']['maps']['maps'] as &$row) {
				$mode =  $row['mapMode'];
				$this->mode_map[$mode] = $this->checkOffset($mode, $this->mode_map);
				$row['mapMode'] = $this->mode_map[$mode];
			}
			unset($row);
			
			//set correct name of current expansion
			$dlc = $result['message']['SERVER_INFO']['gameExpansion'];
			$this->dlc_map[$dlc] = $this->checkOffset($dlc, $this->dlc_map);
			$result['message']['SERVER_INFO']['gameExpansion'] = $this->dlc_map[$dlc];
			
			//set correct name of PRESET
			$preset = $result['message']['SERVER_INFO']['preset'];
			$this->preset_map[$preset] = $this->checkOffset($preset, $this->preset_map);
			$result['message']['SERVER_INFO']['preset'] = $this->preset_map[$preset];
			
			//set names of expansions
			foreach( $result['message']['SERVER_INFO']['gameExpansions'] as &$row) {
				$dlc = $row;
				$row = $this->dlc_map[$row];
			}
			unset($row);
				
			//set name of region
			$region = $result['message']['SERVER_INFO']['region'];
			$this->region_map[$region] = $this->checkOffset($region, $this->region_map);
			$result['message']['SERVER_INFO']['region'] = $this->region_map[$region];
			
			return $result;
		}
		
		protected function humanFriendlyPlayer ($player_data, $big) {
			switch ($big) {
				
				//I've put partiular cases in special methods to keep it clear
				case true:
					$player_data = json_decode($player_data, true);
					return $player_data; //I won't support this huge shit with fucking 2000 lines
					break;
				case false:
					return $player_data = $this->humanFriendlySmallPlayer($player_data);
					break;
			}
		}
		
		//formats data for small chunk of player array
		protected function humanFriendlySmallPlayer($player_data) {
			//we have to decode our json			
			$player_data = json_decode($player_data, true);
			//format stuff...
			//set correct kits names in kitTimesInPercentage
			$player_data['data']['generalStats']['kitTimesInPercentage'] = $this->changeKeys($player_data['data']['generalStats']['kitTimesInPercentage'], $this->kit_map);
			
			//the same fo kitTimes
			$player_data['data']['generalStats']['kitTimes'] = $this->changeKeys($player_data['data']['generalStats']['kitTimes'], $this->kit_map);

			//and again for kitScores
			$player_data['data']['generalStats']['kitScores'] = $this->changeKeys($player_data['data']['generalStats']['kitScores'], $this->kit_map);
			
			//and again for serviceStarProgess
			$player_data['data']['generalStats']['serviceStarsProgress'] = $this->changeKeys($player_data['data']['generalStats']['serviceStarsProgress'], $this->kit_map);
			
			//again, but now for gamemods - gameModesScore	
			$player_data['data']['generalStats']['serviceStars'] = $this->changeKeys($player_data['data']['generalStats']['serviceStars'], $this->mode_map);
			
			$player_data['data']['generalStats']['gameModesScore'] = $this->changeKeys($player_data['data']['generalStats']['gameModesScore'], $this->mode_map);
						
			return $player_data;
		}
		
		protected function humanFriendlyReport($report) {
			//do stuff
			$report = json_decode($report, true);
			
			//create a players array, will use it later when changing ids to usernames
			foreach ($report['players'] as $row) {
				$id = $row['persona']['personaId'];
				$username  =  $row['persona']['user']['username'];
				$players[$id] = $username;
			}
			
			//set correct preset name
			$report['gameMode'] = $this->mode_map[$report['gameMode']];
			
			//set correct map name
			$report['gameServer']['map'] = $this->map_map[$report['gameServer']['map']];
			//Loops time!
			
			
			//set correct usernames for players from both teams
			for ($a = 1; $a <= 2; $a++) {
				foreach ($report['teams'][$a]['players'] as &$row) {
					$id = $row;
					$row = $players[$id];
				}
				unset($row);
			
			}

			//set correct names of commander for both teams
			
			//set correct names for squads for both teams
			$size = count($report['teams']['1']['squads']);
			
			//will be done 2 times, as there can only be 2 teams
			for ($a = 1; $a<=2; $a++) {
				for ($i = 1; $i <= $size; $i++) {
					$report['teams'][$a]['squads'][$i] = $this->setSquadNames($report['teams'][$a]['squads'][$i], $players);
				}
				$size = count($report['teams']['2']['squads']);
				
			}

			//end of spaghetti!
			return 	$report;
		}
		//access by passing full array and full map names:
		//example: $this->changeKeys($player_data["data"]["generalStats"]["kitTimes"], $this->kit_map);
		protected function changeKeys($path, $map) {
			$size = count($path);
			$keys = array_keys($path);
			for ($i=0; $i<$size;  $i++) {
				 $old_key = $keys[$i];
				 
				 //some error handling
				 if (isset($map[$old_key]) and !empty($map[$old_key])) {
				 $new_key= $map[$old_key];
				 } else {
				 $new_key = 'blAPI: Unknown';
				 }
				 
				 $keys[$i] = $new_key;
			}
			return array_combine($keys, $path);
		}
						
		protected function returnData($data, $json, $is_now_json) {
			if ($json and !$is_now_json) {
				return json_encode($data);
			}
			elseif ($json and $is_now_json) {
				return $data;
			}
			elseif (!$json and $is_now_json) {
				return json_decode($data, true);
			}
			elseif (!$json and !$is_now_json) {
				return $data;
			}
			
		}
		
		protected function checkOffset($offset, $map) {
			if (isset($map[$offset]) ) {
				return $map[$offset];
			}
			else {
				return "blAPI: Unknown";
			}
		}
				
		protected function setSquadNames($array, $players) {
			foreach ($array as &$row) {
				$id = $row;
				$row = $players[$id];
			}
			unset($row);
			return $array;
		}
			}
	