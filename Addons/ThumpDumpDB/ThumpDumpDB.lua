require "math";
require "string";
require "table";
require "lib/lib_InterfaceOptions";

local FRAME = Component.GetFrame("Main");
local NAME = Component.GetWidget("NAME");

-- Variables
local TDDB_URL = "http://www.thumpdumpdb.com/v2/addon/player";
local Nearby_TDDB_URL = "http://www.thumpdumpdb.com/v2/addon/nearbyplayers";
local PvE_TDDB_URL = "http://www.thumpdumpdb.com/v2/addon/pve_stats";
local Store_TDDB_URL = "http://www.thumpdumpdb.com/v2/addon/printer";
local VersionCheck_TDDB_URL = "http://www.thumpdumpdb.com/v2/addon/versioncheck";
local TDDB_Version = "0.9.2";

local Player_Name
local Player_ID
local Player_EID
local Player_ArmyID
local Player_Battle_Frame
local Player_Loadouts = {};
local Player_Progress = {};
local Player_Inventory = {};
local Player_InstanceID
local Player_Coords
local Player_Connected_Region
local Player_API_Key
local init_login = 1;
local init_options = 1;
local init_message;
local init_webprefs;
local final_send = false;
local final_send_nearby = false;
local final_send_pve = false;
local last_mob_stage = 0;
local current_weapon = 0;
local weapon_ammo_per_burst = 0;
local primary_weapon_type;
local recently_incapacitated = false;
local recently_drowning = false;
local recently_suicidal = false;
local revive_entity_id = 0;
local trying_to_revive_player = false;
local LGV_Race_Current = "";
local LGV_Race_Lock = false;
local PvE_Kill_Recorded = false;
local recent_printer_activity = false;

-- PvE Personal Counters
local Revives = 0;
local Revived = 0;
local Incapacitated = 0;
local Suicide = 0;
local Deaths = 0;
local Primary_Weapon_Shots_Fired = 0;
local Secondary_Weapon_Shots_Fired = 0;
local Scanhammer_Kills = 0;
local Primary_Reloads = 0;
local Secondary_Reloads = 0;
local Accuracy = 0;
local Healed = 0;
local Headshots = 0;
local Drowned = 0;
local Damage_Done = 0;
local Damage_Taken = 0;

-- PvE World Counters (tables are for events which can have gear stages)
local ARES_Missions_Completed = {};
	ARES_Missions_Completed[1] = 0;
	ARES_Missions_Completed[2] = 0;
local Crashed_LGVs_Completed = 0;
local Crashed_Thumpers_Completed = 0;
local Tornados_Completed = {};
	Tornados_Completed[3] = 0;
	Tornados_Completed[4] = 0;
local Watchtowers_Retaken = 0;
local Outposts_Defended = 0;
local LGV_Races_Completed = 0;
local LGV_Race_Fastest_Time = {};
local Chosen_Strike_Teams_Killed = {};
	Chosen_Strike_Teams_Killed[1] = 0;
	Chosen_Strike_Teams_Killed[2] = 0;
	Chosen_Strike_Teams_Killed[3] = 0;
	Chosen_Strike_Teams_Killed[4] = 0;
local Warbringers_Killed = {};
	Warbringers_Killed[3] = 0;
	Warbringers_Killed[4] = 0;
local Watchtowers_Defended = 0;
local Holmgang_Tech_Completed = 0;
local Sunken_Harbor_Invasions_Completed = 0;
local Thump_Dump_Invasions_Completed = 0;
local Raider_Squads_Defeated = 0;
local Chosen_Death_Squads_Defeated = 0;

-- Mob Character Type ID Lists
-- Bandit Mobs
local bandit_id_list = {512,513,514,529,530,532,592,593,594,595,611,761,762,903,962,969,970};
-- Melded Mobs
local melded_id_list = {270,314,409,528,554,562,565,602};
-- Chosen Mobs
local chosen_id_list = {281,326,327,391,392,409,453,543,548,572,708,709,1012};
-- Arhana (Gaea) Mobs
local aranha_id_list = {239,241,243,244,347,348,386,387,430,433,436,448,449,452,505,506,507,508,584,588,590,596,599,641,644,689,690,693,696,888,923,927,929,931,935,937,938,940,942,949,955,1011};

-- Temps
local RED5_TEMP_GEAR = {};

-- Addon Control Variables
local Addon_Enabled = true;
local Addon_Get_Player_Stats = true;
local Addon_Player_Stats_Show_Loadouts = true;
local Addon_Player_Stats_Show_Progress = true;
local Addon_Player_Stats_Show_Inventory = true;
local Addon_Player_Stats_Show_Unlocks = true;
local Addon_Player_Stats_Show_Location = true;
local Addon_Player_Stats_Show_PvE_Kills = true; -- Killboard stats
local Addon_Player_Stats_Show_PvE_Stats = true; -- Personal stats (shots fired, deaths etc...)
local Addon_Player_Stats_Show_PvE_Events = true; -- Events (crashed thumpers, ARES etc..)
local Addon_Get_Other_Player_Stats = true;
local Addon_Player_Show_Workbench_Info = true;
local Addon_Player_Show_Market_Listings = false;
local Addon_Player_Show_Craftables = false;
local Addon_OnExit_Save = true;
local Addon_Show_Prefs_On_Init = true;
local Addon_Show_Log = false;
local Addon_Show_PVE_Log = false;

-- Tables
local table_TDDB_Nearby_Players_Info = {};
local table_TDDB_PvE_Kills = {};
	table_TDDB_PvE_Kills.T1 = {};
	table_TDDB_PvE_Kills.T2 = {};
	table_TDDB_PvE_Kills.T3 = {};
	table_TDDB_PvE_Kills.T4 = {};

--Frame Options
InterfaceOptions.AddMovableFrame({
	frame = FRAME,
	label = "ThumpDump DB",
	scalable = true,
});

--Interface Options
InterfaceOptions.StartGroup({label="Addon"})
	InterfaceOptions.AddCheckBox({
		id="ENABLED",
		label="Enabled",
		default=Addon_Enabled,
		tooltip="Globally enable or disable the addon."
	})
InterfaceOptions.StopGroup()
InterfaceOptions.StartGroup({label="Basic Settings"})
	InterfaceOptions.AddCheckBox({
		id="GET_PLAYER_STATS",
		label="Collect My Player Info",
		default=Addon_Get_Player_Stats,
		tooltip="Enable or disable player stats collection, this option will populate your player profile on ThumpDumpDB.com."
	})
	InterfaceOptions.AddCheckBox({
		id="GET_OTHER_PLAYER_STATS",
		label="Collect Other Player Info",
		default=Addon_Get_Other_Player_Stats,
		tooltip="Enable or disable the collection of basic player information within radar range. Name, Army ID, Location and Archtype is collected."
	})
	InterfaceOptions.AddCheckBox({
		id="ADDON_ONEXIT_SAVE",
		label="Send My Progress On Exit",
		default=Addon_OnExit_Save,
		tooltip="Enable or disable the sending of stats when entering the escape menu."
	})
	InterfaceOptions.AddCheckBox({
		id="ADDON_SHOW_PREFS_ON_INIT",
		label="Show TDDB Preferences On First Load",
		default=Addon_Show_Prefs_On_Init,
		tooltip="Enable or disable displaying addon preferences when the addon starts up."
	})
InterfaceOptions.StopGroup()
InterfaceOptions.StartGroup({label="Website Settings"})
	InterfaceOptions.AddCheckBox({
		id="GET_PLAYER_STATS_SHOW_LOADOUTS",
		label="Show My Character Gear Information",
		default=Addon_Player_Stats_Show_Loadouts,
		tooltip="Enable or disable showing your characters gear on your profile page at ThumpDumpDB.com."
	})
	InterfaceOptions.AddCheckBox({
		id="GET_PLAYER_STATS_SHOW_PROGRESS",
		label="Show My Character Frame Progress",
		default=Addon_Player_Stats_Show_Progress,
		tooltip="Enable or disable showing your character progression (XP values, unlocked frames) on your profile page at ThumpDumpDB.com."
	})
	InterfaceOptions.AddCheckBox({
		id="GET_PLAYER_STATS_SHOW_INVENTORY",
		label="Show My Character Inventory",
		default=Addon_Player_Stats_Show_Inventory,
		tooltip="Enable or disable showing your resource totals and having your inventory checked for entry into 'The Best' pages."
	})
	InterfaceOptions.AddCheckBox({
		id="GET_PLAYER_STATS_SHOW_UNLOCKS",
		label="Show My Character Current Frame Unlocks",
		default=Addon_Player_Stats_Show_Unlocks,
		tooltip="Enable or disable showing your current frame unlocks on your profile at ThumpDumpDB.com."
	})
	InterfaceOptions.AddCheckBox({
		id="GET_PLAYER_STATS_SHOW_LOCATION",
		label="Show My Character Location",
		default=Addon_Player_Stats_Show_Location,
		tooltip="Enable or disable showing your most recent location on your profile page at ThumpDumpDB.com."
	})
	InterfaceOptions.AddCheckBox({
		id="GET_PLAYER_STATS_SHOW_PVE_KILLS",
		label="Show My Character PvE Kills",
		default=Addon_Player_Stats_Show_PvE_Kills,
		tooltip="Enable or disable showing your PvE kills statistics on your profile page at ThumpDumpDB.com."
	})
	InterfaceOptions.AddCheckBox({
		id="GET_PLAYER_STATS_SHOW_PVE_STATS",
		label="Show My Character PvE Stats",
		default=Addon_Player_Stats_Show_PvE_Stats,
		tooltip="Enable or disable showing your PvE base stats (accuracy, reloads, deaths, etc..) on your profile page at ThumpDumpDB.com."
	})
	InterfaceOptions.AddCheckBox({
		id="GET_PLAYER_STATS_SHOW_PVE_EVENTS",
		label="Show My Character PvE Events",
		default=Addon_Player_Stats_Show_PvE_Events,
		tooltip="Enable or disable showing your PvE event statistics (ARES missions completed, crashed thumpers, crashed lgv's, etc...) on your profile page at ThumpDumpDB.com."
	})
	--[[
	--Add API key area
	InterfaceOptions.AddTextInput({
		id="PLAYER_API_KEY",
		label="Your TDDB API Key",
		default=Player_API_Key,
		tooltip="Your special API key. Share this with other sites to allow them to access your data from ThumpDumpDB.com."
	})
	]]--
InterfaceOptions.StopGroup()
InterfaceOptions.StartGroup({label="Store Page Settings"})
	InterfaceOptions.AddCheckBox({
		id="PLAYER_SHOW_WORKBENCH_INFO",
		label="Show My Character Workbench Info On Store Page",
		default=Addon_Player_Show_Workbench_Info,
		tooltip="Enable or disable showing your current crafting queue on your profile page at ThumpDumpDB.com."
	})
	InterfaceOptions.AddCheckBox({
		id="PLAYER_SHOW_CRAFTABLES",
		label="Show My Craftable Components On Store Page",
		default=Addon_Player_Show_Craftables,
		tooltip="Enable or disable showing your craftable components on your profile page at ThumpDumpDB.com."
	})
	InterfaceOptions.AddCheckBox({
		id="PLAYER_SHOW_MARKET_LISTINGS",
		label="Show My Market Listings On Store Page",
		default=Addon_Player_Show_Market_Listings,
		tooltip="Enable or disable showing your current market listings on your profile page at ThumpDumpDB.com."
	})
InterfaceOptions.StopGroup()
InterfaceOptions.StartGroup({label="Debugging"})
	InterfaceOptions.AddCheckBox({
		id="SHOW_LOG",
		label="Show Log",
		default=Addon_Show_Log
	})
	InterfaceOptions.AddCheckBox({
		id="SHOW_PVE_LOG",
		label="Show PvE Log",
		default=Addon_Show_PVE_Log
	})
InterfaceOptions.StopGroup()

function OnOptionChange(id, val)
	local status;
	if id == "ENABLED" then
		Addon_Enabled = val
		Component.SaveSetting("option-checkbox:enabled", Addon_Enabled);
		if Addon_Enabled then
			FRAME:Show()
		else
			-- Hide the frame
			FRAME:Hide()
			-- Disable the data collection; No data should be sent if the entire addon is disabled!
			Addon_Get_Player_Stats = false;
			Addon_Player_Stats_Show_Loadouts = false;
			Addon_Player_Stats_Show_Progress = false;
			Addon_Player_Stats_Show_Inventory = false;
			Addon_Player_Stats_Show_Unlocks = false;
			Addon_Player_Stats_Show_Location = false;
			Addon_Player_Stats_Show_PvE_Kills = false;
			Addon_Player_Stats_Show_PvE_Stats = false;
			Addon_Player_Stats_Show_PvE_Events = false;
			Addon_Get_Other_Player_Stats = false;
			Addon_OnExit_Save = false;
			Addon_Show_Log = false;
			Addon_Show_PVE_Log = false;
			Addon_Player_Show_Workbench_Info = false;
			Addon_Player_Show_Market_Listings = false;
			Addon_Player_Show_Craftables = false;
		end
		if val then
			AddChatMessage("Addon: Enabled - Version " .. tostring(TDDB_Version))
		else
			AddChatMessage("Addon: Disabled")
		end
	end
	if id == "GET_PLAYER_STATS" then
		Addon_Get_Player_Stats = val
		Component.SaveSetting("option-checkbox:get_player_stats", Addon_Get_Player_Stats);
		if init_options == 0 then
			if val then
				AddChatMessage("Get Player Stats: Enabled")
			else
				AddChatMessage("Get Player Stats: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_message = "Player Stats: " .. status;
		end
	end
	if id == "GET_OTHER_PLAYER_STATS" then
		Addon_Get_Other_Player_Stats = val
		Component.SaveSetting("option-checkbox:get_other_player_stats", Addon_Get_Other_Player_Stats);
		if init_options == 0 then
			if val then
				AddChatMessage("Get Other Player Stats: Enabled")
			else
				AddChatMessage("Get Other Player Stats: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_message = init_message .. "; Nearby Stats: " .. status;
		end
	end
	if id == "GET_PLAYER_STATS_SHOW_LOADOUTS" then
		Addon_Player_Stats_Show_Loadouts = val
		Component.SaveSetting("option-checkbox:get_player_stats_show_loadouts", Addon_Player_Stats_Show_Loadouts);
		if init_options == 0 then
			if val then
				AddChatMessage("Website - Show Player Loadouts: Enabled")
			else
				AddChatMessage("Website - Show Player Loadouts: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_webprefs = "Website - Show: Loadouts: " .. status;
		end
	end
	if id == "GET_PLAYER_STATS_SHOW_PROGRESS" then
		Addon_Player_Stats_Show_Progress = val
		Component.SaveSetting("option-checkbox:get_player_stats_show_progress", Addon_Player_Stats_Show_Progress);
		if init_options == 0 then
			if val then
				AddChatMessage("Website - Show Player Progress: Enabled")
			else
				AddChatMessage("Website - Show Player Progress: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_webprefs = init_webprefs .. "; Progress: " .. status;
		end
	end
	if id == "GET_PLAYER_STATS_SHOW_INVENTORY" then
		Addon_Player_Stats_Show_Inventory = val
		Component.SaveSetting("option-checkbox:get_player_stats_show_inventory", Addon_Player_Stats_Show_Inventory);
		if init_options == 0 then
			if val then
				AddChatMessage("Website - Show Inventory: Enabled")
			else
				AddChatMessage("Website - Show Inventory: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_webprefs = init_webprefs .. "; Inventory: " .. status;
		end
	end
	if id == "GET_PLAYER_STATS_SHOW_UNLOCKS" then
		Addon_Player_Stats_Show_Unlocks = val
		Component.SaveSetting("option-checkbox:get_player_stats_show_inventory", Addon_Player_Stats_Show_Unlocks);
		if init_options == 0 then
			if val then
				AddChatMessage("Website - Show Frame Unlocks: Enabled")
			else
				AddChatMessage("Website - Show Frame Unlocks: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_webprefs = init_webprefs .. "; Unlocks: " .. status;
		end
	end
	if id == "GET_PLAYER_STATS_SHOW_LOCATION" then
		Addon_Player_Stats_Show_Location = val
		Component.SaveSetting("option-checkbox:get_player_stats_show_location", Addon_Player_Stats_Show_Location);
		if init_options == 0 then
			if val then
				AddChatMessage("Website - Show Location: Enabled")
			else
				AddChatMessage("Website - Show Location: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_webprefs = init_webprefs .. "; Location: " .. status;
		end
	end
	if id == "GET_PLAYER_STATS_SHOW_PVE_KILLS" then
		Addon_Player_Stats_Show_PvE_Kills = val
		Component.SaveSetting("option-checkbox:get_player_stats_show_pve_kills", Addon_Player_Stats_Show_PvE_Kills);
		if init_options == 0 then
			if val then
				AddChatMessage("Website - Show PvE Kills: Enabled")
			else
				AddChatMessage("Website - Show PvE Kills: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_webprefs = init_webprefs .. "; PvE Kills: " .. status;
		end
	end
	if id == "GET_PLAYER_STATS_SHOW_PVE_STATS" then
		Addon_Player_Stats_Show_PvE_Stats = val
		Component.SaveSetting("option-checkbox:get_player_stats_show_pve_stats", Addon_Player_Stats_Show_PvE_Stats);
		if init_options == 0 then
			if val then
				AddChatMessage("Website - Show PvE Stats: Enabled")
			else
				AddChatMessage("Website - Show PvE Stats: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_webprefs = init_webprefs .. "; PvE Stats: " .. status;
		end
	end
	if id == "GET_PLAYER_STATS_SHOW_PVE_EVENTS" then
		Addon_Player_Stats_Show_PvE_Events = val
		Component.SaveSetting("option-checkbox:get_player_stats_show_pve_events", Addon_Player_Stats_Show_PvE_Events);
		if init_options == 0 then
			if val then
				AddChatMessage("Website - Show PvE Events: Enabled")
			else
				AddChatMessage("Website - Show PvE Events: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_webprefs = init_webprefs .. "; PvE Events: " .. status;
		end
	end
	if id == "PLAYER_SHOW_WORKBENCH_INFO" then
		Addon_Player_Show_Workbench_Info = val
		Component.SaveSetting("option-checkbox:player_show_workbench_info", Addon_Player_Show_Workbench_Info);
		if init_options == 0 then
			if val then
				AddChatMessage("Website - Store - Show Workbench: Enabled")
			else
				AddChatMessage("Website - Store - Show Workbench: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_webprefs = init_webprefs .. "; Show Workbench: " .. status;
		end
	end
	if id == "PLAYER_SHOW_CRAFTABLES" then
		Addon_Player_Show_Craftables = val
		Component.SaveSetting("option-checkbox:player_show_craftables", Addon_Player_Show_Craftables);
		if init_options == 0 then
			if val then
				AddChatMessage("Website - Store - Show Craftables: Enabled")
			else
				AddChatMessage("Website - Store - Show Craftables: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_webprefs = init_webprefs .. "; Show Craftables: " .. status;
		end
	end
	if id == "PLAYER_SHOW_MARKET_LISTINGS" then
		Addon_Player_Show_Market_Listings = val
		Component.SaveSetting("option-checkbox:player_show_market_listings", Addon_Player_Show_Market_Listings);
		if init_options == 0 then
			if val then
				AddChatMessage("Website - Store - Show Market Listings: Enabled")
			else
				AddChatMessage("Website - Store - Show Market Listings: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_webprefs = init_webprefs .. "; Show Market Listings: " .. status;
		end
	end
	if id == "ADDON_SHOW_PREFS_ON_INIT" then
		Addon_Show_Prefs_On_Init = val
		Component.SaveSetting("option-checkbox:show_prefs_on_init", Addon_Show_Prefs_On_Init);
		if init_options == 0 then
			if val then
				AddChatMessage("Show TDDB Prefs On Init: Enabled")
			else
				AddChatMessage("Show TDDB Prefs On Init: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_message = init_message .. "; Prefs On Init: " .. status;
		end
	end
	if id == "ADDON_ONEXIT_SAVE" then
		Addon_OnExit_Save = val
		Component.SaveSetting("option-checkbox:addon_onexit_save", Addon_OnExit_Save);
		if init_options == 0 then
			if val then
				AddChatMessage("OnExit Saving: Enabled")
			else
				AddChatMessage("OnExit Saving: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_message = init_message .. "; OnExit Saving: " .. status;
		end
	end
	if id == "SHOW_LOG" then
		Addon_Show_Log = val
		Component.SaveSetting("option-checkbox:show_log", Addon_Show_Log);
		if init_options == 0 then
			if val then
				AddChatMessage("Show Log: Enabled")
			else
				AddChatMessage("Show Log: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_message = init_message .. "; Logging: " .. status;
		end
	end
	if id == "SHOW_PVE_LOG" then
		Addon_Show_PVE_Log = val
		Component.SaveSetting("option-checkbox:show_pve_log", Addon_Show_PVE_Log);
		if init_options == 0 then
			if val then
				AddChatMessage("Show PvE Log: Enabled")
			else
				AddChatMessage("Show PvE Log: Disabled")
			end
		else
			if val then
				status = "ON";
			else
				status = "OFF";
			end
			init_message = init_message .. "; PvE Logging: " .. status;
			if(Addon_Show_Prefs_On_Init) then
				AddChatMessage(tostring(init_message));
				AddChatMessage(tostring(init_webprefs));
			end
		end
	end
	-- Always do a UI frame refresh
	Update_Frame()
end

function Update_Frame()
	if Addon_Enabled then
		InterfaceOptions.DisableFrameMobility(FRAME, false)
		FRAME:ParamTo("alpha", 1, 100, 0, "ease-in")
	else
		InterfaceOptions.DisableFrameMobility(FRAME, true)
		FRAME:ParamTo("alpha", 0, 100, 0, "ease-out")
	end
end

function OnComponentLoad()
	InterfaceOptions.SetCallbackFunc(OnOptionChange, "ThumpDump DB")
	-- Addon Init
	if Component.GetSetting("option-checkbox:enabled") ~= nil then
		Addon_Enabled = Component.GetSetting("option-checkbox:enabled");
	else
		Component.SaveSetting("option-checkbox:enabled", Addon_Enabled);
	end
	--Show Log
	if Component.GetSetting("option-checkbox:show_log") ~= nil then
		Addon_Show_Log = Component.GetSetting("option-checkbox:show_log");
	else
		Component.SaveSetting("option-checkbox:show_log", Addon_Show_Log);
	end
	--On Exit Saving
	if Component.GetSetting("option-checkbox:addon_onexit_save") ~= nil then
		Addon_OnExit_Save = Component.GetSetting("option-checkbox:addon_onexit_save");
	else
		Component.SaveSetting("option-checkbox:addon_onexit_save", Addon_OnExit_Save);
	end
	--Show Init Message
	if Component.GetSetting("option-checkbox:show_prefs_on_init") ~= nil then
		Addon_Show_Prefs_On_Init = Component.GetSetting("option-checkbox:show_prefs_on_init");
	else
		Component.SaveSetting("option-checkbox:show_prefs_on_init", Addon_Show_Prefs_On_Init);
	end
	--Show Other Player Stats
	if Component.GetSetting("option-checkbox:get_other_player_stats") ~= nil then
		Addon_Get_Other_Player_Stats = Component.GetSetting("option-checkbox:get_other_player_stats");
	else
		Component.SaveSetting("option-checkbox:get_other_player_stats", Addon_Get_Other_Player_Stats);
	end
	--Show Player Stats
	if Component.GetSetting("option-checkbox:get_player_stats") ~= nil then
		Addon_Get_Player_Stats = Component.GetSetting("option-checkbox:get_player_stats");
	else
		Component.SaveSetting("option-checkbox:get_player_stats", Addon_Get_Player_Stats);
	end
	--Show loadouts
	if Component.GetSetting("option-checkbox:get_player_stats_show_loadouts") ~= nil then
		Addon_Player_Stats_Show_Loadouts = Component.GetSetting("option-checkbox:get_player_stats_show_loadouts");
	else
		Component.SaveSetting("option-checkbox:get_player_stats_show_loadouts", Addon_Player_Stats_Show_Loadouts);
	end
	--Show progress
	if Component.GetSetting("option-checkbox:get_player_stats_show_progress") ~= nil then
		Addon_Player_Stats_Show_Progress = Component.GetSetting("option-checkbox:get_player_stats_show_progress");
	else
		Component.SaveSetting("option-checkbox:get_player_stats_show_progress", Addon_Player_Stats_Show_Progress);
	end
	--Show Inventory
	if Component.GetSetting("option-checkbox:get_player_stats_show_inventory") ~= nil then
		Addon_Player_Stats_Show_Inventory = Component.GetSetting("option-checkbox:get_player_stats_show_inventory");
	else
		Component.SaveSetting("option-checkbox:get_player_stats_show_inventory", Addon_Player_Stats_Show_Inventory);
	end
	--Show unlocks
	if Component.GetSetting("option-checkbox:get_player_stats_show_unlocks") ~= nil then
		Addon_Player_Stats_Show_Unlocks = Component.GetSetting("option-checkbox:get_player_stats_show_unlocks");
	else
		Component.SaveSetting("option-checkbox:get_player_stats_show_unlocks", Addon_Player_Stats_Show_Unlocks);
	end
	--Show location
	if Component.GetSetting("option-checkbox:get_player_stats_show_location") ~= nil then
		Addon_Player_Stats_Show_Location = Component.GetSetting("option-checkbox:get_player_stats_show_location");
	else
		Component.SaveSetting("option-checkbox:get_player_stats_show_location", Addon_Player_Stats_Show_Location);
	end
	--Show PvE Stats
	if Component.GetSetting("option-checkbox:get_player_stats_show_pve_stats") ~= nil then
		Addon_Player_Stats_Show_PvE_Stats = Component.GetSetting("option-checkbox:get_player_stats_show_pve_stats");
	else
		Component.SaveSetting("option-checkbox:get_player_stats_show_pve_stats", Addon_Player_Stats_Show_PvE_Stats);
	end
	--Show PvE Kills
	if Component.GetSetting("option-checkbox:get_player_stats_show_pve_kills") ~= nil then
		Addon_Player_Stats_Show_PvE_Kills = Component.GetSetting("option-checkbox:get_player_stats_show_pve_kills");
	else
		Component.SaveSetting("option-checkbox:get_player_stats_show_pve_kills", Addon_Player_Stats_Show_PvE_Kills);
	end
	--Show PvE Events
	if Component.GetSetting("option-checkbox:get_player_stats_show_pve_events") ~= nil then
		Addon_Player_Stats_Show_PvE_Events = Component.GetSetting("option-checkbox:get_player_stats_show_pve_events");
	else
		Component.SaveSetting("option-checkbox:get_player_stats_show_pve_events", Addon_Player_Stats_Show_PvE_Events);
	end
	--Show workbench
	if Component.GetSetting("option-checkbox:player_show_workbench_info") ~= nil then
		Addon_Player_Show_Workbench_Info = Component.GetSetting("option-checkbox:player_show_workbench_info");
	else
		Component.SaveSetting("option-checkbox:player_show_workbench_info", Addon_Player_Show_Workbench_Info);
	end
	--Show craftables
	if Component.GetSetting("option-checkbox:player_show_craftables") ~= nil then
		Addon_Player_Show_Craftables = Component.GetSetting("option-checkbox:player_show_craftables");
	else
		Component.SaveSetting("option-checkbox:player_show_craftables", Addon_Player_Show_Craftables);
	end
	--Show Market Listings
	if Component.GetSetting("option-checkbox:player_show_market_listings") ~= nil then
		Addon_Player_Show_Market_Listings = Component.GetSetting("option-checkbox:player_show_market_listings");
	else
		Component.SaveSetting("option-checkbox:player_show_market_listings", Addon_Player_Show_Market_Listings);
	end
	-- End Addon Init
end

-- Red5 Copypasta
function RED5_GetResourcesFromServer()
	local name, faction, race, sex, id = Player.GetInfo()
	local g_charId = id;
	local c_ResourceUrl = System.GetOperatorSetting("clientapi_host").."/api/v3/characters/"..g_charId.."/resources/quantities"

	if not HTTP.IsRequestPending(c_ResourceUrl) then
		HTTP.IssueRequest(c_ResourceUrl, "GET", nil, RED5_ServerResourcesResponse)
	else
		if Addon_Show_Log then 
			AddChatMessage("request pending (resources).");
		end
		callback(RED5_GetResourcesFromServer, nil, 3);
	end
end

function RED5_ServerResourcesResponse(resources, err)
	if err then
		warn(tostring(err.message))
		return nil
	end
	Player_Inventory = resources;
	
	if Addon_Show_Log then 
		AddChatMessage("Starting data collection...");
	end
	
	if Addon_Get_Player_Stats then
		TDDB_Get_Player_Data();
	end
	
	if Addon_Show_Log then 
		AddChatMessage("Data collection end.");
	end

	return nil;
end

function RED5_Get_Inventory_Gear()
	local name, faction, race, sex, id = Player.GetInfo()
	local g_charId = id;
	local c_ResourceUrl = System.GetOperatorSetting("clientapi_host").."/api/v3/characters/"..g_charId.."/inventories/gear/items"
		
	if not HTTP.IsRequestPending(c_ResourceUrl) then
		HTTP.IssueRequest(c_ResourceUrl, "GET", nil, RED5_Get_Slotted_Gear);
	else
		if Addon_Show_Log then 
			AddChatMessage("request pending (gear - items).");
		end
		callback(RED5_Get_Inventory_Gear, nil, 3);
	end
end

function RED5_Get_Slotted_Gear(args, err)
	-- We will always hit a callback on UI refresh so only save args when args is not null.
	if(args ~= nil) then
		RED5_TEMP_GEAR = args;
	end
	local name, faction, race, sex, id = Player.GetInfo()
	local g_charId = id;
	local c_ResourceUrl = System.GetOperatorSetting("clientapi_host").."/api/v3/characters/"..g_charId.."/garage_slots"
		
	if not HTTP.IsRequestPending(c_ResourceUrl) then
		HTTP.IssueRequest(c_ResourceUrl, "GET", nil, TDDB_Get_Equipment);
	else
		if Addon_Show_Log then 
			AddChatMessage("request pending (gear - equipped).");
		end
		callback(RED5_Get_Slotted_Gear, nil, 3);
	end
end

function RED5_Get_Progress_Unlocks()
	local name, faction, race, sex, id = Player.GetInfo()
	local g_charId = id;
	local c_ResourceUrl = System.GetOperatorSetting("clientapi_host").."/api/v2/characters/"..g_charId.."/unlocks/tech_trees"

	if not HTTP.IsRequestPending(c_ResourceUrl) then
		HTTP.IssueRequest(c_ResourceUrl, "GET", nil, TDDB_Get_Tech_Tree_Unlocks);
	else
		if Addon_Show_Log then 
			AddChatMessage("request pending (unlocks).");
		end
		callback(RED5_Get_Progress_Unlocks, nil, 3);
	end
end

function RED5_Get_Crafting_Queue()
	local name, faction, race, sex, id = Player.GetInfo()
	local g_charId = id;
	local c_ResourceUrl = System.GetOperatorSetting("clientapi_host").."/api/v3/characters/"..g_charId.."/manufacturing/workbenches"
		
	if not HTTP.IsRequestPending(c_ResourceUrl) then
		HTTP.IssueRequest(c_ResourceUrl, "GET", nil, TDDB_Get_Crafting_Queues);
	else
		if Addon_Show_Log then 
			AddChatMessage("request pending (crafting - workbenches).");
		end
		callback(RED5_Get_Crafting_Queue, nil, 3);
	end
	--Call the workbench queue again in a few min, the first check is on printer open and we need to find out if anything has changed.
	if(recent_printer_activity) then
		recent_printer_activity = false;
		callback(RED5_Get_Crafting_Queue, nil, 180);
	end
end

function AddChatMessage(text)
	if text then
		Component.GenerateEvent("MY_CHAT_MESSAGE", {channel="system", author="TDDB Log", text=text})
	end
end

function AddCriticalChatMessage(text)
	if text then
		Component.GenerateEvent("MY_CHAT_MESSAGE", {channel="broadcast", author="TDDB Important", text=text})
	end
end
-- End Red5 copypasta

function TDDB_Get_Crafting_Queues(args, err)
	--We only want the ready_at, started_at and blueprint_id, everything else isn't very important
	if(args ~= nil) then
		for i=1,2 do
			args[tostring(i)].inputs = nil;
			args[tostring(i)].rb_complete_cost = nil;
			args[tostring(i)].seconds_remaining = nil;
			args[tostring(i)].workbench_guid = nil;
			args[tostring(i)].can_load = nil;
		end
		
		if Addon_Show_Log then
			AddChatMessage("Start Crafting Queue data packet creation.");
		end
		
		--Prep a nice packet
		if(Player_Name == nil) then
			-- Fallback for if player name is nil
			local temp;
			--Get player name
			Player_Name = Player.GetInfo();
			--Get the player Entity ID
			Player_EID = Player.GetTargetId();
			--Call Game.GetTargetInfo for data population
			temp = Game.GetTargetInfo(Player_EID);
			--Get the player UID
			Player_ID = temp.db_id;
		end
		
		local table_TDDB_Packet = {
			-- Player information
			["Player_Name"] = tostring(Player_Name),
			["Player_ID"] = tostring(Player_ID),
			["Player_EID"] = tostring(Player_EID),
			-- Crafting slot information
			["Player_Craft_Queue"] = args,
		};
		
		if Addon_Show_Log then
			AddChatMessage("End  Crafting Queue data packet creation.");
		end
		
		-- Send that data!
		if Addon_Show_Log then
			AddChatMessage("Sending Crafting Queue data...");
		end
		TDDB_Send_Data(table_TDDB_Packet,Store_TDDB_URL);
	end
end

function TDDB_Get_Equipment(args, err)
	if(err ~= nil) then
		-- return gracefully and wait for the next callback, SIN is most likely down.
		if Addon_Show_Log then 
			AddChatMessage("SIN query was rejected or failed, waiting for next callback. (TDDB_Get_Equipment)");
		end
		return false;
	end
	-- This function will specifically cross compare gear gu_id's with garage_slots gu_id's to make sure the user has them equiped.
	local _TEMP_Loadout_List = Player.GetLoadoutList();
	local _TEMP_Chassis_to_Loadout_List = {};
	for i=1, #_TEMP_Loadout_List do
		_TEMP_Chassis_to_Loadout_List[i] = {};
		_TEMP_Chassis_to_Loadout_List[i].chassis_id = _TEMP_Loadout_List[i].item_types.chassis;
		_TEMP_Chassis_to_Loadout_List[i].loadout_id = _TEMP_Loadout_List[i].id;
	end
	
	_TEMP_Loadout_List = nil;
	
	--[[ 
	Generate a lookup table for all gear then compare the guid's, if we match then use the lookup table to immediately pull a given index.
	After we have found a match remove it from the lookup table so we have one less thing to iterate through, this is essentially a decrease by one 
	algorithm so we are getting linear runtime. This assumes that all player equipped gear exists in their inventory.
	]]--
	local gear_lookup_table = {};
	for i=1, #RED5_TEMP_GEAR do
		gear_lookup_table[tonumber(RED5_TEMP_GEAR[i].item_id)] = RED5_TEMP_GEAR[i];
	end
	RED5_TEMP_GEAR = nil;
	
	local character_gear_info = {};
	for j=1, #_TEMP_Chassis_to_Loadout_List do
		for i=1, #args do
			if (tostring(_TEMP_Chassis_to_Loadout_List[j].loadout_id) == tostring(args[i].id)) then
				character_gear_info[j] = {};
				character_gear_info[j].Gear = {};
				character_gear_info[j].Weapons = {};
				character_gear_info[j].Player_ID = args[i].character_guid;
				character_gear_info[j].Chassis_ID = _TEMP_Chassis_to_Loadout_List[j].chassis_id;
				--[[ Every loadout now has "equipped_slots" where:
					Slot  1: Primary Weapon
					Slot  2: Secondary Weapon
					Slot  3: Jumpjets
					Slot  4: Servos
					Slot  5: Plating
					Slot  6: Ability 1
					Slot  7: Ability 2
					Slot  8: Ability 3
					Slot  9: Ability 4
					Slot 10: Passive Module
					Slot 11: Secret key item for battleframe level
				]]--
				for k=1, #args[i].equipped_slots do
					--Gear
					if(args[i].equipped_slots[k].slot_type_id > 2) then
						if(args[i].equipped_slots[k].slot_type_id == 11) then
							--We dont want this item, its virtually useless to us
						else
							table.insert(character_gear_info[j].Gear,args[i].equipped_slots[k]);
						end
					end
					--Weapons
					if((args[i].equipped_slots[k].slot_type_id == 1) or (args[i].equipped_slots[k].slot_type_id == 2)) then
						table.insert(character_gear_info[j].Weapons,args[i].equipped_slots[k]);
					end
				end
			end
		end
		
		for k=1, #character_gear_info[j].Gear do
			if(gear_lookup_table[tonumber(character_gear_info[j].Gear[k].item_guid)] ~= nil) then
				character_gear_info[j].Gear[k].info = {};
				character_gear_info[j].Gear[k].info = gear_lookup_table[tonumber(character_gear_info[j].Gear[k].item_guid)];
				character_gear_info[j].Gear[k].info.owner_guid = nil;
				character_gear_info[j].Gear[k].info.character_guid = nil;
				character_gear_info[j].Gear[k].info.item_id = nil;
				character_gear_info[j].Gear[k].info.updated_at = nil;
				character_gear_info[j].Gear[k].info.created_at = nil;
				character_gear_info[j].Gear[k].info.bound_to_owner = nil;
				gear_lookup_table[tonumber(character_gear_info[j].Gear[k].item_guid)] = nil;
			end
		end
		for k=1, #character_gear_info[j].Weapons do
			if(gear_lookup_table[tonumber(character_gear_info[j].Weapons[k].item_guid)] ~= nil) then
				character_gear_info[j].Weapons[k].info = {};
				character_gear_info[j].Weapons[k].info = gear_lookup_table[tonumber(character_gear_info[j].Weapons[k].item_guid)];
				character_gear_info[j].Weapons[k].info.owner_guid = nil;
				character_gear_info[j].Weapons[k].info.character_guid = nil;
				character_gear_info[j].Weapons[k].info.item_id = nil;
				character_gear_info[j].Weapons[k].modules = nil;
				character_gear_info[j].Weapons[k].info.updated_at = nil;
				character_gear_info[j].Weapons[k].info.created_at = nil;
				character_gear_info[j].Weapons[k].info.bound_to_owner = nil;
				gear_lookup_table[tonumber(character_gear_info[j].Weapons[k].item_guid)] = nil;
			end
		end
	end
	args = nil;
	gear_lookup_table = nil;
	
	-- Trim fields, this is a HUGE table to json and send otherwise
	for i=1, #_TEMP_Chassis_to_Loadout_List do
		for k=1, #character_gear_info[i].Gear do
			character_gear_info[i].Gear[k].item_guid = nil;
			character_gear_info[i].Gear[k].slot_type_id = nil;
			if(character_gear_info[i].Gear[k].info ~= nil) then
				if((character_gear_info[i].Gear[k].sdb_id == 0) and (character_gear_info[i].Gear[k].info.item_sdb_id ~= nil)) then
					character_gear_info[i].Gear[k].sdb_id = nil;
				end
				if(next(character_gear_info[i].Gear[k].info.attribute_modifiers) == nil) then
					character_gear_info[i].Gear[k].info.attribute_modifiers = nil;
				end
			end
			if((character_gear_info[i].Gear[k].info == nil) and (character_gear_info[i].Gear[k].sdb_id ~= nil)) then
				character_gear_info[i].Gear[k].info = {};
				character_gear_info[i].Gear[k].info.quality = 0;
				character_gear_info[i].Gear[k].info.item_sdb_id = character_gear_info[i].Gear[k].sdb_id;
				character_gear_info[i].Gear[k].sdb_id = nil;
			end
			if(character_gear_info[i].Gear[k].info.type_code ~= nil) then
				character_gear_info[i].Gear[k].info.type_code = nil;
			end
		end
		for k=1, #character_gear_info[i].Weapons do
			character_gear_info[i].Weapons[k].item_guid = nil;
			character_gear_info[i].Weapons[k].slot_type_id = nil;
			if(character_gear_info[i].Weapons[k].info ~= nil) then
				if((character_gear_info[i].Weapons[k].sdb_id == 0) and (character_gear_info[i].Weapons[k].info.item_sdb_id ~= nil)) then
					character_gear_info[i].Weapons[k].sdb_id = nil;
				end
				if(next(character_gear_info[i].Weapons[k].info.attribute_modifiers) == nil) then
					character_gear_info[i].Weapons[k].info.attribute_modifiers = nil;
				end
			end
			if((character_gear_info[i].Weapons[k].info == nil) and (character_gear_info[i].Weapons[k].sdb_id ~= nil)) then
				character_gear_info[i].Weapons[k].info = {};
				character_gear_info[i].Weapons[k].info.quality = 0;
				character_gear_info[i].Weapons[k].info.item_sdb_id = character_gear_info[i].Weapons[k].sdb_id;
				character_gear_info[i].Weapons[k].sdb_id = nil;
			end
			if(character_gear_info[i].Weapons[k].info.type_code ~= nil) then
				character_gear_info[i].Weapons[k].info.type_code = nil;
			end
		end
	end
	
    --Remove the gear that couldn't be linked
	for i=1, #_TEMP_Chassis_to_Loadout_List do
		local k=1;
		while k <= #character_gear_info[i].Gear do
			if (character_gear_info[i].Gear[k].info == nil) then
				table.remove(character_gear_info[i].Gear, k);
			else
				k = k + 1;
			end
		end
	end
	
	for i=1, #_TEMP_Chassis_to_Loadout_List do
		table.insert(Player_Loadouts,character_gear_info[i]);
	end
	log(tostring(Player_Loadouts));
	RED5_Get_Progress_Unlocks();
end

function TDDB_Get_Tech_Tree_Unlocks(args, err)
	local progression_unlocks = {};
	local current_battleframe_chassis_id = Player.GetCurrentLoadout().items.chassis;
	Player_Progress.chassis_id = current_battleframe_chassis_id;
	--Clean out the unlocks
	for chassis,cert_id_list in pairs(args) do
		--Add the chassis to our list
		progression_unlocks[chassis] = {};
		for i=1, #cert_id_list do
			if(tonumber(cert_id_list[i]) > 701) then
				table.insert(progression_unlocks[chassis], cert_id_list[i]);
			end
		end
	end
	Player_Progress.unlocks = progression_unlocks;
	RED5_GetResourcesFromServer();
end

function TDDB_Increment_CharacterTypeId(tier, value)
	if(table_TDDB_PvE_Kills[tostring(tier)][tonumber(value)]) then
		table_TDDB_PvE_Kills[tostring(tier)][tonumber(value)] = table_TDDB_PvE_Kills[tostring(tier)][tonumber(value)] + 1;
	else
		table_TDDB_PvE_Kills[tostring(tier)][tonumber(value)] = 1;
	end
	if(Addon_Show_PVE_Log) then
		log(tostring(table_TDDB_PvE_Kills));
	end
end

function TDDB_PVE_Listener(args)
	if Addon_Enabled then
		if ((Addon_Get_Player_Stats) and (Game.GetPvPMatchId() == nil)) then
			if(args.event == "on_interact_attempt") then
				if(revive_entity_id ~= 0) then
					local entity_interaction = Player.GetAvailableInteractives(revive_entity_id);
					if(entity_interaction) then
						trying_to_revive_player = true;
					end
				end
			end
			
			if(args.event == "on_interact_available") then
				if(args.entityId) then
					local entity_info = Game.GetTargetInfo(args.entityId);
					if (entity_info ~= nil) then
						if (entity_info.isNpc == false) then
							-- This is a player, we may be able to revive them!
							local action = Player.GetInteracteeInfo(args.entityId);
							revive_entity_id = action.entityId;
						end
					end
				end
			end
		
			if(args.event == "on_interact_end") then
				if(trying_to_revive_player) then
					if(args.percent == 100) then
						-- You revived someone!
						Revives = Revives + 1;
						trying_to_revive_player = false;
						revive_entity_id = 0;
						if(Addon_Show_PVE_Log) then
							AddChatMessage("Revives: " .. Revives);
						end
					end
				end
			end
			
			if((args.event == "on_revive_end") and (args.percent == 100)) then
				-- You were revived!
				Revived = Revived + 1;
				recently_incapacitated = false;
				if(Addon_Show_PVE_Log) then
					AddChatMessage("Revived: " .. Revived);
				end
			end
			
			if((args.event == "on_took_hit") and (args.damage > 0)) then
				if(args.type == "drowning") then
					recently_drowning = true;
				else
					recently_drowning = false;
				end
				Damage_Taken = Damage_Taken + args.damage;
				if(Addon_Show_PVE_Log) then
					log(tostring("Damage Taken: " .. Damage_Taken));
				end
			end
			
			if(args.event == "on_incapacitate") then
				Incapacitated = Incapacitated + 1;
				recently_incapacitated = true;
				if(Addon_Show_PVE_Log) then
					AddChatMessage("Incapacitated: " .. Incapacitated);
				end
			end
			
			if(args.event == "on_spawn") then
				if(recently_incapacitated) then
					-- Reset our incapacitated var
					recently_incapacitated = false;
					
					-- You died ;(
					Deaths = Deaths + 1;
					if(Addon_Show_PVE_Log) then
						AddChatMessage("Deaths: " .. Deaths);
					end
				end
				if(recently_drowning) then
					Drowned = Drowned + 1;
					if(Addon_Show_PVE_Log) then
						AddChatMessage("Drowned: " .. Drowned);
					end
					-- Reset our drowning var
					recently_drowning = false;
				end
				if(recently_suicidal) then
					-- Ya done goofed.
					Suicide = Suicide + 1;
					if(Addon_Show_PVE_Log) then
						AddChatMessage("Suicides: " .. Suicide);
					end
					-- Reset our suicide var
					recently_suicidal = false;
				end
			end
			if((args.event == "on_combat_event") and (args.SourceId == Player_EID)) then
				if(args.Method == "Scan Hammer") then
					Scanhammer_Kills = Scanhammer_Kills + 1;
					if(Addon_Show_PVE_Log) then
						AddChatMessage("Scanhammer Kills: " .. Scanhammer_Kills);
					end
				end
				if((args.Event == "Downed") and (args.TargetId == nil)) then
					recently_suicidal = true;
				end
			end
			
			if((args.event == "on_combat_event") and (args.Event == "HeadShot")) then
				if(args.TargetId == Player_EID) then
					Headshots = Headshots + 1;
					if(Addon_Show_PVE_Log) then
						AddChatMessage("Headshots: " .. Headshots);
					end
				end
			end
			
			if(args.event == "on_weapon_burst") then
				if(current_weapon == 0) then
					--shots fired on primary
					Primary_Weapon_Shots_Fired = Primary_Weapon_Shots_Fired + weapon_ammo_per_burst;
					if(Addon_Show_PVE_Log) then
						log(tostring("Primary Weapon Shots Fired: " .. Primary_Weapon_Shots_Fired));
					end
				elseif(current_weapon == 1) then
					--shots fired on secondary
					Secondary_Weapon_Shots_Fired = Secondary_Weapon_Shots_Fired + weapon_ammo_per_burst;
					if(Addon_Show_PVE_Log) then
						log(tostring("Secondary Weapon Shots Fired: " .. Secondary_Weapon_Shots_Fired));
					end
				end
			end
			
			if((args.event == "on_weapon_reload") and (args.finish == true)) then
				if(current_weapon == 0) then
					Primary_Reloads = Primary_Reloads + 1;
					if(Addon_Show_PVE_Log) then
						log(tostring("Primary Weapon Reloads: " .. Primary_Reloads));
					end
				elseif(current_weapon == 1) then
					Secondary_Reloads = Secondary_Reloads + 1;
					if(Addon_Show_PVE_Log) then
						log(tostring("Secondary Weapon Reloads: " .. Secondary_Reloads));
					end
				end
			end
			
			if((args.event == "on_hit_target_confirm") and (args.entityId ~= Player_EID)) then
				if((args.damageType ~= "Health Only") and (args.damage > 0)) then
					Damage_Done = Damage_Done + args.damage;
					if(Addon_Show_PVE_Log) then
						log(tostring("Damage Done: " .. Damage_Done));
					end
				end
				Accuracy = Accuracy + 1;
				if(Addon_Show_PVE_Log) then
					log(tostring("Accuracy (hit): " .. Accuracy));
				end
			end
			
			if((args.event == "on_hit_target_confirm") and (args.damageType == "Health Only")) then
				-- Make sure the entity was a player
				local Player_Info = Game.GetTargetInfo(args.entityId);
				if((Player_Info.isNpc == false) and (Player_Info.db_id ~= Player_ID)) then
					Healed = Healed + math.abs(args.damage);
					if(Addon_Show_PVE_Log) then
						log(tostring("Healed: " .. Healed));
					end
				end
			end
				
			if(args.event == "on_weapon_changed") then
				local WeaponInfo = Player.GetWeaponInfo();
				if(WeaponInfo) then
					weapon_ammo_per_burst = WeaponInfo.AmmoPerBurst;
					if(WeaponInfo.WeaponType ~= nil) then
						-- Find out what weapon we switched to.
						if(current_weapon == 2) then
							if(primary_weapon_type == WeaponInfo.WeaponType) then
								-- bringing out your secondary
								current_weapon = 1;
							else
								-- bringing out that main weapon
								current_weapon = 0;
							end
						end
						if(current_weapon == 0) then
							-- switched to secondary
							current_weapon = 1;
						end
						if((current_weapon == 1) and (primary_weapon_type == WeaponInfo.WeaponType)) then
							-- switched to primary
							current_weapon = 0;
						end
					end
				else
					-- You holstered your weapon.
					current_weapon = 2;
				end
				if(Addon_Show_PVE_Log) then
					log("Weapon index: " .. tostring(current_weapon));
				end
			end
			if(Addon_Show_PVE_Log) then
				--log(tostring(args));
			end
		end
	end
end

function TDDB_Reward_Listener(args)
	if Addon_Enabled then
		if ((Addon_Get_Player_Stats) and (Game.GetPvPMatchId() == nil)) then
			-- Look at that reward, thats a nice reward.
			if((args.entityId) and (args.rewardType == "XP_REWARD_KILL")) then
				local entity_info = Game.GetTargetInfo(args.entityId);
				-- Setup a system so we know what stage the event is they are working on (if they are working on one)
				-- This is based on last mob gear level killed and honestly I can't seem to find a better way till a key is added to the rewards table.
				if(entity_info ~= nil) then
					if(last_mob_stage ~= entity_info.gearStage) then
						last_mob_stage = entity_info.gearStage;
						if(Addon_Show_PVE_Log) then
							AddChatMessage("Changed Mission Stage: " .. last_mob_stage);
						end
					end
					-- Set our recorded flag to false initially before we iterate through each array
					--[[
						These arrays run in linear time but it is entirely possible to do this in constant time by using $array[$key] composition instead, right now I don't think
						it's necessary to do this but if more mobs are added (which they will) we should revisit this section and change it to constant runtime to save some cycles.
					]]--
					PvE_Kill_Recorded = false;
					if(entity_info.isNpc and entity_info.hostile) then
						local tier = "T" .. tostring(entity_info.gearStage);
						if(Addon_Show_PVE_Log) then
							log(tostring(entity_info));
						end
						if(entity_info.faction == "bandit") then
							for _,value in pairs(bandit_id_list) do
								if (tonumber(value) == tonumber(entity_info.characterTypeId)) then
									TDDB_Increment_CharacterTypeId(tier,value);
									PvE_Kill_Recorded = true;
								end
							end
						end
						
						if(entity_info.faction == "melding") then
							for _,value in pairs(melded_id_list) do
								if (tonumber(value) == tonumber(entity_info.characterTypeId)) then
									TDDB_Increment_CharacterTypeId(tier,value);
									PvE_Kill_Recorded = true;
								end
							end
						end
						
						if(entity_info.faction == "chosen") then
							for _,value in pairs(chosen_id_list) do
								if (tonumber(value) == tonumber(entity_info.characterTypeId)) then
									TDDB_Increment_CharacterTypeId(tier,value);
									PvE_Kill_Recorded = true;
								end
							end
						end
						
						if(entity_info.faction == "gaea") then
							for _,value in pairs(aranha_id_list) do
								if (tonumber(value) == tonumber(entity_info.characterTypeId)) then
									TDDB_Increment_CharacterTypeId(tier,value);
									PvE_Kill_Recorded = true;
								end
							end
						end
						if(Addon_Show_PVE_Log) then
							AddChatMessage("Killed: " .. tostring(entity_info.name) .. ", Faction: " .. tostring(entity_info.faction) .. ", Stage: " .. last_mob_stage .. ", ID: " .. tonumber(entity_info.characterTypeId) .. ", Recorded: " .. tostring(PvE_Kill_Recorded));
						end
					end
				end
			end
			
			if((args.event == "on_encounter_ui_updated") and (LGV_Race_Lock == false)) then
				local encounter_info = Game.GetEncounterUiFields(args.encounterId);
				if(encounter_info ~= nil) then
					if((encounter_info.hudtimer_label ~= nil) and (encounter_info.hudtimer_timer ~= nil)) then
						-- There are other things that will set this stuff so a lock will be put in place if anything matches.
						-- We can figure out which race terminal you are at depending on chunk X,Y.
						local position = Game.WorldToChunkCoord(Player.GetPosition().x, Player.GetPosition().y);
						if(Addon_Show_PVE_Log) then
							log("LGV Race Lock: " .. tostring(LGV_Race_Lock));
							log("Racing to: " .. tostring(encounter_info.hudtimer_label));
						end
						if((position.chunkX == 241) and (position.chunkY == 920)) then
							--Thump Dump to Copa race
							LGV_Race_Current = "Thump-Copa";
							LGV_Race_Lock = true;
						end
						-- Get the hudtimer label placement (end point)
						if((position.chunkX == 245) and (position.chunkY == 915)) then
							--Sunken Harbor to Copa race
							LGV_Race_Current = "Sunken-Copa";
							LGV_Race_Lock = true;
						end
						if((position.chunkX == 242) and (position.chunkY == 916)) then
							--Transhub to Sunken Harbor race
							LGV_Race_Current = "Trans-Sunken";
							LGV_Race_Lock = true;
						end
						if((position.chunkX == 243) and (position.chunkY == 917)) then
							-- The Copacabana terminals are on the same chunk so check X,Y also
							if((position.x <= 0.3) and (position.y <= 0.5)) then
								-- We totally overshoot the X,Y values just in case
								--Copa to ThumpDump race
								LGV_Race_Current = "Copa-Thump";
								LGV_Race_Lock = true;
							end
							if((position.x >= 0.6) and (position.y <= 0.2)) then
								--Copa to Transhub race
								LGV_Race_Current = "Copa-Trans";
								LGV_Race_Lock = true;
							end
						end
						if(LGV_Race_Lock) then
							if(Addon_Show_PVE_Log) then
								AddChatMessage("Race Started: " .. tostring(LGV_Race_Current));
							end
						end
					end
					if((encounter_info.hudtimer_label ~= nil) and (encounter_info.hudtimer_timer == nil)) then
						-- LGV race failed
						LGV_Race_Current = "";
						LGV_Race_Lock = false;
						if(Addon_Show_PVE_Log) then
							log("LGV Race Failed");
						end
					end
				end
			end
			if(args.event == "on_encounter_reward") then
				if(args.title == "Chosen Incursion") then
					if(last_mob_stage < 3) then
						-- Warbringers spawn at 3 or 4 so something is wrong, bump to 3
						last_mob_stage = 3;
					end
					Warbringers_Killed[tonumber(last_mob_stage)] = Warbringers_Killed[tonumber(last_mob_stage)] + 1;
				end
				if(args.title == "ARES Mission Rewards") then
					if(last_mob_stage > 2) then
						-- ARES missions spawn at 1 or 2 so something is wrong, drop to 2
						last_mob_stage = 2;
					end
					ARES_Missions_Completed[tonumber(last_mob_stage)] = ARES_Missions_Completed[tonumber(last_mob_stage)] + 1;
				end
				if(args.title == "Melding Tornado") then
					if(last_mob_stage < 3) then
						-- Melding tornados spawn at 3 or 4 so something is wrong, bump to 3
						last_mob_stage = 3;
					end
					Tornados_Completed[tonumber(last_mob_stage)] = Tornados_Completed[tonumber(last_mob_stage)] + 1;
				end
				if(args.title == "Data Worm Successful") then
					Crashed_LGVs_Completed = Crashed_LGVs_Completed + 1;
				end
				if(args.title == "Crashed Thumper") then
					Crashed_Thumpers_Completed = Crashed_Thumpers_Completed + 1;
				end
				if(args.title == "Chosen Strike Team Defeated!") then
					Chosen_Strike_Teams_Killed[tonumber(last_mob_stage)] = Chosen_Strike_Teams_Killed[tonumber(last_mob_stage)] + 1;
				end
				if(args.title == "LGV Race") then
					LGV_Races_Completed = LGV_Races_Completed + 1;
					if(LGV_Race_Fastest_Time[tostring(LGV_Race_Current)] == nil) then
						LGV_Race_Fastest_Time[tostring(LGV_Race_Current)] = args.stats[1].value;
					else
						if(LGV_Race_Fastest_Time[tostring(LGV_Race_Current)] < args.stats[1].value) then
							LGV_Race_Fastest_Time[tostring(LGV_Race_Current)] = args.stats[1].value;
						end
					end
					-- Your time
					if(Addon_Show_PVE_Log) then
						AddChatMessage("LGV Race Total Time: " .. tostring(args.stats[1].value) .. " seconds. Race: " .. tostring(LGV_Race_Current));
					end
					LGV_Race_Lock = false;
				end
				if(args.title == "Outpost Defense Success!") then
					Outposts_Defended = Outposts_Defended + 1;
				end
				if(args.title == "Watchtower Reclamation Successful!") then
					Watchtowers_Retaken = Watchtowers_Retaken + 1;
				end
				if(args.title == "Watchtower Defense Successful!") then
					Watchtowers_Defended = Watchtowers_Defended + 1;
				end
				if(args.title == "Pirate Raid") then
					Holmgang_Tech_Completed = Holmgang_Tech_Completed + 1;
				end
				if(args.title == "Invasion of Sunken Harbor") then
					Sunken_Harbor_Invasions_Completed = Sunken_Harbor_Invasions_Completed + 1;
				end
				if(args.title == "Invasion of Thump Dump") then
					Thump_Dump_Invasions_Completed = Thump_Dump_Invasions_Completed + 1;
				end
				if(args.title == "Raider Squad Defeated!") then
					Raider_Squads_Defeated = Raider_Squads_Defeated + 1;
				end
				if(args.title == "Chosen Death Squad Defeated!") then
					Chosen_Death_Squads_Defeated = Chosen_Death_Squads_Defeated + 1;
				end
				if(Addon_Show_PVE_Log) then
					AddChatMessage("Completed Event: " .. tostring(args.title) .. ", Stage (if applicable): " .. last_mob_stage);
					--log(tostring(args));
				end
			end
		end
	end
end

function TDDB_Get_Player_Data()
	if Addon_Show_Log then
		AddChatMessage("DEBUG: Setting player info variables...");
	end
	
	local temp = {};
	
	--Get player name
	Player_Name = Player.GetInfo();
	--Get the player Entity ID
	Player_EID = Player.GetTargetId();
	--Call Game.GetTargetInfo for data population
	temp = Game.GetTargetInfo(Player_EID);
	--Get the player UID
	Player_ID = temp.db_id;
	--Get the player army ID
	Player_ArmyID = temp.armyId;
	--Returns current archtype name
	Player_Battle_Frame = Player.GetCurrentArchtype();
	--Loadouts is now a custom (crafted) gear listing
	--** REMINDER: Player_Loadout is set by TDDB_Get_Equipment! **
	--Returns progress (XP, s_db, lifetime_xp) of all "owned" battleframes
	Player_Progress.xp = Player.GetAllProgressionXp();
	--** REMINDER: Player_Inventory is set by RED5_ServerResourcesResponse! **
	--Get the current player instance ID
	Player_InstanceID = Chat.GetInstanceID();
	--Get the players X,Y and Z coordinates
	Player_Coords = Game.WorldToChunkCoord(Player.GetPosition().x, Player.GetPosition().y);
	--Get our currently connected region
	Player_Connected_Region = Game.GetConnectedRegion();
	--Rev up those website prefs
	local _Temp_WebsitePrefs = {};
	_Temp_WebsitePrefs.show_loadout = Addon_Player_Stats_Show_Loadouts and 1 or 0;
	_Temp_WebsitePrefs.show_progress = Addon_Player_Stats_Show_Progress and 1 or 0;
	_Temp_WebsitePrefs.show_inventory = Addon_Player_Stats_Show_Inventory and 1 or 0;
	_Temp_WebsitePrefs.show_unlocks = Addon_Player_Stats_Show_Unlocks and 1 or 0;
	_Temp_WebsitePrefs.show_location = Addon_Player_Stats_Show_Location and 1 or 0;
	_Temp_WebsitePrefs.show_pve_kills = Addon_Player_Stats_Show_PvE_Kills and 1 or 0;
	_Temp_WebsitePrefs.show_pve_stats = Addon_Player_Stats_Show_PvE_Stats and 1 or 0;
	_Temp_WebsitePrefs.show_pve_events = Addon_Player_Stats_Show_PvE_Events and 1 or 0;
	_Temp_WebsitePrefs.show_workbench = Addon_Player_Show_Workbench_Info and 1 or 0;
	_Temp_WebsitePrefs.show_craftables = Addon_Player_Show_Craftables and 1 or 0;
	_Temp_WebsitePrefs.show_market_listings = Addon_Player_Show_Market_Listings and 1 or 0;

	if final_send then
		RED5_Get_Crafting_Queue();
	end
	
	if Addon_Show_Log then
		AddChatMessage("Finished getting player info.");
	end
	
	-- Setup the data packet
	if Addon_Show_Log then
		AddChatMessage("Setting up JSON packet for TDDB...");
	end

	local table_TDDB_Packet = {
		-- Player information
		["Player_Instance"] = Player_InstanceID,
		["Player_Name"] = tostring(Player_Name),
		["Player_ID"] = tostring(Player_ID),
		["Player_EID"] = tostring(Player_EID),
		["ArmyID"] = tostring(Player_ArmyID),
		["Battle_Frame"] = Player_Battle_Frame,
		["Loadouts"] = Player_Loadouts,
		["Progress"] = Player_Progress,
		["Inventory"] = Player_Inventory,
		["Player_Coords"] = Player_Coords,
		["Player_Region"] = Player_Connected_Region,
		["Website_Prefs"] = _Temp_WebsitePrefs,
	};
	
	-- Release some keys to free up ram.
	Player_Loadouts = {};
	Player_Progress = {};
	Player_Inventory = {};
	
	--Set our init variable so OnBattleFrameChanged() can run when frames change.
	init_login = 0;
	init_options = 0;
	init_message = nil;
	init_webprefs = nil;
	
	TDDB_Send_Data(table_TDDB_Packet,TDDB_URL);
end

function Get_Nearby_Player_Data()
	-- Who is near me and what do they do?
	if Addon_Show_Log then
		AddChatMessage("Setting up variables for nearby player data...");
	end
	local targets = Game.GetAvailableTargets();
	local temp = {};
	for i = 1, #targets do
		temp[i] = Game.GetTargetInfo(targets[i]);
		temp[i].bounds = Game.GetTargetBounds(targets[i]);
	end
	
	if Addon_Show_Log then
		AddChatMessage("Getting nearby player data...");
	end
	local temp_region = Game.GetConnectedRegion();
	for i=1, #temp do
		if (temp[i].type == "character" and temp[i].isNpc == false and (temp[i].db_id ~= Player_ID)) then
			local temp2 = {};
			-- Setup a fallback just in case Get_Nearby_Player_Data executes faster than TDDB_Get_Player_Data can populate its data set
			if Player_InstanceID ~= nil then
				temp2.Player_Instance = Player_InstanceID;
			else
				temp2.Player_Instance = Chat.GetInstanceID();
			end
			temp2.Player_ID = temp[i].db_id;
			temp2.Battleframe = temp[i].battleframe;
			temp2.Coords = Game.WorldToChunkCoord(temp[i].bounds.x, temp[i].bounds.y);
			temp2.Player_Name = temp[i].name;
			temp2.ArmyID = temp[i].armyId;
			temp2.Player_Region = temp_region;
			-- Setup a fallback just in case Get_Nearby_Player_Data executes faster than TDDB_Get_Player_Data can populate its data set
			if Player_ID ~= nil then
				temp2.Spotter_ID = Player_ID;
			else
				temp2.Spotter_ID = Game.GetTargetInfo(Game.GetTargetIdByName(Player.GetInfo())).db_id;
			end
			table.insert(table_TDDB_Nearby_Players_Info,temp2);
		end
	end
end

function TDDB_Get_PvE_Stats()
	--We don't really have to do much, the counters are counting (ahh ahh ahhhhhhh) so prep the TDDB_Packet and send it!
	if Addon_Show_Log then
		AddChatMessage("Start PvE stats data packet creation...");
	end
	local _TEMP_table_TDDB_PvE_Stats = {};
	local _TEMP_table_TDDB_PvE_Events = {};
	-- Kill table, directly from Ikea.
	local _TEMP_table_TDDB_PvE_Kills = table_TDDB_PvE_Kills;
	
	-- Look at all those events
	_TEMP_table_TDDB_PvE_Events.ARES_Missions = ARES_Missions_Completed;
	_TEMP_table_TDDB_PvE_Events.Crashed_LGVs = Crashed_LGVs_Completed;
	_TEMP_table_TDDB_PvE_Events.Crashed_Thumpers = Crashed_Thumpers_Completed;
	_TEMP_table_TDDB_PvE_Events.Tornados = Tornados_Completed;
	_TEMP_table_TDDB_PvE_Events.Watchtowers_Retaken = Watchtowers_Retaken;
	_TEMP_table_TDDB_PvE_Events.Outposts_Defended = Outposts_Defended;
	_TEMP_table_TDDB_PvE_Events.LGV_Races = LGV_Races_Completed;
	_TEMP_table_TDDB_PvE_Events.LGV_Race_Fastest_Time = LGV_Race_Fastest_Time;
	_TEMP_table_TDDB_PvE_Events.Strike_Teams = Chosen_Strike_Teams_Killed;
	_TEMP_table_TDDB_PvE_Events.Warbringers = Warbringers_Killed;
	_TEMP_table_TDDB_PvE_Events.Watchtowers_Defended = Watchtowers_Defended;
	_TEMP_table_TDDB_PvE_Events.Holmgang_Tech_Completed = Holmgang_Tech_Completed;
	_TEMP_table_TDDB_PvE_Events.Sunken_Harbor_Invasions_Completed = Sunken_Harbor_Invasions_Completed;
	_TEMP_table_TDDB_PvE_Events.Thump_Dump_Invasions_Completed = Thump_Dump_Invasions_Completed;
	_TEMP_table_TDDB_PvE_Events.Raider_Squads_Defeated = Raider_Squads_Defeated;
	_TEMP_table_TDDB_PvE_Events.Chosen_Death_Squads_Defeated = Chosen_Death_Squads_Defeated;
	
	-- Never seen so many personal stats, keep those to yourself.
	_TEMP_table_TDDB_PvE_Stats.Revives = Revives;
	_TEMP_table_TDDB_PvE_Stats.Revived = Revived;
	_TEMP_table_TDDB_PvE_Stats.Deaths = Deaths;
	_TEMP_table_TDDB_PvE_Stats.Incapacitated = Incapacitated;
	_TEMP_table_TDDB_PvE_Stats.Suicides = Suicide;
	_TEMP_table_TDDB_PvE_Stats.Primary_Weapon_Shots_Fired = Primary_Weapon_Shots_Fired;
	_TEMP_table_TDDB_PvE_Stats.Secondary_Weapon_Shots_Fired = Secondary_Weapon_Shots_Fired;
	_TEMP_table_TDDB_PvE_Stats.Scanhammer_Kills = Scanhammer_Kills;
	_TEMP_table_TDDB_PvE_Stats.Primary_Reloads = Primary_Reloads;
	_TEMP_table_TDDB_PvE_Stats.Secondary_Reloads = Secondary_Reloads;
	_TEMP_table_TDDB_PvE_Stats.Accuracy = Accuracy;
	_TEMP_table_TDDB_PvE_Stats.Healed = Healed;
	_TEMP_table_TDDB_PvE_Stats.Headshots = Headshots;
	_TEMP_table_TDDB_PvE_Stats.Drowned = Drowned;
	_TEMP_table_TDDB_PvE_Stats.Damage_Done = Damage_Done;
	_TEMP_table_TDDB_PvE_Stats.Damage_Taken = Damage_Taken;
	
	if(Player_Name == nil) then
		-- Fallback for if player name is nil
		local temp;
		--Get player name
		Player_Name = Player.GetInfo();
		--Get the player Entity ID
		Player_EID = Player.GetTargetId();
		--Call Game.GetTargetInfo for data population
		temp = Game.GetTargetInfo(Player_EID);
		--Get the player UID
		Player_ID = temp.db_id;
	end
	
	local table_TDDB_Packet = {
		-- Player information
		["Player_Instance"] = Player_InstanceID,
		["Player_Name"] = tostring(Player_Name),
		["Player_ID"] = tostring(Player_ID),
		["Player_EID"] = tostring(Player_EID),
		-- PeeVeeEees information
		["PvE_Events"] = _TEMP_table_TDDB_PvE_Events,
		["PvE_Kills"] = _TEMP_table_TDDB_PvE_Kills,
		["PvE_Stats"] = _TEMP_table_TDDB_PvE_Stats,
	};
	if Addon_Show_Log then
		AddChatMessage("End PvE stats data packet creation.");
	end
	
	if Addon_Show_Log then
		AddChatMessage("Reset PvE Stats (All).");
	end
	
	--Release / reset all our stats
	Revives = 0;
	Revived = 0;
	Deaths = 0;
	Incapacitated = 0;
	Suicide = 0;
	Primary_Weapon_Shots_Fired = 0;
	Secondary_Weapon_Shots_Fired = 0;
	Scanhammer_Kills = 0;
	Primary_Reloads = 0;
	Secondary_Reloads = 0;
	Accuracy = 0;
	Healed = 0;
	Headshots = 0;
	Drowned = 0;
	Damage_Done = 0;
	Damage_Taken = 0;

	table_TDDB_PvE_Kills = {};
	table_TDDB_PvE_Kills.T1 = {};
	table_TDDB_PvE_Kills.T2 = {};
	table_TDDB_PvE_Kills.T3 = {};
	table_TDDB_PvE_Kills.T4 = {};
	
	ARES_Missions_Completed = {};
		ARES_Missions_Completed[1] = 0;
		ARES_Missions_Completed[2] = 0;
	Crashed_LGVs_Completed = 0;
	Crashed_Thumpers_Completed = 0;
	Tornados_Completed = {};
		Tornados_Completed[3] = 0;
		Tornados_Completed[4] = 0;
	Watchtowers_Retaken = 0;
	Outposts_Defended = 0;
	LGV_Races_Completed = 0;
	LGV_Race_Fastest_Time = {};
	Chosen_Strike_Teams_Killed = {};
		Chosen_Strike_Teams_Killed[1] = 0;
		Chosen_Strike_Teams_Killed[2] = 0;
		Chosen_Strike_Teams_Killed[3] = 0;
		Chosen_Strike_Teams_Killed[4] = 0;
	Warbringers_Killed = {};
		Warbringers_Killed[3] = 0;
		Warbringers_Killed[4] = 0;
	Watchtowers_Defended = 0;
	Holmgang_Tech_Completed = 0;
	Sunken_Harbor_Invasions_Completed = 0;
	Thump_Dump_Invasions_Completed = 0;
	Raider_Squads_Defeated = 0;
	Chosen_Death_Squads_Defeated = 0;
	
	-- Send that data!
	if Addon_Show_Log then
		AddChatMessage("Sending PvE data...");
	end
	TDDB_Send_Data(table_TDDB_Packet,PvE_TDDB_URL);
end

function Callback_Get_PvE_Stats()
	if Addon_Enabled then
		if Addon_Get_Player_Stats then
			if(Game.GetPvPMatchId() == nil) then
				TDDB_Get_PvE_Stats();
				if Addon_Show_Log then
					AddChatMessage("Callback (PvE) created.");
				end
				callback(Callback_Get_PvE_Stats, nil, 600);
			else
				if Addon_Show_Log then
					AddChatMessage("You are currently in PVP, data will not be collected.");
				end
			end
		else
			-- Keep the callback running just in case.
			if Addon_Show_Log then
				AddChatMessage("Callback (PvE - option OFF) created.");
			end
			callback(Callback_Get_PvE_Stats, nil, 600);
		end
	end
end

function Callback_Get_Player_Data()
	if Addon_Enabled then
		if Addon_Get_Player_Stats then
			if(Game.GetPvPMatchId() == nil) then
				RED5_Get_Inventory_Gear();
				if Addon_Show_Log then
					AddChatMessage("Callback (Player) created.");
				end
				callback(Callback_Get_Player_Data, nil, 1800);
			else
				if Addon_Show_Log then
					AddChatMessage("You are currently in PVP, data will not be collected.");
				end
			end
		else
			-- Keep the callback running just in case.
			if Addon_Show_Log then
				AddChatMessage("Callback (Player - option OFF) created.");
			end
			callback(Callback_Get_Player_Data, nil, 1800);
		end
	end
end

function Callback_Get_Nearby_Player_Data()
	if Addon_Enabled then
		if Addon_Get_Other_Player_Stats and (Game.GetPvPMatchId() == nil) then
			-- Get some player data
			Get_Nearby_Player_Data();

			if Addon_Show_Log then
				AddChatMessage("Setting up nearby player packet for sending...");
			end
			
			-- Setup the packet for nearby player info
			local table_TDDB_Packet = {
				-- Nearby player information
				["Nearby Player Info"] = table_TDDB_Nearby_Players_Info,
			};

			-- Only send the packet if we actually have data
			if next(table_TDDB_Nearby_Players_Info) ~= nil then
				TDDB_Send_Data(table_TDDB_Packet,Nearby_TDDB_URL);
				-- We are done with the packet, clear the table and free up some memory.
				-- ** WARNING: Reset variable **
				table_TDDB_Nearby_Players_Info = {};
			end
			
			if Addon_Show_Log then
				AddChatMessage("Nearby player data sent!");
			end
			if (not final_send_nearby) then
				-- We don't want callbacks stacking
				if Addon_Show_Log then
					AddChatMessage("Callback (Nearby) created.");
				end
				callback(Callback_Get_Nearby_Player_Data, nil, 300);
			end
		else
			-- Could be in PVP, keep our nearby player table clean.
			-- ** WARNING: Reset variable **
			table_TDDB_Nearby_Players_Info = {};
			-- Keep the callback going but extend it a bit, maybe they will want to send data later.
			if Addon_Show_Log then
				AddChatMessage("Callback (Nearby - option OFF) created.");
			end
			callback(Callback_Get_Nearby_Player_Data, nil, 600);
		end
	end
end

function TDDB_Send_Data(Packet,URL)
	if Addon_Show_Log then
		AddChatMessage("Data send start.");
	end
	-- Do a secondary check just in case the addon was unable to determine PVP status
	if(Game.GetPvPMatchId() == nil) then
		if not HTTP.IsRequestPending(URL) then
			HTTP.IssueRequest(URL, "POST", Packet, TDDB_Response);
		end
	else
		if Addon_Show_Log then
			AddChatMessage("You are currently in PVP, data will not be collected.");
		end
	end
end
	
function TDDB_Response(args, err)
	if (err) then
		warn(tostring(err));
	else
		if Addon_Show_Log then
			AddChatMessage(tostring(args));
		end
	end
	if Addon_Show_Log then
		AddChatMessage("Data send end.");
	end
	if final_send then
		AddChatMessage("Player data received.");
		final_send = false;
		-- Now do the nearby data
		if Addon_Get_Other_Player_Stats then
			final_send_nearby = true;
			if Addon_Show_Log then
				AddChatMessage("Callback (Nearby - on exit) created.");
			end
			callback(Callback_Get_Nearby_Player_Data, nil, 0);
		end
	end
	if final_send_nearby then
		AddChatMessage("Nearby Player data received.");
		final_send_nearby = false;
		-- Now do PvE!
		final_send_pve = true;
		if Addon_Show_Log then
			AddChatMessage("Callback (PvE - on exit) created.");
		end
		TDDB_Get_PvE_Stats();
	end
	if final_send_pve then
		AddChatMessage("PvE stats received.");
		final_send_pve = false;
	end
	return nil;
end

function TDDB_Version_Checker()
	--[[
		It should be mentioned that NOT forwarding an HTTP.IssueRequest to a response function and thus not giving a callback is a very very bad idea for any URL's that
		need to be hit more than one time. Do not do this unless you are absolutely certain that the URL will only be hit one time.
	]]--
	HTTP.IssueRequest(VersionCheck_TDDB_URL, "GET", nil, function (args, err)
		if(tostring(args.Version) ~= tostring(TDDB_Version)) then
			AddCriticalChatMessage("A new version is available, please check our website for more information. (www.thumpdumpdb.com)");
		end
		if((tostring(args.Build) ~= tostring(System.GetBuildId())) and (tonumber(args.Strict) == 1)) then
			AddCriticalChatMessage("Addon has been disabled for this build of Firefall, please check our website for more information. (www.thumpdumpdb.com)");
			-- Disable all the things!
			Addon_Enabled = false;
			Addon_Get_Player_Stats = false;
			Addon_Player_Stats_Show_Loadouts = false;
			Addon_Player_Stats_Show_Progress = false;
			Addon_Player_Stats_Show_Inventory = false;
			Addon_Player_Stats_Show_Unlocks = false;
			Addon_Player_Stats_Show_Location = false;
			Addon_Player_Stats_Show_PvE_Kills = false;
			Addon_Player_Stats_Show_PvE_Stats = false;
			Addon_Player_Stats_Show_PvE_Events = false;
			Addon_Get_Other_Player_Stats = false;
			Addon_OnExit_Save = false;
			Addon_Show_Log = false;
			Addon_Show_PVE_Log = false;
		end
		if(args.Msg ~= "") then
			-- We have a message!
			AddChatMessage(tostring(args.Msg));
		end
	end);
end

function OnPlayerReady()
	--[[ ASYNC PROCESS TREE:
		Init: Callback_Get_Player_Data()
		Calls:
			-> RED5_Get_Inventory_Gear()
			Response Forward:
				-> RED5_Get_Slotted_Gear()
				Response Forward:
					-> TDDB_Get_Equipment()
					Calls:
						-> RED5_Get_Progress_Unlocks()
						Response Forward:
							-> TDDB_Get_Tech_Tree_Unlocks()
							Calls:
								-> RED5_GetResourcesFromServer()
								Response Forward:
									-> TDDB_Get_Player_Data()
									Calls:
										->TDDB_Send_Data()
									
		Init: Callback_Get_Nearby_Player_Data()
		Calls:
			-> Get_Nearby_Player_Data()
			Calls:
				-> TDDB_Send_Data()
		
		Init: Callback_Get_PvE_Stats()
		Calls:
			-> TDDB_Get_PvE_Stats()
			Calls:
				-> TDDB_Send_Data()
				
		Init: OnMaybeExit()
		Calls:
			-> RED5_Get_Inventory_Gear()
			Response Forward:
				-> RED5_Get_Slotted_Gear()
				Response Forward:
					-> TDDB_Get_Equipment()
					Calls:
						-> RED5_Get_Progress_Unlocks()
						Response Forward:
							-> TDDB_Get_Tech_Tree_Unlocks()
							Calls:
								-> RED5_GetResourcesFromServer()
								Response Forward:
									-> TDDB_Get_Player_Data()
									Calls:
										-> RED5_Get_Crafting_Queue()
										Calls:
											-> TDDB_Send_Data()
											Response Forward:
												-> TDDB_Response()
												Calls:
													-> Callback_Get_Nearby_Player_Data()
													Calls:
														-> Get_Nearby_Player_Data()
														Calls:
															-> TDDB_Send_Data()
															Response Forward:
																-> TDDB_Get_PvE_Stats()
																Calls:
																	-> TDDB_Send_Data()
																
		Init: OnBattleFrameChanged()
		Calls:
			-> RED5_Get_Inventory_Gear()
			Response Forward:
				-> RED5_Get_Slotted_Gear()
				Response Forward:
					-> TDDB_Get_Equipment()
					Calls:
						-> RED5_Get_Progress_Unlocks()
						Response Forward:
							-> TDDB_Get_Tech_Tree_Unlocks()
							Calls:
								-> RED5_GetResourcesFromServer()
								Response Forward:
									-> TDDB_Get_Player_Data()
									Calls:
										->TDDB_Send_Data()
		
		Init: TDDB_Reward_Listener()
		Calls:
			-> Nothing
			
		Init: TDDB_PVE_Listener()
		Calls:
			-> Nothing
			
		Init: TDDB_Printer_Listner()
		Calls:
			-> RED5_Get_Crafting_Queue()
			Calls:
				-> TDDB_Send_Data()
	]]--
	if Addon_Enabled then
		-- Do a version check
		TDDB_Version_Checker();
		if Addon_Get_Player_Stats then
			-- Get our current primary weapon type in order to setup a functional weapon swap mechanic for PvE stats
			local weaponInfo = Player.GetWeaponInfo();
			primary_weapon_type = weaponInfo.WeaponType;
			if Addon_Show_Log then 
				AddChatMessage("Calling Red5 Resource collection...");
			end
			-- If the player is currently PVP'ing we do not want their data
			if(Game.GetPvPMatchId() == nil) then
				if Addon_Show_Log then
					AddChatMessage("Callback (init Player) created.");
				end
				callback(Callback_Get_Player_Data, nil, 0);
				-- Call our PvE stats collection in 10min.
				callback(Callback_Get_PvE_Stats, nil, 600);
			else
				if Addon_Show_Log then
					AddChatMessage("You are currently in PVP, data will not be collected.");
				end
			end
		end
		if Addon_Get_Other_Player_Stats then
			if Addon_Show_log then
				AddChatMessage("Starting TDDB Nearby Player Harvester callback...");
			end
			if(Game.GetPvPMatchId() == nil) then
				if Addon_Show_Log then
					AddChatMessage("Callback (init Nearby) created.");
				end
				callback(Callback_Get_Nearby_Player_Data, nil, 5);
			else
				if Addon_Show_Log then
					AddChatMessage("You are currently in PVP, data will not be collected.");
				end
			end
		end
	end
end

function TDDB_Printer_Listner(args)
	if Addon_Get_Player_Stats then
		--WHY ARE WE YELLING?
		if(args.terminal_type == "WORKBENCH") then
			--Crafting ain't easy
			if Addon_Show_log then
				AddChatMessage("Recipe list updated, gathering crafting certificate information for TDDB...");
			end
			recent_printer_activity = true;
			--It turns out the Red5 is just doing a SendHTTPRequest() instead of checking to see if a request is pending so we need to wait a few seconds.
			callback(RED5_Get_Crafting_Queue, nil, 10);
		end
	end
end

function OnBattleFrameChanged()
	if Addon_Enabled then
		if(Game.GetPvPMatchId() == nil) then
			-- Get our current primary weapon type in order to setup a functional weapon swap mechanic for PvE stats
			local weaponInfo = Player.GetWeaponInfo();
			primary_weapon_type = weaponInfo.WeaponType;
			-- We don't want to mess with any callbacks so leave Nearby Player stuff alone.
			if init_login == 0 and Addon_Get_Player_Stats then
				if Addon_Show_Log then 
					AddChatMessage("Battleframe changed, resending your player information now...");
					AddChatMessage("Calling Red5 Resource collection...");
				end
				RED5_Get_Inventory_Gear();
			end
		else
			if Addon_Show_Log then
				AddChatMessage("You are currently in PVP, data will not be collected.");
			end
		end
	end
end

function OnMaybeExit()
	if Addon_Enabled then
		if Addon_OnExit_Save then
			if Addon_Get_Player_Stats then
				if(Game.GetPvPMatchId() == nil) then
					AddCriticalChatMessage("Please wait 10 seconds for TDDB to receive your data.")
					final_send = true;
					RED5_Get_Inventory_Gear();
				else
					if Addon_Show_Log then
						AddChatMessage("You are currently in PVP, data will not be collected.");
					end
				end
			end
		end
	end
end

function SetOptionsAvailability()
	InterfaceOptions.DisableFrameMobility(FRAME, not addon_Enabled);
end