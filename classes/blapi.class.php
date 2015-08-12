<?php

	class blAPI {
		
		//stores data about maps (levels) in bf4 - every map is called in a different way than in-game
		//example - real name: Golmud Railway, battlelog name: MP_Journey
		protected $map_map = array(
			//vanilla
			"MP_Tremors" => "Dawnbreaker",
			"MP_Flooded" => "Flood Zone",
			"MP_Journey" => "Golmud Railway",
			"MP_Resort" => "Hainan Resort",
			"MP_Damage" => "Lancang Dam",
			"MP_Prison" => "Operation Locker",
			"MP_Naval" => "Paracel Storm",
			"MP_TheDish" => "Rouge Transmisson",
			"MP_Siege" => "Siege of Shanghai",
			"MP_Abandoned" => "Zavod 311",
				
			//China Rising
			"XP1_001" => "Silk Road",
			"XP1_002" => "Altai Range",
			"XP1_003" => "Guilin Peaks",
			"XP1_004" => "Dragon Pass",
				
			//Second Assault
			"XP0_Caspian" => "Caspain Border",
			"XP0_Oman" => "Gulf of Oman",
			"XP0_Firestorm" => "Operation Firestorm",
			"XP0_Metro" => "Operation Metro",
				
			//Naval Strike
			"XP2_001" => "Lost Islands",
			"XP2_002" => "Nansha Strike",
			"XP2_003" => "Wave Breaker",
			"XP2_004" => "Operation Mortar",
				
			//Dragon's Teeth
			"XP3_UrbanGdn" => "Lumphini Garden",
			"XP3_MarketPl" => "Pearl Market",
			"XP3_Prpganda" => "Propaganda",
			"XP3_WtrFront" => "Sunken Dragon",
				
			//Final Stand
			"XP4_WlkrFtry" => "Giants of Karelia",
			"XP4_SubBase" => "Hammerhead",
			"XP4_Titan" => "Hangar 21",
			"XP4_Arctic" => "Operation Whiteout"
		);

		//preset map
		protected $preset_map = array(
				"1" => "Normal",
				"2" => "Hardcore",			
				"4" => "Infantry Only",
				"8" => "Custom",
				"32" => "Classic"	
		);
		
		//gamemodes map
		protected $mode_map = array(
				"8388608" => "Air Superiority",
				"524288" => "Capture the Flag",
				"134217728" => "Carrier Assault",
				"67108864" => "Carrier Assault Large",
				"34359738368" => "Chain Link",
				"1" => "Conquest",
				"64" => "Conquest Large",
				"16777216" => "Defuse",
				"1024" => "Domination",
				"512" => "Gun Master",
				"2097152" => "Obliteration",
				"2" => "Rush",
				"8" => "Squad Deathmatch",
				"137438953472" => "Squad Obliteration",
				"32" => "Team Deathmatch",				
		);
		
		//DLCs map
		protected $dlc_map = array (
				"1048576" => "China Rising",
				"524288" => "Second Assault",
				"2097152" => "Naval Strike",
				"4194304" => "Dragon's Teeth",
				"8388608" => "Final Stand"
		);
		
		//region map
		protected $region_map = array (
				"1" => "North America",
				"2" => "South America",
				"4" => "Antarctica",
				"8" => "Africa",
				"16" => "Europe",
				"32" => "Asia",
				"64" => "Oceania"
		);
		
		
		
		//config map
		protected $config_map = [
				"game" => "bf4",
				"base_url" => "http://battlelog.battlefield.com/bf4/",
		];
		
		public function __contruct() {
			//nothing to do here			
		}
		
		/* START OF PUBLIC API CALLS */
		
		
		/* public function getServerData($server_url, $json = false, $human_friendly = false)
		 * 
		 * Purpose: Get data about particular server. 
		 * Args: 
		 * 		$server_url* - URL of the server (*OBLIGATORY)
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
				//we have to decode json here anyway, cause we want to change some values to more user-friendly
				$result = json_decode($result, true);
				
				//set correct name of CURRENT MAP
				$map = $result['message']['SERVER_INFO']['map'];
				$result['message']['SERVER_INFO']['map'] = $this->map_map[$map];
				
				
				//set correct names of maps IN ROTATION
				foreach ($result['message']['SERVER_INFO']['maps']['maps'] as $row) {
					$name =  $row['map'];
					$row['map'] = $this->map_map[$name];
				}
				
				//set correct name of PRESET
				$preset = $result['message']['SERVER_INFO']['preset'];
				$result['message']['SERVER_INFO']['preset'] = $this->preset_map[$preset];
								
				
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
		 * Purpose:
		 * Args:
		 * 
		 * Returns:
		 * Throws:
		 */
		public function getBattleReport($battle_report) {
			
		}
		
		
		/* public function getPlayerData ($player)
		 * 
		 * Purpose:
		 * Args:
		 * 
		 * Returns:
		 * Throws:
		 */
		public function getPlayerData ($player) {
			//big big data
			http://battlelog.battlefield.com/bf4/warsawoverviewpopulate/ID/1/
			
			//small data
			http://battlelog.battlefield.com/bf4/warsawdetailedstatspopulate/ID/1/
		}
		
		/* END OF PUBLIC API CALLS */
		
		protected function query($url) {
			//will use either cURL or simply file_get_contents			
		}
	
	}