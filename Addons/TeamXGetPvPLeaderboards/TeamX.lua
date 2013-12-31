require "math";
require "string";
require "table";
require "lib/lib_InterfaceOptions"

local FRAME = Component.GetFrame("Main");
local NAME = Component.GetWidget("NAME");

-- Variables
local PVP_TDDB_URL = "http://www.thumpdumpdb.com/qa/addon/pvpstats/?fn=pvp";
local PVP_UPDATE_TDDB_URL = "http://thumpdumpdb.com/qa/addon/pvpstatstime";

-- Addon Control Variables
local Addon_Enabled = true

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
function Get_Leaderboard_Data(URL)
	if not HTTP.IsRequestPending(URL) then
		HTTP.IssueRequest(URL, "GET", nil, Response)
	else
		callback(Get_Leaderboard_Data, URL, 0.03);
	end
end

function Response(data, err)
	if err then
		warn(tostring(err.message))
		return nil
	end
	local packet = data;
	local append_url;

	if(data.title == "Jetball Leaderboard (Preseason) ") then
		append_url = "_jetball";
	end
	
	if(data.title == "Team Death Match Leaderboard (Preseason) ") then
		append_url = "_tdm";
	end
	
	if(data.title == "Sabotage Leaderboard (Preseason) ") then
		append_url = "_sabotage";
	end
	
	if(data.title == "Harvester Leaderboard (Preseason) ") then
		append_url = "_harvester";
	end
	
	packet.url = PVP_TDDB_URL .. append_url;
	
	TDDB_Send_Data(packet);
end

function update_pvp_leaderboards()
	HTTP.IssueRequest(PVP_UPDATE_TDDB_URL, "GET", nil, nil);
end

function Testin_thangs()
	-- Ok, so we need some army data.
	local TDM_URL = System.GetOperatorSetting("ingame_host") .. "/leaderboards.json?gametype=tdm&page=1&per_page=500";
	local Jetball_URL = System.GetOperatorSetting("ingame_host") .. "/leaderboards.json?gametype=jetball&page=1&per_page=500";
	local Sabotage_URL = System.GetOperatorSetting("ingame_host") .. "/leaderboards.json?gametype=sabotage&page=1&per_page=500";
	local Harvester_URL = System.GetOperatorSetting("ingame_host") .. "/leaderboards.json?gametype=harvester&page=1&per_page=500";
	Get_Leaderboard_Data(TDM_URL);
	Get_Leaderboard_Data(Jetball_URL);
	Get_Leaderboard_Data(Sabotage_URL);
	Get_Leaderboard_Data(Harvester_URL);
	--Issue a callback, get this going again in 1h
	callback(Testin_thangs, nil, 3600);
	callback(update_pvp_leaderboards, nil, 300);
end
-- END TESTING DATA --

function TDDB_Send_Data(Packet)
	if not HTTP.IsRequestPending(Packet.url) then
		HTTP.IssueRequest(Packet.url, "POST", Packet, TDDB_Response);
	else
		callback(TDDB_Send_Data, Packet, 0.25);
	end
end
	
function TDDB_Response(args, err)
	if (err) then
		warn(tostring(err));
	else
		if Addon_Show_Log then
			log(tostring(args));
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