require "math";
require "string";
require "table";
require "lib/lib_InterfaceOptions";
require "lib/lib_MapMarker";

local FRAME = Component.GetFrame("Main");
local NAME = Component.GetWidget("NAME");

-- Variables

-- Temps
local w_PLATES	= {};

-- Addon Control Variables
local Addon_Enabled = true;

--Frame Options
InterfaceOptions.AddMovableFrame({
	frame = FRAME,
	label = "ThumpDump DB",
	scalable = true,
});

--Interface Options
InterfaceOptions.StartGroup({label="Addon"})
InterfaceOptions.AddCheckBox({id="ENABLED", label="Enabled", default=Addon_Enabled})
InterfaceOptions.StopGroup()

function OnOptionChange(id, val)
	
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

end

function OnEntityAvailable(args)
	if (Game.GetTargetInfo(args.entityId).hostile) or (Game.GetTargetInfo(args.entityId).type == "character") then
		--log(tostring(args));
		--log("enemy found:");
		--log(tostring(Game.GetTargetInfo(args.entityId)));
		--log(tostring(Game.GetTargetVitals(args.entityId)));
		--log(tostring(Game.GetMapCoordinates()));
		if(Game.GetTargetInfo(args.entityId).hostile) then
			--log(tostring(Game.GetTargetCharacterInfo(args.entityId)));
			--log(tostring(Game.GetTargetInfo(args.entityId).characterTypeId));
			--log(tostring(Game.GetCharacterTypeInfo(Game.GetTargetInfo(args.entityId).characterTypeId)));
			--log(tostring(Game.GetItemInfoByType(Game.GetCharacterTypeInfo(Game.GetTargetInfo(args.entityId).characterTypeId).weapon1TypeId)));
			--log(tostring(Game.GetItemInfoByType(Game.GetCharacterTypeInfo(Game.GetTargetInfo(args.entityId).characterTypeId).chassisTypeId)));
			--log(tostring(Game.GetTargetBounds(args.entityId)));
		end
		local plate = {tag=tostring(args.entityId), entityId=args.entityId};
		plate.frame = Component.CreateFrame("TrackingFrame", plate.tag);
		plate.anchor = plate.frame:GetAnchor();
		plate.group = Component.CreateWidget("plate", plate.frame);

		plate.header = {group=plate.group:GetChild("header")};
		plate.header.title = plate.header.group:GetChild("title");
		plate.header.name = plate.header.group:GetChild("name");
		if(Game.GetTargetInfo(args.entityId).title ~= nil) then
			plate.header.title:SetText(Game.GetTargetInfo(args.entityId).title);
		end
		plate.header.name:SetText(Game.GetTargetInfo(args.entityId).name);
		
		plate.info = {group=plate.group:GetChild("information")};
		plate.info.max_hp = plate.info.group:GetChild("max_hp");
		plate.info.current_hp = plate.info.group:GetChild("current_hp");
		plate.info.mob_type = plate.info.group:GetChild("mob_type");
		plate.info.faction = plate.info.group:GetChild("faction");
		plate.info.weapon_id = plate.info.group:GetChild("weapon_id");
		plate.info.chassis_id = plate.info.group:GetChild("chassis_id");
		plate.info.coord_x = plate.info.group:GetChild("coord_x");
		plate.info.coord_y = plate.info.group:GetChild("coord_y");
		plate.info.coord_z = plate.info.group:GetChild("coord_z");
		
		plate.info.max_hp:SetText(Game.GetTargetVitals(args.entityId).MaxHealth);
		plate.info.current_hp:SetText(Game.GetTargetVitals(args.entityId).Health);
		--plate.info.faction:SetText(Game.GetTargetInfo(args.entityId).faction);
		if(Game.GetTargetInfo(args.entityId).hostile) then
			--plate.info.mob_type:SetText(Game.GetTargetInfo(args.entityId).characterType);
			--plate.info.weapon_id:SetText(tostring(Game.GetCharacterTypeInfo(Game.GetTargetInfo(args.entityId).characterTypeId).weapon1TypeId));
			--plate.info.chassis_id:SetText(tostring(Game.GetCharacterTypeInfo(Game.GetTargetInfo(args.entityId).characterTypeId).chassisTypeId));
			--plate.info.coord_x:SetText((Game.GetTargetBounds(args.entityId)).x - Game.GetMapCoordinates().x);
			--plate.info.coord_y:SetText((Game.GetTargetBounds(args.entityId)).y - Game.GetMapCoordinates().y);
			--plate.info.coord_z:SetText((Game.GetTargetBounds(args.entityId)).z - Game.GetMapCoordinates().z);
		end
		plate.color = "00FF00";
		if(Game.GetTargetInfo(args.entityId).title ~= nil) then
			plate.header.title:SetTextColor(plate.color);
		end
		plate.header.name:SetTextColor(plate.color);
	
		plate.anchor:BindToEntity(args.entityId, "FX_Head", false, true);
		plate.frame:SetBounds(0,0,0,0)
		plate.anchor:SetParam("rotation",{axis={x=0,y=0,z=1}, angle=0});
		plate.frame:SetScaleRamp(1, 1, 1, 1);
		--PLATE.FRAME:SetFocalMode(true);
		plate.frame:SetScene("world");
		plate.anchor:LookAt("screen");
		plate.anchor:SetParam("translation", {x=0, y=0, z=0.25})
		plate.anchor:SetParam("entity_bounds_offset", {x=0, y=0, z=0})
		plate.group:SetDims("width:100; height:5");	
		plate.frame:BindEvent("OnGotFocus", "OnPlateGotFocus");
		plate.frame:BindEvent("OnLostFocus", "OnPlateLostFocus");
		w_PLATES[plate.tag] = plate;
		--if (bindSuccess) then
		--	PLATE.ANCHOR:SetParam("translation", {x=0, y=0, z=.25});
		--else
		--	PLATE.ANCHOR:SetParam("entity_bounds_offset", {x=0, y=0, z=1});
		--end
	end
end

function OnPlateGotFocus(args)
	local plate = GetPlateFromArgs(args);
	if (plate) then
		plate:SetParam("alpha", 1);
	end
end

function OnPlateLostFocus(args)
	local plate = GetPlateFromArgs(args);
	if (plate) then
		plate:SetParam("alpha", 0);
	end
end

function GetPlateFromArgs(args)
	local tag;
	if (args.frame) then
		tag = args.frame:GetName();
	elseif (args.entityId) then
		tag = tostring(args.entityId);
	else
		error("cannot find plate from args:"..tostring(args));
	end
	return w_PLATES[tag];
end


function OnEntityLost(args)
	-- args = {entityId[, timeout]}
	--UpdateEntityPlate(args);
	local plate = GetPlateFromArgs(args);
	if (not plate) then
		return;	-- none of our concern
	end
	
	if (plate.group) then
		Component.RemoveWidget(plate.group);
		plate.group = nil;	
	end
	if (plate.frame) then
		Component.RemoveFrame(plate.frame);
		plate.frame = nil;	
	end
end

function UpdateEntityPlate(args)
	if(args.entityId) then
		local entity = args.entityId;
		local plate = GetPlateFromArgs(args);
		if(plate) then
			plate.info.current_hp:SetText(Game.GetTargetVitals(entity).Health);
			if(Game.GetTargetInfo(args.entityId).hostile) then
				--plate.info.coord_x:SetText((Game.GetTargetBounds(args.entityId)).x - Game.GetMapCoordinates().x);
				--plate.info.coord_y:SetText((Game.GetTargetBounds(args.entityId)).y - Game.GetMapCoordinates().y);
				--plate.info.coord_z:SetText((Game.GetTargetBounds(args.entityId)).z - Game.GetMapCoordinates().z);
			end
		end
	end
end

function OnEntityStatusChanged(args)
	-- args = {entityId}
	local plate = GetPlateFromArgs(args);
	if (not plate) then
		return;	-- none of our concern
	end
	local status = Game.GetTargetStatus(args.entityId);
	if(status.state == "dead") then
		if (plate.group) then
			Component.RemoveWidget(plate.group);
			plate.group = nil;	
		end
		if (plate.frame) then
			Component.RemoveFrame(plate.frame);
			plate.frame = nil;	
		end
	else
		UpdateEntityPlate(args);
	end
end

function OnEntityVitalsChanged(args)
	local plate = GetPlateFromArgs(args);
	if (not plate) then
		return;	-- none of our concern
	end
	local status = Game.GetTargetVitals(args.entityId);
	if(status) then
		if(status.Health == 0) then
			if (plate.group) then
				Component.RemoveWidget(plate.group);
				plate.group = nil;	
			end
			if (plate.frame) then
				Component.RemoveFrame(plate.frame);
				plate.frame = nil;	
			end
		else
			UpdateEntityPlate(args);
		end
	end
end

function OnHideEntity(args)
	local entityId = args.entityId;
	local hide = args.hide;
	local plate = GetPlateFromArgs(args);
	if (hide) then
		if (plate) then
			OnEntityLost({entityId=entityId, timeout=1});
		end
	else
		local info = Game.GetTargetInfo(entityId);
		if (info and not plate) then
			OnEntityAvailable({entityId=entityId, type=info.type});
		end
	end
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