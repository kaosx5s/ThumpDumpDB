<?php
class ValidationController extends BaseController {
	// So, want to validate how many inches are in eight inches?
	
	private $class_name = "ValidationController";
	
	/*
		This class was created for the sole purpose to house validation functions, if you need 
		to validate a player name, db_id, eid or anything else use the functions housed in here.
		
		If for some reason you don't find a function for your specific input first think about if 
		it needs to be validated or not, if so add a new function to this file that does it.
	*/
	
	/*
		Function: Validate db_id
		Assumptions: None
		Action: Attempts to validate a db_id
		Inputs: db_id -> A player db_id
		Outputs:
			False: returns false if at any time the input fails a test
			True: returns true if the input passes all the tests
	*/
	public function Validate_DBID($input){
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
		if( preg_match('/[^0-9]/', $input) ) {
			Log::warn("Player ID was more than just numbers: " . $input);
			return false;
		}

		//19 Characters long?
		if( strlen($input) !== 19 ) {
			Log::warn("Player ID was not 19 characters: " . $input);
			return false;
		}

		//Starts with a 9?
		if( substr($input, 0, 1) !== '9' ) {
			Log::warn("Player ID did not start with a 9: " . $input);
			return false;
		}
		return true;
	}
	
	/*
		Function: Validate db_id Exists
		Assumptions: The db_id input is a valid input
		Action: Checks if the db_id exists within our database and than matches against the player name and api key.
		Inputs: db_id -> A player db_id
				player_name -> A player name
				api_key -> An api key
		Outputs:
			False: returns false if at any time the input fails a test
			True: returns true if the input passes all the tests
	*/
	public function Validate_DBID_Exists($db_id,$player_name,$api_key){
		/*
		 ***********************************
		 ** Check existing name/db_id     **
		 ***********************************
		 *  Does the db_id exist?
		 *  Does the submitted name match existing?
		 */
		$check_existing_sql = Player::where('db_id','=',$db_id)->order_by('created_at','DESC')->first();
		if( $check_existing_sql ) {
			if( $check_existing_sql->name != $player_name ) {
				Log::warn("Existing db_id does not match existing name({$db_id}|in:{$player_name}|existing:{$check_existing_sql->name})");
				return false;
			}
			if($check_existing_sql->api_key != $api_key){
				//api key isn't blank and does not match!
				Log::warn("API key missmatch: " . $db_id);
				return false;
			}
		}
		return true;
	}
	
	/*
		Function: Validate Player Name
		Assumptions: db_id is a valid input
		Action: Attempts to validate a player name
		Inputs: db_id -> A player db_id
				input -> A player name + army tag
		Outputs:
			False: returns false if at any time the input fails a test
			True: returns true if the input passes all the tests
	*/
	public function Validate_Player_Name($db_id,$input){
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
		if( strlen($input) > 30 || trim(strlen($input)) < 3 ) {
			Log::warn("Player ($db_id) name was longer than 30 characters, should be <= 23: " . $input);
			return false;
		}

		//Warn if longer than expected (that's what she said)
		//But allow for armytag length too
		if( strlen($input) > 27 ) {
			Log::warn("Player ($db_id) sent a character name longer than max expected length of 27: " . $input);
		}
		return true;
	}

	/*
		Function: Validate WebPrefs
		Assumptions: db_id is a valid input
		Action: Attempts to validate an object of webprefs
		Inputs: db_id -> A player db_id
				input -> An object containing webprefs
		Outputs:
			False: returns false if at any time the input fails a test
			True: returns true if the input passes all the tests
	*/	
	public function Validate_WebPrefs($db_id,$input){
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
			isset($input->Website_Prefs) &&
			isset($input->Website_Prefs->show_loadout) &&
			isset($input->Website_Prefs->show_inventory) &&
			isset($input->Website_Prefs->show_progress) &&
			isset($input->Website_Prefs->show_unlocks) &&
			isset($input->Website_Prefs->show_pve_kills) &&
			isset($input->Website_Prefs->show_pve_stats) &&
			isset($input->Website_Prefs->show_pve_events) &&
			isset($input->Website_Prefs->show_location)
		){
			foreach($input->Website_Prefs as $pref){
				//allowed values 1|0
				if( $pref !== 0 && $pref !== 1){
					Log::warn("Player ($db_id) sent invalid Website Prefs values: " . implode(',', (array) $line->Website_Prefs) );
					return false;
				}
			}
		}else{
			Log::warn("Player ($db_id) did not send all website prefs keys");
			return false;
		};
		return true;
	}
	
	/*
		Function: Validate Instance ID
		Assumptions: db_id is a valid input
		Action: Attempts to validate an instance id
		Inputs: db_id -> A player db_id
				input -> A instance id to verify
		Outputs:
			False: returns false if at any time the input fails a test
			True: returns true if the input passes all the tests
	*/
	public function Validate_Instance_ID($db_id,$input){
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
		if( $input === -1 ) {
			Log::warn("Player ($db_id) Instance ID was -1: " . $input);
			return false;
		}

		//Sending only digits
		if( preg_match('/[^0-9]/', $input) ) {
			Log::warn("Player ($db_id) Instance ID contained something other than a number: " . $input);
			return false;
		}

		//Log if non regular instanceid (can be legit)
		if( strlen($line->Player_Instance) < 5 ) {
			Log::warn("Player ($db_id) Instance ID was less than 5 digits long: " . $input);
		}
		return true;
	}

	/*
		Function: Validate EID
		Assumptions: db_id is a valid input
		Action: Attempts to validate a EID
		Inputs: db_id -> A player db_id
				input -> An EID to verify
		Outputs:
			False: returns false if at any time the input fails a test
			True: returns true if the input passes all the tests
	*/	
	public function Validate_EID($db_id,$input){
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
		if( preg_match('/[^0-9]/', $input) ) {
			Log::warn("Player ($db_id) EID contained something other than a number: " . $input);
			return false;
		}

		//EID should be as long as a db_id
		if( strlen($input) !== 19 ) {
			Log::warn("Player ($db_id) EID was not the expected 19 digits long: " . $input);
			return false;
		}
		return true;
	}

	/*
		Function: Validate Army ID
		Assumptions: db_id is a valid input
		Action: Attempts to validate an army id
		Inputs: db_id -> A player db_id
				input -> An army ID to verify
		Outputs:
			False: returns false if at any time the input fails a test
			True: returns true if the input passes all the tests
	*/	
	public function Validate_Army_ID($db_id,$input){
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

		//Numbers only plx
		if( preg_match('/[^0-9]/', $line->ArmyID) ){
			Log::warn("Player ($db_id) ArmyID was not numeric and not nil: " . $input);
			return false;
		}
		return true;
	}

	/*
		Function: Validate Archetype
		Assumptions: db_id is a valid input
		Action: Attempts to validate a archetype
		Inputs: db_id -> A player db_id
				input -> An archtype to verify
		Outputs:
			False: returns false if at any time the input fails a test
			True: returns true if the input passes all the tests
	*/		
	public function Validate_Archetype($db_id,$input){
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
		if( !in_array($input, $expected_archetypes) ){
			Log::warn("Player ($db_id) sent unexpected battleframe: " . $input);
			return false;
		};
		return true;
	}

	/*
		Function: Validate Region
		Assumptions: db_id is a valid input
		Action: Attempts to validate a region
		Inputs: db_id -> A player db_id
				input -> A region to verify
		Outputs:
			False: returns false if at any time the input fails a test
			True: returns true if the input passes all the tests
	*/		
	public function Validate_Region($db_id,$input){
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
		if( isset($input) ){
			$player_region = preg_replace('/[^A-Z0-9\-]/i', '', $input);
		}
		return true;
	}
	
	/*
		Function: Validate PVE Stats
		Assumptions: db_id is a valid input
		Action: Attempts to validate an object of PVE Stats
		Inputs: db_id -> A player db_id
				input -> An object containing PVE Stats
		Outputs:
			False: returns false if at any time the input fails a test
			True: returns true if the input passes all the tests
	*/		
	public function Validate_PVE_Stats($db_id,$input){
		/*
		 ***********************************
		 ** CHECK FOR CRAZY IN PVE STATS  **
		 ***********************************
		 *  Integer
		 *  Greater than 0
		 */
		$pve_stats_data = $input;

		foreach ($pve_stats_data as $key => $value)
		{
			//I can't add letters
			if( !is_numeric($value) ) {
				Log::warn("Player ($db_id) sent a PvEStats->$key that was not numeric");
				return false;
			}

			//All positive thinking here, except for accuracy
			if( $value < 0 && $key != 'Accuracy' ) {
			   Log::warn("Player ($db_id) sent a PvEStats->$key that was less than zero");
			   return false;
			}

			//check suspect values, over 9000 is so web2.0
			if( $key != 'Primary_Weapon_Shots_Fired' && $key != 'Secondary_Weapon_Shots_Fired' && $key != 'Healed' && $key != 'Damage_Done' ) {
				if( $value > 99999 ) {
					Log::warn("Player ({$db_id}) sent value > 99999: $key=$value");
				}
			}
		}
		
		//check shots_fired, but not fire shtos
		if( !$pve_stats_data->Primary_Weapon_Shots_Fired > 100000 || !$pve_stats_data->Secondary_Weapon_Shots_Fired > 100000 || !$pve_stats_data->Healed > 100000 || !$pve_stats_data->Damage_Done > 100000 ) {
			Log::warn("Player ({$db_id}) sent value > 100000 for shots fired/healed.");
		}
		return true;
	}

	/*
		Function: Validate PVE Events
		Assumptions: db_id is a valid input
		Action: Attempts to validate an object of PVE Events
		Inputs: db_id -> A player db_id
				input -> An object containing PVE Events
		Outputs:
			False: returns false if at any time the input fails a test
			True: returns true if the input passes all the tests
	*/		
	public function Validate_PVE_Events($db_id,$input){
		/*
		 ***********************************
		 ** CHECK FOR CRAZY IN PVE EVENTS **
		 ***********************************
		 *  Integer
		 *  Greater than 0
		 */
		$pve_events_data = $input;

		foreach ($pve_events_data as $key => $value)
		{
			if( is_object($value) || is_array($value) ) {
				foreach ($value as $k => $v)
				{
					if( !is_numeric($v) || $v < 0 ) {
						Log::warn("Player ({$db_id}) sent a PvEEvent that was not numeric, or less than zero: ($key)");
						return false;
					}
				}
			}else{
				if( !is_numeric($v) || $v < 0 ) {
					Log::warn("Player ({$db_id}) sent a PvEEvent that was not numeric, or less than zero: ($key)");
					return false;
				}
			}
		}
		return true;
	}

	/*
		Function: Validate PVE Kills
		Assumptions: db_id is a valid input
		Action: Attempts to validate an object of PVE Kills
		Inputs: db_id -> A player db_id
				input -> An object containing PVE Kills
		Outputs:
			False: returns false if at any time the input fails a test
			True: returns true if the input passes all the tests
	*/		
	public function Validate_PVE_Kills($db_id,$input){
		/*
		 ***********************************
		 ** CHECK FOR CRAZY IN PVE KILLS  **
		 ***********************************
		 *  Integer
		 *  Greater than 0
		 */
		$pve_kills_data = $input;

		foreach ($pve_kills_data as $key => $value)
		{
			if( $key == 'T1' || $key == 'T2' || $key == 'T3' || $key == 'T4' ) {
				foreach ($value as $k => $v)
				{
					//keys must be numeric
					if( !is_numeric($k) ) {
						Log::warn("Player ({$db_id}) sent PvE Kills that were non numeric mob id");
						return false;
					}

					if( !is_numeric($v) || $v > 10000 ) {
						Log::warn("Player ({$db_id}) sent PvE Kills that were non numeric value, or val greater than 10000");
					}
				}

			}else{
				Log::warn("Player ({$db_id}) sent PvE Kills with unexpected key");
				return false;
			}
		}
		return true;
	}
	
	/*
		Function: Validate Printer Queue
		Assumptions: db_id is a valid input
		Action: Attempts to validate an object of printer queues
		Inputs: db_id -> A player db_id
				input -> An object containing printer queues
		Outputs:
			False: returns false if at any time the input fails a test
			True: returns true if the input passes all the tests
	*/	
	public function Validate_Printer_Queue($db_id,$input){
		/*
		 ***********************************
		 ** Verify times and ids are ints **
		 ***********************************
		 *  ready_at, started_at, blueprint_id
		 */
		foreach ($input->Player_Craft_Queue as $cq)
		{
			//we expect only three keys
			if( !isset($cq->ready_at) || !isset($cq->started_at) || !isset($cq->blueprint_id) ) {
				continue;
			}else{
				if( !is_numeric($cq->ready_at) &&
					!is_numeric($cq->started_at) &&
					!is_numeric($cq->blueprint_id)
				){
					Log::warn("Player ({$db_id}) sent Craft Queue values that contained something other than a number.");
					return false;
				}
			}//all keys set
		}//foreach
		return true;
	}
}
?>