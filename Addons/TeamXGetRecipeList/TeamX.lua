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

-- TESTING DATA --
function Testin_thangs()
	local recipe_list = {};	
	recipe_list = Game.GetRecipeList();
	
	local recipe_breakdown = {};
	
	for i,v in pairs(recipe_list) do
		local info = Game.GetRecipe(recipe_list[i].id);
		info.cert_outputs = nil;
		recipe_list[i].inputs = info.inputs;
	end
	
	pack_that_data_up = {};
	
	table.insert(pack_that_data_up,recipe_list);
	
	return pack_that_data_up;
end
-- END TESTING DATA --

function TDDB_Send_Data(Packet)
	if Addon_Show_Log then
		AddChatMessage("Data send start.");
	end
	
	if not HTTP.IsRequestPending() then
		HTTP.IssueRequest(Main_TDDB_URL, "POST", Packet, TDDB_Response);
	end
end
	
function TDDB_Response(args, err)
	if (err) then
		warn(tostring(err));
	else
		if Addon_Show_Log then
			AddChatMessage(table.tostring(args));
		end
	end
	if Addon_Show_Log then
		AddChatMessage("Data send end.");
	end
	
	return nil;
end

function OnPlayerReady()
	-- TESTING THANGS --
	local stuff={};
	stuff = Testin_thangs();

	TDDB_Send_Data(stuff);
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