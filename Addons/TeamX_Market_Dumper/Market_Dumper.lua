require "string";
require "math";
require "table";

local category_list;
local category_index = 1;
local current_category;
local current_category_name;
local max_pages = 0;
local current_page = 1;
local market_data = {};

function Get_Red5_Market_Categories()
	-- Get the category listing
	local category_url = System.GetOperatorSetting("market_host") .. "/api/v1/market_categories";
	if not HTTP.IsRequestPending(category_url) then
		HTTP.IssueRequest(category_url, "GET", nil, Get_Red5_Market_Set_Category_List);
	end
end

function Get_Red5_Market_Set_Category_List(args,err)
	category_list = args;
	--If you want to override the category_index, do it here
	
	Get_Red5_Market_Dump(tonumber(category_index));
end

function Get_Red5_Market_Dump(category_index)
	current_category = category_list[category_index].id;
	current_category_name = category_list[category_index].name:gsub("%s+", "");
	log(tostring(current_category) .. " Page: " .. tostring(current_page) .. " of: " .. tostring(max_pages));
	local market_url = System.GetOperatorSetting("market_host") .. "/api/v1/search?query=category:" .. current_category .. "&page=" .. current_page .. "&per_page=10"
	if not HTTP.IsRequestPending(market_url) then
		HTTP.IssueRequest(market_url, "GET", nil, TDDB_Market_Data_Dumper);
	end
end

function TDDB_Market_Data_Dumper(args,err)
	table.insert(market_data,args);
	max_pages = math.ceil(tonumber(args.total) / 10);
	if(current_page < max_pages) then
		current_page = current_page + 1;
		callback(Get_Red5_Market_Dump,category_index,0.15);
	elseif((current_page == max_pages) or (max_pages == 0)) then
		current_page = 1;
		local url = "http://www.thumpdumpdb.com/qa/addon/market/?fn=" .. current_category .. "_" .. tostring(current_category_name);
		local TDDB_Packet = market_data;
		market_data = {};
		HTTP.IssueRequest(url, "POST", TDDB_Packet, TDDB_Market_Data_dumper_Callback);
	end
end

function TDDB_Market_Data_dumper_Callback(args,err)
	if(category_index ~= #category_list) then
		category_index = category_index + 1;
		callback(Get_Red5_Market_Dump,category_index,0.15);
	else
		return;
	end
end

function OnPlayerReady()
	Get_Red5_Market_Categories();
end