<?php

class Player_Data_Controller extends Base_Controller {

    public $restful = true;

    /*
     *  Armies
     *
     *  Look at my armies, my armies are amazing
     */
    public function get_armies()
    {
        $per_page = 50;

        //PAGINATION AND ORDERING
        if( Input::has('order') ) {
            $ord = Input::get('order');
            if($ord == 'asc' || $ord == 'desc'){
                $order = strtoupper($ord);
            }else{
                $order = 'ASC';
            }
        }else{
            $order = 'ASC';
        }

        //Sort by?
        switch( Input::get('sort') ) {
            case 'members':
                $sort = 'member_count';
                break;
            case 'commander':
                $sort = 'commander';
                break;
            case 'playstyle':
                $sort = 'playstyle';
                break;
            case 'intensity':
                $sort = 'personality';
                break;
            case 'recruiting':
                $sort = 'is_recruiting';
                break;
            default:
                $sort = 'name';
                break;
        }

        //Pull army data, don't use base controller "version" as armies are updated more frequently
        $armies = Army::
            where(function($query) {
                $query->where('version','=', DB::raw('(SELECT MAX(version) FROM `armies`)'));
            })
            ->order_by($sort, $order)
            ->paginate($per_page);

        //Misc data: total #, distribution of players
        $armies_data = DB::query('SELECT COUNT(*) as numArmies, MAX(version) as latest FROM armies WHERE version = (SELECT MAX(version) FROM `armies`)');
        $armies_distribution = DB::query('SELECT member_count, COUNT(*) as occurrences FROM armies WHERE version = (SELECT MAX(version) FROM `armies`) GROUP BY member_count DESC ');

        $armies->appends(
            array(
                'sort'      => Input::get('sort'),
                'order'     => Input::get('order')
            ))->links();

        return View::make('player.armies')
            ->with('title', 'Army Database')
            ->with(compact('armies'))
            ->with(compact('armies_data'))
            ->with(compact('armies_distribution'));
    }


    /*
     *  Army Profile
     *
     *  Pull up details on a single army
     */
    public function get_army_profile($armyid = NULL)
    {
        //Can't look up nothing...
        if($armyid == NULL) {
            return Response::error('404');
        }

        //Select only the latest army
        $army = Army::with('armymembers')
            ->where('armyId','=',$armyid)
            ->order_by('version','DESC')
            ->first();

        //somehow no army was found?  why not search box?
            //or army newly created and not up to date
        if( !$army ) {
            return Response::error('404');
        }

        //try to determine the army tag based off player data
        $tag = Player::where(function($query) use ($army){
            $query->where('armyId','=',$army->armyid);
            $query->where('armyTag','>',"''");
        })
            ->order_by('updated_at', 'DESC')
            ->first();

        return View::make('player.army_profile')
            ->with('title', 'Profile for ' . htmlentities($army->name))
            ->with(compact('army'))
            ->with(compact('tag'));
    }


    /*
     *  Players list
     *
     *  I herd u liek players, so here's all of them
     */
    public function get_players()
    {
        $per_page = 50;

        //PAGINATION AND ORDERING
        if( Input::has('order') ) {
            $ord = Input::get('order');
            if($ord == 'asc' || $ord == 'desc'){
                $order = strtoupper($ord);
            }else{
                $order = 'DESC';
            }
        }else{
            $order = 'DESC';
        }

        //Sort by?
        switch( Input::get('sort') ) {
            case 'name':
                $sort = 'name';
                break;
            case 'instance':
                $sort = 'instanceid';
                break;
            case 'archetype':
                $sort = 'current_archetype';
                break;
            case 'seen':
                $sort = 'updated_at';
                break;
            default:
                $sort = 'updated_at';
                break;
        }

        //Grab those players
        $players = Player::
            order_by('addon_user','desc')
            ->order_by($sort, $order)
            ->paginate($per_page);

        $players->appends(
            array(
                'sort'      => Input::get('sort'),
                'order'     => Input::get('order')
            ))->links();

        return View::make('player.players')
            ->with('title', 'Player Database')
            ->with(compact('players'));
    }


    /*
     *  Player Profile
     *
     *  Look at that player, that's a nice player.
     */
    public function get_player_profile($name = NULL)
    {

        //searching by name, null name = no buenos
        if($name == NULL) {
            return Response::error('404');
        }else{
            $input_name = urldecode($name);
        }

        //Look up the player, latest to account for deletes with the same name
        $player = Player::where('name','=',$input_name)
            ->order_by('created_at', 'DESC')
            ->first();

        //no player found, why not search?
        if(!$player) {
            return Response::error('404');
        }
        /*
        if( $player->name == 'Thehink' ) {
            return View::make('player.player_profile_banned')
            ->with('title', 'Player Profile for ' . $player->name)
            ->with(compact('player'));
        }
        */

        //But wait there's more, let's see if we know where that player has been
        $locations = false;/*Location::where('db_id','=',$player->db_id)
            ->order_by('created_at','DESC')
            ->first();*/

        //provide the spotters name if a location has been sighted
        if($locations){
            $spotter = Player::where('db_id','=',$locations->spotter_db_id)
                ->only('name');
        }else{
            $spotter = NULL;
        }


        //calculate the players closest POI
        if($locations) {

            if( !Cache::has('poi_lookup') ) {
                Cache::forever('poi_lookup', PointOfInterest::get(array('name','coord_x','coord_y')) );
            }

            $pois = Cache::get('poi_lookup');

            $poi_locs = $pois;
            $poi_lastx = round($locations->coord_x);
            $poi_lasty = round($locations->coord_y);

            $poi_nearest = [999999,'n/a'];
            foreach($poi_locs as $pois)
            {
                $xs = 0;
                $ys = 0;

                $xs = $poi_lastx - intval($pois->coord_x);
                    $xs = $xs * $xs;
                $ys = $poi_lasty - intval($pois->coord_y);
                    $ys = $ys * $ys;
                $distance = round(sqrt( $xs + $ys));

                if( $distance < $poi_nearest[0] ){
                    $poi_nearest = [$distance, $pois->name];
                }
            }
            $nearestloc = $poi_nearest[1];

        }else{
            $nearestloc = NULL;
        }


        //Does the player have an army?
        $army = Army::where('armyId','=',$player->armyid)
            ->order_by('created_at', 'DESC')
            ->first();

        //escape for output in title
        $playerName = htmlentities($player->name);

		//Player website preferences
		$web_prefs = WebsitePref::where('db_id','=',$player->db_id)->first();
		if(isset($web_prefs->attributes)){
			$web_prefs = $web_prefs->attributes;
		};

		if(isset($web_prefs['show_inventory'])){
			if($web_prefs['show_inventory'] == 1){
				$inventory = Inventory::where('db_id','=',$player->db_id)->order_by('updated_at','DESC')->first();
			}else{
				$inventory = null;
			};
		}else{
			$inventory = Inventory::where('db_id','=',$player->db_id)->order_by('updated_at','DESC')->first();
		};
		/*
			Mappings:
				local c_RawToRefinedConversion = {
					["78014"] = "77703", --Copper
					["78015"] = "77704", --Iron
					["78016"] = "77705", --Aluminum
					["78017"] = "77706", --Carbon
					["78018"] = "77707", --Silicate
					["78019"] = "77708", --Ceramics
					["78020"] = "77709", --Methine
					["78021"] = "77710", --Octine
					["78022"] = "77711", --Nitrine
					["82420"] = "82419", --Radine
					["78023"] = "77713", --Petrochemical
					["78024"] = "77714", --Biopolymer
					["78025"] = "77715", --Xenografts
					["78026"] = "77716", --Toxins
					["78027"] = "77736", --Regenics
					["78028"] = "77737", --Anabolics
				}
				local c_RefinedToRawConversion = {
					["77703"] = "78014", --Copper
					["77704"] = "78015", --Iron
					["77705"] = "78016", --Aluminum
					["77706"] = "78017", --Carbon
					["77707"] = "78018", --Silicate
					["77708"] = "78019", --Ceramics
					["77709"] = "78020", --Methine
					["77710"] = "78021", --Octine
					["77711"] = "78022", --Nitrine
					["82419"] = "82420", --Radine
					["77713"] = "78023", --Petrochemical
					["77714"] = "78024", --Biopolymer
					["77715"] = "78025", --Xenografts
					["77716"] = "78026", --Toxins
					["77736"] = "78027", --Regenics
					["77737"] = "78028", --Anabolics
				}
				local c_ResourceIds = {
					["10"] = "Crystite",
					["80404"] = "AMPS",
					["30412"] = "CHITS",
					["30404"] = "SiftedEarth",
					["77703"] = "Metal",
					["77704"] = "Metal",
					["77705"] = "Metal",
					["77706"] = "Carbon",
					["77707"] = "Carbon",
					["77708"] = "Carbon",
					["77709"] = "Ceramic",
					["77710"] = "Ceramic",
					["77711"] = "Ceramic",
					["77713"] = "Biomaterial",
					["77714"] = "Biomaterial",
					["77715"] = "Biomaterial",
					["77716"] = "Polymer",
					["77736"] = "Polymer",
					["77737"] = "Polymer",
					["82419"] = "Ceramic",
				}


		*/
		if($inventory){
			$inventory = unserialize($inventory->inventory);

            if( isset($inventory->{10}) ) {
                $player_crystite_amount = $inventory->{10};
            }else{
                $player_crystite_amount = 0;
            }

			$cache_key_inventory = $player->db_id . "_inventory";
			if(Cache::Get($cache_key_inventory) != $inventory){
				$raw_resource_names = array('Copper','Iron','Aluminum','Carbon','Silicate','Ceramics','Methine','Octine','Nitrine','Radine','Petrochemical','Biopolymer','Xenografts','Toxins','Regenics','Anabolics');
				$raw_resource_ids = array(78014,78015,78016,78017,78018,78019,78020,78021,78022,82420,78023,78024,78025,78026,78027,78028);
				$refined_resource_ids = array(77703,77704,77705,77706,77707,77708,77709,77710,77711,82419,77713,77714,77715,77716,77736,77737);
				//Convert inventory to an array
				$inventory = (array)$inventory;

				//Get a cache (or make one) of all the resource item image paths
				if(!Cache::has('resource_asset_paths_dump')){
					$resource_ids = array_merge($raw_resource_ids,$refined_resource_ids);
					$resource_icons = hWebIcon::where(
						function($query) use($resource_ids){
							$query->where('version','=', Base_Controller::getVersionDate());
							$query->where_in('itemTypeId',$resource_ids);
						}
					)->get(array('itemtypeid','asset_path'));
					Cache::forever('resource_asset_paths_dump',$resource_icons);
				}else{
					$resource_icons = Cache::Get('resource_asset_paths_dump');
				};

				$player_raw_resources = array();
				$player_refined_resources = array();
				$counter_raw = 0;
				$counter_refined = 0;
				foreach($inventory as $key=>$value){
					if(in_array($key,$raw_resource_ids)){
						$player_raw_resources[$counter_raw]['name'] = $raw_resource_names[array_search($key,$raw_resource_ids)];
						$player_raw_resources[$counter_raw]['id'] = $key;
						$player_raw_resources[$counter_raw]['amt'] = $value;
						for($i=0;$i<count($resource_icons);$i++){
							if($resource_icons[$i]->attributes['itemtypeid'] == $key){
								$player_raw_resources[$counter_raw]['asset_path'] = $resource_icons[$i]->attributes['asset_path'];
							};
						};
						$counter_raw++;
					};
					if(in_array($key,$refined_resource_ids)){
						$player_refined_resources[$counter_refined]['name'] = $raw_resource_names[array_search($key,$refined_resource_ids)];
						$player_refined_resources[$counter_refined]['id'] = $key;
						$player_refined_resources[$counter_refined]['amt'] = $value;
						for($i=0;$i<count($resource_icons);$i++){
							if($resource_icons[$i]->attributes['itemtypeid'] == $key){
								$player_refined_resources[$counter_refined]['asset_path'] = $resource_icons[$i]->attributes['asset_path'];
							};
						};
						$counter_refined++;
					};
				};
				//set some cache
				Cache::forever($cache_key_inventory . "_refined_resources",$player_refined_resources);
				Cache::forever($cache_key_inventory . "_raw_resources",$player_raw_resources);
			}else{
				$player_refined_resources = Cache::Get($cache_key_inventory . "_refined_resources");
				$player_raw_resources = Cache::Get($cache_key_inventory . "_raw_resources");
			};
			//set inventory to 1, we don't need everything else exposed on the players page
			$inventory = 1;
		};


        //loadouts
        /*
            Mappings:
                $loadout->Gear[$i] = Currently equipped items array
                $loadout->Gear[$i]->slot_index
                $loadout->Gear[$i]->info->durability->pool
                $loadout->Gear[$i]->info->durability->current
                $loadout->Gear[$i]->info->attribute_modifiers[$k] = array of custom attributes for this item
                $loadout->Gear[$i]->info->quality = The quality of the crafted item
                $loadout->Gear[$i]->info->creator_guid = the creators unique player ID
                $loadout->Gear[$i]->info->item_sdb_id = The root item this was crafted from

                $loadout->Weapons[$i] = Currently equiped weapons array
                $loadout->Weapons[$i]->info->durability->pool
                $loadout->Weapons[$i]->info->durability->current
                $loadout->Weapons[$i]->info->attribute_modifiers[$k]
                $loadout->Weapons[$i]->info->quality
                $loadout->Weapons[$i]->info->creator_guid
                $loadout->Weapons[$i]->info->item_sdb_id
                $loadout->Weapons[$i]->allocated_power
                $loadout->Weapons[$i]->slot_index = Weapon slot, 0 is main hand, 1 is alt weapon
        */
        /*
            Attribute modifiers mapping:
                5   = Jet Energy Recharge
                6   = health
                7   = health regen
                12  = Run Speed
                29  = weapon splash radius
                35  = Energy (for jetting)
                37  = Jump Height
                950 = Max durability pool
                951 = Mass (unmodified - YOU MUST take the abs of this to get it to display correctly!)
                952 = Power (unmodified - YOU MUST take the abs of this to get it to display correctly!)
                953 = CPU (unmodified - YOU MUST take the abs of this to get it to display correctly!)
                956 = clip size
                954 = damage per round
                977 = Damage
                978 = Debuff Duration
                1121= Air Sprint

                Defaults for weapons are set in the "hstats" table.
        */
		if(isset($web_prefs['show_loadout'])){
			if($web_prefs['show_loadout'] == 1){
				$loadout = Loadout::where('db_id','=',$player->db_id)->first();
			}else{
				$loadout = null;
			};
		}else{
			$loadout = Loadout::where('db_id','=',$player->db_id)->first();
		};
		if($loadout){
			//Lets play the cache game, where all the stuff is stored locally and the points don't matter!
			$loadout = unserialize($loadout->entry);

			//VERSION 0.6 CHECKING (Array = Good, Object = BAD!)
			if(gettype($loadout) == "object"){
				//This is from version 0.6, we can no longer use this data.
				$loadout = null;
			};
			$cache_key_loadouts = $player->db_id . "_loadouts";
			$loadout_md5 = md5(serialize($loadout));
			if((Cache::Get($cache_key_loadouts . '_md5') != $loadout_md5) && ($loadout != null)){
				//Oh I am playing the game, the one that will take me to my end.

				//Make sure this isnt a terrible send
				if(isset($loadout[0]->Gear)){
					for($k=0;$k<count($loadout);$k++){
						if($loadout[$k]->Chassis_ID == 77733 || $loadout[$k]->Chassis_ID == 82394 || $loadout[$k]->Chassis_ID == 31334){
							//ignore the training frame
						}else{
							//Break each loadout into its own cache
							Cache::forever($cache_key_loadouts . '_' . $loadout[$k]->Chassis_ID,$loadout[$k]);
						};
					};
					//Cache the loadout md5 so we can call it again later
					Cache::forever($cache_key_loadouts . '_md5',$loadout_md5);
					//and finally set loadout=1 so we know to display equipped gear data
					$loadout=1;
				};
			};
		};

        //progress (feat. obama)
		if(isset($web_prefs['show_progress'])){
			if($web_prefs['show_progress'] == 1){
				$progress = Progress::where('db_id','=',$player->db_id)->order_by('updated_at','DESC')->first();
			}else{
				$progress = null;
			};
		}else{
			$progress = Progress::where('db_id','=',$player->db_id)->order_by('updated_at','DESC')->first();
		};

        /*
            Mappings:
                $progress->xp[$i].sdb_id = ItemTypeId for chassis
                $progress->xp[$i].name = battle frame name
                $progress->xp[$i].lifetime_xp = amount of xp gained overall
                $progress->xp[$i].current_xp = currently allocated XP (WARNING: key may not exist!)
                $progress->chassis_id = current chassis ID, use this to identify what battleframe the player was last using
                $progress->unlocks.array() = certificate array for currently equipped chassis
        */
		if($progress){
			//Progression graph builder
			$player_progresses_cache_key = $player->db_id . "_graph";
			$player_id = $player->db_id;
			$frame_progress_javascript = array();
			if(Cache::has($player_progresses_cache_key)){
				//pull from cache
				$frame_progress_javascript = Cache::get($player_progresses_cache_key);
			}else{
				//don't cache the query, that wont help us; cache the javascript output for highcharts instead.
				$player_progresses = DB::query('SELECT DISTINCT(`db_id`),`entry`,unix_timestamp(`updated_at`) AS `updated_at` FROM `progresses` WHERE db_id= ' . $player_id . '  AND `created_at` > DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY `created_at` ORDER BY `updated_at` ASC LIMIT 7');

				//check chassis_id exists
				if(!empty($player_progresses)){
					$check_chassis = unserialize($player_progresses[0]->entry);
				}else{
					$check_chassis = "";
					$check_chassis_id = false;
				};
				if(!isset($check_chassis->chassis_id)){
					$check_chassis_id = false;
				}else{
					$check_chassis_id = true;
				}

				if($player_progresses && $check_chassis_id){
					$frame_progress = array();
					for($i=0;$i<count($player_progresses);$i++){
						$day_progress=unserialize($player_progresses[$i]->entry);
						unset($day_progress->unlocks);
						foreach($day_progress->xp as $key=>$value){
							if($value->sdb_id == 77733 || $value->sdb_id == 82394 || $value->sdb_id == 31334){
								//ignore training frame
							}else{
								$frame_progress[$value->name][$player_progresses[$i]->updated_at] = $value->lifetime_xp;
							};
						};
					};

					//Make it json datas
					$frame_progress_javascript = array();
					foreach($frame_progress as $battle_frame_name=>$value){
						$xp_data_string = "[";
						foreach($value as $day=>$xp_amt){
							$xp_data_string .= "[" . ($day)*1000 . "," . $xp_amt . "],";
							$frame_progress_javascript[$battle_frame_name] = rtrim($xp_data_string, ",") . "]";
						};
					};
				}else{
					$frame_progress_javascript = null;
					$progress = false;
				};
				//build cache
				Cache::put($player_progresses_cache_key, $frame_progress_javascript, 30);
			};
		}else{
			$frame_progress_javascript = null;
            $progress = false;
		};

		//Frames & Unlocks
        if($progress){
			$cache_key_progress = $player->db_id . "_progress";
			$progress = $progress->entry;
			$progress_md5 = md5($progress);
			if(Cache::Get($cache_key_progress . "_md5") != $progress_md5){
				$progress = unserialize($progress);
				if(isset($progress->xp)){
					$battle_frame_id_list = array();
					for($i=0;$i<count($progress->xp);$i++){
						$battle_frame_id_list[$i] = $progress->xp[$i]->sdb_id;
					};
					$battle_frame_images = hWebIcon::where(
						function($query) use($battle_frame_id_list){
								$query->where('version','=', Base_Controller::getVersionDate());
								$query->where_in('itemTypeId', $battle_frame_id_list);
						}
					)->get();
					//set some cache
					Cache::forever($cache_key_progress . "_battleframe_images",$battle_frame_images);
				}else{
					$battle_frame_images = null;
				};
				if(isset($web_prefs['show_unlocks'])){
					$show_unlocks = $web_prefs['show_unlocks'];
				}else{
					//Assume show unlocks is 1
					$show_unlocks = 1;
				};
				if((isset($progress->unlocks)) && ($show_unlocks == 1)){
					//VERSION 0.6 CHECK (Array = BAD, Object = Good!)
					if(gettype($progress->unlocks) == "array"){
						//NOPE.
						$battle_frame_unlocks = null;
					}else if(gettype($progress->unlocks) == "object"){
						//Looks like 0.7+ to me!
						//Create a cache for each frame unlock set
						foreach($progress->unlocks as $chassis_id=>$cert_array){
							if($chassis_id == 77733 || $chassis_id == 82394 || $chassis_id == 31334){
								//ignore training frame
							}else{
								Cache::forever($cache_key_progress . "_" . $chassis_id,$cert_array);
							};
						};
					};
				};
				//Cache the progress variable so we can do a quick compare on a later load
				Cache::forever($cache_key_progress, $progress);
				Cache::forever($cache_key_progress . "_md5", $progress_md5);
			}else{
				//Assign cache values to local variable names.
				$battle_frame_images = Cache::Get($cache_key_progress . "_battleframe_images");
				$progress = Cache::Get($cache_key_progress);
			};
		}else{
			$battle_frame_unlocks = null;
		};

        return View::make('player.player_profile')
            ->with('title', 'Player Profile for ' . $playerName)
            ->with(compact('player'))
            ->with(compact('locations'))
            ->with(compact('nearestloc'))
            ->with(compact('army'))
            ->with(compact('inventory'))
			->with(compact('player_raw_resources'))
            ->with(compact('player_refined_resources'))
			->with(compact('player_crystite_amount'))
            ->with(compact('loadout'))
            ->with(compact('battle_frame_unlocks'))
            ->with(compact('progress'))
			->with(compact('frame_progress_javascript'))
            ->with(compact('battle_frame_images'))
			->with(compact('web_prefs'))
            ->with(compact('spotter'));
    }

	public function get_player_profile_chassis($name,$chassis_id){

		//quality control (get it?)
		function quality_control($quality){
			switch($quality){
				case 0:
					return "common";
					break;
				case 1000:
					return "legendary";
					break;
				case $quality >= 901:
					return "epic";
					break;
				case $quality >= 701:
					return "rare";
					break;
				case $quality >= 401:
					return "uncommon";
					break;
				default:
					return "common";
					break;
			};
		};

        //searching by name, null name = no buenos
        if($name == NULL) {
            return Response::error('404');
        }else{
            $input_name = urldecode($name);
        }

        //Look up the player, latest to account for deletes with the same name
        $player = Player::where('name','=',$input_name)
            ->order_by('created_at', 'DESC')
            ->first();

        //no player found, why not search?
        if(!$player) {
            return Response::error('404');
        }

		//Pull this chassis_id loadout from cache
		$cache_key_loadouts = $player->db_id . "_loadouts";
		$loadout = Cache::Get($cache_key_loadouts . '_' . $chassis_id);
		$cache_set_check = Cache::Get($cache_key_loadouts . "_" . $chassis_id . "_md5");
		if($cache_set_check == md5(serialize($loadout))){
			//load this chassis entirely from cache
			$player_item_abilities = Cache::Get($cache_key_loadouts . "_" . $chassis_id . "_abilities");
			$player_item_gear = Cache::Get($cache_key_loadouts . "_" . $chassis_id . "_gear");
			$player_item_weapons = Cache::Get($cache_key_loadouts . "_" . $chassis_id . "_weapons");
		}else{
			//List of "secret" gear we don't want displayed
			$processor_framemods = array(75780,75782,75784,75786,75915,75916,75917,75918,75878,75691,76013,76014,76015,76016,76017);
			$player_gear_sdb_id = array();
			//Add all of our gear and abilities to the list
			for($i=0;$i<count($loadout->Gear);$i++){
				if(isset($loadout->Gear[$i]->info->item_sdb_id)){
					$player_gear_sdb_id[$i] = $loadout->Gear[$i]->info->item_sdb_id;
				};
			};
			//Now add weapons to that
			for($i=0;$i<count($loadout->Weapons);$i++){
				if(isset($loadout->Weapons[$i]->info->item_sdb_id)){
					$player_gear_sdb_id[] = $loadout->Weapons[$i]->info->item_sdb_id;
				};
			};
			$player_gear = array();
			if(!empty($player_gear_sdb_id)){
				$player_gear = hWebIcon::where(
					function($query) use($player_gear_sdb_id){
							$query->where_in('itemTypeId', $player_gear_sdb_id);
							$query->where('version','=', Base_Controller::getVersionDate());
					}
				)->get();
				//reindex the get result
				$player_gear_base_item = array();
				foreach ($player_gear_sdb_id as $id){
					foreach($player_gear as $item){
						if($item->itemtypeid == $id){
							$player_gear_base_item[] = $item;
						}
					}
				}
				//remove player_gear variable;
				$player_gear = null;
			}else{
				$player_gear_base_item = null;
			};

			$gear_counter = 0;
			$ability_counter = 0;
			$weapon_counter = 0;
			$player_item_abilities = array();
			$player_item_gear = array();
			$player_item_weapons = array();
			for($i=0;$i<count($player_gear_base_item);$i++){
				$player_gear_icons[$i] = $player_gear_base_item[$i]->asset_path;
				//the gear is either going to be an ability, frame mod or weapon
				if(isset($player_gear_base_item[$i]->backpack_id)){
					//do nothing we don't display backpacks
				};
				if(isset($player_gear_base_item[$i]->abilitymodule_id)){
					//we has an ability
					$temp_ability_id = $player_gear_base_item[$i]->attributes['itemtypeid'];
					$player_equipped_ability_info = AbilityModule::where(
						function($query) use($temp_ability_id){
							$query->where('itemTypeId','=',$temp_ability_id);
							$query->where('version','=', Base_Controller::getVersionDate());
						}
					)->first();
					$ability_base_info_reqs = hConstraint::where(
						function($query) use($temp_ability_id){
							$query->where('itemTypeId','=', $temp_ability_id);
							$query->where('version','=', Base_Controller::getVersionDate());
						}
					)->first();
					//Add check to see if slot index is 5 or higher
					$player_item_abilities[$ability_counter]['asset_path'] = $player_gear_base_item[$i]->asset_path;
					$player_item_abilities[$ability_counter]['name'] = $player_equipped_ability_info->attributes['name'];
					$player_item_abilities[$ability_counter]['desc'] = $player_equipped_ability_info->attributes['description'];
					//Add check to see if durability is over 1,000
					if(isset($loadout->Gear[$i]->info->durability)){
						$player_item_abilities[$ability_counter]['durability'] = $loadout->Gear[$i]->info->durability;
					}else{
						$player_item_abilities[$ability_counter]['durability'] = null;
					};
					//Add check to see if quality is over 1,000
					$player_item_abilities[$ability_counter]['quality'] = $loadout->Gear[$i]->info->quality;
					$player_item_abilities[$ability_counter]['quality_color'] = quality_control($loadout->Gear[$i]->info->quality);
					if(isset($loadout->Gear[$i]->info->attribute_modifiers)){
						$player_item_abilities[$ability_counter]['attribute_modifiers'] = $loadout->Gear[$i]->info->attribute_modifiers;
					}else{
						$player_item_abilities[$ability_counter]['attribute_modifiers'] = null;
					};
					//Add check to see if allocated power is over 100
					$player_item_abilities[$ability_counter]['allocated_power'] = $loadout->Gear[$i]->allocated_power;
					$player_item_abilities[$ability_counter]['base_constraint_info'] = $ability_base_info_reqs->attributes;
					$ability_counter++;
				};
				if(isset($player_gear_base_item[$i]->framemodule_id) && !in_array($player_gear_base_item[$i]->attributes['itemtypeid'], $processor_framemods)){
					//we has frame mod
					if($gear_counter < 9){
						$temp_framemod_id = $player_gear_base_item[$i]->attributes['itemtypeid'];
						$player_equipped_gear_info = FrameModule::where(
							function($query) use($temp_framemod_id){
								$query->where('itemTypeId','=', $temp_framemod_id);
								$query->where('version','=', Base_Controller::getVersionDate());
							}
						)->first();
						$gear_base_info_reqs = hConstraint::where(
							function($query) use($temp_framemod_id){
								$query->where('itemTypeId','=', $temp_framemod_id);
								$query->where('version','=', Base_Controller::getVersionDate());
							}
						)->first();
						$player_item_gear[$gear_counter]['asset_path'] = $player_gear_base_item[$i]->asset_path;
						$player_item_gear[$gear_counter]['name'] = $player_equipped_gear_info->attributes['name'];
						$player_item_gear[$gear_counter]['desc'] = $player_equipped_gear_info->attributes['description'];
						//assume zero if null.
						if(isset($loadout->Gear[$i]->info->quality)){
							$player_item_gear[$gear_counter]['quality'] = $loadout->Gear[$i]->info->quality;
						}else{
							$player_item_gear[$gear_counter]['quality'] = 0;
						};
						$player_item_gear[$gear_counter]['quality_color'] = quality_control($loadout->Gear[$i]->info->quality);
						if(isset($loadout->Gear[$i]->info->durability)){
							$player_item_gear[$gear_counter]['durability'] = $loadout->Gear[$i]->info->durability;
						}else{
							$player_item_gear[$gear_counter]['durability'] = null;
						};
						if(isset($loadout->Gear[$i]->info->attribute_modifiers)){
							$player_item_gear[$gear_counter]['attribute_modifiers'] = $loadout->Gear[$i]->info->attribute_modifiers;
						}else{
							$player_item_gear[$gear_counter]['attribute_modifiers'] = null;
						};
						$player_item_gear[$gear_counter]['base_constraint_info'] = $gear_base_info_reqs->attributes;
						$gear_counter++;
					}else{
						Log::warn("Player ($player->db_id) has more than eight frame items in loadouts: " . $player_gear_base_item[$i]);
					};
				};
				if(isset($player_gear_base_item[$i]->weapon_id)){
					//we has weapon
					if($weapon_counter < 2){
						$temp_weapon_id = $player_gear_base_item[$i]->attributes['itemtypeid'];
						$player_equipped_weapon_info = Weapon::where(
							function($query) use($temp_weapon_id){
								$query->where('itemTypeId','=', $temp_weapon_id);
								$query->where('version','=', Base_Controller::getVersionDate());
							}
						)->first();
						$weapon_base_info = hStat::where(
							function($query) use($temp_weapon_id){
								$query->where('itemTypeId','=', $temp_weapon_id);
								$query->where('version','=', Base_Controller::getVersionDate());
							}
						)->first();
						$weapon_base_info_reqs = hConstraint::where(
							function($query) use($temp_weapon_id){
								$query->where('itemTypeId','=', $temp_weapon_id);
								$query->where('version','=', Base_Controller::getVersionDate());
							}
						)->first();
						$player_item_weapons[$weapon_counter]['asset_path'] = $player_gear_base_item[$i]->asset_path;
						$player_item_weapons[$weapon_counter]['name'] = $player_equipped_weapon_info->attributes['name'];
						$player_item_weapons[$weapon_counter]['desc'] = $player_equipped_weapon_info->attributes['description'];
						if(isset($loadout->Weapons[$weapon_counter]->info->durability)){
							$player_item_weapons[$weapon_counter]['durability'] = $loadout->Weapons[$weapon_counter]->info->durability;
						}else{
							$player_item_weapons[$weapon_counter]['durability'] = null;
						};
						$player_item_weapons[$weapon_counter]['quality'] = $loadout->Weapons[$weapon_counter]->info->quality;
						$player_item_weapons[$weapon_counter]['quality_color'] = quality_control($loadout->Weapons[$weapon_counter]->info->quality);
						if(isset($loadout->Weapons[$weapon_counter]->info->attribute_modifiers)){
							$player_item_weapons[$weapon_counter]['attribute_modifiers'] = $loadout->Weapons[$weapon_counter]->info->attribute_modifiers;
						}else{
							$player_item_weapons[$weapon_counter]['attribute_modifiers'] = null;
						};
						$player_item_weapons[$weapon_counter]['base_info'] = $weapon_base_info->attributes;
						$player_item_weapons[$weapon_counter]['base_constraint_info'] = $weapon_base_info_reqs->attributes;
						$player_item_weapons[$weapon_counter]['allocated_power'] = $loadout->Weapons[$weapon_counter]->allocated_power;
						$weapon_counter++;
					}else{
						Log::warn("Player ($player->db_id) has more than two weapons in loadouts: " . $player_gear_base_item[$i]);
					};
				};
			};
			//cache the loadout md5
			Cache::forever($cache_key_loadouts . "_" . $chassis_id . "_md5",md5(serialize($loadout)));
			//cache the rest of the loadout to seperate files for easy loading
			Cache::forever($cache_key_loadouts . "_" . $chassis_id . "_abilities",$player_item_abilities);
			Cache::forever($cache_key_loadouts . "_" . $chassis_id . "_gear",$player_item_gear);
			Cache::forever($cache_key_loadouts . "_" . $chassis_id . "_weapons",$player_item_weapons);
		};

        return View::make('player.player_profile_chassis')
            ->with(compact('player_item_abilities'))
			->with(compact('player_item_gear'))
            ->with(compact('player_item_weapons'));
	}

	public function get_player_profile_unlocks($name,$chassis_id){
        //searching by name, null name = no buenos
        if($name == NULL) {
            return Response::error('404');
        }else{
            $input_name = urldecode($name);
        }

        //Look up the player, latest to account for deletes with the same name
        $player = Player::where('name','=',$input_name)
            ->order_by('created_at', 'DESC')
            ->first();

        //no player found, why not search?
        if(!$player) {
            return Response::error('404');
        }

		/*
			Unlock Mappings:
					MIN: 702 	MAX: 731
				Mass:
					721,720,719,718,717,702,705,706,707,708
				Power:
					722,723,724,725,726,703,709,710,711,712
				CPU:
					727,728,729,730,731,704,713,714,715,716
		*/
		$mass = array(721,720,719,718,717,702,705,706,707,708);
		$power = array(722,723,724,725,726,703,709,710,711,712);
		$cpu = array(727,728,729,730,731,704,713,714,715,716);

		//Only load from cache
		$current_battle_frame_unlocks = array();
		$cache_key_progress = $player->db_id . "_progress";
		$cert_array = Cache::Get($cache_key_progress . "_" . $chassis_id);
		$cache_set_check = Cache::Get($cache_key_progress . "_" . $chassis_id . "_results");
		if($cert_array){
			if($cache_set_check['md5'] == md5(serialize($cert_array))){
				//pull the results from cache
				$current_battle_frame_unlocks = $cache_set_check;
			}else{
				$current_battle_frame_unlocks['chassis_id'] = $chassis_id;
				$current_battle_frame_unlocks['mass'] = array();
				$current_battle_frame_unlocks['power'] = array();
				$current_battle_frame_unlocks['cpu'] = array();
				for($i=0;$i<count($cert_array);$i++){
					//OK GUYS - we assume that someone wont have something unlocked HIGHER than another (as it is a tier system)...
					if(false !== $key = array_search($cert_array[$i],$mass)){
						$unlock_id = $cert_array[$i];

						$current_battle_frame_unlocks['mass'][$key] = $cert_array[$i];
						$unlock_info = Certification::find($unlock_id);
						$current_battle_frame_unlocks['mass_name'][$key] = $unlock_info->attributes['name'];
						$current_battle_frame_unlocks['mass_icon'][$key] = $unlock_info->attributes['web_icon'];

					};
					if(false !== $key = array_search($cert_array[$i],$power)){
						$current_battle_frame_unlocks['power'][$key] = $cert_array[$i];
						$unlock_id = $cert_array[$i];

						$current_battle_frame_unlocks['power'][$key] = $cert_array[$i];
						$unlock_info = Certification::find($unlock_id);
						$current_battle_frame_unlocks['power_name'][$key] = $unlock_info->attributes['name'];
						$current_battle_frame_unlocks['power_icon'][$key] = $unlock_info->attributes['web_icon'];
					};
					if(false !== $key = array_search($cert_array[$i],$cpu)){
						$current_battle_frame_unlocks['cpu'][$key] = $cert_array[$i];
						$unlock_id = $cert_array[$i];

						$current_battle_frame_unlocks['cpu'][$key] = $cert_array[$i];
						$unlock_info = Certification::find($unlock_id);
						$current_battle_frame_unlocks['cpu_name'][$key] = $unlock_info->attributes['name'];
						$current_battle_frame_unlocks['cpu_icon'][$key] = $unlock_info->attributes['web_icon'];
					};
				};
				//cache the cert_array md5
				$current_battle_frame_unlocks['md5'] = md5(serialize($cert_array));
				//write the results to the cache
				Cache::forever($cache_key_progress . "_" . $chassis_id . "_results",$current_battle_frame_unlocks);
			};
		};

		return View::make('player.player_profile_unlocks')
            ->with(compact('current_battle_frame_unlocks'));
	}
	
	public function get_player_pve_stats($name,$time){
        //searching by name, null name = no buenos
        if($name == NULL) {
            return Response::error('404');
        }else{
            $input_name = urldecode($name);
        }

        //Look up the player, latest to account for deletes with the same name
        $player = Player::where('name','=',$input_name)
            ->order_by('created_at', 'DESC')
            ->first();

        //no player found, why not search?
        if(!$player) {
            return Response::error('404');
        };
		
		//Player website preferences
		$web_prefs = WebsitePref::where('db_id','=',$player->db_id)->first();
		if(isset($web_prefs->attributes)){
			$web_prefs = $web_prefs->attributes;
		};

		function stats_number_formatting($n){
			if($n>1000000000000) return round(($n/1000000000000),1).' T';
			else if($n>1000000000) return round(($n/1000000000),1).' B';
			else if($n>1000000) return round(($n/1000000),1).' M';
			else if($n>1000) return round(($n/1000),1).' K';
			return number_format($n);
		}

		//Global (technically) default for pve stats data, dont worry about setting it to null depending on webprefs, simply skip the logic.
		$pve_stats_data = "";
		$player_db_id = $player->db_id;
        $todays_date = gmdate("Y-m-d");
		
        //I worked hard once; it was awful.
        if( urldecode($time) == 'all' ) {

            //Check the cache all date to see if it's out of date
            if( Cache::has($player_db_id . '_pve_stats_all')) {
                $pve_stats_data = Cache::get($player_db_id . '_pve_stats_all');
            }else{

                //no cache, time to make some money, make mysql do work.  uh. gettin in shape.
                //Stats
                $pve_stats_bindings = array($player_db_id, $todays_date);

                $pve_stats_sql = "SELECT 
                    SUM(`accuracy`) AS 'accuracy', SUM(`damage_done`) AS 'damage_done', SUM(`damage_taken`) AS 'damage_taken', 
                    SUM(`deaths`) AS 'deaths', SUM(`drowned`) AS 'drowned', SUM(`headshots`) AS 'headshots', SUM(`healed`) AS 'healed', 
                    SUM(`incapacitated`) AS 'incapacitated', SUM(`primary_reloads`) AS 'primary_reloads', 
                    SUM(`primary_weapon_shots_fired`) AS 'primary_weapon_shots_fired', SUM(`revived`) AS 'revived', SUM(`revives`) AS 'revives', 
                    SUM(`scanhammer_kills`) AS 'scanhammer_kills', SUM(`secondary_reloads`) AS 'secondary_reloads', 
                    SUM(`secondary_weapon_shots_fired`) AS 'secondary_weapon_shots_fired', SUM(`suicides`) AS 'suicides'
                    FROM `pvestats` WHERE ( `db_id` = ? AND DATE(`created_at`) < DATE(?) )";
                $pve_stats_query = DB::query($pve_stats_sql, $pve_stats_bindings);

                $pve_events_sql = "SELECT
                    SUM(ares_missions_0) AS 'ares_missions_0', SUM(ares_missions_1) AS 'ares_missions_1', SUM(crashed_lgvs) AS 'crashed_lgvs',
                    SUM(crashed_thumpers) AS 'crashed_thumpers', SUM(holmgang_tech_completed) AS 'holmgang_tech_completed', SUM(lgv_races) AS 'lgv_races',
                    MIN(lgv_fastest_time_sunken_copa) AS 'lgv_fastest_time_sunken_copa', MIN(lgv_fastest_time_thump_copa) AS 'lgv_fastest_time_thump_copa',
                    MIN(lgv_fastest_time_copa_trans) AS 'lgv_fastest_time_copa_trans', MIN(lgv_fastest_time_copa_thump) AS 'lgv_fastest_time_copa_thump',
                    MIN(lgv_fastest_time_trans_sunken) AS 'lgv_fastest_time_trans_sunken', SUM(outposts_defended) AS 'outposts_defended',
                    SUM(strike_teams_0) AS 'strike_teams_0', SUM(strike_teams_1) AS 'strike_teams_1', SUM(strike_teams_2) AS 'strike_teams_2',
                    SUM(strike_teams_3) AS 'strike_teams_3', SUM(sunken_harbor_invasions_completed) AS 'sunken_harbor_invasions_completed',
                    SUM(thump_dump_invasions_completed) AS 'thump_dump_invasions_completed', SUM(tornados_3) AS 'tornados_3', SUM(tornados_4) AS 'tornados_4',
                    SUM(warbringers_3) AS 'warbringers_3', SUM(warbringers_4) AS 'warbringers_4', SUM(watchtowers_defended) AS 'watchtowers_defended',
                    SUM(watchtowers_retaken) AS 'watchtowers_retaken', SUM(raider_squads_defeated) AS 'raider_squads_defeated', SUM(chosen_death_squads) as 'chosen_death_squads'
                    FROM `pveevents` WHERE ( `db_id` = ? AND DATE(`created_at`) < DATE(?) )";
                $pve_events_query = DB::query($pve_events_sql, $pve_stats_bindings);

                $pve_kills_sql = "SELECT t1,t2,t3,t4 FROM `pvekills` WHERE ( `db_id` = ? AND DATE(`created_at`) < DATE(?) )";
                $pve_kills_query = DB::query($pve_kills_sql, $pve_stats_bindings);
                //PVE KILLS IS SPECIAL.  So let's sum up what we have to do.
                $pve_kills_datas = array( 't1'=>array(), 't2'=>array(), 't3'=>array(), 't4'=>array() );
                foreach ($pve_kills_query as $kills)
                {
                    //each t1,t2,t3,t4
                    foreach ($kills as $key => $value)
                    {
                        $kill = unserialize($value);
                        if( !empty($kill) ) {

                            foreach ($kill as $k => $v)
                            {
                                if( array_key_exists($k, $pve_kills_datas[$key]) ) {
                                    $temp = $pve_kills_datas[$key][$k] + $v;
                                    $pve_kills_datas[$key][$k] = $temp;
                                }else{
                                    $pve_kills_datas[$key][$k] = $v;
                                }
                                unset($kill);
                            }

                        }
                        unset($kills);
                    }
                }

                //Can has data?  For real?
                $pve_stats_data = array( 'stats'=>'', 'events'=>'', 'kills'=>'' );
                if( !empty($pve_stats_query) && !is_null($pve_stats_query[0]->accuracy) ) {
                    $pve_stats_data['stats'] = $pve_stats_query[0];
                }
                if( !empty($pve_events_query) && !is_null($pve_events_query[0]->ares_missions_0) ) {
                    $pve_stats_data['events'] = $pve_events_query[0];
                }
                if( !empty($pve_kills_datas) ) {
                    $pve_stats_data['kills'] = $pve_kills_datas;
                }

                //set cache to expire "tomorrow"
                $now = strtotime('now');
                $tomorrow = strtotime('tomorrow');
                $caches_expires = round( ($tomorrow - $now) / 60 );
                $cache_expires_minutes = ( $caches_expires > 0 ) ? $caches_expires : 1;

                Cache::put( $player_db_id . '_pve_stats_all', $pve_stats_data, $cache_expires_minutes );
            }//no all cache exists
        }//all

		
        //X5s did all that hard work... hard work is not Xtops approved.  We must slack off!
        //Your hard work is not recognized in Fort Kick Ass.
        if( urldecode($time) == 'today' ) {

            if( Cache::has($player_db_id . '_pve_stats_' . $todays_date) ) {
                $pve_stats_data = Cache::get( $player_db_id . '_pve_stats_' . $todays_date );
            }else{

                //Stats
                $pve_stats_query = PvEStat::where(function($query) use ($player_db_id, $todays_date) {
                    $query->where('db_id','=',$player_db_id);
                    $query->where(DB::raw('DATE(created_at)'), '=', $todays_date);
                })->first();
                //Events
                $pve_events_query = PvEEvent::where(function($query) use ($player_db_id, $todays_date) {
                    $query->where('db_id','=',$player_db_id);
                    $query->where(DB::raw('DATE(created_at)'), '=', $todays_date);
                })->first();
                //Kills
                $pve_kills_query = PvEKill::where(function($query) use ($player_db_id, $todays_date) {
                    $query->where('db_id','=',$player_db_id);
                    $query->where(DB::raw('DATE(created_at)'), '=', $todays_date);
                })->first();

                //Format the data, we don't want square pegs in round holes... but maybe round pegs in square holes?
                $pve_stats = new stdClass();
                $pve_stats_allowed_keys = array(
                    'accuracy','damage_done','damage_taken','deaths','drowned','headshots',
                    'healed','incapacitated','primary_reloads','primary_weapon_shots_fired','revived',
                    'revives','scanhammer_kills','secondary_reloads','secondary_weapon_shots_fired','suicides'
                );
                if( $pve_stats_query ) {
                    foreach($pve_stats_query->original as $key => $value)
                    {
                        if( in_array($key, $pve_stats_allowed_keys) ) {
                            $pve_stats->$key = $value;
                        }
                    }
                }

                $pve_events = new stdClass();
                $pve_events_allowed_keys = array(
                    'ares_missions_0','ares_missions_1','crashed_lgvs','crashed_thumpers','holmgang_tech_completed',
                    'lgv_races','lgv_fastest_time_sunken_copa','lgv_fastest_time_thump_copa','lgv_fastest_time_copa_trans',
                    'lgv_fastest_time_copa_thump','lgv_fastest_time_trans_sunken','outposts_defended',
                    'strike_teams_0','strike_teams_1','strike_teams_2','strike_teams_3','sunken_harbor_invasions_completed',
                    'thump_dump_invasions_completed','tornados_3','tornados_4','warbringers_3','warbringers_4',
                    'watchtowers_defended','watchtowers_retaken','raider_squads_defeated','chosen_death_squads'
                );
                if( $pve_events_query ) {
                    foreach($pve_events_query->original as $key => $value)
                    {
                        if( in_array($key, $pve_events_allowed_keys) ) {
                            $pve_events->$key = $value;
                        }
                    }
                }

                $pve_kills = new stdClass();
                $pve_kills_allowed_keys = array(
                    't1','t2','t3','t4'
                );
                if( $pve_kills_query ) {
                    foreach($pve_kills_query->original as $key => $value)
                    {
                        if( in_array($key, $pve_kills_allowed_keys) ) {
                            $pve_kills->$key = unserialize($value);
                        }
                    }
                }

                //Can has data?  For real?
                $pve_stats_data = array( 'stats'=>'', 'events'=>'', 'kills'=>'' );

                if( !empty($pve_stats) ) {
                    $pve_stats_data['stats'] = $pve_stats;
                }
                if( !empty($pve_events) ) {
                    $pve_stats_data['events'] = $pve_events;
                }
                if( !empty($pve_kills) ) {
                    $pve_stats_data['kills'] = $pve_kills;
                }

                Cache::put( $player_db_id . '_pve_stats_' . $todays_date, $pve_stats_data, 5 );
            }//no today cache exists

        }//today onry

        return View::make('player.player_profile_pve_stats')
            ->with(compact('pve_stats_data'))
			->with(compact('web_prefs'));
	}
	

    /*
     *  Show a players single battleframe
     *
     */
    public function get_single_battleframe($name = NULL, $frame_name = NULL)
    {

        //searching by name, null name = no buenos
        if($name == NULL || $frame_name == NULL) {
            return Response::error('404');
        }else{
            $input_name = urldecode($name);
        }

        $valid_frame_name = array('assault','firecat','tigerclaw','biotech','recluse','dragonfly','dreadnaught','rhino','mammoth','engineer','electron','bastion','recon','raptor','nighthawk','arsenal');
        if( !in_array($frame_name, $valid_frame_name) ) {
            return Response::error('404');
        }

        //Look up the player, latest to account for deletes with the same name
        $player = Player::where('name','=',$input_name)
            ->order_by('created_at', 'DESC')
            ->first();

        //no player found, why not search?
        if(!$player) {
            return Response::error('404');
        }

        //escape for output in title
        $playerName = htmlentities($player->name);

        //Player website preferences
        $web_prefs = WebsitePref::where('db_id','=',$player->db_id)->first();
        if(isset($web_prefs->attributes)){
            $web_prefs = $web_prefs->attributes;
        };

        //loadouts
        /*
            Mappings:
                $loadout->Gear[$i] = Currently equipped items array
                $loadout->Gear[$i]->slot_index
                $loadout->Gear[$i]->info->durability->pool
                $loadout->Gear[$i]->info->durability->current
                $loadout->Gear[$i]->info->attribute_modifiers[$k] = array of custom attributes for this item
                $loadout->Gear[$i]->info->quality = The quality of the crafted item
                $loadout->Gear[$i]->info->creator_guid = the creators unique player ID
                $loadout->Gear[$i]->info->item_sdb_id = The root item this was crafted from

                $loadout->Weapons[$i] = Currently equiped weapons array
                $loadout->Weapons[$i]->info->durability->pool
                $loadout->Weapons[$i]->info->durability->current
                $loadout->Weapons[$i]->info->attribute_modifiers[$k]
                $loadout->Weapons[$i]->info->quality
                $loadout->Weapons[$i]->info->creator_guid
                $loadout->Weapons[$i]->info->item_sdb_id
                $loadout->Weapons[$i]->allocated_power
                $loadout->Weapons[$i]->slot_index = Weapon slot, 0 is main hand, 1 is alt weapon
        */
        /*
            Attribute modifiers mapping:
                5   = Jet Energy Recharge
                6   = health
                7   = health regen
                12  = Run Speed
                29  = weapon splash radius
                35  = Energy (for jetting)
                37  = Jump Height
                950 = Max durability pool
                951 = Mass (unmodified - YOU MUST take the abs of this to get it to display correctly!)
                952 = Power (unmodified - YOU MUST take the abs of this to get it to display correctly!)
                953 = CPU (unmodified - YOU MUST take the abs of this to get it to display correctly!)
                956 = clip size
                954 = damage per round
                977 = Damage
                978 = Debuff Duration
                1121= Air Sprint

                Defaults for weapons are set in the "hstats" table.
        */
        if(isset($web_prefs['show_loadout'])){
            if($web_prefs['show_loadout'] == 1){
                $loadout = Loadout::where('db_id','=',$player->db_id)->first();
            }else{
                $loadout = null;
            };
        }else{
            $loadout = Loadout::where('db_id','=',$player->db_id)->first();
        };
        if($loadout){
            //Lets play the cache game, where all the stuff is stored locally and the points don't matter!
            $loadout = unserialize($loadout->entry);

            //VERSION 0.6 CHECKING (Array = Good, Object = BAD!)
            if(gettype($loadout) == "object"){
                //This is from version 0.6, we can no longer use this data.
                $loadout = null;
            };
            $cache_key_loadouts = $player->db_id . "_loadouts";
            $loadout_md5 = md5(serialize($loadout));
            if((Cache::Get($cache_key_loadouts . '_md5') != $loadout_md5) && ($loadout != null)){
                //Oh I am playing the game, the one that will take me to my end.

                //Make sure this isnt a terrible send
                if(isset($loadout[0]->Gear)){
                    for($k=0;$k<count($loadout);$k++){
                        if($loadout[$k]->Chassis_ID == 77733 || $loadout[$k]->Chassis_ID == 82394 || $loadout[$k]->Chassis_ID == 31334 ){
                            //ignore the training frame
                        }else{
                            //Break each loadout into its own cache
                            Cache::forever($cache_key_loadouts . '_' . $loadout[$k]->Chassis_ID,$loadout[$k]);
                        };
                    };
                    //Cache the loadout md5 so we can call it again later
                    Cache::forever($cache_key_loadouts . '_md5',$loadout_md5);
                    //and finally set loadout=1 so we know to display equipped gear data
                    $loadout=1;
                };
            };
        };

        //progress (feat. obama)
        if(isset($web_prefs['show_progress'])){
            if($web_prefs['show_progress'] == 1){
                $progress = Progress::where('db_id','=',$player->db_id)->order_by('updated_at','DESC')->first();
            }else{
                $progress = null;
            };
        }else{
            $progress = Progress::where('db_id','=',$player->db_id)->order_by('updated_at','DESC')->first();
        };

        /*
            Mappings:
                $progress->xp[$i].sdb_id = ItemTypeId for chassis
                $progress->xp[$i].name = battle frame name
                $progress->xp[$i].lifetime_xp = amount of xp gained overall
                $progress->xp[$i].current_xp = currently allocated XP (WARNING: key may not exist!)
                $progress->chassis_id = current chassis ID, use this to identify what battleframe the player was last using
                $progress->unlocks.array() = certificate array for currently equipped chassis
        */
		if($progress){
			//Progression graph builder
			$player_progresses_cache_key = $player->db_id . "_graph";
			$player_id = $player->db_id;
			$frame_progress_javascript = array();
			if(Cache::has($player_progresses_cache_key)){
				//pull from cache
				$frame_progress_javascript = Cache::get($player_progresses_cache_key);
			}else{
				//don't cache the query, that wont help us; cache the javascript output for highcharts instead.
				$player_progresses = DB::query('SELECT DISTINCT(`db_id`),`entry`,unix_timestamp(`updated_at`) AS `updated_at` FROM `progresses` WHERE db_id= ' . $player_id . '  AND `created_at` > DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY `created_at` ORDER BY `updated_at` ASC LIMIT 7');

				//check chassis_id exists
				if(!empty($player_progresses)){
					$check_chassis = unserialize($player_progresses[0]->entry);
				}else{
					$check_chassis = "";
					$check_chassis_id = false;
				};
				if(!isset($check_chassis->chassis_id)){
					$check_chassis_id = false;
				}else{
					$check_chassis_id = true;
				}

				if($player_progresses && $check_chassis_id){
					$frame_progress = array();
					for($i=0;$i<count($player_progresses);$i++){
						$day_progress=unserialize($player_progresses[$i]->entry);
						unset($day_progress->unlocks);
						foreach($day_progress->xp as $key=>$value){
							if($value->sdb_id == 77733 || $value->sdb_id == 82394 || $value->sdb_id == 31334){
								//ignore training frame
							}else{
								$frame_progress[$value->name][$player_progresses[$i]->updated_at] = $value->lifetime_xp;
							};
						};
					};

					//Make it json datas
					$frame_progress_javascript = array();
					foreach($frame_progress as $battle_frame_name=>$value){
						$xp_data_string = "[";
						foreach($value as $day=>$xp_amt){
							$xp_data_string .= "[" . ($day)*1000 . "," . $xp_amt . "],";
							$frame_progress_javascript[$battle_frame_name] = rtrim($xp_data_string, ",") . "]";
						};
					};
				}else{
					$frame_progress_javascript = null;
					$progress = false;
				};
				//build cache
				Cache::put($player_progresses_cache_key, $frame_progress_javascript, 30);
			};
		}else{
			$frame_progress_javascript = null;
            $progress = false;
		};

        //Frames & Unlocks
        if($progress){
            $progress = unserialize($progress->entry);
            $cache_key_progress = $player->db_id . "_progress";
            if(Cache::Get($cache_key_progress) != $progress){
                if(isset($progress->xp)){
                    $battle_frame_id_list = array();
                    for($i=0;$i<count($progress->xp);$i++){
                        $battle_frame_id_list[$i] = $progress->xp[$i]->sdb_id;
                    };
                    $battle_frame_images = hWebIcon::where(
                        function($query) use($battle_frame_id_list){
                                $query->where('version','=', Base_Controller::getVersionDate());
                                $query->where_in('itemTypeId', $battle_frame_id_list);
                        }
                    )->get();
                    //set some cache
                    Cache::forever($cache_key_progress . "_battleframe_images",$battle_frame_images);
                }else{
                    $battle_frame_images = null;
                };
                if(isset($web_prefs['show_unlocks'])){
                    $show_unlocks = $web_prefs['show_unlocks'];
                }else{
                    //Assume show unlocks is 1
                    $show_unlocks = 1;
                };
                if((isset($progress->unlocks)) && ($show_unlocks == 1)){
                    //VERSION 0.6 CHECK (Array = BAD, Object = Good!)
                    if(gettype($progress->unlocks) == "array"){
                        //NOPE.
                        $battle_frame_unlocks = null;
                    }else if(gettype($progress->unlocks) == "object"){
                        //Looks like 0.7+ to me!
                        //Create a cache for each frame unlock set
                        foreach($progress->unlocks as $chassis_id=>$cert_array){
                            if($chassis_id == 77733 || $chassis_id == 82394 || $chassis_id = 31334){
                                //ignore training frame
                            }else{
                                Cache::forever($cache_key_progress . "_" . $chassis_id,$cert_array);
                            };
                        };
                    };
                };
                //Cache the progress variable so we can do a quick compare on a later load
                Cache::forever($cache_key_progress, $progress);
            }else{
                //Assign cache values to local variable names.
                $battle_frame_images = Cache::Get($cache_key_progress . "_battleframe_images");
                $progress = Cache::Get($cache_key_progress);
            };
        }else{
            $battle_frame_unlocks = null;
        };

        return View::make('player.single_battleframe')
            ->with('title', 'Player Profile for ' . $playerName)
            ->with(compact('player'))
            ->with(compact('frame_name'))
            ->with(compact('loadout'))
            ->with(compact('battle_frame_unlocks'))
            ->with(compact('progress'))
            ->with(compact('frame_progress_javascript'))
            ->with(compact('battle_frame_images'))
            ->with(compact('web_prefs'));
    }


    public function get_stalker()
    {
        $locations = DB::query('SELECT * FROM `locations` GROUP BY db_id ORDER BY created_at DESC LIMIT 20');

        return View::make('player.stalker')
            ->with('title', 'Stalker')
            ->with(compact('locations'));
    }


}

?>
