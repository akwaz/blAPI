<?php

	class blAPI {
		
		protected  $settings_map = array();
		
		//stores data about maps (levels) in bf4 - every map is called in a different way than in-game
		//example - real name: Golmud Railway, battlelog name: MP_Journey
		protected  $map_map;
		
		//preset map
		protected  $preset_map;
			
		//gamemodes map
		protected  $mode_map;
				
		//DLCs map
		protected  $dlc_map;
				
		//region map
		protected  $region_map;		
		
		//config map
		protected  $config_map;
				
		public function __construct($game) {
			$this->loadCfg($game);
		}	
		
		/* START OF PUBLIC API CALLS */
		
		
		/* public function getServerData($server_url, $json = false, $human_friendly = false)
		 * 
		 * Purpose: Get data about particular server. 
		 * Args: 
		 * 		*$server_url* - URL of the server (*OBLIGATORY)
		 *		$json - false by default, if you want recieve a JSON object, set this to TRUE (optional)
		 * 		$human_friendly - false by default. If true, output data will be converted into ready-to-use vals
		 * 						  like real name of map, correct preset name (optional)
		 * Returns: array or JSON object
		 * Throws: nothing
		 */
		public function getServerData($server_url, $json = false, $human_friendly = false) {
			$url = $server_url . '?json=1';
			$result = $this->query($url);
	
			if ($human_friendly) {
				
				//pass $result to special method
				$result = $this->humanFriendlyServer($result);	
				
				//return json object or php array
				// if json = true, we have to encode it again, cause we've already change JSON to php array in humanFriendlyServer
				if ($json) {
					return json_encode($result);
				}
				else {
					return $result;
				}
			}
			//if user doesn't want human-friendly response...
			else {
				//if json = true, return $result without decoding
				if ($json) {return $result;}
				//otherwise decode JSON objcet to assoc array
				else {return json_decode($result, true);}
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
		public function getBattleReport($battle_report) {
			
		}
		
		
		/* public function getPlayerData ($player)
		 * 
		 * Purpose: Get data about one player
		 * Args:
		 * 		*$player_id* - player id, number can by found between "stats" and platform strings, e.g 
		 * 					   soldier/ArekTheMLGPro/stats/ ->this-> 887022216/pc/ (*OBLIGATORY)
		 * Returns: array or JSON object
		 * Throws: nothing
		 */
		public function getPlayerData ($player_id) {
			//big big data
			/*http://battlelog.battlefield.com/bf4/warsawoverviewpopulate/ID/1/
			
			//small data
			/*http://battlelog.battlefield.com/bf4/warsawdetailedstatspopulate/ID/1/*/
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
				$this->map_map = require_once "cfg/bf4/bf4_map_map.php";
				$this->dlc_map = require_once 'cfg/bf4/bf4_dlc_map.php';
				$this->mode_map = require_once "classes/cfg/bf4/bf4_mode_map.php";
				$this->config_map = require_once 'classes/cfg/bf4/bf4_config_map.php';
			}
			//elseif ($smth) {}
		}
		
		protected function humanFriendlyServer ($result) {
			//we have to decode json here anyway, cause we want to change some values to more user-friendly
			$result = json_decode($result, true);
		
			//set correct name of CURRENT MAP
			$map = $result['message']['SERVER_INFO']['map'];
			$result['message']['SERVER_INFO']['map'] = $this->map_map[$map];
		
			//set name of current mode
			$mode = $result['message']['SERVER_INFO']['mapMode'];
			$result['message']['SERVER_INFO']['mapMode'] = $this->mode_map[$mode];
			
			//set correct names of maps IN ROTATION
			$i = 0;
			foreach ($result['message']['SERVER_INFO']['maps']['maps'] as &$row) {
				$name =  $row['map'];
				$maps[$i] = $this->map_map[$name]; //to set next map later
				$row['map'] = $this->map_map[$name];
				$i++;
			}
			unset($row); //unset to break the reference cause by &
			
			//set next map
			$nextIndex = $result['message']['SERVER_INFO']['maps']['nextMapIndex'];
			$result['message']['SERVER_INFO']['maps']['nextMapIndex'] = $maps[$nextIndex];
			//set correct names of modes in rotation
			foreach ($result['message']['SERVER_INFO']['maps']['maps'] as &$row) {
				$mode =  $row['mapMode'];
				$row['mapMode'] = $this->mode_map[$mode];
			}
			unset($row);
			
			//set correct name of current expansion
			$dlc = $result['message']['SERVER_INFO']['gameExpansion'];
			$result['message']['SERVER_INFO']['gameExpansion'] = $this->dlc_map[$dlc];
			
			//set correct name of PRESET
			$preset = $result['message']['SERVER_INFO']['preset'];
			$result['message']['SERVER_INFO']['preset'] = $this->preset_map[$preset];
			
			//set names of expansions
			foreach( $result['message']['SERVER_INFO']['gameExpansions'] as &$row) {
				$dlc = $row;
				$row = $this->dlc_map[$row];
			}
			unset($row);
				
			//set name of region
			$region = $result['message']['SERVER_INFO']['region'];
			$result['message']['SERVER_INFO']['region'] = $this->region_map[$region];
			
			return $result;
		}
	}