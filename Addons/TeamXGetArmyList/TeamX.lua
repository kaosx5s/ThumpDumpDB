require "math";
require "string";
require "table";
require "lib/lib_InterfaceOptions"

local FRAME = Component.GetFrame("Main");
local NAME = Component.GetWidget("NAME");

-- Variables
local Army_TDDB_URL = "http://www.thumpdumpdb.com/v1/addon/dumps/?fn=armylist";
local Roster_TDDB_URL = "http://www.thumpdumpdb.com/v1/addon/dumps/?fn=roster";

local Army_Data = {};
local Army_Data_Link_List = {}; -- Not to be confused with a linked list! ;p
local Army_Data_Info_and_Roster = {};
local list_id_holder = 1;
local list_index_holder = 1;

-- Addon Control Variables
local Addon_Enabled = true

local temp

--Frame Options
InterfaceOptions.AddMovableFrame({
	frame = FRAME,
	label = "ThumpDump DB",
	scalable = true,
});

--Interface Options
InterfaceOptions.StartGroup({label_key="Basic"})
InterfaceOptions.AddCheckBox({id="ENABLED", label="Enabled", default=Addon_Enabled})
InterfaceOptions.StopGroup()

function OnOptionChange(id, val)
	if id == "ENABLED" then
		Addon_Enabled = val
		if Addon_Enabled then
			FRAME:Show()
		else
			-- Hide the frame
			FRAME:Hide()
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
end

-- TESTING DATA --
function callback_Army_Roster(id)
	if id <= #Army_Data_Link_List then
		if id % 500 == 0 then
			-- iterate K
			list_id_holder = list_id_holder+1;
			log('army link list iterated!');
			local Army_Data_Info_and_Roster_temp = Army_Data_Info_and_Roster;
			TDDB_Send_Data(Army_Data_Info_and_Roster_temp,Roster_TDDB_URL);
			log('WARNING: Army Rosters sent, resetting tables!');
			Army_Data_Info_and_Roster = {};
		end
		list_index_holder = id+1;
		Get_Army_Roster(Army_Data_Link_List[id]);
	else
		-- At the end of our army list, great work computer.
		log('hit the end of the army link list');
		Exit_Army_List_Info();
	end
end

function Get_Army_Roster(Army_ID)
	URL = System.GetOperatorSetting("ingame_host")..Army_ID..".json";
	if not HTTP.IsRequestPending(URL) then
		HTTP.IssueRequest(URL, "GET", {}, RED5_ServerResourcesResponse_Roster)
	else
		callback(Get_Army_Roster, URL, 0.01);
	end
end

function RED5_ServerResourcesResponse_Roster(data, err)
	if err then
		log("error getting data");
		--Just keep going
		callback_Army_Roster(list_index_holder);
	else
		local temp = {};
		temp[list_id_holder] = {};
		temp[list_id_holder].army_id = Army_Data_Link_List[list_index_holder-1];
		temp[list_id_holder].army_info = data.army;
		temp[list_id_holder].army_roster = data.army_roster;
		table.insert(Army_Data_Info_and_Roster, temp);
		callback_Army_Roster(list_index_holder);
	end
end

function Get_Army_Data(URL)
	if URL == nil or URL == "" then
		URL = System.GetOperatorSetting("ingame_host").."/armies.json?page=1&per_page=1000"
	end

	if not HTTP.IsRequestPending(URL) then
		HTTP.IssueRequest(URL, "GET", nil, RED5_ServerResourcesResponse)
	else
		callback(Get_Army_Data, URL, 0.03);
	end
end

function RED5_ServerResourcesResponse(data, err)
	if err then
		warn(tostring(err.message))
		return nil
	end
	
	Army_Data[data.next_page_url] = data.army_list;
	for i=1, #data.army_list.results do
		local temp = {}
		temp = data.army_list.results[i].link;
		table.insert(Army_Data_Link_List, temp);
	end
	
	--Turns out the roster info iteration returns more data, if for any reason you want just the basic army data send this instead
	--TDDB_Send_Data(Army_Data[data.next_page_url],Army_TDDB_URL);
	
	if data.next_page_url ~= "empty" then
		Get_Army_Data(System.GetOperatorSetting("ingame_host")..data.next_page_url);
	else
		log('hit the end of the army list');
		Exit_Army_List();
	end
	
	return nil;
end

function Exit_Army_List()
	-- Null out the table listing, save some memory
	Army_Data = {};
	
	-- Send over the army link list
	TDDB_Send_Data(Army_Data_Link_List,Army_TDDB_URL);
	
	-- Get some info and rosters brah
	callback_Army_Roster(1);
end

function Exit_Army_List_Info()
	-- Null out the tables and save!
	Army_Data_Link_List = {};
	
	--Send out the last data chunk
	local Army_Data_Info_and_Roster_temp = Army_Data_Info_and_Roster;
	TDDB_Send_Data(Army_Data_Info_and_Roster_temp,Roster_TDDB_URL);
	
	-- Null out the tables
	Army_Data_Info_and_Roster = {};
	Army_Data = {};
	
end

function Testin_thangs()
	-- Ok, so we need some army data.
	Get_Army_Data(next_URL);
end
-- END TESTING DATA --

function TDDB_Send_Data(Packet,URL)
	if not HTTP.IsRequestPending(URL) then
		HTTP.IssueRequest(URL, "POST", Packet, TDDB_Response);
	else
		callback(TDDB_Send_Data, {Packet,URL}, 0.25);
	end
end
	
function TDDB_Response(args, err)
	if (err) then
		warn(tostring(err));
	else
		if Addon_Show_Log then
			log(table.tostring(args));
		end
	end
	
	return nil;
end

function OnPlayerReady()
	-- TESTING THANGS --
	Testin_thangs();
end

function SetOptionsAvailability()
	InterfaceOptions.DisableFrameMobility(FRAME, not addon_Enabled);
end

-- Utility Functions --
function table.length(tbl)
  local count = 0
  for _ in pairs(tbl) do count = count + 1 end
  return count
end

function table.tostring( tbl )
  local result, done = {}, {}
  for k, v in ipairs( tbl ) do
    table.insert( result, table.val_to_str( v ) )
    done[ k ] = true
  end
  for k, v in pairs( tbl ) do
    if not done[ k ] then
      table.insert( result,
        table.key_to_str( k ) .. "=" .. table.val_to_str( v ) )
    end
  end
  return "{" .. table.concat( result, "," ) .. "}"
end

function table.val_to_str ( v )
  if "string" == type( v ) then
    v = string.gsub( v, "\n", "\\n" )
    if string.match( string.gsub(v,"[^'\"]",""), '^"+$' ) then
      return "'" .. v .. "'"
    end
    return '"' .. string.gsub(v,'"', '\\"' ) .. '"'
  else
    return "table" == type( v ) and table.tostring( v ) or
      tostring( v )
  end
end

function table.key_to_str ( k )
  if "string" == type( k ) and string.match( k, "^[_%a][_%a%d]*$" ) then
    return k
  else
    return "[" .. table.val_to_str( k ) .. "]"
  end
end