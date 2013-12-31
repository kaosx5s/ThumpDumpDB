<?php

class Addon_V2_Controller extends Base_Controller {

    public $restful = true;

    private $logpath;

    private $user_ip;

    private $date;

    private $date_only;

    private $logdate;

    private $savedata;


    public function __construct()
    {
        $this->logpath = 'storage/logs/qa/';

        $this->user_ip = $_SERVER['REMOTE_ADDR'];

        $this->date    = date('Y-m-d H:i:s');

        $this->date_only= date('Y-m-d');

        $this->logdate = date('Y-m-d-H-i-s');

        $this->savedata= false;
    }


    public function get_download()
    {
        return View::make('home.addon')
            ->with('title','Download Addon');
    }

    public function get_update()
    {
        return View::make('home.addon_update')
            ->with('title','Addon Melder Update Support');
    }

    /*
     *  PLAYER INFO
     *
     */
    public function post_player()
    {
        $log_header = "addon_v2.php@player [" . $this->user_ip . "] - ($this->logdate): ";

        $line = Input::json();

        if($this->savedata){file_put_contents($this->logpath.$this->logdate.'_player.json', serialize($line));}

        $datlog = new DatLog();
            $datlog->ip = ip2long($this->user_ip);
            $datlog->db_id = ( isset($line->Player_ID) ) ? $line->Player_ID : NULL;
            $datlog->name = ( isset($line->Player_Name) ) ? $line->Player_Name : NULL;
            $datlog->category = 'player';
            $datlog->save();

        try {

            /*
             ********************************
             ** Validate Minimum Keys Exist**
             ********************************
             *  # Fail if invalid
             */

            //Minimum keys
            if(
                !isset($line->Inventory) ||
                !isset($line->Progress) ||
                !isset($line->Website_Prefs) ||
                !isset($line->Player_ID) ||
                !isset($line->Player_Name) ||
                !isset($line->Player_Instance) ||
                !isset($line->Loadouts) ||
                !isset($line->Player_EID) ||
                !isset($line->Player_Coords) ||
                !isset($line->ArmyID) ||
                !isset($line->Battle_Frame)
            ) { throw new Exception('Player did not send all keys.'); }


            /*
             ********************************
             ** Validate Player ID (db_id) **
             ********************************
             *  # Fail if invalid
             *  @ sets $player_db_id
             *
             *  -check digits only
             *  -19 characters long
             *  -Begins with a 9
             */

            //Digits only?
            if( preg_match('/[^0-9]/', $line->Player_ID) ) {
                throw new Exception("Player ID was more than just numbers: " . $line->Player_ID);
            }

            //19 Characters long?
            if( strlen($line->Player_ID) !== 19 ) {
                throw new Exception("Player ID was not 19 characters: " . $line->Player_ID);
            }

            //Starts with a 9?
            if( substr($line->Player_ID, 0, 1) !== '9' ) {
                throw new Exception("Player ID did not start with a 9: " . $line->Player_ID);
            }

            $player_db_id = $line->Player_ID;


            /*
             **********************************
             ** Validate Player Name/Army Tag**
             **********************************
             *  # Warn if invalid
             *  @ sets $player_name
             *  @ sets $player_army_tag
             *
             *  -15 + 6 characters or less ~23 to be safe
             *  -We don't care about character content as much as FF does
             */

            //Check if name is way too long, or too short
            if( strlen($line->Player_Name) > 30 || trim(strlen($line->Player_Name)) < 3 ) {
                throw new Exception("Player ($player_db_id) name was longer than 30 characters, should be <= 23: " . $line->Player_Name);
            }

            //Warn if longer than expected (that's what she said)
            //But allow for armytag length too
            if( strlen($line->Player_Name) > 27 ) {
                Log::warn("Player ($player_db_id) sent a character name longer than max expected length of 27: " . $line->Player_Name);
            }

            //Name is ok, but does it have an army tag?  Does it blend?
            if( strpos($line->Player_Name, ']') ) {

                $last = strripos($line->Player_Name, ']') + 1;
                $player_army_tag = substr($line->Player_Name, 0, $last);
                $player_name = trim(substr($line->Player_Name, $last));

            }else{
                $player_army_tag = NULL;
                $player_name = $line->Player_Name;
            }



            /*
             ***********************************
             ** Check existing name/db_id     **
             ***********************************
             *  Does the db_id exist?
             *  Does the submitted name match existing?
             */
            $check_existing_sql = Player::where('db_id','=',$player_db_id)->order_by('created_at','DESC')->first();
            if( $check_existing_sql ) {
              if( $check_existing_sql->name != $player_name ) {
                throw new Exception("Existing db_id does not match existing name: (player sent:{$player_db_id}|{$player_name};existing:{$check_existing_sql->db_id}|{$check_existing_sql->name};)");
                Log::warn("Existing db_id does not match existing name({$player_db_id}|in:{$player_name}|existing:{$check_existing_sql->name})");
              }
            }



            /*
             ********************************
             ** Validate Website Prefs     **
             ********************************
             *  # Allow, but only if valid
             *  @ (self contained)
             *
             *  -Contain all keys
             *  -Only 0 or 1 values
             */

            //Minimum keys
            if(
                isset($line->Website_Prefs) &&
                isset($line->Website_Prefs->show_loadout) &&
                isset($line->Website_Prefs->show_inventory) &&
                isset($line->Website_Prefs->show_progress) &&
                isset($line->Website_Prefs->show_unlocks) &&
                isset($line->Website_Prefs->show_pve_kills) &&
                isset($line->Website_Prefs->show_pve_stats) &&
                isset($line->Website_Prefs->show_pve_events) &&
                isset($line->Website_Prefs->show_location)
            ) {
                //Get ready to save, check for boolean digit values only
                $save_prefs = true;
                foreach ($line->Website_Prefs as $pref)
                {
                    //allowed values 1|0
                    if( $pref !== 0 && $pref !== 1) {
                        $save_prefs = false;
                    }
                }

                //because not everyone uses the latest version
                $show_workbench = ( isset($line->Website_Prefs->show_workbench) ) ? $line->Website_Prefs->show_workbench : 0;
                $show_craftables = ( isset($line->Website_Prefs->show_craftables) ) ? $line->Website_Prefs->show_craftables : 0;
                $show_market_listings = ( isset($line->Website_Prefs->show_market_listings) ) ? $line->Website_Prefs->show_market_listings : 0;

                //IF Website Prefs are appropriate, right click, save as
                if( $save_prefs ) {
                    $prefs = $line->Website_Prefs;

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

                    $bindings_webpref = array($player_db_id,
                        $prefs->show_loadout, $prefs->show_progress, $prefs->show_inventory, $prefs->show_unlocks,
                        $prefs->show_pve_kills, $prefs->show_pve_stats, $prefs->show_pve_events, $prefs->show_location,
                        $show_workbench, $show_craftables, $show_market_listings, 
                        $this->date, $this->date,
                        $prefs->show_loadout, $prefs->show_progress, $prefs->show_inventory, $prefs->show_unlocks,
                        $prefs->show_pve_kills, $prefs->show_pve_stats, $prefs->show_pve_events, $prefs->show_location,
                        $show_workbench, $show_craftables, $show_market_listings, 
                        $this->date);

                    DB::query($query_webpref, $bindings_webpref);
                }else{
                    Log::warn("Player ($player_db_id) sent invalid Website Prefs values: " . implode(',', (array) $line->Website_Prefs) );
                }

            }else{
                Log::warn( $log_header . "Player ($player_db_id) did not send all website prefs or correct keys: " );
            }





            /*
             **********************************
             ** Validate Instance ID         **
             **********************************
             *  # Fail if not numeric, or 9|10 long
             *  @ sets $player_instance_id
             *
             *  -Greater than 0
             *  -Expected length 9 or 10, but others possible
             */

            //Not negative 1 for some reason
            if( $line->Player_Instance === -1 ) {
                throw new Exception("Player ($player_db_id) Instance ID was -1: " . $line->Player_Instance);
            }

            //Sending only digits
            if( preg_match('/[^0-9]/', $line->Player_Instance) ) {
                throw new Exception("Player ($player_db_id) Instance ID contained something other than a number: " . $line->Player_Instance);
            }

            //Log if non regular instanceid (can be legit)
            if( strlen($line->Player_Instance) < 5 ) {
                Log::warn("Player ($player_db_id) Instance ID was less than 5 digits long: " . $line->Player_Instance);
            }

            $player_instance_id = $line->Player_Instance;


            /*
             **********************************
             ** Validate Player EID          **
             **********************************
             *  # Fail is non numeric, NULL otherwise
             *  @ sets $player_eid
             *
             *  -Greater than 0
             *  -Expected length 9 or 10, but others possible
             */

            //Sent only numbers
            if( preg_match('/[^0-9]/', $line->Player_EID) ) {
                throw new Exception("Player ($player_db_id) EID contained something other than a number: " . $line->Player_EID);
            }

            //EID should be as long as a db_id
            if( strlen($line->Player_EID) !== 19 ) {
                Log::warn("Player ($player_db_id) EID was not the expected 19 digits long: " . $line->Player_EID);
                $line->Player_EID = NULL;
            }

            $player_eid = $line->Player_EID;


            /*
             **********************************
             ** Validate Army ID             **
             **********************************
             *  # Fail if set and not nil or numeric
             *  @ sets $player_army_id
             *
             *  -Greater than 0
             *  -Expected length 9 or 10, but others possible
             */

            //ArmyID nil -> null
            if( isset($line->ArmyID) ) {

                if( $line->ArmyID == 'nil' || $line->ArmyID == false ){
                    $player_army_id = NULL;

                }else{

                    //Numbers only plx
                    if( preg_match('/[^0-9]/', $line->ArmyID) ){
                        throw new Exception("Player ($player_db_id) ArmyID was not numeric and not nil: " . $line->ArmyID);
                    }else{
                        $player_army_id = $line->ArmyID;
                    }
                }

            }else{
                $player_army_id = NULL;
            }


            /*
             **********************************
             ** Validate Current Archetype   **
             **********************************
             *  # Fail to null
             *  @ sets $player_current_archetype
             *
             *  -in array expected
             *  -guardian,recon,medic,bunker,berzerker,unknown,NULL
             */
            $expected_archetypes = array('guardian','recon','medic','bunker','berzerker','unknown');

            //Valid archetype?  I don't know what I expected
            if( !in_array($line->Battle_Frame, $expected_archetypes) ){
                $player_current_archetype = NULL;
                Log::warn("Player ($player_db_id) sent unexpected battleframe: " . $line->Battle_Frame);
            }else{
                $player_current_archetype = $line->Battle_Frame;
            }

            /*
             **********************************
             ** Validate Player Region       **
             **********************************
             *  # Fail to null
             *  @ sets $player_region
             *
             *  Is alpha/-/num..
             */

            //Valid region; but not the nether region
            if( isset($line->Player_Region) ){
                $player_region = preg_replace('/[^A-Z0-9\-]/i', '', $line->Player_Region);
            }else{
                $player_region = "";
            }


            /*
             **********************************
             ** Validate Player Coords       **
             **********************************
             *  # Fail if out of bounds
             *  @ sets $player_coord_x
             *  @ sets $player_coord_y
             *  @ sets $player_coord_z
             *  @ sets $spotter_db_id
             *
             *  -check keys (x,y,z)
             *  -coords within 0->3328 +/-
             *  -spotter_db_id is db_id
             */
/*
            $spotter_db_id = $player_db_id;

            //Minimum keys
            if(
                !isset($line->Player_Coords->x) ||
                !isset($line->Player_Coords->y) ||
                !isset($line->Player_Coords->z) ||
                !isset($line->Player_Coords->chunkX) ||
                !isset($line->Player_Coords->chunkY)
            ) { throw new Exception("Player ($player_db_id) didn't send all location keys");

            }else{

                $save_loc = true;
                $log_loc = false;
                foreach ($line->Player_Coords as $k => $v)
                {
                    //float values for locations
                    if( $k == 'x' || $k == 'y' || $k == 'z' ) {
                        if( !is_float($v) && $v !== 0 ) {
                            $log_loc = true;
                        }
                    }else{
                    //integers for chunks
                        if( !is_numeric($v) ) {
                            $log_loc = true;
                        }
                    }
                }

                //We're going to allow all locations, but log unexpected ones
                if( $save_loc ) {
                    $loc =  new Location;
                        $loc->db_id = $player_db_id;
                        $loc->spotter_db_id = $spotter_db_id;
                        $loc->name = $player_name;
                        $loc->instanceId = $player_instance_id;
                        $loc->current_archetype = $player_current_archetype;
                        $loc->coord_x = $line->Player_Coords->x;
                        $loc->coord_y = $line->Player_Coords->y;
                        $loc->coord_z = $line->Player_Coords->z;
                        $loc->chunkX = $line->Player_Coords->chunkX;
                        $loc->chunkY = $line->Player_Coords->chunkY;

                        $loc->save();

                        if($log_loc) {
                            Log::warn("Player ($player_db_id) sent unexpected coordinates: (locid: {$loc->id}) " . implode(',', (array) $line->Player_Coords) );
                        }
                }else{
                    Log::warn("Player ($player_db_id) sent out of bounds coordinates: " . implode(',', (array) $line->Player_Coords) );
                }

            }
*/

            //-------------------------------------------------------------------------------------------------------------
            //  UPDATE/INSERT PLAYER, FOLLOW WITH INVENTORY/PROGRESS/LOADOUTS ON SUCCESS ----------------------------------
            //-------------------------------------------------------------------------------------------------------------

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
                $player_name, $player_army_tag, $player_instance_id, $player_db_id, $player_eid,
                $player_army_id, $player_current_archetype, $player_region,
                $this->date, $this->date,
                $player_name, $player_army_tag, $player_instance_id, $player_army_id,
                $player_eid, $player_current_archetype, $is_addon_user, $player_region,
                $this->date
            );

            try {
                if( !DB::query($query_add_player,$bindings) ){
                    throw new Exception('Add/Update player query failed');
                }


                /*
                 **********************************
                 ** Set Inventory If Not Empty   **
                 **********************************
                 */
                if(!empty($line->Inventory)) {
                    //Check the last send for inventory, see if we need to update, or make a new row
                    $last_inventory_update = Inventory::where(function($query) use ($player_db_id) {
                        $query->where('db_id','=', $player_db_id);
                        $query->where( DB::raw('DATE(updated_at)') , '=', $this->date_only );
                    })->order_by('id','desc')->first('id');

                    if( $last_inventory_update ) {
                        $query_update_inv = "UPDATE `inventories` SET `inventory` = ?, updated_at = ? WHERE `id` = ?";
                        $bindings_update_inventory = array( serialize($line->Inventory), $this->date, $last_inventory_update->id);

                        if( DB::query($query_update_inv, $bindings_update_inventory) === false ){
                            throw new Exception('Error updating inventory.');
                        }

                    }else{
                        $inv = new Inventory;
                            $inv->db_id = $player_db_id;
                            $inv->inventory = serialize($line->Inventory);
                        if( !$inv->save() ){
                            throw new Exception('Add inventory query failed:');
                        }
                    }
                }//not empty inventory



                /*
                 **********************************
                 ** Set Progress If Not Empty   **
                 **********************************
                 */
                if(!empty($line->Progress)) {
                    //Check the last send for progress, see if we need to update, or make a new row
                    $last_progress_update = Progress::where(function($query) use ($player_db_id) {
                        $query->where('db_id','=', $player_db_id);
                        $query->where( DB::raw('DATE(updated_at)') , '=', $this->date_only );
                    })->order_by('id','desc')->first('id');

                    if( $last_progress_update ) {
                        $query_update_prog = "UPDATE `progresses` SET `entry` = ?, updated_at = ? WHERE `id` = ?";
                        $bindings_update_progress = array( serialize($line->Progress), $this->date, $last_progress_update->id);

                        if( DB::query($query_update_prog, $bindings_update_progress) === false ){
                            throw new Exception('Error updating progress.');
                        }

                    }else{
                        $progress = new Progress;
                            $progress->db_id = $player_db_id;
                            $progress->entry = serialize($line->Progress);
                        if( !$progress->save() ){
                            throw new Exception('Add progress query failed:');
                        }
                    }
                }//not empty progress


                /*
                 **********************************
                 ** Set Loadouts If Not Empty   **
                 **********************************
                 */
                if(!empty($line->Loadouts)) {
                    $query_add_loadout = "INSERT INTO `loadouts` ";
                    $query_add_loadout .= "(db_id, entry, created_at, updated_at) ";
                    $query_add_loadout .= "VALUES (?, ?, ?, ?) ";
                    $query_add_loadout .= "ON DUPLICATE KEY UPDATE ";
                    $query_add_loadout .= "entry = ?, updated_at = ?";

                    $loadout_entry = serialize($line->Loadouts);

                    $bindings = array(
                        $player_db_id, $loadout_entry, $this->date, $this->date,
                        $loadout_entry, $this->date
                    );

                    if( !DB::query($query_add_loadout, $bindings) ){
                        throw new Exception('Add loadout query failed:');
                    }
                }



            }catch (Exception $e) {
                Log::info($log_header.$e->getMessage());
                file_put_contents($this->logpath.$this->logdate.'_bad_player.json', serialize($line));
            }



        }catch (Exception $e){
            Log::info($log_header.$e->getMessage());
            file_put_contents($this->logpath.$this->logdate.'_bad_player.json', serialize($line));
        }


        return Response::json(array('ThumpDumpDB','(Player) Thanks'));
    }


    /*
     *  NEARBY PLAYER INFOS
     *
     */
    public function post_nearbyplayers()
    {
        $log_header = "addon_v2.php@nearby [" . $this->user_ip . "] - ($this->logdate): ";

        $line = Input::json();

        $datlog = new DatLog();
            $datlog->ip = ip2long($this->user_ip);
            $datlog->spotter_db_id = ( isset($line->{'Nearby Player Info'}[0]->Spotter_ID) ) ? $line->{'Nearby Player Info'}[0]->Spotter_ID : NULL;
            $datlog->category = 'nearby';
            $datlog->save();

        if($this->savedata){file_put_contents($this->logpath.$this->logdate.'_nearby.json', serialize($line));}

        try {

            /*
             ********************************
             ** Does it even exist?        **
             ********************************
             */
            if(isset($line->{'Nearby Player Info'})){
                $nearbyplayers = $line->{'Nearby Player Info'};

                //Make sure it's not empty
                if( count($nearbyplayers) == 0 ) {
                    throw new Exception("Player sent 0 peoples to /nearbyplayers, this shouldn't happen");
                }
            }else{
                throw new Exception('No nearby player key found');
            }


            foreach ($nearbyplayers as $nearby)
            {

                /*
                 ********************************
                 ** Check minimum keys per spot**
                 ********************************
                 *  ArmyID not always set
                 */

                if(
                    !isset( $nearby->Player_Instance ) ||
                    !isset( $nearby->Spotter_ID ) ||
                    !isset( $nearby->Battleframe ) ||
                    !isset( $nearby->Coords ) ||
                    !isset( $nearby->Player_ID ) ||
                    !isset( $nearby->Player_Name )
                ) { throw new Exception("Nearby player entry did not send all keys"); }


                /*
                 ********************************
                 ** Validate Player ID (db_id) **
                 ********************************
                 *  # Fail if invalid
                 *  @ sets $nearby_db_id
                 *
                 *  -check digits only
                 *  -19 characters long
                 *  -Begins with a 9
                 */

                //Digits only?
                if( preg_match('/[^0-9]/', $nearby->Player_ID) ) {
                    throw new Exception("Nearby player ID was more than just numbers: " . $nearby->Player_ID);
                }

                //19 Characters long?
                if( strlen($nearby->Player_ID) !== 19 ) {
                    throw new Exception("Nearby player ID was not 19 characters: " . $nearby->Player_ID);
                }

                //Starts with a 9?
                if( substr($nearby->Player_ID, 0, 1) !== '9' ) {
                    throw new Exception("Nearby player ID did not start with a 9: " . $nearby->Player_ID);
                }

                $nearby_db_id = $nearby->Player_ID;


                /*
                 **********************************
                 ** Validate Player Name         **
                 **********************************
                 *  # Warn if invalid
                 *  @ sets $nearby_name
                 *  @ sets $nearby_army_tag
                 *
                 *  -15 + 6 characters or less ~23
                 *  -We don't care about character content as much as FF does
                 */

                //Check if name is way too long
                if( strlen($nearby->Player_Name) > 30 || strlen($nearby->Player_Name) < 3 ) {
                    throw new Exception("Player ($nearby_db_id) name was longer than 30 characters, should be <= 23: " . $nearby->Player_Name);
                }

                //Warn if longer than expected (that's what she said)
                //But allow for armytag length too
                if( strlen($nearby->Player_Name) > 27 ) {
                    Log::warn("Player ($nearby_db_id) sent a character name longer than max expected length of 27: " . $nearby->Player_Name);
                }

                //Name is ok, but does it have an army tag?  Does it blend?
                if( strpos($nearby->Player_Name, ']') ) {

                    $last = strripos($nearby->Player_Name, ']') + 1;
                    $nearby_army_tag = substr($nearby->Player_Name, 0, $last);
                    $nearby_name = trim(substr($nearby->Player_Name, $last));

                }else{
                    $nearby_army_tag = NULL;
                    $nearby_name = $nearby->Player_Name;
                }


                /*
                 **********************************
                 ** Validate Army ID             **
                 **********************************
                 *  # Fail if set and not nil or numeric
                 *  @ sets $player_army_id
                 *
                 *  -Greater than 0
                 *  -Expected length 9 or 10, but others possible
                 */

                //ArmyID nil -> null
                if( isset($nearby->ArmyID) ) {

                    if( $nearby->ArmyID == 'nil' || $nearby->ArmyID == false ){
                        $nearby_army_id = NULL;

                    }else{

                        //Numbers only plx
                        if( preg_match('/[^0-9]/', $nearby->ArmyID) ){
                            throw new Exception("Player ($nearby_db_id) ArmyID was not numeric and not nil: " . $nearby->ArmyID);
                        }else{
                            $nearby_army_id = $nearby->ArmyID;
                        }
                    }

                }else{
                    $nearby_army_id = NULL;
                }


                /*
                 **********************************
                 ** Validate Instance ID         **
                 **********************************
                 *  # Fail if not numeric, or 9|10 long
                 *  @ sets $nearby_instance_id
                 *
                 *  -Greater than 0
                 *  -Expected length 9 or 10, but others possible
                 */

                //Not negative 1 for some reason
                if( $nearby->Player_Instance === -1 ) {
                    throw new Exception("Nearby player ($nearby_db_id) Instance ID was -1: " . $nearby->Player_Instance);
                }

                //Sending only digits
                if( preg_match('/[^0-9]/', $nearby->Player_Instance) ) {
                    throw new Exception("Nearby player ($nearby_db_id) Instance ID contained something other than a number: " . $nearby->Player_Instance);
                }

                //Log if non regular instanceid (can be legit)
                if( strlen($nearby->Player_Instance) < 5 ) {
                    Log::warn("Nearby player ($nearby_db_id) Instance ID was less than 5 digits long: " . $nearby->Player_Instance);
                }

                $nearby_instance_id = $nearby->Player_Instance;


                /*
                 **********************************
                 ** Validate Current Archetype   **
                 **********************************
                 *  # Fail to null
                 *  @ sets $nearby_current_archetype
                 *
                 *  -in array expected
                 *  -guardian,recon,medic,bunker,berzerker,unknown,NULL
                 */
                $expected_archetypes = array('guardian','recon','medic','bunker','berzerker','unknown');

                //Valid archetype?  I don't know what I expected
                if( !in_array($nearby->Battleframe, $expected_archetypes) ){
                    $nearby_current_archetype = NULL;
                    Log::warn("Nearby player ($nearby_db_id) sent unexpected battleframe: " . $nearby->Battleframe);
                }else{
                    $nearby_current_archetype = $nearby->Battleframe;
                }


                /*
                 ********************************
                 ** Validate Spotter ID (db_id) **
                 ********************************
                 *  # Fail if invalid
                 *  @ sets $nearby_spotter_db_id
                 *
                 *  -check digits only
                 *  -19 characters long
                 *  -Begins with a 9
                 */

                //Digits only?
                if( preg_match('/[^0-9]/', $nearby->Spotter_ID) ) {
                    throw new Exception("Nearby spotter player ID was more than just numbers: " . $nearby->Spotter_ID);
                }

                //19 Characters long?
                if( strlen($nearby->Spotter_ID) !== 19 ) {
                    throw new Exception("Nearby spotter player ID was not 19 characters: " . $nearby->Spotter_ID);
                }

                //Starts with a 9?
                if( substr($nearby->Spotter_ID, 0, 1) !== '9' ) {
                    throw new Exception("Nearby spotter player ID did not start with a 9: " . $nearby->Spotter_ID);
                }

                $nearby_spotter_db_id = $nearby->Spotter_ID;


                /*
                 **********************************
                 ** Validate Nearby Coords       **
                 **********************************
                 *  # Fail if out of bounds
                 *  @ sets $nearby_coord_x
                 *  @ sets $nearby_coord_y
                 *  @ sets $nearby_coord_z
                 *
                 *  -check keys (x,y,z)
                 *  -coords within 0->3328 +/-
                 */
/*
                //Minimum keys
                if(
                    !isset($nearby->Coords->x) ||
                    !isset($nearby->Coords->y) ||
                    !isset($nearby->Coords->z)
                ) { throw new Exception("Nearby player ($nearby_spotter_db_id) didn't send all location keys");

                }else{

                    $save_loc = true;
                    $log_loc = false;
                    foreach ($nearby->Coords as $k => $v)
                    {
                        //float values for locations
                        if( $k == 'x' || $k == 'y' || $k == 'z' ) {
                            if( !is_float($v) && $v !== 0 ) {
                                $log_loc = true;
                            }
                        }else{
                        //integers for chunks
                            if( !is_numeric($v) ) {
                                $log_loc = true;
                            }
                        }
                    }

                    //allowing all locations, logging if unexpected
                    if( $save_loc ) {
                        $loc =  new Location;
                            $loc->db_id = $nearby_db_id;
                            $loc->spotter_db_id = $nearby_spotter_db_id;
                            $loc->name = $nearby_name;
                            $loc->instanceId = $nearby_instance_id;
                            $loc->current_archetype = $nearby_current_archetype;
                            $loc->coord_x = $nearby->Coords->x;
                            $loc->coord_y = $nearby->Coords->y;
                            $loc->coord_z = $nearby->Coords->z;
                            $loc->chunkX = $nearby->Coords->chunkX;
                            $loc->chunkY = $nearby->Coords->chunkY;

                            $loc->save();

                            if($log_loc) {
                                Log::warn("Nearby ($nearby_db_id) sent unexpected coordinates: (locid: {$loc->id}) " . implode(',', (array) $nearby->Coords) );
                            }
                    }else{
                        throw new Exception("Nearby player ($nearby_spotter_db_id) sent invalid coordinates: " . implode(',', (array) $nearby->Coords) );
                    }
                }
*/

                $query_add_player = "INSERT INTO `players` ";
                $query_add_player .= "(name, armyTag, instanceId, db_id,";
                $query_add_player .= "armyId, current_archetype,";
                $query_add_player .= "created_at, updated_at) ";
                $query_add_player .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?) ";
                $query_add_player .= "ON DUPLICATE KEY UPDATE ";
                $query_add_player .= "name = ?, armyTag = ?, instanceId = ?, armyId = ?, ";
                $query_add_player .= "current_archetype = ?, updated_at = ?";

                $bindings = array(
                    $nearby_name, $nearby_army_tag, $nearby_instance_id, $nearby_db_id,
                    $nearby_army_id, $nearby_current_archetype,
                    $this->date, $this->date,
                    $nearby_name, $nearby_army_tag, $nearby_instance_id, $nearby_army_id,
                    $nearby_current_archetype, $this->date
                );

                if( !DB::query($query_add_player,$bindings) ) {
                    throw new Exception('Failed adding to players query in nearbyplayers');
                }


            }//each nearbyplayer


        } catch (Exception $e) {
            Log::info($log_header.$e->getMessage());
            file_put_contents($this->logpath.$this->logdate.'_nearby.json', serialize($line));
        }


        return Response::json(array('ThumpDumpDB'=>'(Nearby) Thanks'));
    }



    public function post_pve_stats()
    {
        $log_header = "addon_v2.php@pve [" . $this->user_ip . "] - ($this->logdate): ";

        $line = Input::json();

        if($this->savedata){file_put_contents($this->logpath.$this->logdate.'_pve.json', serialize($line));}

        $datlog = new DatLog();
            $datlog->ip = ip2long($this->user_ip);
            $datlog->db_id = ( isset($line->Player_ID) ) ? $line->Player_ID : NULL;
            $datlog->name = ( isset($line->Player_Name) ) ? $line->Player_Name : NULL;
            $datlog->category = 'pve';
            $datlog->save();

        try {

            /*
             ********************************
             ** Validate Minimum Keys Exist**
             ********************************
             *  # Fail if invalid
             */

            //Minimum keys
            if(
                !isset($line->Player_Instance) ||
                !isset($line->PvE_Events) ||
                !isset($line->Player_EID) ||
                !isset($line->PvE_Stats) ||
                !isset($line->PvE_Kills) ||
                !isset($line->Player_ID) ||
                !isset($line->Player_Name)
            ) { throw new Exception('Player did not send all keys.'); }


            /*
             ********************************
             ** Validate Player ID (db_id) **
             ********************************
             *  # Fail if invalid
             *  @ sets $player_db_id
             *
             *  -check digits only
             *  -19 characters long
             *  -Begins with a 9
             */

            //Digits only?
            if( preg_match('/[^0-9]/', $line->Player_ID) ) {
                throw new Exception("Player ID was more than just numbers: " . $line->Player_ID);
            }

            //19 Characters long?
            if( strlen($line->Player_ID) !== 19 ) {
                throw new Exception("Player ID was not 19 characters: " . $line->Player_ID);
            }

            //Starts with a 9?
            if( substr($line->Player_ID, 0, 1) !== '9' ) {
                throw new Exception("Player ID did not start with a 9: " . $line->Player_ID);
            }

            $player_db_id = $line->Player_ID;


            /*
             **********************************
             ** Validate Player EID          **
             **********************************
             *  # Fail is non numeric, NULL otherwise
             *  @ sets $player_eid
             *
             *  -Greater than 0
             *  -Expected length 9 or 10, but others possible
             */

            //Sent only numbers
            if( preg_match('/[^0-9]/', $line->Player_EID) ) {
                throw new Exception("Player ($player_db_id) EID contained something other than a number: " . $line->Player_EID);
            }

            //EID should be as long as a db_id
            if( strlen($line->Player_EID) !== 19 ) {
                Log::warn("Player ($player_db_id) EID was not the expected 19 digits long: " . $line->Player_EID);
                $line->Player_EID = NULL;
            }

            $player_eid = $line->Player_EID;


            /*
             **********************************
             ** Validate Player Name/Army Tag**
             **********************************
             *  # Warn if invalid
             *  @ sets $player_name
             *  @ sets $player_army_tag
             *
             *  -15 + 6 characters or less ~23 to be safe
             *  -We don't care about character content as much as FF does
             */

            //Check if name is way too long, or too short
            if( strlen($line->Player_Name) > 30 || trim(strlen($line->Player_Name)) < 3 ) {
                throw new Exception("Player ($player_db_id) name was longer than 30 characters, should be <= 23: " . $line->Player_Name);
            }

            //Warn if longer than expected (that's what she said)
            //But allow for armytag length too
            if( strlen($line->Player_Name) > 27 ) {
                Log::warn("Player ($player_db_id) sent a character name longer than max expected length of 27: " . $line->Player_Name);
            }

            //Name is ok, but does it have an army tag?  Does it blend?
            if( strpos($line->Player_Name, ']') ) {

                $last = strripos($line->Player_Name, ']') + 1;
                $player_army_tag = substr($line->Player_Name, 0, $last);
                $player_name = trim(substr($line->Player_Name, $last));

            }else{
                $player_army_tag = NULL;
                $player_name = $line->Player_Name;
            }


            /*
             ***********************************
             ** Check existing name/db_id/eid **
             ***********************************
             *  Does the db_id exist?
             *  Does the submitted name match existing?
             *  Does the submitted eid match existing?
             */
            $check_existing_sql = Player::where('db_id','=',$player_db_id)->order_by('created_at','DESC')->first();
            if( $check_existing_sql ) {
                if( $check_existing_sql->name != $player_name ) {
                    throw new Exception("Existing db_id does not match existing name: (player sent:{$player_db_id}|{$player_name};existing:{$check_existing_sql->db_id}|{$check_existing_sql->name};)");
                    Log::warn("Existing db_id does not match existing name({$player_db_id}|in:{$player_name}|existing:{$check_existing_sql->name})");
                }

                if( $check_existing_sql->e_id != $player_eid ) {
                    throw new Exception("Existing db_id does not match existing name({$player_db_id}|in:{$player_eid}|existing:{$check_existing_sql->e_id})");
                    Log::warn("Existing db_id does not match existing eid({$player_db_id}|in:{$player_eid}|existing:{$check_existing_sql->e_id})");
                }
            }



            //Let's store some data
            //First up, PVE STATS

            /*
             ***********************************
             ** CHECK FOR CRAZY IN PVE STATS  **
             ***********************************
             *  Integer
             *  Greater than 0
             */
            $pve_stats_data = $line->PvE_Stats;

            foreach ($pve_stats_data as $key => $value)
            {
                //I can't add letters
                if( !is_numeric($value) ) {
                    throw new Exception("PvEStats->$key was not numeric");
                }

                //All positive thinking here, except for accuracy
                if( $value < 0 && $key != 'Accuracy' ) {
                   throw new Exception("PvEStats->$key was less than zero");
                }

                //check suspect values, over 9000 is so web2.0
                if( $key != 'Primary_Weapon_Shots_Fired' && $key != 'Secondary_Weapon_Shots_Fired' && $key != 'Healed' && $key != 'Damage_Done' ) {
                    if( $value > 99999 ) {
                        Log::warn("Player ({$player_db_id}) sent value > 99999: $key=$value");
                    }
                }
            }

            //check shots_fired, but not fire shtos
            if( !$pve_stats_data->Primary_Weapon_Shots_Fired > 100000 || !$pve_stats_data->Secondary_Weapon_Shots_Fired > 100000 || !$pve_stats_data->Healed > 100000 || !$pve_stats_data->Damage_Done > 100000 ) {
                Log::warn("Player ({$player_db_id}) sent value > 100000 for shots fired/healed.");
            }


            //Check for duplicate date entry, add to existing data if so
            $last_pve_stats_update = PvEStat::where(function($query) use ($player_db_id) {
                $query->where('db_id','=', $player_db_id);
                $query->where( DB::raw('DATE(updated_at)') , '=', $this->date_only );
            })->order_by('id','desc')->first('id');

            if( $last_pve_stats_update ) {
                $query_update_pvestats = "UPDATE `pvestats` SET ";
                $query_update_pvestats .= "`accuracy` = `accuracy` + ?, ";
                $query_update_pvestats .= "`damage_done` = `damage_done` + ?, ";
                $query_update_pvestats .= "`damage_taken` = `damage_taken` + ?, ";
                $query_update_pvestats .= "`deaths` = `deaths` + ?, ";
                $query_update_pvestats .= "`drowned` = `drowned` + ?, ";
                $query_update_pvestats .= "`headshots` = `headshots` + ?, ";
                $query_update_pvestats .= "`healed` = `healed` + ?, ";
                $query_update_pvestats .= "`incapacitated` = `incapacitated` + ?, ";
                $query_update_pvestats .= "`primary_reloads` = `primary_reloads` + ?, ";
                $query_update_pvestats .= "`primary_weapon_shots_fired` = `primary_weapon_shots_fired` + ?, ";
                $query_update_pvestats .= "`revived` = `revived` + ?, ";
                $query_update_pvestats .= "`revives` = `revives` + ?, ";
                $query_update_pvestats .= "`scanhammer_kills` = `scanhammer_kills` + ?, ";
                $query_update_pvestats .= "`secondary_reloads` = `secondary_reloads` + ?, ";
                $query_update_pvestats .= "`secondary_weapon_shots_fired` = `secondary_weapon_shots_fired` + ?, ";
                $query_update_pvestats .= "`suicides` = `suicides` + ?, ";
                $query_update_pvestats .= "`updated_at` = ? WHERE `id` = ?";

                $bindings_update_pvestats = array(
                    $pve_stats_data->Accuracy,
                    $pve_stats_data->Damage_Done,
                    $pve_stats_data->Damage_Taken,
                    $pve_stats_data->Deaths,
                    $pve_stats_data->Drowned,
                    $pve_stats_data->Headshots,
                    $pve_stats_data->Healed,
                    $pve_stats_data->Incapacitated,
                    $pve_stats_data->Primary_Reloads,
                    $pve_stats_data->Primary_Weapon_Shots_Fired,
                    $pve_stats_data->Revived,
                    $pve_stats_data->Revives,
                    $pve_stats_data->Scanhammer_Kills,
                    $pve_stats_data->Secondary_Reloads,
                    $pve_stats_data->Secondary_Weapon_Shots_Fired,
                    $pve_stats_data->Suicides,
                    $this->date, $last_pve_stats_update->id);

                if( DB::query($query_update_pvestats, $bindings_update_pvestats) === false ){
                    throw new Exception('Error updating pve stats.');
                }

            }else{
                $pvestat = new PvEStat;
                    $pvestat->db_id = $player_db_id;
                    $pvestat->Accuracy = $pve_stats_data->Accuracy;
                    $pvestat->Damage_Done = $pve_stats_data->Damage_Done;
                    $pvestat->Damage_Taken = $pve_stats_data->Damage_Taken;
                    $pvestat->Deaths = $pve_stats_data->Deaths;
                    $pvestat->Drowned = $pve_stats_data->Drowned;
                    $pvestat->Headshots = $pve_stats_data->Headshots;
                    $pvestat->Healed = $pve_stats_data->Healed;
                    $pvestat->Incapacitated = $pve_stats_data->Incapacitated;
                    $pvestat->Primary_Reloads = $pve_stats_data->Primary_Reloads;
                    $pvestat->Primary_Weapon_Shots_Fired = $pve_stats_data->Primary_Weapon_Shots_Fired;
                    $pvestat->Revived = $pve_stats_data->Revived;
                    $pvestat->Revives = $pve_stats_data->Revives;
                    $pvestat->Scanhammer_Kills = $pve_stats_data->Scanhammer_Kills;
                    $pvestat->Secondary_Reloads = $pve_stats_data->Secondary_Reloads;
                    $pvestat->Secondary_Weapon_Shots_Fired = $pve_stats_data->Secondary_Weapon_Shots_Fired;
                    $pvestat->Suicides = $pve_stats_data->Suicides;
                if( !$pvestat->save() ){
                    throw new Exception('Add PvEStat query failed:');
                }
            }


            /*
             ***********************************
             ** CHECK FOR CRAZY IN PVE EVENTS **
             ***********************************
             *  Integer
             *  Greater than 0
             */
            $pve_events_data = $line->PvE_Events;

            foreach ($pve_events_data as $key => $value)
            {
                if( is_object($value) || is_array($value) ) {
                    foreach ($value as $k => $v)
                    {
                        if( !is_numeric($v) || $v < 0 ) {
                            throw new Exception("PvEEvent was not numeric, or less than zero: ($key)");
                        }
                    }
                }else{
                    if( !is_numeric($value) || $value < 0 ) {
                        throw new Exception("PvEEvent was not numeric, or less than zero: ($key)");
                    }
                }
            }

            //Check for duplicate date entry, add to existing data if so
            $last_pve_events_update = PvEEvent::where(function($query) use ($player_db_id) {
                $query->where('db_id','=', $player_db_id);
                $query->where( DB::raw('DATE(updated_at)') , '=', $this->date_only );
            })->order_by('id','desc')->first('id');

            if( $last_pve_events_update ) {
                $query_update_pveevents = "UPDATE `pveevents` SET ";
                $query_update_pveevents .= "`ares_missions_0` = `ares_missions_0` + ?, ";
                $query_update_pveevents .= "`ares_missions_1` = `ares_missions_1` + ?, ";
                $query_update_pveevents .= "`crashed_lgvs` = `crashed_lgvs` + ?, ";
                $query_update_pveevents .= "`raider_squads_defeated` = `raider_squads_defeated` + ?, ";
                $query_update_pveevents .= "`crashed_thumpers` = `crashed_thumpers` + ?, ";
                $query_update_pveevents .= "`holmgang_tech_completed` = `holmgang_tech_completed` + ?, ";
                $query_update_pveevents .= "`lgv_races` = `lgv_races` + ?, ";
                $query_update_pveevents .= "`lgv_fastest_time_sunken_copa` = LEAST(`lgv_fastest_time_sunken_copa`, ?), ";
                $query_update_pveevents .= "`lgv_fastest_time_thump_copa` = LEAST(`lgv_fastest_time_thump_copa`, ?), ";
                $query_update_pveevents .= "`lgv_fastest_time_copa_trans` = LEAST(`lgv_fastest_time_copa_trans`, ?), ";
                $query_update_pveevents .= "`lgv_fastest_time_copa_thump` = LEAST(`lgv_fastest_time_copa_thump`, ?), ";
                $query_update_pveevents .= "`lgv_fastest_time_trans_sunken` = LEAST(`lgv_fastest_time_trans_sunken`, ?), ";
                $query_update_pveevents .= "`outposts_defended` = `outposts_defended` + ?, ";
                $query_update_pveevents .= "`strike_teams_0` = `strike_teams_0` + ?, ";
                $query_update_pveevents .= "`strike_teams_1` = `strike_teams_1` + ?, ";
                $query_update_pveevents .= "`strike_teams_2` = `strike_teams_2` + ?, ";
                $query_update_pveevents .= "`strike_teams_3` = `strike_teams_3` + ?, ";
                $query_update_pveevents .= "`sunken_harbor_invasions_completed` = `sunken_harbor_invasions_completed` + ?, ";
                $query_update_pveevents .= "`thump_dump_invasions_completed` = `thump_dump_invasions_completed` + ?, ";
                $query_update_pveevents .= "`tornados_3` = `tornados_3` + ?, ";
                $query_update_pveevents .= "`tornados_4` = `tornados_4` + ?, ";
                $query_update_pveevents .= "`warbringers_3` = `warbringers_3` + ?, ";
                $query_update_pveevents .= "`warbringers_4` = `warbringers_4` + ?, ";
                $query_update_pveevents .= "`watchtowers_defended` = `watchtowers_defended` + ?, ";
                $query_update_pveevents .= "`watchtowers_retaken` = `watchtowers_retaken` + ?, ";
                $query_update_pveevents .= "`chosen_death_squads` = `chosen_death_squads` + ?, ";
                $query_update_pveevents .= "updated_at = ? WHERE `id` = ?";

                //pesky race values
                $pve_events_data_lgv_sunken_copa = isset( $pve_events_data->LGV_Race_Fastest_Time->{'Sunken-Copa'} ) ? $pve_events_data->LGV_Race_Fastest_Time->{'Sunken-Copa'} : 999.0;
                $pve_events_data_lgv_thump_copa = isset( $pve_events_data->LGV_Race_Fastest_Time->{'Thump-Copa'} ) ? $pve_events_data->LGV_Race_Fastest_Time->{'Thump-Copa'} : 999.0;
                $pve_events_data_lgv_copa_trans = isset( $pve_events_data->LGV_Race_Fastest_Time->{'Copa-Trans'} ) ? $pve_events_data->LGV_Race_Fastest_Time->{'Copa-Trans'} : 999.0;
                $pve_events_data_lgv_copa_thump = isset( $pve_events_data->LGV_Race_Fastest_Time->{'Copa-Thump'} ) ? $pve_events_data->LGV_Race_Fastest_Time->{'Copa-Thump'} : 999.0;
                $pve_events_data_lgv_trans_sunken = isset( $pve_events_data->LGV_Race_Fastest_Time->{'Trans-Sunken'} ) ? $pve_events_data->LGV_Race_Fastest_Time->{'Trans-Sunken'} : 999.0;

                if( isset($pve_events_data->Raider_Squads_Defeated) ) {
                    $raider_squads_defeated = $pve_events_data->Raider_Squads_Defeated;
                }else{
                    $raider_squads_defeated = 0;
                }

                if( isset($pve_events_data->Chosen_Death_Squads_Defeated) ) {
                    $chosen_death_squads = $pve_events_data->Chosen_Death_Squads_Defeated;
                }else{
                    $chosen_death_squads = 0;
                }

                $bindings_update_pveevents = array(
                    $pve_events_data->ARES_Missions[0],
                    $pve_events_data->ARES_Missions[1],
                    $pve_events_data->Crashed_LGVs,
                    $raider_squads_defeated,
                    $pve_events_data->Crashed_Thumpers,
                    $pve_events_data->Holmgang_Tech_Completed,
                    $pve_events_data->LGV_Races,
                    $pve_events_data_lgv_sunken_copa,
                    $pve_events_data_lgv_thump_copa,
                    $pve_events_data_lgv_copa_trans,
                    $pve_events_data_lgv_copa_thump,
                    $pve_events_data_lgv_trans_sunken,
                    $pve_events_data->Outposts_Defended,
                    $pve_events_data->Strike_Teams[0],
                    $pve_events_data->Strike_Teams[1],
                    $pve_events_data->Strike_Teams[2],
                    $pve_events_data->Strike_Teams[3],
                    $pve_events_data->Sunken_Harbor_Invasions_Completed,
                    $pve_events_data->Thump_Dump_Invasions_Completed,
                    $pve_events_data->Tornados->{3},
                    $pve_events_data->Tornados->{4},
                    $pve_events_data->Warbringers->{3},
                    $pve_events_data->Warbringers->{4},
                    $pve_events_data->Watchtowers_Defended,
                    $pve_events_data->Watchtowers_Retaken,
                    $chosen_death_squads,
                    $this->date, $last_pve_events_update->id);

                if( DB::query($query_update_pveevents, $bindings_update_pveevents) === false ){
                    throw new Exception('Error updating pve events.');
                }

            }else{
                $pveevent = new PvEEvent;
                    $pveevent->db_id = $player_db_id;
                    $pveevent->ares_missions_0 = $pve_events_data->ARES_Missions[0];
                    $pveevent->ares_missions_1 = $pve_events_data->ARES_Missions[1];
                    $pveevent->crashed_lgvs = $pve_events_data->Crashed_LGVs;
                    if( isset($pve_events_data->Raider_Squads_Defeated) ) {
                        $pveevent->raider_squads_defeated = $pve_events_data->Raider_Squads_Defeated;
                    }
                    $pveevent->crashed_thumpers = $pve_events_data->Crashed_Thumpers;
                    $pveevent->holmgang_tech_completed = $pve_events_data->Holmgang_Tech_Completed;
                    $pveevent->lgv_races = $pve_events_data->LGV_Races;

                    //check if any race times have been sent, default, high impossible value
                    $pveevent->lgv_fastest_time_sunken_copa = 999.0;
                    $pveevent->lgv_fastest_time_thump_copa = 999.0;
                    $pveevent->lgv_fastest_time_copa_trans = 999.0;
                    $pveevent->lgv_fastest_time_copa_thump = 999.0;
                    $pveevent->lgv_fastest_time_trans_sunken = 999.0;
                    if( count($pve_events_data->LGV_Race_Fastest_Time) > 0 ) {
                        foreach ($pve_events_data->LGV_Race_Fastest_Time as $key => $value)
                        {
                            switch ( strtolower($key) ) {
                                case 'sunken-copa':
                                    $pveevent->lgv_fastest_time_sunken_copa = $value;
                                    break;
                                case 'thump-copa':
                                    $pveevent->lgv_fastest_time_thump_copa = $value;
                                    break;
                                case 'copa-trans':
                                    $pveevent->lgv_fastest_time_copa_trans = $value;
                                    break;
                                case 'copa-thump':
                                    $pveevent->lgv_fastest_time_copa_thump = $value;
                                    break;
                                case 'trans-sunken':
                                    $pveevent->lgv_fastest_time_trans_sunken = $value;
                                    break;
                                default:
                                    break;
                            }
                        }

                    }

                    $pveevent->outposts_defended = $pve_events_data->Outposts_Defended;
                    $pveevent->strike_teams_0 = $pve_events_data->Strike_Teams[0];
                    $pveevent->strike_teams_1 = $pve_events_data->Strike_Teams[1];
                    $pveevent->strike_teams_2 = $pve_events_data->Strike_Teams[2];
                    $pveevent->strike_teams_3 = $pve_events_data->Strike_Teams[3];
                    $pveevent->sunken_harbor_invasions_completed = $pve_events_data->Sunken_Harbor_Invasions_Completed;
                    $pveevent->thump_dump_invasions_completed = $pve_events_data->Thump_Dump_Invasions_Completed;
                    $pveevent->tornados_3 = $pve_events_data->Tornados->{3};
                    $pveevent->tornados_4 = $pve_events_data->Tornados->{4};
                    $pveevent->warbringers_3 = $pve_events_data->Warbringers->{3};
                    $pveevent->warbringers_4 = $pve_events_data->Warbringers->{4};
                    $pveevent->watchtowers_defended = $pve_events_data->Watchtowers_Defended;
                    $pveevent->watchtowers_retaken = $pve_events_data->Watchtowers_Retaken;
                    if( isset($pve_events_data->Chosen_Death_Squads_Defeated) ) {
                        $pveevent->chosen_death_squads = $pve_events_data->Chosen_Death_Squads_Defeated;
                    }
                if( !$pveevent->save() ){
                    throw new Exception('Add PvEEvent query failed:');
                }
            }


            /*
             ***********************************
             ** CHECK FOR CRAZY IN PVE KILLS  **
             ***********************************
             *  Integer
             *  Greater than 0
             */
            $pve_kills_data = $line->PvE_Kills;

            foreach ($pve_kills_data as $key => $value)
            {
                if( $key == 'T1' || $key == 'T2' || $key == 'T3' || $key == 'T4' ) {
                    foreach ($value as $k => $v)
                    {
                        //keys must be numeric
                        if( !is_numeric($k) ) {
                            throw new Exception("PvE Kills sent non numeric mob id");
                        }

                        if( !is_numeric($v) || $v > 10000 ) {
                            Log::warn($log_header.$logdate.": PvE Kills sent non numeric value, or val greater than 10000");
                        }
                    }

                }else{
                    throw new Exception("PvE Kills sent unexpected key");
                }
            }

            //Check for duplicate date entry, add to existing data if so
            $last_pve_kills_update = PvEKill::where(function($query) use ($player_db_id) {
                $query->where('db_id','=', $player_db_id);
                $query->where( DB::raw('DATE(updated_at)') , '=', $this->date_only );
            })->order_by('id','desc')->first();

            //do this one backwards
            if( !$last_pve_kills_update ) {
                //no data, so we add data
                $pvekill = new PvEKill;
                $pvekill->db_id = $player_db_id;
                $pvekill->t1 = serialize($pve_kills_data->T1);
                $pvekill->t2 = serialize($pve_kills_data->T2);
                $pvekill->t3 = serialize($pve_kills_data->T3);
                $pvekill->t4 = serialize($pve_kills_data->T4);
                if( !$pvekill->save() ){
                    throw new Exception('Add PvEKill query failed:');
                }
            }else{

                // JUSTICE FRIENDS, ASSEMBLE
                $new_t1 = $new_t2 = $new_t3 = $new_t4 = '';

                //EXISTING T1 , ASSIMILATE
                $current_t1 = unserialize($last_pve_kills_update->t1);

                if( count($current_t1) > 0 ) {
                  //We have existing data.  By our keys combined, we are captain planet!
                  $temp = array();

                    //Add existing data to temp
                    foreach ($current_t1 as $key => $value)
                    {
                        $temp[$key] = $value;
                    }

                    //Add new data to temp, if exists
                    if( count($pve_kills_data->T1) > 0 ) {
                        foreach ($pve_kills_data->T1 as $key => $value)
                        {
                            //if they key exists, add to it
                            if( array_key_exists($key, $temp) ) {
                                $temp_val = $temp[$key];
                                $temp[$key] = $temp_val + $value;
                            }else{
                                $temp[$key] = $value;
                            }
                        }
                    }

                    $new_t1 = serialize( (object) $temp);
                }else{
                    //No existing data... so use the input one
                    $new_t1 = serialize($pve_kills_data->T1);
                }


                //EXISTING T2 , ASSIMILATE
                $current_t2 = unserialize($last_pve_kills_update->t2);

                if( count($current_t2) > 0 ) {
                    //We have existing data.  By our keys combined, we are captain planet!
                    $temp = array();

                    //Add existing data to temp
                    foreach ($current_t2 as $key => $value)
                    {
                        $temp[$key] = $value;
                    }

                    //Add new data to temp, if exists
                    if( count($pve_kills_data->T2) > 0 ) {
                        foreach ($pve_kills_data->T2 as $key => $value)
                        {
                            //if they key exists, add to it
                            if( array_key_exists($key, $temp) ) {
                                $temp_val = $temp[$key];
                                $temp[$key] = $temp_val + $value;
                            }else{
                               $temp[$key] = $value;
                            }
                        }
                    }
                    $new_t2 = serialize( (object) $temp);
                }else{
                    //No existing data... so use the input one
                    $new_t2 = serialize($pve_kills_data->T2);
                }


                //EXISTING T3 , ASSIMILATE
                $current_t3 = unserialize($last_pve_kills_update->t3);

                if( count($current_t3) > 0 ) {
                  //We have existing data.  By our keys combined, we are captain planet!
                  $temp = array();

                  //Add existing data to temp
                  foreach ($current_t3 as $key => $value)
                  {
                    $temp[$key] = $value;
                  }

                  //Add new data to temp, if exists
                  if( count($pve_kills_data->T3) > 0 ) {
                    foreach ($pve_kills_data->T3 as $key => $value)
                    {
                      //if they key exists, add to it
                      if( array_key_exists($key, $temp) ) {
                        $temp_val = $temp[$key];
                        $temp[$key] = $temp_val + $value;
                      }else{
                        $temp[$key] = $value;
                      }
                    }
                  }

                  $new_t3 = serialize( (object) $temp);
                }else{
                  //No existing data... so use the input one
                  $new_t3 = serialize($pve_kills_data->T3);
                }


                //EXISTING T4 , ASSIMILATE
                $current_t4 = unserialize($last_pve_kills_update->t4);

                if( count($current_t4) > 0 ) {
                  //We have existing data.  By our keys combined, we are captain planet!
                  $temp = array();

                  //Add existing data to temp
                  foreach ($current_t4 as $key => $value)
                  {
                    $temp[$key] = $value;
                  }

                  //Add new data to temp, if exists
                  if( count($pve_kills_data->T4) > 0 ) {
                    foreach ($pve_kills_data->T4 as $key => $value)
                    {
                      //if they key exists, add to it
                      if( array_key_exists($key, $temp) ) {
                        $temp_val = $temp[$key];
                        $temp[$key] = $temp_val + $value;
                      }else{
                        $temp[$key] = $value;
                      }
                    }
                  }

                  $new_t4 = serialize( (object) $temp);
                }else{
                  //No existing data... so use the input one
                  $new_t4 = serialize($pve_kills_data->T4);
                }


                $query_update_pvekills = "UPDATE `pvekills` SET ";
                $query_update_pvekills .= "`t1` = ?, `t2` = ?, `t3` = ?, `t4` = ?, ";
                $query_update_pvekills .= "updated_at = ? WHERE `id` = ?";

                $bindings_update_pvekills = array(
                    $new_t1, $new_t2, $new_t3, $new_t4,
                    $this->date, $last_pve_kills_update->id);

                if( DB::query($query_update_pvekills, $bindings_update_pvekills) === false ){
                    throw new Exception('Error updating pve kills.');
                }
            }


        } catch (Exception $e) {
            Log::info($log_header.$e->getMessage());
            file_put_contents($this->logpath.$this->logdate.'_pve.json', serialize($line));
        }

        return Response::json(array('ThumpDumpDB'=>'(PVE) Thanks'));

    }//PVE DATA


    /*
     *  PLAYER PRINTERS (WORKBENCH)
     *
     */
    public function post_printer()
    {

        $log_header = "addon_v2.php@printer [" . $this->user_ip . "] - ($this->logdate): ";

        $line = Input::json();

        $datlog = new DatLog();
            $datlog->ip = ip2long($this->user_ip);
            $datlog->db_id = ( isset($line->Player_ID) ) ? $line->Player_ID : NULL;
            $datlog->name = ( isset($line->Player_Name) ) ? $line->Player_Name : NULL;
            $datlog->category = 'printer';
            $datlog->save();

        if($this->savedata){file_put_contents($this->logpath.$this->logdate.'_printer.json', serialize($line));}

        try {

            /*
             ********************************
             ** Validate Minimum Keys Exist**
             ********************************
             *  # Fail if invalid
             */

            //Minimum keys
            if(
                !isset($line->Player_Craft_Queue) ||
                !isset($line->Player_EID) ||
                !isset($line->Player_ID) ||
                !isset($line->Player_Name)
            ) { throw new Exception('Player did not send all keys.'); }

            /*
             *  If Craft Queue is empty, bail out
             */
            if( count( (array) $line->Player_Craft_Queue) < 1) {
                return Response::json(array('ThumpDumpDB'=>'(Printer) Thanks'));
            }


            /*
             ********************************
             ** Validate Player ID (db_id) **
             ********************************
             *  # Fail if invalid
             *  @ sets $player_db_id
             *
             *  -check digits only
             *  -19 characters long
             *  -Begins with a 9
             */

            //Digits only?
            if( preg_match('/[^0-9]/', $line->Player_ID) ) {
                throw new Exception("Player ID was more than just numbers: " . $line->Player_ID);
            }

            //19 Characters long?
            if( strlen($line->Player_ID) !== 19 ) {
                throw new Exception("Player ID was not 19 characters: " . $line->Player_ID);
            }

            //Starts with a 9?
            if( substr($line->Player_ID, 0, 1) !== '9' ) {
                throw new Exception("Player ID did not start with a 9: " . $line->Player_ID);
            }

            $player_db_id = $line->Player_ID;


            /*
             **********************************
             ** Validate Player EID          **
             **********************************
             *  # Fail is non numeric, NULL otherwise
             *  @ sets $player_eid
             *
             *  -Greater than 0
             *  -Expected length 9 or 10, but others possible
             */

            //Sent only numbers
            if( preg_match('/[^0-9]/', $line->Player_EID) ) {
                throw new Exception("Player ($player_db_id) EID contained something other than a number: " . $line->Player_EID);
            }

            //EID should be as long as a db_id
            if( strlen($line->Player_EID) !== 19 ) {
                Log::warn("Player ($player_db_id) EID was not the expected 19 digits long: " . $line->Player_EID);
                $line->Player_EID = NULL;
            }

            $player_eid = $line->Player_EID;


            /*
             **********************************
             ** Validate Player Name/Army Tag**
             **********************************
             *  # Warn if invalid
             *  @ sets $player_name
             *  @ sets $player_army_tag
             *
             *  -15 + 6 characters or less ~23 to be safe
             *  -We don't care about character content as much as FF does
             */

            //Check if name is way too long, or too short
            if( strlen($line->Player_Name) > 30 || trim(strlen($line->Player_Name)) < 3 ) {
                throw new Exception("Player ($player_db_id) name was longer than 30 characters, should be <= 23: " . $line->Player_Name);
            }

            //Warn if longer than expected (that's what she said)
            //But allow for armytag length too
            if( strlen($line->Player_Name) > 25 ) {
                Log::warn("Player ($player_db_id) sent a character name longer than max expected length of 23: " . $line->Player_Name);
            }

            //Name is ok, but does it have an army tag?  Does it blend?
            if( strpos($line->Player_Name, ']') ) {

                $last = strripos($line->Player_Name, ']') + 1;
                $player_army_tag = substr($line->Player_Name, 0, $last);
                $player_name = trim(substr($line->Player_Name, $last));

            }else{
                $player_army_tag = NULL;
                $player_name = $line->Player_Name;
            }


            /*
             ***********************************
             ** Verify times and ids are ints **
             ***********************************
             *  ready_at, started_at, blueprint_id
             */
            $player_crafts = array();
            $now = date('Y-m-d H:i:s');
            foreach ($line->Player_Craft_Queue as $cq)
            {
                //we expect only three keys
                if( !isset($cq->ready_at) || !isset($cq->started_at) || !isset($cq->blueprint_id) ) {
                    continue;
                }else{


                    if( is_numeric($cq->ready_at) &&
                        is_numeric($cq->started_at) &&
                        is_numeric($cq->blueprint_id)
                    ) {
                        
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

                }//all keys set
            }//foreach


            /*
             ***********************************
             ** Check existing name/db_id/eid **
             ***********************************
             *  Does the db_id exist?
             *  Does the submitted name match existing?
             *  Does the submitted eid match existing?
             */
            $check_existing_sql = Player::where('db_id','=',$player_db_id)->order_by('created_at','DESC')->first();
            if( $check_existing_sql ) {
                if( $check_existing_sql->name != $player_name ) {
                    throw new Exception("Existing db_id does not match existing name({$player_db_id}|in:{$player_name}|existing:{$check_existing_sql->name})");
                    Log::warn("Existing db_id does not match existing name({$player_db_id}|in:{$player_name}|existing:{$check_existing_sql->name})");
                }

                if( $check_existing_sql->e_id != $player_eid ) {
                    throw new Exception("Existing db_id does not match existing name({$player_db_id}|in:{$player_eid}|existing:{$check_existing_sql->e_id})");
                    Log::warn("Existing db_id does not match existing eid({$player_db_id}|in:{$player_eid}|existing:{$check_existing_sql->e_id})");
                }
            }


            //Delete existing records before adding new ones
            DB::table('printers')->where('db_id','=',$player_db_id)->delete();
            //add new records
            if( count($player_crafts) > 0 ) {
                $crafts_in = DB::table('printers')->insert($player_crafts);
            }else{
                $crafts_in = false;
            }

            Cache::forever($player_db_id . '_printer', $player_crafts);



        } catch (Exception $e) {
            Log::info($log_header.$e->getMessage());
            file_put_contents($this->logpath.$this->logdate.'_printer.json', serialize($line));
        }

        return Response::json(array('ThumpDumpDB'=>'(Printer) Thanks'));
    }


    public function post_dumps()
    {
        if(isset($_GET['fn'])){
            $fn = $_GET['fn'];
        }else{
            $fn = false;
        }

        $suffix = '_dump';
        if( $fn ){
            if(is_string($fn) && strlen($fn) < 20) {
                $suffix = '_'.$fn;
            }
        }
        $json_raw = @file_get_contents('php://input');

        if($fn == 'giibt') {
            $json_raw = str_replace('\n',' ', $json_raw);
        }

        $server_date = date("Y_m_d_H_i_s");
        file_put_contents('public/addon/storage/dumps/'.$server_date.$suffix.'.json', $json_raw);
            return Response::json(array('TDDB:'=>'Like a truck.'));
    }

}

?>
