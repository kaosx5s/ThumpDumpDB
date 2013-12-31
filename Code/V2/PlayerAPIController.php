<?php
class PlayerAPIController extends BaseController {

	private $class_name = "PlayerAPIController";

	/*
		================================================
		============== Utility Functions ===============
		================================================
		Contains:
			Player_Name_to_DBID($player_name)
			Player_Current_Frame($db_id)
			Player_Market_Listings($db_id)
			Player_Chassis_Loadout($db_id,$chassis_id)
	*/
	/*
		Function: Player Name to DBID
		Assumptions: none
		Action: Attempts to find a db_id for a given player name
		Inputs: player_name -> A urlencoded player name
		Outputs:
			Error: error returned if player is not found
			Success: returns the (int)db_id of the given player name
	*/
	public function Player_Name_to_DBID($player_name){
       //searching by name, null name = no buenos
        if($player_name == NULL) {
            return Response::json(array('status' => 'error', 'error' => 'No player name given.'));
        }else{
            $player_name = urldecode($player_name);
        }
        //Look up the player, latest to account for deletes with the same name
        $player_db_id = Player::where('name','=',$player_name)
            ->orderBy('created_at', 'DESC')
            ->first(array('db_id'));
		
        //if no player was found
        if(!$player_db_id) {
            return Response::json(array('status' => 'error', 'error' => 'Player not found.'));
        }else{
			$player_db_id->toJson();
			return $player_db_id;
		};
	}

	/*
		Function: Player Current Frame
		Assumptions: player exists in our database
		Action: Attempts to return the current chassis id
		Inputs: db_id -> A player db_id
		Outputs:
			Error: error returned if player is not found
			Success: returns the current chassis id
	*/
	public function Player_Current_Frame($db_id){
		$progress = PlayerAPIController::Get_Player_Progress($db_id,0);
		$progress = json_decode($progress,true);
		if(isset($progress['error'])){
			return Response::json(array('status' => 'error', 'error' => 'Player progress has errored.'));
		};
		return Response::json(array('status' => 'success', 'current_frame_id' => $progress['chassis_id']));
	}

	/*
		Function: Player Market Listings
		Assumptions: player exists in our database
		Action: Attempts to return a json array of currently placed market listings for a given player
		Inputs: db_id -> A player db_id
		Outputs:
			Error: error returned if player is not found
			Success: returns the current list of market listings for the given player
	*/
	public function Player_Market_Listings($db_id){
		$web_prefs = PlayerAPIController::Get_Player_Webprefs($db_id);
		if($web_prefs['show_market_listings'] != 1){
			return Response::json(array('status' => 'error', 'error' => 'Player is not allowing market listings to be shown.'));
		};
		//find out what this player has currently listed on the market
		if(Cache::has($this->class_name . "_" . $db_id . "_Market_Listings")){
			//pull from cache
			$cached_market_listings = Cache::get($this->class_name . "_" . $db_id . "_Market_Listings");
		}else{
			$market_listings = MarketListing::where('active','=','1')->where('character_guid','=',$player->db_id)->get(array('item_sdb_id','expires_at','price_cy','price_per_unit','rarity','quantity','title','icon','ff_id','category'));
			if(empty($market_listings)){
				//market listings is empty
				return Response::json(array('status' => 'error', 'error' => 'Player has no current market listings.'));
			};
			$market_stat_ids = array();
			$market_stat_cats = array();
			foreach($market_listings as $listing){
				$market_stat_ids[] = $listing->attributes['ff_id'];
				$market_stat_cats[] = $listing->attributes['category'];
			};
			if(count($market_stat_cats) < 1) {
				$stats = false;
			}
			$market_stat_cats_unique = array_unique($market_stat_cats);

			$item_stats_lookup = false;
			foreach ($market_stat_cats_unique as $statcat){
				switch ($statcat) {
					case 'AbilityModule':
						$stats = MarketStatAbilityModule::where_in('marketlisting_id', $market_stat_ids)->get();
						foreach ($stats as $stat){
							$temp = '<table>';
							$ustats = (array) unserialize($stat->stats);
							ksort($ustats);
							foreach ($ustats as $key => $value){
								$key = str_replace('_', ' ', $key);
								$key = ucwords($key);
								$value = number_format($value,2);
								$temp .= '<tr><td>'.htmlentities($key) . ': </td><td>' . htmlentities($value) . '</td></tr>';
							}
							$temp .= '</table>';
							$item_stats_lookup[(string) $stat->marketlisting_id] = $temp;
						}
						break;
					case 'CraftingComponent':
						$stats = MarketStatCraftingComponent::where_in('marketlisting_id', $market_stat_ids)->get();
						foreach ($stats as $stat){
							$temp = '';
							$key_lookup = array(
								'mass' => 'MASS',
								'power' => 'PWR',
								'cpu' => 'CPU'
							);
							ksort($stat->attributes);
							foreach ($stat->attributes as $key => $value){
								if($value > 0 && array_key_exists($key, $key_lookup)) {
									$temp .= htmlentities($key_lookup[$key]) . ': ' . htmlentities($value) . '<br>';
								}                        
							}
							$item_stats_lookup[(string) $stat->marketlisting_id] = $temp;
						}
						break;
					case 'Jumpjet':
						$stats = MarketStatJumpjet::where_in('marketlisting_id', $market_stat_ids)->get();
						foreach ($stats as $stat){
							$temp = '<table>';
							$key_lookup = array('id','updated_at','created_at','marketlisting_id');
							ksort($stat->attributes);
							foreach ($stat->attributes as $key => $value){
								if(!in_array($key, $key_lookup)) {
									$key = str_replace('_', ' ', $key);
									$key = ucwords($key);
									$value = number_format($value,2);
									$temp .= '<tr><td>'.htmlentities($key) . ': </td><td>' . htmlentities($value) . '</td></tr>';
								}
							}
							$temp .= '</table>';
							$item_stats_lookup[(string) $stat->marketlisting_id] = $temp;
						}
						break;
					case 'Plating':
						$stats = MarketStatPlating::where_in('marketlisting_id', $market_stat_ids)->get();
						foreach ($stats as $stat){
							$temp = '<table>';
							$key_lookup = array('id','updated_at','created_at','marketlisting_id');
							ksort($stat->attributes);
							foreach ($stat->attributes as $key => $value){
								if(!in_array($key, $key_lookup)) {
									$key = str_replace('_', ' ', $key);
									$key = ucwords($key);
									$value = number_format($value,2);
									$temp .= '<tr><td>'.htmlentities($key) . ': </td><td>' . htmlentities($value) . '</td></tr>';
								}
							}
							$temp .= '</table>';
							$item_stats_lookup[(string) $stat->marketlisting_id] = $temp;
						}
						break;
					case 'Resource':
						$stats = MarketStatResource::where_in('marketlisting_id', $market_stat_ids)->get();     
						foreach ($stats as $stat){
							$temp = '<table>';
							$key_lookup = array(
								1 => 'Thermal Resistance',
								2 => 'Conductivity',
								3 => 'Malleability',
								4 => 'Density',
								5 => 'Toughness',
								'quality' => 'Quality'
							);
							$temp_ar = array();
							foreach ($stat->attributes as $key => $value){
								if(array_key_exists($key, $key_lookup)) {
									$temp_ar[$key] = '<tr><td>'.htmlentities($key_lookup[$key]) . ': </td><td>' . htmlentities($value) . '</td></tr>';
								}
							}
								//because resources are special, we alpha order them numerically
								$temp .= $temp_ar[2];
								$temp .= $temp_ar[4];
								$temp .= $temp_ar[3];
								$temp .= $temp_ar[1];
								$temp .= $temp_ar[5];
								$temp .= $temp_ar['quality'];
							$temp .= '</table>';
							$item_stats_lookup[(string) $stat->marketlisting_id] = $temp;
						}
						break;
					case 'Servo':
						$stats = MarketStatServo::where_in('marketlisting_id', $market_stat_ids)->get();
						foreach ($stats as $stat){
							$temp = '<table>';
							$key_lookup = array('id','updated_at','created_at','marketlisting_id');
							ksort($stat->attributes);
							foreach ($stat->attributes as $key => $value)
							{
								if(!in_array($key, $key_lookup)) {
									$key = str_replace('_', ' ', $key);
									$key = ucwords($key);
									$value = number_format($value,2);
									$temp .= '<tr><td>'.htmlentities($key) . ': </td><td>' . htmlentities($value) . '</td></tr>';
								}
							}
							$temp .= '</table>';
							$item_stats_lookup[(string) $stat->marketlisting_id] = $temp;
						}
						break;
					case 'Weapon':
						$stats = MarketStatWeapon::where_in('marketlisting_id', $market_stat_ids)->get();
						foreach ($stats as $stat){
							$temp = '<table>';
							$key_lookup = array('id','updated_at','created_at','marketlisting_id');
							ksort($stat->attributes);
							foreach ($stat->attributes as $key => $value){
								if(!in_array($key, $key_lookup)) {
									$key = str_replace('_', ' ', $key);
									$key = ucwords($key);
									$value = number_format($value,2);
									$temp .= '<tr><td>'.htmlentities($key) . ': </td><td>' . htmlentities($value) . '</td></tr>';
								}
							}
							$temp .= '</table>';
							$item_stats_lookup[(string) $stat->marketlisting_id] = $temp;
						}
						break;
					default:
						break;
				}
			}
			//Cache
			$cached_market_listings = array();
			$cached_market_listings['data'] = $market_listings;
			$cached_market_listings['stats'] = $item_stats_lookup;
			Cache::put($this->class_name . "_" . $db_id . "_Market_Listings",$cached_market_listings,30);
		};
		$cached_market_listings['status'] = "success";
		return json_encode($cached_market_listings);
	}
	
	/*
		Function: Player Chassis Loadout
		Assumptions: player exists in our database
		Action: Attempts to return a json containing a single frames parsed information
		Inputs: db_id -> A player db_id
				chassis_id -> A chassis id to look for
		Outputs:
			Error: error returned if player is not found
			Success: returns the requested chassis id information in parsed format
	*/
	public function Player_Chassis_Loadout($db_id,$chassis_id){
		if(empty($chassis_id)){
			return Response::json(array('status' => 'error', 'error' => 'You must specify a chassis id.'));
		};
		
		$loadout = PlayerAPIController::Get_Player_Loadout($db_id);
		if(isset($loadout['error'])){
			return Response::json(array('status' => 'error', 'error' => 'Player loadout has errored.'));
		};
		//Maybe we should add a check against "acceptable" chassis id's?
		
		$loadout = json_decode($loadout);
		$loadout_data = array();
		for($i=0;$i<count($loadout);$i++){
			if(($loadout[$i]->Chassis_ID) == $chassis_id){
				//found our chassis data
				$loadout_data = $loadout[$i];
			};
		};
		$loadout = null;
		if(empty($loadout_data)){
			//player does not have this chassis_id
			return Response::json(array('status' => 'error', 'error' => 'Player does not have this battleframe unlocked.'));
		};
		//parse that battleframe data!
		//Check the cache to see if we currently have this frame on file (already parsed), if we do then pull from cache
		$cache_set_check = Cache::Get($this->class_name . "_" . $db_id . "_Loadout_" . $chassis_id . "_MD5");
		if($cache_set_check == md5(json_encode($loadout_data))){
			//load this chassis entirely from cache
			$cached_chassis_loadout = Cache::Get($this->class_name . "_" . $db_id . "_Loadout_" . $chassis_id);
		}else{
			//List of "secret" gear we don't want displayed
			$processor_framemods = array(75780,75782,75784,75786,75915,75916,75917,75918,75878,75691,76013,76014,76015,76016,76017);
			$player_gear_base_item = array();
			//Add all of our gear and abilities to the list
			for($i=0;$i<count($loadout_data->Gear);$i++){
				if(isset($loadout_data->Gear[$i]->info->item_sdb_id)){
					$player_gear_base_item[$i] = $loadout_data->Gear[$i]->info->item_sdb_id;
				};
			};
			//Now add weapons to that
			for($i=0;$i<count($loadout_data->Weapons);$i++){
				if(isset($loadout_data->Weapons[$i]->info->item_sdb_id)){
					$player_gear_base_item[] = $loadout_data->Weapons[$i]->info->item_sdb_id;
				};
			};
			$player_gear = array();
			if(empty($player_gear_base_item)){
				$player_gear_base_item = null;
			};

			$gear_counter = 0;
			$ability_counter = 0;
			$weapon_counter = 0;
			$player_item_abilities = array();
			$player_item_gear = array();
			$player_item_weapons = array();
			$ItemAPI = new ItemAPIController();
			for($i=0;$i<count($player_gear_base_item);$i++){
				$base_item_info = json_decode($ItemAPI->Get_Item_Information($player_gear_base_item[$i]), true);
				if($base_item_info['type'] == "ability_module"){
					//we has an ability
					$player_item_abilities[$ability_counter]['asset_path'] = $base_item_info['asset_path'];
					$player_item_abilities[$ability_counter]['name'] = $base_item_info['name'];
					$player_item_abilities[$ability_counter]['description'] = $base_item_info['description'];
					//Add check to see if durability is over 1,000
					if(isset($loadout_data->Gear[$i]->info->durability)){
						$player_item_abilities[$ability_counter]['durability'] = $loadout_data->Gear[$i]->info->durability;
					}else{
						$player_item_abilities[$ability_counter]['durability'] = null;
					};
					//Add check to see if quality is over 1,000
					$player_item_abilities[$ability_counter]['quality'] = $loadout_data->Gear[$i]->info->quality;
					$player_item_abilities[$ability_counter]['quality_color'] = $ItemAPI->Quality_Control($loadout_data->Gear[$i]->info->quality);
					if(isset($loadout_data->Gear[$i]->info->attribute_modifiers)){
						$player_item_abilities[$ability_counter]['attribute_modifiers'] = $loadout_data->Gear[$i]->info->attribute_modifiers;
					}else{
						$player_item_abilities[$ability_counter]['attribute_modifiers'] = null;
					};
					//Add check to see if allocated power is over 100
					$player_item_abilities[$ability_counter]['allocated_power'] = $loadout_data->Gear[$i]->allocated_power;
					$player_item_abilities[$ability_counter]['base_constraint_cpu'] = $base_item_info['cpu'];
					$player_item_abilities[$ability_counter]['base_constraint_mass'] = $base_item_info['mass'];
					$player_item_abilities[$ability_counter]['base_constraint_power'] = $base_item_info['power'];
					$ability_counter++;
				};
				if(($base_item_info['type'] == "frame_module") && !in_array($player_gear_base_item[$i], $processor_framemods)){
					//we has frame mod
					if($gear_counter < 9){
						$player_item_gear[$gear_counter]['asset_path'] = $base_item_info['asset_path'];
						$player_item_gear[$gear_counter]['name'] = $base_item_info['name'];
						$player_item_gear[$gear_counter]['description'] = $base_item_info['description'];
						//assume zero if null.
						if(isset($loadout_data->Gear[$i]->info->quality)){
							$player_item_gear[$gear_counter]['quality'] = $loadout_data->Gear[$i]->info->quality;
						}else{
							$player_item_gear[$gear_counter]['quality'] = 0;
						};
						$player_item_gear[$gear_counter]['quality_color'] = $ItemAPI->Quality_Control($loadout_data->Gear[$i]->info->quality);
						if(isset($loadout_data->Gear[$i]->info->durability)){
							$player_item_gear[$gear_counter]['durability'] = $loadout_data->Gear[$i]->info->durability;
						}else{
							$player_item_gear[$gear_counter]['durability'] = null;
						};
						if(isset($loadout_data->Gear[$i]->info->attribute_modifiers)){
							$player_item_gear[$gear_counter]['attribute_modifiers'] = $loadout_data->Gear[$i]->info->attribute_modifiers;
						}else{
							$player_item_gear[$gear_counter]['attribute_modifiers'] = null;
						};
						$player_item_gear[$gear_counter]['base_constraint_cpu'] = $base_item_info['cpu'];
						$player_item_gear[$gear_counter]['base_constraint_mass'] = $base_item_info['mass'];
						$player_item_gear[$gear_counter]['base_constraint_power'] = $base_item_info['power'];
						$gear_counter++;
					}else{
						Log::warn("Player ($player->db_id) has more than eight frame items in loadouts: " . $player_gear_base_item[$i]);
					};
				};
				if($base_item_info['type'] == "weapon"){
					//we has weapon
					if($weapon_counter < 2){
						$player_item_weapons[$weapon_counter]['asset_path'] = $base_item_info['asset_path'];
						$player_item_weapons[$weapon_counter]['name'] = $base_item_info['name'];
						$player_item_weapons[$weapon_counter]['description'] = $base_item_info['description'];
						if(isset($loadout_data->Weapons[$weapon_counter]->info->durability)){
							$player_item_weapons[$weapon_counter]['durability'] = $loadout_data->Weapons[$weapon_counter]->info->durability;
						}else{
							$player_item_weapons[$weapon_counter]['durability'] = null;
						};
						$player_item_weapons[$weapon_counter]['quality'] = $loadout_data->Weapons[$weapon_counter]->info->quality;
						$player_item_weapons[$weapon_counter]['quality_color'] = $ItemAPI->Quality_Control($loadout_data->Weapons[$weapon_counter]->info->quality);
						if(isset($loadout_data->Weapons[$weapon_counter]->info->attribute_modifiers)){
							$player_item_weapons[$weapon_counter]['attribute_modifiers'] = $loadout_data->Weapons[$weapon_counter]->info->attribute_modifiers;
						}else{
							$player_item_weapons[$weapon_counter]['attribute_modifiers'] = null;
						};
						$player_item_weapons[$weapon_counter]['ammoPerBurst'] = $base_item_info['ammoPerBurst'];
						$player_item_weapons[$weapon_counter]['clipSize'] = $base_item_info['clipSize'];
						$player_item_weapons[$weapon_counter]['damagePerRound'] = $base_item_info['damagePerRound'];
						$player_item_weapons[$weapon_counter]['damagePerSecond'] = $base_item_info['damagePerSecond'];
						$player_item_weapons[$weapon_counter]['healthPerRound'] = $base_item_info['healthPerRound'];
						$player_item_weapons[$weapon_counter]['maxAmmo'] = $base_item_info['maxAmmo'];
						$player_item_weapons[$weapon_counter]['range'] = $base_item_info['range'];
						$player_item_weapons[$weapon_counter]['reloadTime'] = $base_item_info['reloadTime'];
						$player_item_weapons[$weapon_counter]['roundsPerBurst'] = $base_item_info['roundsPerBurst'];
						$player_item_weapons[$weapon_counter]['roundsPerMinute'] = $base_item_info['roundsPerMinute'];
						$player_item_weapons[$weapon_counter]['splashRadius'] = $base_item_info['splashRadius'];
						$player_item_weapons[$weapon_counter]['spread'] = $base_item_info['spread'];
						$player_item_weapons[$weapon_counter]['base_constraint_cpu'] = $base_item_info['cpu'];
						$player_item_weapons[$weapon_counter]['base_constraint_mass'] = $base_item_info['mass'];
						$player_item_weapons[$weapon_counter]['base_constraint_power'] = $base_item_info['power'];
						$player_item_weapons[$weapon_counter]['allocated_power'] = $loadout_data->Weapons[$weapon_counter]->allocated_power;
						$weapon_counter++;
					}else{
						Log::warn("Player ($player->db_id) has more than two weapons in loadouts: " . $player_gear_base_item[$i]);
					};
				};
			};
			//cache the loadout md5
			Cache::forever($this->class_name . "_" . $db_id . "_Loadout_" . $chassis_id . "_MD5",md5(json_encode($loadout_data)));
			//cache the rest of the loadout to seperate files for easy loading
			$cached_chassis_loadout = array();
			$cached_chassis_loadout['abilities'] = $player_item_abilities;
			$cached_chassis_loadout['gear'] = $player_item_gear;
			$cached_chassis_loadout['weapons'] = $player_item_weapons;
			Cache::forever($this->class_name . "_" . $db_id . "_Loadout_" . $chassis_id,$cached_chassis_loadout);
		};
		$cached_chassis_loadout['status'] = "success";
		return(json_encode($cached_chassis_loadout));
	}
	
	/*
		================================================
		================ Get Functions =================
		================================================
		Contains:
					Get_Player_Webprefs($db_id)
					Get_Player_Inventory($db_id)
					Get_Player_Loadout($db_id)
					Get_Player_Location($db_id)
					Get_Player_Basic_Info($db_id)
					Get_Player_Progress($db_id,<int>$date:range(0-7))
					Get_Player_Unlocks($db_id)
					Get_Player_Craftables($db_id)
					Get_Player_Workbench($db_id)
	*/
	/*
		Function: Player Webprefs
		Assumptions: The player already exists in our database
		Action: Return a players webprefs
		Inputs: db_id -> A player db_id to lookup webprefs with
		Outputs:
			Success: returns the players webprefs in a json array
	*/
	public function Get_Player_Webprefs($db_id){
		/*
			Current Webprefs: 
				show_loadout
				show_progress
				show_inventory
				show_unlocks
				show_pve_kills
				show_pve_events
				show_pve_stats
				show_location
				show_workbench
				show_craftables
				show_market_listings
		*/
		$web_prefs = WebsitePref::where('db_id','=',$db_id)->first(array('show_loadout','show_progress','show_inventory','show_unlocks','show_pve_kills','show_pve_events','show_pve_stats','show_location','show_workbench','show_craftables','show_market_listings'));
		if(!$web_prefs){
			return Response::json(array('status' => 'error', 'error' => 'Player has no web preferences set, this player is most likely not an addon user.'));
		}else{
			$web_prefs->toJson();
			return $web_prefs;
		};
	}
	
	/*
		Function: Get Player Inventory
		Assumptions: The player already exists in our database
		Action: Return a players inventory
		Inputs: db_id -> A player db_id
		Outputs:
			Success: returns the players inventory in a json array
	*/
	public function Get_Player_Inventory($db_id){
		$web_prefs = PlayerAPIController::Get_Player_Webprefs($db_id);
		if($web_prefs['show_inventory'] != 1){
			return Response::json(array('status' => 'error', 'error' => 'Player is not allowing inventory viewing.'));
		}else{
			if(Cache::has($this->class_name . "_" . $db_id . "_Inventory")){
				//pull from cache
				$inventory = Cache::get($this->class_name . "_" . $db_id . "_Inventory");
			}else{
				$inventory = Inventory::where('db_id','=',$db_id)->first(array('inventory'));
				if(!$inventory){
					return Response::json(array('status' => 'error', 'error' => 'Player does not an inventory database entry.'));
				}else{
					$inventory = gzuncompress($inventory['inventory']);
				};
			};
			return $inventory;
		};
	}

	/*
		Function: Get Player Loadout
		Assumptions: The player already exists in our database
		Action: Return a players loadout
		Inputs: db_id -> A player db_id
		Outputs:
			Success: returns the players loadout in a json array
	*/
	public function Get_Player_Loadout($db_id){
		$web_prefs = PlayerAPIController::Get_Player_Webprefs($db_id);
		if($web_prefs['show_loadout'] != 1){
			return Response::json(array('status' => 'error', 'error' => 'Player is not allowing loadout viewing.'));
		}else{
			if(Cache::has($this->class_name . "_" . $db_id . "_Loadout")){
				//pull from cache
				$loadout = Cache::get($this->class_name . "_" . $db_id . "_Loadout");
			}else{
				$loadout = Loadout::where('db_id','=',$db_id)->first(array('entry'));
				if(!$loadout){
					return Response::json(array('status' => 'error', 'error' => 'Player does not an loadout database entry.'));
				}else{
					$loadout = gzuncompress($loadout['entry']);
					$loadout = json_decode($loadout,true);
					//clean out the player db_id
					for($i=0;$i<count($loadout);$i++){
						unset($loadout[$i]->Player_ID);
					};
				};
			};
			return json_encode($loadout);
		};
	}
	
	/*
		Function: Get Player Location
		Assumptions: The player already exists in our database
		Action: Return a players inventory
		Inputs: db_id -> A player db_id
				date -> A date to range back to from the current date
		Outputs:
			Success: returns a json array of this players location.
	*/
	public function Get_Player_Location($db_id,$date){
	
	}
	
	/*
		Function: Get Player Basic Info
		Assumptions: The player already exists in our database
		Action: Return a players basic info
		Inputs: db_id -> A player db_id
		Outputs:
			Success: returns the players basic info in a json array
	*/
	public function Get_Player_Basic_Info($db_id){
		if(Cache::has($this->class_name . "_" . $db_id . "_Basic")){
			//pull from cache
			$basic_info = Cache::get($this->class_name . "_" . $db_id . "_Basic");
		}else{
			$basic_info = Player::where('db_id','=',$db_id)->first(array('instanceId','current_archetype','armyId','armyTag','status','addon_user'));
			//we already have this players db_id (its the input), if this query fails something has gone terribly wrong.
		};
		return json_encode($basic_info);
	}
	
	/*
		Function: Get Player Progress
		Assumptions: The player already exists in our database
		Action: Return a players progress
		Inputs: db_id -> A player db_id
		Outputs:
			Success: returns the players progress in a json array
	*/
	public function Get_Player_Progress($db_id,$date){
		$web_prefs = PlayerAPIController::Get_Player_Webprefs($db_id);
		if($web_prefs['show_progress'] != 1){
			return Response::json(array('status' => 'error', 'error' => 'Player is not allowing progress viewing.'));
		}else{
			if($date == 0){
				if(Cache::has($this->class_name . "_" . $db_id . "_Progress")){
					//pull from cache
					$progress = Cache::get($this->class_name . "_" . $db_id . "_Progress");
				}else{
					$progress = Progress::where('db_id','=',$db_id)->first(array('entry'));
				};
			}else{
				if($date > 7){
					//you cannot go farther back than one week.
					$date = 7;
				};
				//query for all progresses between $date and todays date with a max of 7
				$progress = Progress::where('db_id','=',$db_id)->get(array('entry'));
			};
			if(!$progress){
				return Response::json(array('status' => 'error', 'error' => 'Player does not an progress database entry.'));
			}else{
				$progress = gzuncompress($progress['entry']);
				$progress = json_decode($progress,true);
				//unset the unlocks, we are not asking for them
				unset($progress->unlocks);
			};
			return json_encode($progress);
		};
	}
	
	/*
		Function: Get Player Unlocks
		Assumptions: The player already exists in our database
		Action: Return a players unlocks
		Inputs: db_id -> A player db_id
		Outputs:
			Success: returns the players unlocks in a json array
	*/
	public function Get_Player_Unlocks($db_id){
		$web_prefs = PlayerAPIController::Get_Player_Webprefs($db_id);
		if($web_prefs['show_unlocks'] != 1){
			return Response::json(array('status' => 'error', 'error' => 'Player is not allowing unlocks viewing.'));
		}else{
			if(Cache::has($this->class_name . "_" . $db_id . "_Unlocks")){
				//pull from cache
				$unlocks = Cache::get($this->class_name . "_" . $db_id . "_Unlocks");
			}else{
				$unlocks = Progress::where('db_id','=',$db_id)->first(array('entry'));
			};
			if(!$unlocks){
				return Response::json(array('status' => 'error', 'error' => 'Player does not an unlocks database entry.'));
			}else{
				$unlocks = gzuncompress($unlocks['entry']);
				$unlocks = json_decode($unlocks,true);
				//unset the xp, we are not asking for them
				unset($unlocks->xp);
				unset($unlocks->chassis_id);
			};
			return json_encode($unlocks);
		};
	}
	
	/*
		Function: Get Player Craftables
		Assumptions: The player already exists in our database
		Action: Return a players craftable items
		Inputs: db_id -> A player db_id
		Outputs:
			Success: returns the players craftables in a json array
	*/
	public function Get_Player_Craftables($db_id){
		$web_prefs = PlayerAPIController::Get_Player_Webprefs($db_id);
		if($web_prefs['show_craftables'] != 1){
			return Response::json(array('status' => 'error', 'error' => 'Player is not allowing craftables viewing.'));
		}else{
			$unlocks = PlayerAPIController::Get_Player_Unlocks($db_id);
			$md5_unlocks = md5($unlocks);
			$unlocks = json_decode($unlocks,true);
			if($unlocks['status'] != "error"){
				if(Cache::Get($this->class_name . "_" . $db_id . "_Craftables_MD5") != $md5_unlocks){
					$master_cert_list = array();
					
					//all odd number certs from 799 to 1245
					//category names from 766 to 783
					/*
						766 = base assault
						767 = base biotech
						768 = base dreadnaught
						769 = base engineer
						770 = base recon
						
						774 = firecat
						775 = tigerclaw
						776 = dragonfly
						777 = recluse
						778 = rhino
						779 = mammoth
						780 = electron
						781 = bastion
						782 = nighthawk
						783 = raptor
					*/
					//certs we don't care about
					$useless_certs = array(784,785,786,788,789,790,791,792,794,1154,1359,1366,1367,1371,1372,1378,1379,1380,1381,1382,1383,1384,1385,1386,1387,1388,1389,1390,1391,1392);

					$unlocked_base_assault = 0;
					$unlocked_base_biotech = 0;
					$unlocked_base_dreadnaught = 0;
					$unlocked_base_engineer = 0;
					$unlocked_base_recon = 0;
					
					$unlocked_firecat = 0;
					$unlocked_tigerclaw = 0;
					$unlocked_dragonfly = 0;
					$unlocked_recluse = 0;
					$unlocked_rhino = 0;
					$unlocked_mammoth = 0;
					$unlocked_electron = 0;
					$unlocked_bastion = 0;
					$unlocked_nighthawk = 0;
					$unlocked_raptor = 0;
					
					foreach($unlocks as $progress_certs){
						//Make a master list of certs, note that certs are in numerical sorted order so we are able to do a fast merge
						for($i=0;$i<count($progress_certs);$i++){
							if(!in_array($progress_certs[$i],$master_cert_list)){
								if(!in_array($progress_certs[$i],$useless_certs)){
									//unlock flags
									if($progress_certs[$i] == 766){
										$unlocked_base_assault = 1;
									};
									if($progress_certs[$i] == 767){
										$unlocked_base_biotech = 1;
									};
									if($progress_certs[$i] == 768){
										$unlocked_base_dreadnaught = 1;
									};
									if($progress_certs[$i] == 769){
										$unlocked_base_engineer = 1;
									};
									if($progress_certs[$i] == 770){
										$unlocked_base_recon = 1;
									};
									if($progress_certs[$i] == 774){
										$unlocked_firecat = 1;
									};
									if($progress_certs[$i] == 775){
										$unlocked_tigerclaw = 1;
									};
									if($progress_certs[$i] == 776){
										$unlocked_dragonfly = 1;
									};
									if($progress_certs[$i] == 777){
										$unlocked_recluse = 1;
									};
									if($progress_certs[$i] == 778){
										$unlocked_rhino = 1;
									};
									if($progress_certs[$i] == 779){
										$unlocked_mammoth = 1;
									};
									if($progress_certs[$i] == 780){
										$unlocked_electron = 1;
									};
									if($progress_certs[$i] == 781){
										$unlocked_bastion = 1;
									};
									if($progress_certs[$i] == 782){
										$unlocked_nighthawk = 1;
									};
									if($progress_certs[$i] == 783){
										$unlocked_raptor = 1;
									};
								
									if(($progress_certs[$i] > 764 && $progress_certs[$i] < 798) || ($progress_certs[$i] > 1246)){
										//add to the master list
										$master_cert_list[] = $progress_certs[$i];
									}else if(($progress_certs[$i] > 797 && $progress_certs[$i] < 1246) && ($progress_certs[$i] % 2 == 0)){
										//add to the master list
										$master_cert_list[] = $progress_certs[$i];
									};
								};
							};
						};
					};
					//free up some memory
					$progress = null;
					sort($master_cert_list);
					
					$cert_info = Certification::where(
						function($query) use($master_cert_list){
							$query->where_in('id', $master_cert_list);
						}
					)->get(array('id','name'));
					
					//look at all these arrays
					//jumpjets, secondary weapons {Assault rifle, burst rifle, grenade launcher, SMG, shotgun}, servos
					$base_shared_certs = array(910,982,984,986,988,990,992,1222,1224,1226,1228,1230,1242,1244,1345,1346,1347,1348,1349,1355,1356);
					//accord absorbtion plating, afterburners, bombs away, burn jets, crater, overcharge, plasma cannon, shockwave, assault plating
					$base_assault_certs = array(787,793,795,798,800,802,806,828,996,1016,1018,1020,1022,1024,1026,1028,1202,1204,1232,1246,1247,1248,1249,1250,1335,1336,1350);
					//accord chemical sprayer, accord siphoning plating, healing generator, healing wave, needler, poison ball, poison trail, triage, biotech plating
					$base_biotech_certs = array(804,808,812,814,824,830,832,1008,1010,1054,1056,1058,1060,1062,1064,1210,1212,1236,1256,1264,1265,1266,1267,1268,1339,1340,1352);
					//Absorption Bomb, Accord Explosive Rounds, Accord Mortar Arcfold, Accord Repulsor Blast, Heavy Armor, Turret Mode, Heavy Machine Gun, Resilient Plating
					$base_dreadnaught_certs = array(862,866,872,880,940,998,1012,1014,1090,1092,1094,1096,1098,1100,1206,1208,1234,1281,1282,1283,1284,1285,1286,1337,1338,1351);
					//Anti-Personnel Turret, Charged Pulse Generator, Claymore, Deployable Shield, Heavy Turret, Supply Station, Accord Nanite Deployment, engineer plating, Sticky Grenade Launcher
					$base_engineer_certs = array(942,944,946,948,950,994,1004,1006,1126,1128,1130,1132,1134,1136,1214,1216,1238,1299,1300,1301,1302,1303,1304,1341,1342,1353);
					//Accord Artillery Strike, Cryo Grenade, Decoy, Proximity Reponse, Resonating Bolts, SIN Beacon, Accord Regenerative Plating, R36 Assault Rifle
					$base_recon_certs = array(904,918,922,928,932,934,936,1000,1002,1166,1168,1170,1172,1174,1176,1218,1220,1240,1317,1318,1319,1320,1321,1322,1343,1344,1354);
					
					//ADV frames
					//fuel air bomb, immolate, incinerator, inferno dash, thermal cannon, thermal wave
					$base_firecat_certs = array(810,816,818,820,822,826,1030,1032,1034,1036,1038,1040,1251,1252,1253,1254,1255,1257);
					//fusion cannon, Disruption, Missile Shot, Tether Field, Trailblaze, Auxiliary Tanks
					$base_tigerclaw_certs = array(834,838,842,844,848,852,1042,1044,1046,1048,1050,1052,1258,1259,1260,1261,1262,1263);
					//bio rifle, Emergency Response, Healing Ball, Healing Dome, Healing Pillar, Rally
					$base_dragonfly_certs = array(836,846,850,856,860,864,1066,1068,1070,1072,1074,1076,1269,1270,1271,1272,1273,1274);
					//bio crossbow, creeping death, evacuate, kinetic shot, necrosis, necrotic poison
					$base_recluse_certs = array(874,878,884,888,890,898,1078,1080,1082,1084,1086,1088,1275,1276,1277,1278,1279,1280);
					//Charge!, Dreadfield, Gravity Field Grenade, Sundering Wave, Heavy Laser MG, Personal Shield
					$base_rhino_certs = array(912,916,920,924,926,930,1114,1116,1118,1120,1122,1124,1293,1294,1295,1296,1297,1298);
					//Shield Wall, Teleport Shot, Thunderdome, Tremors, Imminent Threat, Heavy Plasma MG
					$base_mammoth_certs = array(892,896,900,902,906,908,938,1102,1104,1106,1108,1110,1112,1287,1288,1289,1290,1291,1292,1357,1358);
					//Boomerang Shot, Bulwark, Electrical Storm, Overclocking Station, Shock Rail, Fail-Safe
					$base_electron_certs = array(964,966,968,970,972,974,1150,1152,1156,1158,1162,1164,1311,1312,1313,1314,1315,1316);
					//Energy Wall, Fortify, Multi Turret, Sentinel Pod, Tesla Rifle, Overseer
					$base_bastion_certs = array(952,954,956,958,960,962,1138,1140,1142,1144,1146,1148,1305,1306,1307,1308,1309,1310);
					//Eruption Rounds, Execute Shot, Remote Explosive, Smoke Screen, Sniper Rifle, Ambush
					$base_nighthawk_certs = array(870,876,882,886,894,914,980,1178,1180,1182,1184,1186,1188,1323,1324,1325,1326,1327,1328);
					//Overload, Power Field, SIN Scrambler, Teleport Beacon, Conduit, Charge Rifle
					$base_raptor_certs = array(840,854,858,868,976,978,1190,1192,1194,1196,1198,1200,1329,1330,1331,1332,1333,1334);

					$base_shared_items = array();
					$base_assault_items = array();
					$base_biotech_items = array();
					$base_dreadnaught_items = array();
					$base_engineer_items = array();
					$base_recon_items = array();
					
					$base_firecat_items = array();
					$base_tigerclaw_items = array();
					$base_dragonfly_items = array();
					$base_recluse_items = array();
					$base_rhino_items = array();
					$base_mammoth_items = array();
					$base_electron_items = array();
					$base_bastion_items = array();
					$base_nighthawk_items = array();
					$base_raptor_items = array();

					for($i=0;$i<count($cert_info);$i++){
						//base items
						if(in_array($cert_info[$i]->id,$base_shared_certs)){
							$base_shared_items[] = $cert_info[$i]->name;
						};
						if($unlocked_base_assault){
							if(in_array($cert_info[$i]->id,$base_assault_certs)){
								$base_assault_items[] = $cert_info[$i]->name;
							};
						};
						if($unlocked_base_biotech){
							if(in_array($cert_info[$i]->id,$base_biotech_certs)){
								$base_biotech_items[] = $cert_info[$i]->name;
							};
						};
						if($unlocked_base_dreadnaught){
							if(in_array($cert_info[$i]->id,$base_dreadnaught_certs)){
								$base_dreadnaught_items[] = $cert_info[$i]->name;
							};
						};
						if($unlocked_base_engineer){
							if(in_array($cert_info[$i]->id,$base_engineer_certs)){
								$base_engineer_items[] = $cert_info[$i]->name;
							};
						};
						if($unlocked_base_recon){
							if(in_array($cert_info[$i]->id,$base_recon_certs)){
								$base_recon_items[] = $cert_info[$i]->name;
							};
						};
						//ASSAULT ADV
						if($unlocked_firecat){
							if(in_array($cert_info[$i]->id,$base_firecat_certs)){
								$base_firecat_items[] = $cert_info[$i]->name;
							};
						};
						if($unlocked_tigerclaw){
							if(in_array($cert_info[$i]->id,$base_tigerclaw_certs)){
								$base_tigerclaw_items[] = $cert_info[$i]->name;
							};
						};
						//BIOTECH ADV
						if($unlocked_dragonfly){
							if(in_array($cert_info[$i]->id,$base_dragonfly_certs)){
								$base_dragonfly_items[] = $cert_info[$i]->name;
							};
						};
						if($unlocked_recluse){
							if(in_array($cert_info[$i]->id,$base_recluse_certs)){
								$base_recluse_items[] = $cert_info[$i]->name;
							};
						};
						//DREADNAUGHT ADV
						if($unlocked_rhino){
							if(in_array($cert_info[$i]->id,$base_rhino_certs)){
								$base_rhino_items[] = $cert_info[$i]->name;
							};
						};
						if($unlocked_mammoth){
							if(in_array($cert_info[$i]->id,$base_mammoth_certs)){
								$base_mammoth_items[] = $cert_info[$i]->name;
							};
						};
						//ENGINEER ADV
						if($unlocked_electron){
							if(in_array($cert_info[$i]->id,$base_electron_certs)){
								$base_electron_items[] = $cert_info[$i]->name;
							};
						};
						if($unlocked_bastion){
							if(in_array($cert_info[$i]->id,$base_bastion_certs)){
								$base_bastion_items[] = $cert_info[$i]->name;
							};
						};
						//RECON ADV
						if($unlocked_nighthawk){
							if(in_array($cert_info[$i]->id,$base_nighthawk_certs)){
								$base_nighthawk_items[] = $cert_info[$i]->name;
							};
						};
						if($unlocked_raptor){
							if(in_array($cert_info[$i]->id,$base_raptor_certs)){
								$base_raptor_items[] = $cert_info[$i]->name;
							};
						};
					};
					asort($base_shared_items);
					asort($base_assault_items);
					asort($base_biotech_items);
					asort($base_dreadnaught_items);
					asort($base_engineer_items);
					asort($base_recon_items);
					asort($base_firecat_items);
					asort($base_tigerclaw_items);
					asort($base_dragonfly_items);
					asort($base_recluse_items);
					asort($base_rhino_items);
					asort($base_mammoth_items);
					asort($base_electron_items);
					asort($base_bastion_items);
					asort($base_nighthawk_items);
					asort($base_raptor_items);
					
					//cache it, cache it good
					$cached_can_craft = array();
					$cached_can_craft['base_shared_items'] = $base_shared_items;
					$cached_can_craft['base_assault_items'] = $base_assault_items;
					$cached_can_craft['base_biotech_items'] = $base_biotech_items;
					$cached_can_craft['base_dreadnaught_items'] = $base_dreadnaught_items;
					$cached_can_craft['base_engineer_items'] = $base_engineer_items;
					$cached_can_craft['base_recon_items'] = $base_recon_items;
					$cached_can_craft['base_firecat_items'] = $base_firecat_items;
					$cached_can_craft['base_tigerclaw_items'] = $base_tigerclaw_items;
					$cached_can_craft['base_dragonfly_items'] = $base_dragonfly_items;
					$cached_can_craft['base_recluse_items'] = $base_recluse_items;
					$cached_can_craft['base_rhino_items'] = $base_rhino_items;
					$cached_can_craft['base_mammoth_items'] = $base_mammoth_items;
					$cached_can_craft['base_electron_items'] = $base_electron_items;
					$cached_can_craft['base_bastion_items'] = $base_bastion_items;
					$cached_can_craft['base_nighthawk_items'] = $base_nighthawk_items;
					$cached_can_craft['base_raptor_items'] = $base_raptor_items;
					Cache::forever($this->class_name . "_" . $db_id . "_Craftables_MD5",$cached_can_craft);
					Cache::forever($this->class_name . "_" . $db_id . "_Craftables",$md5_unlocks);
					//set progress to 1 for the view control params.
					$progress = 1;
				}else{
					//load it all from cache
					$cached_can_craft = Cache::Get($this->class_name . "_" . $db_id . "_Craftables");
					//set all those variables
					$base_shared_items = $cached_can_craft['base_shared_items'];
					$base_assault_items = $cached_can_craft['base_assault_items'];
					$base_biotech_items = $cached_can_craft['base_biotech_items'];
					$base_dreadnaught_items = $cached_can_craft['base_dreadnaught_items'];
					$base_engineer_items = $cached_can_craft['base_engineer_items'];
					$base_recon_items = $cached_can_craft['base_recon_items'];
					$base_firecat_items = $cached_can_craft['base_firecat_items'];
					$base_tigerclaw_items = $cached_can_craft['base_tigerclaw_items'];
					$base_dragonfly_items = $cached_can_craft['base_dragonfly_items'];
					$base_recluse_items = $cached_can_craft['base_recluse_items'];
					$base_rhino_items = $cached_can_craft['base_rhino_items'];
					$base_mammoth_items = $cached_can_craft['base_mammoth_items'];
					$base_electron_items = $cached_can_craft['base_electron_items'];
					$base_bastion_items = $cached_can_craft['base_bastion_items'];
					$base_nighthawk_items = $cached_can_craft['base_nighthawk_items'];
					$base_raptor_items = $cached_can_craft['base_raptor_items'];
				};
			}else{
				return Response::json(array('status' => 'error', 'error' => 'Player unlocks returned an error.'));
			};
			$cached_can_craft['status'] = 'success';
			return json_encode($cached_can_craft);
		};
	}
	
	/*
		Function: Get Player Workbench
		Assumptions: The player already exists in our database
		Action: Return a players workbench
		Inputs: db_id -> A player db_id
		Outputs:
			Success: returns the players workbench(es) in a json array
	*/
	public function Get_Player_Workbench($db_id){
		$web_prefs = PlayerAPIController::Get_Player_Webprefs($db_id);
		if($web_prefs['show_workbench'] != 1){
			return Response::json(array('status' => 'error', 'error' => 'Player is not allowing workbench viewing.'));
		}else{			
			//Workbench Stuff
			$workbench_info = array();
			if( Cache::has($this->class_name . "_" . $db_id . "_Workbench")) {
				$workbench_info = Cache::get($this->class_name . "_" . $db_id . "_Workbench");
			}else{
				$wbinfo = Printer::where('db_id','=',$player->db_id)->get();
				foreach ($wbinfo as $wb)
				{
					$workbench_info[] = $wb->original;
				}
				Cache::forever($this->class_name . "_" . $db_id . "_Workbench", $workbench_info);
			}
			if(!empty($workbench_info)){
				$workbench_info_blueprints = array();
				for($i=0;$i<count($workbench_info);$i++){
					//assume Medium health pack for large health packs, this is temporary till we can figure out whats going on with itemtypeid 85374
					if($workbench_info[$i]['blueprint_id'] == 85374){
						$workbench_info[$i]['blueprint_id'] = 75096;
					};
					$workbench_info_blueprints[] = $workbench_info[$i]['blueprint_id'];
				};
				$workbench_results = Recipe::with(array('outputs'))->where_in('itemtypeid',$workbench_info_blueprints)->get(array('itemtypeid','description','name'));
				
				$workbench_output_ids = array();
				$workbench_output_names = array();
				//at this point we will always have something in the workbench so don't worry about null
				foreach($workbench_results as $workbench_result){
					if($workbench_result->relationships['outputs'] != null){
						//add the output itemtypeid to the list
						print_r($workbench_result->relationships['outputs']);
					}else{
						//add the blueprint_id as an itemtypeid because its a cert
						$workbench_output_ids[] = $workbench_result->attributes['itemtypeid'];
						if(strstr($workbench_result->attributes['description'],"Research")){
							$workbench_output_names[ $workbench_result->attributes['itemtypeid'] ] = $workbench_result->attributes['description'];
						}else{
							$workbench_output_names[ $workbench_result->attributes['itemtypeid'] ] = $workbench_result->attributes['name'];
						};
					};
				};
				unset($workbench_results);
				
				//get the icons
				$wb_icons = hWebIcon::where(
					function($query) use($workbench_output_ids){
						$query->where('version','=', Base_Controller::getVersionDate());
						$query->where_in('itemTypeId',$workbench_output_ids);
					}
				)->get(array('itemtypeid','asset_path'));
				foreach ($wb_icons as $wb)
				{
					$workbench_output_icons[ $wb->itemtypeid ] = $wb->asset_path;
				}
				unset($wb_icons);
				
				//strip info & append info
				for($i=0;$i<count($workbench_info);$i++){
					unset($workbench_info[$i]['db_id']);
					if(isset($workbench_info[$i]['id'])){
						unset($workbench_info[$i]['id']);
					};
					unset($workbench_info[$i]['created_at']);
					unset($workbench_info[$i]['updated_at']);
					unset($workbench_info[$i]['blueprint_id']);
					if(isset($workbench_output_ids[$i])){
						$workbench_info[$i]['item'] = $workbench_output_names[$workbench_output_ids[$i]];
					}else{
						$workbench_info[$i]['item'] = 'Undefined';
					};
					if(isset($workbench_output_icons[$i])){
						$workbench_info[$i]['icon'] = $workbench_output_icons[$workbench_results->attributes['itemtypeid']];
					}else{
						$workbench_info[$i]['icon'] = "";
					};
				};
			};
			$workbench_info['status'] = 'success';
			return json_encode($workbench_info);
		};
	}
	
	/*
		================================================
		================ Set Functions =================
		================================================
		Contains:
					Set_Player_Inventory($db_id)
					Set_Player_Loadout($db_id)
					Set_Player_Location($db_id)
					Set_Player_Basic_Info($db_id)
					Set_Player_Progress($db_id)
					Set_Player_Webprefs($db_id)
					Set_Player_Workbench($db_id)
	*/
	/*
		Function: Sets Player Inventory
		Assumptions: None.
		Action: Writes a players inventory to both DB and Cache
		Inputs: db_id -> A player db_id
				input -> A pre-validated inventory JSON string
		Outputs:
			True -> if we have the most current data on file OR the data was successfully put into the DB
			False -> if we encounter an error
	*/
	public function Set_Player_Inventory($db_id, $input){
		if(Cache::has($this->class_name . "_" . $db_id . "_Inventory_MD5")){
			//check the input MD5 against this
			if(md5($input) == Cache::get($this->class_name . "_" . $db_id . "_Inventory_MD5")){
				return true;
			};
		}else{
			//If we have gotten to this point than we either don't have a cache on file or the data is not the same so update/insert and build cache
			$last_inventory_update = Inventory::where(function($query) use ($db_id) {
				$query->where('db_id','=', $db_id);
				$query->where( DB::raw('DATE(updated_at)') , '=', $this->date_only );
			})->order_by('id','desc')->first('id');
			try{
				if( $last_inventory_update ) {
					$query_update_inv = "UPDATE `inventories` SET `inventory` = ?, updated_at = ? WHERE `id` = ?";
					$bindings_update_inventory = array( gzcompress(json_encode($input),5), $this->date, $last_inventory_update->id);

					if( DB::query($query_update_inv, $bindings_update_inventory) === false ){
						throw new Exception('Error updating inventory.');
					};
				}else{
					$inv = new Inventory;
						$inv->db_id = $db_id;
						$inv->inventory = gzcompress(json_encode($input),5);
					if( !$inv->save() ){
						throw new Exception('Add inventory query failed:');
					};
				};
            }catch (Exception $e) {
                Log::info($log_header.$e->getMessage());
                file_put_contents($this->logpath.$this->logdate.'_bad_player.json', json_encode($input));
				return false;
            }
		};
		//set cache file and cache MD5 file
		Cache::forever($this->class_name . "_" . $db_id . "_Inventory",json_encode($input));
		Cache::forever($this->class_name . "_" . $db_id . "_Inventory_MD5",md5($input));
		return true;
	}
	
	/*
		Function: Sets Player Loadout
		Assumptions: None.
		Action: Writes a players loadout to both DB and Cache
		Inputs: db_id -> A player db_id
				input -> A pre-validated loadout JSON string
		Outputs:
			True -> if we have the most current data on file OR the data was successfully put into the DB
			False -> if we encounter an error
	*/
	public function Set_Player_Loadout($db_id, $input){
		if(Cache::has($this->class_name . "_" . $db_id . "_Loadout_MD5")){
			//check the input MD5 against this
			if(md5($input) == Cache::get($this->class_name . "_" . $db_id . "_Loadout_MD5")){
				return true;
			};
		}else{
			try{
				$query_add_loadout = "INSERT INTO `loadouts` ";
				$query_add_loadout .= "(db_id, entry, created_at, updated_at) ";
				$query_add_loadout .= "VALUES (?, ?, ?, ?) ";
				$query_add_loadout .= "ON DUPLICATE KEY UPDATE ";
				$query_add_loadout .= "entry = ?, updated_at = ?";

				$loadout_entry = gzcompress(json_encode($input),5);

				$bindings = array(
					$db_id, $loadout_entry, $this->date, $this->date,
					$loadout_entry, $this->date
				);

				if( !DB::query($query_add_loadout, $bindings) ){
					throw new Exception('Add loadout query failed:');
				}
            }catch (Exception $e) {
                Log::info($log_header.$e->getMessage());
                file_put_contents($this->logpath.$this->logdate.'_bad_player.json', json_encode($input));
				return false;
            }
		};
		//set cache file and cache MD5 file
		Cache::forever($this->class_name . "_" . $db_id . "_Loadout",json_encode($input));
		Cache::forever($this->class_name . "_" . $db_id . "_Loadout_MD5",md5($input));
		return true;
	}
	
	public function Set_Player_Location($db_id, $input){
	
	}
	
	/*
		Function: Sets Player Basic Info
		Assumptions: All input array keys are set.
		Action: Writes a players basic info to both DB and Cache
		Inputs: db_id -> A player db_id
				input -> An array with the following keys:
							array['player_name']
							array['play_army_tag']
							array['player_instance_id']
							array['player_eid']
							array['player_army_id']
							array['player_current_archetype']
							array['player_region']
		Outputs:
			True -> if we have the most current data on file OR the data was successfully put into the DB
			False -> if we encounter an error
	*/
	public function Set_Player_Basic_Info($db_id, $input){
		if(Cache::has($this->class_name . "_" . $db_id . "_Basic_MD5")){
			//check the input MD5 against this
			if(md5($input) == Cache::get($this->class_name . "_" . $db_id . "_Basic_MD5")){
				Player::where('db_id','=',$db_id)->touch();
				return true;
			};
		}else{
			try {
				$is_addon_user = 1;
				$query_add_player = "INSERT INTO `players` ";
				$query_add_player .= "(name, armyTag, instanceId, db_id, e_id,";
				$query_add_player .= "armyId, current_archetype, region, ";
				$query_add_player .= "created_at, updated_at) ";
				$query_add_player .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
				$query_add_player .= "ON DUPLICATE KEY UPDATE ";
				$query_add_player .= "name = ?, armyTag = ?, instanceId = ?, armyId = ?, ";
				$query_add_player .= "e_id = ?, current_archetype = ?, addon_user = ?, region = ?, ";
				$query_add_player .= "updated_at = ?";

				$bindings = array(
					$input['player_name'], $input['player_army_tag'], $input['player_instance_id'], $db_id, $input['player_eid'],
					$input['player_army_id'], $input['player_current_archetype'], $input['player_region'],
					$this->date, $this->date,
					$input['player_name'], $input['player_army_tag'], $input['player_instance_id'], $input['player_army_id'],
					$input['player_eid'], $input['player_current_archetype'], $is_addon_user, $input['player_region'],
					$this->date
				);
                if( !DB::query($query_add_player,$bindings) ){
                    throw new Exception('Add/Update player query failed');
                }
            }catch (Exception $e) {
                Log::info($log_header.$e->getMessage());
                file_put_contents($this->logpath.$this->logdate.'_bad_player.json', json_encode($input));
				return false;
            }
		};
		//set cache file and cache MD5 file
		Cache::forever($this->class_name . "_" . $db_id . "_Basic",json_encode($input));
		Cache::forever($this->class_name . "_" . $db_id . "_Basic_MD5",md5($input));
		return true;
	}

	/*
		Function: Sets Player Progress
		Assumptions: None.
		Action: Writes a players progress to both DB and Cache
		Inputs: db_id -> A player db_id
				input -> A pre-validated progress JSON string
		Outputs:
			True -> if we have the most current data on file OR the data was successfully put into the DB
			False -> if we encounter an error
	*/
	public function Set_Player_Progress($db_id, $input){
		if(Cache::has($this->class_name . "_" . $db_id . "_Progress_MD5")){
			//check the input MD5 against this
			if(md5($input) == Cache::get($this->class_name . "_" . $db_id . "_Progress_MD5")){
				return true;
			};
		}else{
		   //Check the last send for progress, see if we need to update, or make a new row
			$last_progress_update = Progress::where(function($query) use ($db_id) {
				$query->where('db_id','=', $db_id);
				$query->where( DB::raw('DATE(updated_at)') , '=', $this->date_only );
			})->order_by('id','desc')->first('id');
			try{
				if( $last_progress_update ) {
					$query_update_prog = "UPDATE `progresses` SET `entry` = ?, updated_at = ? WHERE `id` = ?";
					$bindings_update_progress = array( gzcompress(json_encode($input),5), $this->date, $last_progress_update->id);

					if( DB::query($query_update_prog, $bindings_update_progress) === false ){
						throw new Exception('Error updating progress.');
					}

				}else{
					$progress = new Progress;
						$progress->db_id = $db_id;
						$progress->entry = gzcompress(json_encode($input),5);
					if( !$progress->save() ){
						throw new Exception('Add progress query failed:');
					}
				}
            }catch (Exception $e) {
                Log::info($log_header.$e->getMessage());
                file_put_contents($this->logpath.$this->logdate.'_bad_player.json', json_encode($input));
				return false;
            }
		}
		//set cache file and cache MD5 file
		Cache::forever($this->class_name . "_" . $db_id . "_Progress",json_encode($input));
		Cache::forever($this->class_name . "_" . $db_id . "_Progress_MD5",md5($input));
		return true;
	}

	/*
		Function: Sets Player WebPrefs
		Assumptions: None.
		Action: Writes a players webprefs to DB
		Inputs: db_id -> A player db_id
				input -> A pre-validated webprefs object
		Outputs:
			True -> the data was successfully put into the DB
			False -> if we encounter an error
	*/
	public function Set_Player_WebPrefs($db_id, $input){
		try{
			$prefs = $input;

			$query_webpref = 'INSERT INTO `websiteprefs` (db_id, ';
			$query_webpref .= 'show_loadout, show_progress, show_inventory, show_unlocks, ';
			$query_webpref .= 'show_pve_kills, show_pve_stats, show_pve_events, show_location, ';
			$query_webpref .= 'show_workbench, show_craftables, show_market_listings, ';
			$query_webpref .= 'created_at, updated_at) VALUES ';
			$query_webpref .= '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE ';
			$query_webpref .= '`show_loadout` = ?, `show_progress` = ?, `show_inventory` = ?, show_unlocks = ?, ';
			$query_webpref .= '`show_pve_kills` = ?, `show_pve_stats` = ?, `show_pve_events` = ?, `show_location` = ?, ';
			$query_webpref .= '`show_workbench` = ?, `show_craftables` = ?, `show_market_listings` = ?, ';
			$query_webpref .= '`updated_at` = ?';

			$bindings_webpref = array($db_id,
				$prefs->show_loadout, $prefs->show_progress, $prefs->show_inventory, $prefs->show_unlocks,
				$prefs->show_pve_kills, $prefs->show_pve_stats, $prefs->show_pve_events, $prefs->show_location,
				$show_workbench, $show_craftables, $show_market_listings, 
				$this->date, $this->date,
				$prefs->show_loadout, $prefs->show_progress, $prefs->show_inventory, $prefs->show_unlocks,
				$prefs->show_pve_kills, $prefs->show_pve_stats, $prefs->show_pve_events, $prefs->show_location,
				$prefs->show_workbench, $prefs->show_craftables, $prefs->show_market_listings, 
				$this->date);

			if(DB::query($query_webpref, $bindings_webpref) === false){
				throw new Exception('Error updating webprefs.');
			};
		}catch (Exception $e) {
			Log::info($log_header.$e->getMessage());
			file_put_contents($this->logpath.$this->logdate.'_bad_player.json', json_encode($input));
			return false;
		}
		return true;
	}
	
	public function Set_Player_Workbench($db_id, $input){
		$player_crafts = array();
		$now = date('Y-m-d H:i:s');
		try{
			foreach($input->Player_Craft_Queue as $cq){
				//we expect only three keys
				if( !isset($cq->ready_at) || !isset($cq->started_at) || !isset($cq->blueprint_id) ) {
					return false;
				};
				if( is_numeric($cq->ready_at) && is_numeric($cq->started_at) && is_numeric($cq->blueprint_id)){
					$player_crafts[] = array(
						'db_id' => $player_db_id, 
						'ready_at' => $cq->ready_at, 
						'started_at' => $cq->started_at, 
						'blueprint_id' => $cq->blueprint_id,
						'created_at' => $now,
						'updated_at' => $now
					);
				}else{
					throw new Exception("Craft Queue values contained something other than a number.");
				}
			}//foreach
			
			//Delete existing records before adding new ones
			DB::table('printers')->where('db_id','=',$player_db_id)->delete();
			//add new records
			if(count($player_crafts) > 0){
				$crafts_in = DB::table('printers')->insert($player_crafts);
			}else{
				$crafts_in = false;
			}
			//cache
			Cache::forever($this->class_name . "_" . $db_id . "_Workbench", $player_crafts);
        }catch(Exception $e){
            Log::info($log_header.$e->getMessage());
            file_put_contents($this->logpath.$this->logdate.'_printer.json', serialize($line));
			return false;
        };
		return true;
	}
}
?>