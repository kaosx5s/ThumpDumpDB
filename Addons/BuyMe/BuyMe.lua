require "math";
require "string";
require "table";
require "lib/lib_InterfaceOptions"

local FRAME = Component.GetFrame("Main");
local NAME = Component.GetWidget("NAME");

-- Variables
local Main_TDDB_URL = "http://www.thumpdumpdb.com/v1/addon/dumps/?fn=recipes";

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

function RED5_Get_Inventory_Gear()
	local name, faction, race, sex, id = Player.GetInfo()
	local g_charId = id;
	local c_ResourceUrl = System.GetOperatorSetting("clientapi_host").."/api/v3/characters/"..g_charId.."/inventories/gear/items"
		
	if not HTTP.IsRequestPending(c_ResourceUrl) then
		HTTP.IssueRequest(c_ResourceUrl, "GET", nil, Inventory_Response);
	else
		if Addon_Show_Log then 
			AddChatMessage("request pending (gear - items).");
		end
		callback(RED5_Get_Inventory_Gear, nil, 3);
	end
end

-- TESTING DATA --
function Testin_thangs()
	-- attempt to purchase something from the market
	local ff_id = 26710931;
	-- this item had a guid of 13065485359518546429, check for that in inventory after purchase
	local market_buy_url = System.GetOperatorSetting("market_host").."/api/v1/buy/".. tonumber(ff_id);
	local blank_table = {};
	HTTP.IssueRequest(market_buy_url, "POST", blank_table, function (args,err)
		if(err) then
			log(tostring(err));
		end
		
		if(args) then
			log(tostring(args));
		end
	end);

	RED5_Get_Inventory_Gear();
end

function Inventory_Response(args,err)
	log(tostring(args));
end
-- END TESTING DATA --

function OnPlayerReady()
	-- TESTING THANGS --
	Testin_thangs();
end

function SetOptionsAvailability()
	InterfaceOptions.DisableFrameMobility(FRAME, not addon_Enabled);
end

-- Utility Functions --
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