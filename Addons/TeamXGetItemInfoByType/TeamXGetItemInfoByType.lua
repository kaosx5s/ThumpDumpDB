require "math";
require "string";
require "table";
require "lib/lib_InterfaceOptions"

local FRAME = Component.GetFrame("ThumpDump DB GetItemInfoByType");
local NAME = Component.GetWidget("NAME");

-- Variables
local addon_Enabled

local Main_TDDB_URL = "http://www.thumpdumpdb.com/v1/addon/dumps/?fn=giibt";
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
	TDDB_Get_Data();
end

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

	local count=1;
	local highest=0;
	local k=0;
	for i=1,100000 do
		temp = Game.GetItemInfoByType(i);
		if((temp ~= nil) and (table.empty(temp) == false)) then
			ginfo[i] = temp;
			highest=i;
			if(count % 1000 == 0) then
				log(tostring(table_length(ginfo)));
				big_ginfo[k]=ginfo;
				ginfo = {};
				k=k+1;
			end
			count=count+1;
		end
	end
	-- all those leftovers ;(
	big_ginfo[k]=ginfo;
	
	log(tostring(count));
	log(tostring(highest));
	log("TeamX: Data send start.");
	local table_TDDB_Packet = {};
	
	for n=0,k do
		log("TeamX: Setting up JSON packet for TDDB...");
		table_TDDB_Packet = {
			['fullitems_'..n] = big_ginfo[n];
		};
		TDDB_Send_Data(table_TDDB_Packet);
		table_TDDB_Packet = {};
	end
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
		log(tostring(args));
	end
	log("TeamX: Data send end.");
	log("~ - - - - - - - - - - - - [ TEAM X ] - - - - - - - - - - - - ~");
	return nil;
end

function SetOptionsAvailability()
	InterfaceOptions.DisableFrameMobility(FRAME, not addon_Enabled);
end