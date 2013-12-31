require "string";
require "math";
require "table";
--require "lib/Callback2";

local category_list = {};
local category_index = {};
local max_pages = {};
local current_page = {};
local category_name = {};
local market_data = {};
local category_totals = {};
local global_index = 0;
local timer = 0;
local hit_db = 0;
local _TEMP_callback_holder = 0;

function callback_timer()
	timer = timer + 1;
end

function callback_controller()
	Get_Red5_Market_Categories();
	--Every 25min grab the good stuff.
	callback(callback_controller, nil, 1800);
end

function callback_penetrator()
	-- Just the tip
	local url = "http://www.thumpdumpdb.com/qa/addon/markettime";
	-- Honestly this should never be pending but why not check anyway.
	if not HTTP.IsRequestPending(url) then
		HTTP.IssueRequest(url, "GET", nil, nil);
		hit_db = 0;
	else
		callback(callback_penetrator, nil, 5);
	end
end

function Get_Red5_Market_Categories()
	-- Start the timer (note we never stop this, so use it for debuggin onry)
	--CYCLE = Callback2.CreateCycle(callback_timer, nil);
	--CYCLE:Run(1);
	-- Get the category listing
	local category_url = System.GetOperatorSetting("market_host") .. "/api/v1/market_categories";
	if not HTTP.IsRequestPending(category_url) then
		HTTP.IssueRequest(category_url, "GET", nil, Get_Red5_Market_Set_Category_List);
	end
end

function Get_Red5_Market_Set_Category_List(args,err)
	-- Strip bad categories out of the list
	if((args ~= nil) and (next(category_list) == nil)) then
		for i=1, #args do
			if((args[i].id ~= 0) and (args[i].id ~= 3378) and (args[i].id ~= 80) and (args[i].id ~= 1590) and (args[i].id ~= 89) and (args[i].id ~= 1603) and (args[i].id ~= 207)) then
				table.insert(category_list,args[i]);
			end
		end
		--log(tostring(category_list));
	end
	
	local index_counter = 1;
	-- What am I going to do with all this thread? (its not really threading thanks to shitty http callbacks, but it makes sure the delta of un-used time is approximately zero.)
	for i=1, #category_list do
		--Distribute threads
		category_index[index_counter] = category_list[index_counter].id;
		category_name[index_counter] = category_list[index_counter].name:gsub("%s+", "");
		current_page[index_counter] = 1;
		max_pages[index_counter] = 2;
		market_data[category_index[index_counter]] = {};
		category_totals[category_index] = {};
		Get_Red5_Market_Dump(index_counter);
		index_counter = index_counter + 1;
	end
	--log(tostring(category_index));
	--log(tostring(category_name));
	--log(tostring(current_page));
	--log(tostring(market_data));
end

function Get_Red5_Market_Dump(index_counter)
	if(index_counter ~= nil) then
		--log(tostring(category_index[index_counter]) .. " Page: " .. tostring(current_page[index_counter]) .. " of: " .. tostring(max_pages[index_counter]));
		local market_url = System.GetOperatorSetting("market_host") .. "/api/v1/search?query=category:" .. tonumber(category_index[index_counter]) .. "&page=" .. tostring(current_page[index_counter]) .. "&per_page=10"
		if not HTTP.IsRequestPending(market_url) then
			global_index = index_counter;
			HTTP.IssueRequest(market_url, "GET", nil, TDDB_Get_Market_Data_Response);
		else
			--log("!!!WARNING: had to issue callback for " .. tostring(category_index[index_counter]) .. " on page: " .. tostring(current_page[index_counter]) .. "!!!");
			callback(Get_Red5_Market_Dump, index_counter, 0.15);
		end
	else
		log("index counter was null!");
	end
end

function TDDB_Get_Market_Data_Response(args, err)
	if(args ~= nil) then
		table.insert(market_data[category_index[global_index]],args);
		if(args.total ~= nil) then
			category_totals[category_index] = args.total;
		end
		max_pages[global_index] = math.ceil(tonumber(category_totals[category_index]) / 10);
		if(current_page[global_index] < max_pages[global_index]) then
			current_page[global_index] = current_page[global_index] + 1;
			callback(Get_Red5_Market_Dump, global_index, 0.05);
		elseif((current_page[global_index] == max_pages[global_index]) or (max_pages[global_index] == 0)) then
			local TDDB_Packet = market_data[category_index[global_index]];
			market_data[category_index[global_index]] = nil;
			TDDB_Send_Market_Data(TDDB_Packet,category_index[global_index],category_name[global_index])
		end
	else
		log("args was nil! - reparsing page - " .. category_name[global_index] .. " page: " .. current_page[global_index]);
		callback(Get_Red5_Market_Dump, global_index, 0.05);
	end
end

function TDDB_Send_Market_Data(TDDB_Packet,category,category_name)
	if(_TEMP_callback_holder ~= 0) then
		--this normally happens when the previous send is still going and a callback was issued.
		TDDB_Packet = _TEMP_callback_holder.packet;
		category = _TEMP_callback_holder.category;
		category_name = _TEMP_callback_holder.category_name;
		_TEMP_callback_holder = 0;
	end
	local url = "http://www.thumpdumpdb.com/qa/addon/market/?fn=" .. tostring(category) .. "_" .. tostring(category_name);
	if not HTTP.IsRequestPending(url) then
		HTTP.IssueRequest(url, "POST", TDDB_Packet, nil);
		if(((category == 58) or (category == 61)) and (hit_db == 0)) then
			hit_db = 1;
			callback(callback_penetrator, nil, 300);
		end
		--log("total run time: " .. tostring(timer));
	else
		_TEMP_callback_holder = {};
		_TEMP_callback_holder.packet = TDDB_Packet;
		_TEMP_callback_holder.category = category;
		_TEMP_callback_holder.category_name = category_name;
		callback(TDDB_Send_Market_Data, _TEMP_callback_holder, 0.25);
	end
end

function OnPlayerReady()
	callback_controller();
end