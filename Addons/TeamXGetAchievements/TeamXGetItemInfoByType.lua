require "math";
require "string";
require "table";
require "lib/lib_InterfaceOptions"

local FRAME = Component.GetFrame("ThumpDump DB GetItemInfoByType");
local NAME = Component.GetWidget("NAME");

-- Variables
local addon_Enabled

local Main_TDDB_URL = "http://www.thumpdumpdb.com/v1/addon/dumps/?fn=achievements";
local Player_Abilities = {};
local ginfo = {};
local big_ginfo = {};


local temp

-- Tables
local table_TDDB_Nearby_Players_Info = {};

-- Testing Variables


--Frame Options
InterfaceOptions.AddMovableFrame({
	frame = FRAME,
	label = "ThumpDump DB",
	scalable = true,
});

--Interface Options
InterfaceOptions.AddCheckBox({id="NAME", label="Show callsign", default=true})
InterfaceOptions.AddCheckBox({id="ENABLED", label="Enabled?", default=true})

function OnComponentLoad()

end

-- Red5 Copypasta
function RED5_Web_GetItemInfo(item_table, case)
	local result = {};
	local type
	local info
	
	if TeamX_Debug == 2 then
		log(tostring(item_table));
	end
	
	for i in pairs(item_table) do 
		if tonumber(case) == 0 then
			type = tonumber(item_table[i].item_type);
		elseif tonumber(case) == 1 then
			type = tonumber(item_table[i].itemTypeId);
		end
		
		info = Game.GetItemInfoByType(type);
		
		if (info ~= nil) then
			table.insert(result, type, info);
		end
	end
	return result;
end

function RED5_Web_GetLoadouts()
	local ldts = Player.GetLoadoutList();
	local ret = {};
	for i=1,#ldts do
		local loadout = ldts[i];
		if not loadout.is_default then
			local tbl = { id=tostring(loadout.id), name=loadout.name, level=loadout.level, quality=loadout.quality, archtype=loadout.archtype, items={} };
			tbl.items.battleframe = { item_id=loadout.items.chassis, item_type=loadout.item_types.chassis, modules=loadout.modules.chassis };
			tbl.items.backpack = { item_id=loadout.items.backpack, item_type=loadout.item_types.backpack, modules=loadout.modules.backpack };
			tbl.items.weapon1 = { item_id=loadout.items.primary_weapon, item_type=loadout.item_types.primary_weapon, modules=loadout.modules.primary_weapon };
			tbl.items.weapon2 = { item_id=loadout.items.secondary_weapon, item_type=loadout.item_types.secondary_weapon, modules=loadout.modules.secondary_weapon };
			ret[tbl.id] = tbl;
		end
	end
	--log("Web Loadouts: "..tostring(ret));
	local active = tostring(Player.GetCurrentLoadoutId());
	return active, ret;
end

function RED5_GetResourcesFromServer()
	local c_ResourceUrl = System.GetOperatorSetting("ingame_host").."/inventory/resource_quantities.json"

	if not HTTP.IsRequestPending(c_ResourceUrl) then
		HTTP.IssueRequest(c_ResourceUrl, "GET", nil, RED5_ServerResourcesResponse)
	else
		log("request pending.");
		callback(RED5_GetResourcesFromServer, nil, 0.25);
	end
end

function RED5_ServerResourcesResponse(resources, err)
	if err then
		warn(tostring(err.message))
		return nil
	end
	Player_Inventory = resources;
	log("TeamX: Starting data collection...");
	TDDB_Get_Data();
	log("TeamX: Data collection end.");
end
-- End Red5 copypasta

function OnMessage(args)
	if args.type == "ENABLED" then
		addon_Enabled = (args.data == true or args.data == "true")
		if addon_Enabled then
			NAME:Show()
		else 
			NAME:Hide()	
		end
		SetOptionsAvailability()
	else if args.type == "NAME" then
		addon_Name_Enabled = (args.data == true or args.data == "true")
		if addon_Name_Enabled then
			NAME:Show()
		else 
			NAME:Hide()
		end
	end
	end
end

function OnPlayerReady()
	log("~ - - - - - - - - - - - - [ TEAM X ] - - - - - - - - - - - - ~");
	log("TeamX: Calling Red5 Resource collection...");
	RED5_GetResourcesFromServer();
	
	
	function TDDB_Get_Data()
	
		--ginfo = Game.GetItemInfoByType( 30287 );
		--log(tostring(ginfo));
		
		log('loop --------------------');
		function table.empty(self)
			for _, _ in pairs(self) do
				return false
			end
			return true
		end
		
		function table_length(T)
			local count = 0
			for _ in pairs(T) do count = count + 1 end
			return count
		end

		for i=1,1000 do
			temp = Player.GetAchievementInfo(i);
			if((temp ~= nil) and (table.empty(temp) == false)) then
			--log(tostring(temp));
				ginfo[i] = temp;
			end
		end
		log("TeamX: Data send start.");

			TDDB_Send_Data(ginfo);
			table_TDDB_Packet = {};
	end
	
	function TDDB_Send_Data(packet)
		if not HTTP.IsRequestPending(Main_TDDB_URL) then
			HTTP.IssueRequest(Main_TDDB_URL, "POST", packet, TDDB_Response);
		else
			callback(TDDB_Send_Data, packet, 0.25);
		end
	end
	
	function TDDB_Response(args, err)
		if (err) then
			warn(tostring(err));
		else
			log(table.tostring(args));
		end
		log("TeamX: Data send end.");
		log("~ - - - - - - - - - - - - [ TEAM X ] - - - - - - - - - - - - ~");
		return nil;
	end
end

function SetOptionsAvailability()
	InterfaceOptions.DisableFrameMobility(FRAME, not addon_Enabled);
end

-- Placeholders
function OnExperienceChanged()

end

function OnBattleframeChanged()

end

function OnExperienceChanged()

end
-- End Placeholders

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
