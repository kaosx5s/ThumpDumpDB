<?php
class Market_Graph_Controller extends Base_Controller {
	public $restful = true;
	public function get_market_item_graph($sdb_id, $rarity){
		if(($sdb_id == null) || ($rarity == null)){
			return Response::error('404');
		};
	
		//check the cache, if the date on the cache is different than todays date than remake the graph else return the cache
		$cache_key_graphs = "Market_Graph";
		$cached_graph = Cache::Get($cache_key_graphs . "_" . $sdb_id . "_" . $rarity);
		if(empty($cached_graph)){
			$cached_graph['last_generated'] = 0;				
		};
		if($cached_graph['last_generated'] != gmdate("Y-m-d")){
	
			//Fire up those databases
			//We need to query all the previously listed sdb_id's with the same quality so lets do that.
			$item_listings = MarketListing::where(
				function($query) use($sdb_id,$rarity){
					$query->where('item_sdb_id','=', $sdb_id);
					$query->where('rarity','=', $rarity);
					$query->where('active','=',0);
					$query->where('created_at','<',gmdate("Y-m-d"));
					$query->order_by('created_at', 'ASC');
				}
			)->get(array('created_at','title','rarity','price_cy','price_per_unit','quantity'));
			
			if($item_listings){
				//Look at all these variables!
				$max_listing = 0.0;
				$min_listing = 100000000.00;
				$all_time_max_listing = 0.0;
				$all_time_min_listing = 100000000.00;
				$max_listing_date = "";
				$min_listing_date = "";
				$item_name = $item_listings[0]->title;
				//remove the ^Q or ^###
				$temp_name = explode("^", $item_name);
				$is_resource = 0;
				if(isset($temp_name[1])){
					if($temp_name[1] != "Q"){
						//this is a resource item, we need to use unit price, not price_cy
						$is_resource = 1;
					};
				};
				$item_name = $temp_name[0];
				$market_info_javascript_range = "[";
				$market_info_javascript_average = "[";
				$item_running_average = array();
				$last_date = explode(" ", $item_listings[0]->created_at)[0];
				$last_date_supply = 0;
				$counter = 0;
				$index = 0;
				$i = 0;
				$total_listings = count($item_listings);
				//find an average, max and min price per day
				foreach ($item_listings as $market_listing){
					//babies first market check
					if(($is_resource && $market_listing->quantity > 100) || (!$is_resource)){
						//override for price_cy or price_per_unit
						if($is_resource){
							$price_field = $market_listing->price_per_unit;
						}else{
							$price_field = $market_listing->price_cy;
						};
						$date = explode(" ", $market_listing->created_at)[0];
						if($last_date != $date){
							$last_date = $date;
							if($counter != 0){
								//there is an issue with the first item also being the only item for that day, avoid that (bad programming general)
								if($i != 1){
									//the average has to be calculated because we have more than one data point for this day
									if($is_resource){
										$calc_average = number_format(($item_running_average[$index]['average'] / ($counter)),3);
									}else{
										$calc_average = floor($item_running_average[$index]['average'] / ($counter + 1));
									};
									//check to make sure the average isnt below the min, this happens when we have only two data points and they are significantly different
									if($calc_average < $item_running_average[$index]['low']){
										//re-do the average, this should NEVER happen for resources!
										$calc_average = floor($item_running_average[$index]['average'] / ($counter));
									};
									$item_running_average[$index]['average'] = $calc_average;
								};
							}else{
								//we only have one data point thus it is the average
								$item_running_average[$index]['average'] = $price_field;
								$max_listing = $price_field;
								$min_listing = $price_field;
							};
							$item_running_average[$index]['high'] = $max_listing;
							$item_running_average[$index]['low'] = $min_listing;
							$min_listing = 100000000.00;
							$max_listing = 0.00;
							$counter = 0;
							$index = $index + 1;
							if($is_resource){
								//reset the supply counter
								$last_date_supply = 0;
							};
						};
						if(!empty($item_running_average[$index]['average'])){
							$item_running_average[$index]['average'] = $item_running_average[$index]['average'] + $price_field;
							$item_running_average[$index]['date'] = $date;
						}else{
							$item_running_average[$index]['average'] = $price_field;
						};
						++$counter;
						if($price_field > $max_listing){
							//update max listing
							$max_listing = $price_field;
							$item_running_average[$index]['high'] = $max_listing;
						};
						if($price_field > $all_time_max_listing){
							//update the all time max listing
							$all_time_max_listing = $price_field;
							$max_listing_date = $market_listing->created_at;
						};
						if($price_field < $min_listing){
							//update min listing
							$min_listing = $price_field;
							$item_running_average[$index]['low'] = $min_listing;
							
						};
						if($price_field < $all_time_min_listing){
							//update the all time max listing
							$all_time_min_listing = $price_field;
							$min_listing_date = $market_listing->created_at;
						};
						$item_running_average[$index]['date'] = $date;
						if($is_resource){
							$last_date_supply = $last_date_supply + $market_listing->quantity;
						}else{
							$last_date_supply = $counter;
						};
						++$i;
					};
				};
				//deal with the last element
				//if we are doing resources we want to list the amount of units that were listed, not the amount of listings
				if(!$is_resource){
					$item_running_average[$index]['average'] = floor($item_running_average[$index]['average'] / $last_date_supply);
				}else{
					//deal with single entries
					if($counter == 1){
						//we only had one entry so its unit price is the average.
						$item_running_average[$index]['average'] = number_format($price_field,3);
					}else{
						$item_running_average[$index]['average'] = number_format(($item_running_average[$index]['average'] / ($counter)),3);
					};
				};
				for($i=0;$i<count($item_running_average);$i++){
					$date = $item_running_average[$i]['date'];
					$market_info_javascript_range .= "[" . (strtotime($date))*1000 . "," . $item_running_average[$i]['low'] . "," . $item_running_average[$i]['high'] . "],";
					$market_info_javascript_average .= "[" . (strtotime($date))*1000 . "," . $item_running_average[$i]['average'] . "],";
				};
				//slice the array for an average compare
				if(count($item_running_average >= 2)){
					$item_running_average = array_slice($item_running_average, (count($item_running_average) - 2), 2);
				}else{
					$item_running_average = 0;
				}
				$market_info_javascript_range = rtrim($market_info_javascript_range, ",") . "]";
				$market_info_javascript_average = rtrim($market_info_javascript_average, ",") . "]";
				
				//cache this
				$cached_graph['last_generated'] = gmdate("Y-m-d");
				$cached_graph['js_range'] = $market_info_javascript_range;
				$cached_graph['js_average'] = $market_info_javascript_average;
				$cached_graph['item_name'] = $item_name;
				$cached_graph['min_listing'] = $all_time_min_listing;
				$cached_graph['max_listing'] = $all_time_max_listing;
				$cached_graph['min_listing_date'] = $min_listing_date;
				$cached_graph['max_listing_date'] = $max_listing_date;
				$cached_graph['last_supply'] = $last_date_supply;
				$cached_graph['is_resource'] = $is_resource;
				$cached_graph['last_two_averages'] = $item_running_average;
				Cache::forever($cache_key_graphs . "_" . $sdb_id . "_" . $rarity,$cached_graph);
				
				//set item_listings to something for the view
				$item_listings = 1;
			}else{
				//no graphs!
				$item_listings = 0;
				$item_name = 0;
			};
		}else{
			//load from cache
			$market_info_javascript_range = $cached_graph['js_range'];
			$market_info_javascript_average = $cached_graph['js_average'];
			$item_name = $cached_graph['item_name'];
			$all_time_min_listing = $cached_graph['min_listing'];
			$all_time_max_listing = $cached_graph['max_listing'];
			$min_listing_date = $cached_graph['min_listing_date'];
			$max_listing_date = $cached_graph['max_listing_date'];
			$last_date_supply = $cached_graph['last_supply'];
			$is_resource = $cached_graph['is_resource'];
			$item_running_average = $cached_graph['last_two_averages'];
			$item_listings = 1;
		};
		//Push this data to the view.
        return View::make('market.graph')
			->with(compact('item_listings'))
			->with(compact('item_name'))
            ->with(compact('market_info_javascript_range'))
			->with(compact('market_info_javascript_average'))
			->with(compact('all_time_min_listing'))
			->with(compact('all_time_max_listing'))
			->with(compact('min_listing_date'))
			->with(compact('max_listing_date'))
			->with(compact('last_date_supply'))
			->with(compact('rarity'))
			->with(compact('is_resource'))
			->with(compact('item_running_average'))
			->with(compact('sdb_id'));
	}
}
?>