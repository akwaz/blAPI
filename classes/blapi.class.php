<?php
	class blAPI {
		
		//stores paths to all maps
		private $file_map =  array (
				//universal
				"preset_map" => "classes/cfg/preset_map.php",
				"region_map" => "classes/cfg/region_map.php",
				//bf4
				"bf4_kit_map" => "classes/cfg/bf4/bf4_kit_map.php",
				"bf4_map_map" => "classes/cfg/bf4/bf4_map_map.php",
				"bf4_dlc_map" => "classes/cfg/bf4/bf4_dlc_map.php",
				"bf4_mode_map" => "classes/cfg/bf4/bf4_mode_map.php",
				"bf4_config_map" => "classes/cfg/bf4/bf4_config_map.php",
				//bfh
				"bfh_kit_map" => "classes/cfg/bfh/bfh_kit_map.php",
				"bfh_map_map" => "classes/cfg/bfh/bfh_map_map.php",
				"bfh_dlc_map" => "classes/cfg/bfh/bfh_dlc_map.php",
				"bfh_mode_map" => "classes/cfg/bfh/bfh_mode_map.php",
				"bfh_config_map" => "classes/cfg/bfh/bfh_config_map.php",
		
		);
		
		//stores data about maps (levels)
		private $map_map;
		
		//preset map
		private $preset_map;
			
		//gamemodes map
		private $mode_map;
				
		//DLCs map
		private  $dlc_map;
		
		//kit map
		private $kit_map;
				
		//region map
		private $region_map;		
		
		//config map
		private $config_map;
				
		//stores data about current game - used to catch exceptions
		private $game;
		
		//stores boolean, decides about logging events to a text file
		private $logs;
		
		//used to load correct amount of maps (light version or no)
		private $blapi_light;
		
		private $blapi_logs = array(
				"xd" => "zd"	
		);
		public function __construct($game, $blapi_light = false, $logs = false) {
			if ($game != 'unknown'){ 
				try {
					$this->loadCfg($game, $blapi_light);
					$this->game = $game;
					$this->logs = $logs;
					$this->blapi_light = $blapi_light;
				} 
				catch (Exception $e) {
					$this->error($e);
					exit();
				}
			}
			$this->log("Instance of blAPI class created with args: \$game: " . $game . " , \$blapi_light: " . (int) $blapi_light 
					. ", \$logs: " . (int) $logs);
		}
		
		public function __destruct() {
			$this->log("Instance of blAPI class was destroyed.");
		}
		
		/* START OF PUBLIC METHODS */
		
		//allows user to set/change the game after constructing the object
		public function setGame($game, $light = false) {
			try {
				$this->map_map = array();
				$this->mode_map= array();
				$this->dlc_map= array();
				$this->kit_map= array();
				$this-> config_map= array();
				$this->blapi_light = $light;
				$this->loadCfg($game, $this->blapi_light);
			}
			catch (Exception $e) {
				$this->error($e);
				exit();
			}
			$this->game = $game;
			$this->log("setGame(): game set to " . $game . ", blAPI light: " . (int) $this->blapi_light);
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
		 */
		public function getServerData($server_url, $json = false, $human_friendly = false) {
			$this->log("getServerData() method called with args: \$server_url: " . $server_url . " \$json: " . (int) $json 
					. " \$human_friendly: " . (int) $human_friendly);
			try {
				if (!strpos($server_url, $this->game)) {
					throw new Exception ("getServerData(): URL passed to method comes from incorrect game. Current game: " . $this->game 
						. ". To change game, call setGame() method.");
				}
			} catch (Exception $e) {
				$this->error($e);
				exit();
			}
			$url = $server_url . '?json=1';
			
			try {
				$server_data = $this->query($url);
			} catch (Exception $e) {
				$this->error($e);
				exit();
			}
			
			$this->log("getServerData(): recieved data from Battlelog.");
			if ($human_friendly) {
				try {
					if ($this->blapi_light) throw new Exception("blAPI is running in light mode. You can't use human-friendly feature.");
				} catch (Exception $e) {
					$this->error($e);
					exit();
				}
				$this->log("getServerData(): started human-friendly formatting");
				//pass $result to special method
				$server_data = $this->humanFriendlyServer($server_data);	
				
				//return json object or php array
				$server_data = $this->returnData($server_data, $json, false);
				$this->log("getServerData(): human-friendly data returned.");
				return $server_data;
			}
			//if user doesn't want human-friendly response...
			else {
				$server_data = $this->returnData($server_data, $json, true);
				$this->log("getServerData(): raw data returned.");
				return $server_data;
			}
		}
		
		
		/* public function getBattleReport($battle_report)
		 * 
		 * Purpose: Get data about match from battlereport
		 * Args:
		 * 		*$report_id* - id from Battlelog (it's the FIRST number in battlereport URL, second is our player id btw) (*OBLIGATORY)
		 * 		$json - false by default, if true method will return JSON object (optional)
		 * 		$human_friendly - false by default. If true, output data will be converted into ready-to-use vals
		 * 						  like real name of map, correct preset name (optional)
		 *
		 * Returns: array or JSON object
		 */
		public function getBattleReport($report_id, $json = false, $human_friendly = false) {
			$this->log("getBattleReport() method called with args: \$report_id: " . $report_id . " \$json: " . (int) $json 
					. " \$human_friendly: " . (int) $human_friendly);
			$url = $this->config_map['report_url_start'] . $report_id . $this->config_map['report_url_end'];
			
			try {
				$report = $this->query($url);
			}
			catch (Exception $e) {
					$this->error($e);
					exit();
				}
				
					
			$this->log("getBattleReport(): recieved data from Battelog");
			if ($human_friendly) {
				if ($this->blapi_light) throw new Exception("blAPI is running in light mode. You can't use human-friendly feature.");
				
				$report = $this->humanFriendlyReport($report);
				$report = $this->returnData($report, $json, false);
				$this->log("getBattleReport(): human-friendly data returned.");
				return $report;
			}else {
				$report = $this->returnData($report, $json, true);
				$this->log("getBattleReport(): raw data returned");
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
		 */
		public function getPlayerData ($player_id, $json = false, $human_friendly = false, $big = false) {
			$this->log("getPlayerData method called with args: \$player_id: " . $player_id 
					. " \$json: " . (int) $json . " \$human_friendly: " . (int) $human_friendly . " \$big: " . (int) $big);
			
			if ($big) {
				$url = $this->config_map['big_user_url_start'] . $player_id . $this->config_map['user_url_end'];
			}
			else {
				$url = $this->config_map['small_user_url_start'] . $player_id . $this->config_map['user_url_end'];
			}
			
			try {
				$player_data = $this->query($url);
			}
			catch (Exception $e) {
				$this->error($e);
				exit();
			}
			
			$this->log("getPlayerData: recieved data from Battlelog");
			
			if ($human_friendly) { //if user wants user-friendly output, we will send this var to the special method
				try {
					if ($this->blapi_light) throw new Exception("blAPI is running in light mode. You can't use human-friendly feature.");
				} catch (Exception $e) {
					$this->error($e);
					exit();
				}
				$player_data = $this->humanFriendlyPlayer($player_data, $big);
				$player_data = $this->returnData($player_data, $json, false);
				$this->log("getPlayerData(): returned human-friendly data");
				return $player_data;
			}
			else { //if he doesn't want this, we can return the data...
				$player_data = $this->returnData($player_data, $json, true);
				$this->log("getPlayerData(): returned raw data");
				return $player_data;
			}
		}
		
		/* END OF PUBLIC METHODS */
		
		private function query($url) {
			//will use either cURL or simply file_get_contents
			//$url var must me ready to use when passed to this method
			$result = @file_get_contents($url);
			if (!$result) {
				throw new Exception("query(): file_get_contents() failed with message: " . PHP_EOL . $php_errormsg);
			}
			else {
				return $result;
			}
		}
	
		/* private function loadCfg ($game, $blapi_light)
		 *
		 * Purpose: Set config files
		 * Args:
		 * 		*$game* - string, game shorthand, supported: 'bf4', 'bfh', if it is possible, I will add Battlefront when it's out!
		 * 
		 * 		$blapi_light - If true, method will load only config maps.
		 *	
		 * Returns: nothing
		 * Throws: Exception("File does not exist");
		 */
		
		//stores paths to all files
		
		private function loadCfg($game, $blapi_light) {
			/*now supports blapi light - this version requires only config maps, so if you won't use $human_friendly feature,
			 * you can delete all of files and keep only (game)_config_map.php - it's crucial, class won't run without these files!
			 */
			$this->checkFiles($game, $blapi_light);
			if($blapi_light) {
				//load only config maps
				switch ($game) {
					case 'bf4':
						$this->config_map = require_once $this->file_map['bf4_config_map'];
						break;
					case "bfh":
						$this->config_map = require_once $this->file_map['bfh_config_map'];
						break;	
				}
			}
			else {
				if (empty($this->region_map)){
					//load universal assets - keep it in if to stay away from setting these second time if setGame is called
					$this->region_map = require_once $this->file_map['region_map'];
					$this->preset_map = require_once $this->file_map['preset_map'];
				}
				if ($game == 'bf4') {
				
					//load game-specify assets 
					$this->kit_map = require_once $this->file_map['bf4_kit_map'];
					$this->map_map = require_once $this->file_map['bf4_map_map'];
					$this->dlc_map = require_once $this->file_map['bf4_dlc_map'];
					$this->mode_map = require_once $this->file_map['bf4_mode_map'];
					$this->config_map = require_once $this->file_map['bf4_config_map'];
				}
				elseif ($game == 'bfh') {
					$this->kit_map = require_once $this->file_map['bfh_kit_map'];
					$this->map_map = require_once $this->file_map['bfh_map_map'];
					$this->dlc_map = require_once $this->file_map['bfh_dlc_map'];
					$this->mode_map = require_once $this->file_map['bfh_mode_map'];
					$this->config_map = require_once $this->file_map['bfh_config_map'];
								
				}
			}
		}
		
		private function humanFriendlyServer ($result) {
			//we have to decode json here anyway, cause we want to change some values to more user-friendly
			$result = json_decode($result, true);
		
			//would you like some spaghetti?
			//set correct name of CURRENT MAP
			$map = $result['message']['SERVER_INFO']['map'];
			$result['message']['SERVER_INFO']['map'] = $this->getIndex($map, $this->map_map);
		
			//set name of current mode
			$mode = $result['message']['SERVER_INFO']['mapMode'];
			//$this->mode_map[$mode] = $this->checkIndex($mode, $this->mode_map);
			$result['message']['SERVER_INFO']['mapMode'] = $this->getIndex($mode, $this->mode_map);
			
			//set correct names of maps IN ROTATION
			//side note: I could actually do a method (like in humanFriendlySmallPlayer), so I wouldn't have to copy this foreach 1 million times
			//but dang it.
			//Je n'ai pas d'envie.
			$i = 0;
			foreach ($result['message']['SERVER_INFO']['maps']['maps'] as &$row) {
				$name =  $row['map'];
				//$this->map_map[$name] = $this->checkIndex($name, $this->map_map);
				
				$maps[$i] = $this->getIndex($name, $this->map_map); //to set next map later
				$row['map'] = $this->getIndex($name, $this->map_map);
				$i++; //it's important var, don't delete this!
			}
			unset($row); //unset to break the reference caused by & - encouraged by php manual
			
			//set next map
			$nextIndex = $result['message']['SERVER_INFO']['maps']['nextMapIndex'];
			$this->map_map[$nextIndex] = $this->getIndex($nextIndex, $this->map_map);
			$result['message']['SERVER_INFO']['maps']['nextMapIndex'] = $maps[$nextIndex];
			//set correct names of modes in rotation
			foreach ($result['message']['SERVER_INFO']['maps']['maps'] as &$row) {
				$mode =  $row['mapMode'];
				$row['mapMode'] = $this->getIndex($mode, $this->mode_map);
			}
			unset($row);
			
			//set correct name of current expansion
			$dlc = $result['message']['SERVER_INFO']['gameExpansion'];
			echo $dlc . " - ";
			$result['message']['SERVER_INFO']['gameExpansion'] = $this->getIndex($dlc, $this->dlc_map);
			
			//set correct name of PRESET
			$preset = $result['message']['SERVER_INFO']['preset'];
			$result['message']['SERVER_INFO']['preset'] = $this->getIndex($preset, $this->preset_map);
			
			//set names of expansions
			foreach( $result['message']['SERVER_INFO']['gameExpansions'] as &$row) {
				//$dlc = $row; TODO additional
				echo $row . " - ";
				$meme = $row;
				$row = $this->getIndex($meme, $this->dlc_map);
			}
			unset($row);
				
			//set name of region
			$region = $result['message']['SERVER_INFO']['region'];
			$result['message']['SERVER_INFO']['region'] = $this->getIndex($region, $this->region_map);
			
			return $result;
		}
		
		private function humanFriendlyPlayer ($player_data, $big) {
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
		private function humanFriendlySmallPlayer($player_data) {
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
		
		private function humanFriendlyReport($report) {
			$report = json_decode($report, true);
			
			//create a players array, will use it later when changing ids to usernames
			foreach ($report['players'] as $row) {
				$id = $row['persona']['personaId'];
				$username  =  $row['persona']['user']['username'];
				$players[$id] = $username;
			}
			
			//set correct preset name
			$report['gameMode'] = $this->getIndex($report['gameMode'], $this->mode_map);
			
			//set correct map name
			$report['gameServer']['map'] = $this->getIndex($report['gameServer']['map'], $this->map_map);
			
			//Loops time!
					
			//set correct usernames for players from both teams
			for ($a = 1; $a <= 2; $a++) {
				foreach ($report['teams'][$a]['players'] as &$row) {
					$id = $row;
					$row = $players[$id];
				}
				unset($row);
			
			}

			//set correct names of commander for both teams - TODO
			
			//set correct names for squads for both teams
			$size = count($report['teams']['1']['squads']);
			
			//will be done 2 times, as there can only be 2 teams
			for ($a = 1; $a<=2; $a++) {
				for ($i = 1; $size > 0; $i++) { //squads aren't obligatory numbered 1,2,3 - they can be 1,2,5 - that's why we're iterating as long as there is
					//a squad left
					if (isset($report['teams'][$a]['squads'][$i])) {
						$report['teams'][$a]['squads'][$i] = $this->setSquadNames($report['teams'][$a]['squads'][$i], $players);
						$size--;
					}
				}
				$size = count($report['teams']['2']['squads']);
				
			}

			//end of spaghetti!
			return 	$report;
		}
		//access by passing full array and full map names:
		//example: $this->changeKeys($player_data["data"]["generalStats"]["kitTimes"], $this->kit_map);
		private function changeKeys($path, $map) {
			$size = count($path);
			$keys = array_keys($path);
			for ($i=0; $i<$size;  $i++) {
				 $old_key = $keys[$i];
				 
				 //some error handling
				 if (isset($map[$old_key]) and !empty($map[$old_key])) {
				 	$new_key= $map[$old_key];
				 } else {
				 	//setting new_key to something that won't break all the stuff 
					 $new_key = 'blAPI: Unknown';
				 }
				 
				 $keys[$i] = $new_key;
			}
			return array_combine($keys, $path);
		}
					
		//nice small method that solves all spaghetti problems with data returning.
		private function returnData($data, $json, $is_now_json) {
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
		
		private function getIndex ($index, $map) {
			if (isset($map[strtolower($index)])) {
				return $map[strtolower($index)];
			}
			else {
				//returns special value to avoid breaking the whole flow and causing a III World War
				return "blAPI: Unknown";
			}
		}
				
		private function setSquadNames($array, $players) {
			foreach ($array as &$row) {
				$id = $row;
				$row = $players[$id];
			}
			unset($row);
			return $array;
		}
		
		//spaghetti!
		private function checkFiles($game, $blapi_light) {
			
			//spaghetti!!!
			if (!$blapi_light) {
				if ($game == "bf4") {
					
					//no config map, cause it will be checked outside of if(!$blapi_light)
					if (!file_exists($this->file_map['preset_map'])) {
						throw new Exception("File " . $this->file_map['preset_map'] . " does not exist.");
						exit();
					}
					if (!file_exists($this->file_map['region_map'])) {
						throw new Exception("File " . $this->file_map['region_map'] . " does not exist.");
						exit();
					}
					if (!file_exists($this->file_map['bf4_kit_map'])) {
						throw new Exception("File " . $this->file_map['bf4_kit_map'] . " does not exist.");
						exit();
					}
					if (!file_exists($this->file_map['bf4_map_map'])) {
						throw new Exception("File " . $this->file_map['bf4_map_map'] . " does not exist.");
						exit();
					}
				
					if (!file_exists($this->file_map['bf4_dlc_map'])) {
						throw new Exception("File " . $this->file_map['bf4_dlc_map'] . " does not exist.");
						exit();
					}
					if (!file_exists($this->file_map['bf4_mode_map'])) {
						throw new Exception("File " . $this->file_map['bf4_mode_map'] . " does not exist.");
						exit();
					}
				}
				
				elseif ($game == "bfh") {
					if (!file_exists($this->file_map['preset_map'])) {
						throw new Exception("File " . $this->file_map['preset_map'] . " does not exist.");
						exit();
					}
					if (!file_exists($this->file_map['region_map'])) {
						throw new Exception("File " . $this->file_map['region_map'] . " does not exist.");
						exit();
					}
					if (!file_exists($this->file_map['bfh_kit_map'])) {
						throw new Exception("File " . $this->file_map['bfh_kit_map'] . " does not exist.");
						exit();
					}
					if (!file_exists($this->file_map['bfh_map_map'])) {
						throw new Exception("File " . $this->file_map['bfh_map_map'] . " does not exist.");
						exit();
					}
					
					if (!file_exists($this->file_map['bfh_dlc_map'])) {
						throw new Exception("File " . $this->file_map['bfh_dlc_map'] . " does not exist.");
						exit();
					}
					if (!file_exists($this->file_map['bfh_mode_map'])) {
						throw new Exception("File " . $this->file_map['bfh_mode_map'] . " does not exist.");
						exit();
					}
						
				}
				
			}
			
			/*side note: above we're loading all of maps that are used in "full" blAPI. Below we load only config_map which is the only
			 * thing used in light version of blAPI. It has that flow cause we have to load config_map always, no matter if we're using
			 * light version or full version. This if below is outside of big if(!$blapi_light)
			 */
			
			if ($game == 'bf4') {
				
				if (!file_exists($this->file_map['bf4_config_map'])) {
					throw new Exception("File " . $this->file_map['bf4_config_map'] . " does not exist.");
					exit();
				}
			}
			
			elseif ($game == "bfh") {
				if (!file_exists($this->file_map['bfh_config_map'])) {
					throw new Exception("File " . $this->file_map['bfh_config_map'] . " does not exist.");
					exit();
				}
				
			}
			elseif ($game !== "bf4" && $game !== 'bfh') {
				throw new Exception("Unsupported game passed");
				exit();
			}
				
		}
		
		private function error(Exception $e) {
			echo "<span style='color:red'>blAPI: an error occured: </span>" . $e->getMessage();
			$this->log("Exception: " . $e->getMessage());
		}
		
		private function log($msg) {
			if ($this->logs) {
				$file = fopen("blapi_log.txt", "a+");
				fwrite($file, date("H:i:s d-m-o") . " " . $msg . PHP_EOL);
				fclose($file);
			}
		}
	}
	