require "math";
require "string";
require "table";
require "lib/lib_InterfaceOptions"

local FRAME = Component.GetFrame("Main");
local NAME = Component.GetWidget("NAME");

-- Variables
local DUMP_TDDB_URL = "http://www.thumpdumpdb.com/v1/addon/dumps/?fn=Certs";
local DUMP_TDDB_URL2 = "http://www.thumpdumpdb.com/v1/addon/dumps/?fn=ProgressionUnlocks";

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

function TDDB_Send_Data(Packet,URL)
	HTTP.IssueRequest(URL, "POST", Packet, TDDB_Response);
end
	
function TDDB_Response(args, err)
	if (err) then
		warn(tostring(err));
	else
		if Addon_Show_Log then
			log(table.tostring(args));
		end
	end
	TDDB_Get_Progression_Unlocks();
	return nil;
end

function TDDB_Get_Progression_Unlocks()
	local progression_unlocks = {};
	progression_unlocks = Game.GetProgressionUnlocks();
	HTTP.IssueRequest(DUMP_TDDB_URL2, "POST", progression_unlocks, nil);
end

function OnPlayerReady()
	local certs_table = {};
	for i=1, 1440 do
		local temp_cert_info = Game.GetCertificationInfo(i);
		if(temp_cert_info ~= nil) then
			temp_cert_info.id = i;
			table.insert(certs_table,temp_cert_info);
		end
	end
	TDDB_Send_Data(certs_table,DUMP_TDDB_URL);
	TDDB_Get_Progression_Unlocks();
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