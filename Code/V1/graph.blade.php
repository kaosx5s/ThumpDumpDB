<div id='market_graph'>
<?php
if($item_listings){
	if($is_resource){
		$all_time_min_listing = number_format($all_time_min_listing,3);
		$all_time_max_listing = number_format($all_time_max_listing,3);
		$listed_text = "Unit Price:";
		
		//set the graph plot name
		$plot_name_average = $item_name . " - Listing Unit Price Average";
		$plot_name_range =  $item_name . " - Listing Unit Price Range";
	}else{
		$all_time_min_listing = number_format($all_time_min_listing);
		$all_time_max_listing = number_format($all_time_max_listing);
		$listed_text = "Price:";
		
		//set the graph plot name
		$plot_name_average = $item_name . " - Listing Price Average";
		$plot_name_range =  $item_name . " - Listing Price Range";
	};
	echo "<p>Minimum Listed " . $listed_text . " " . $all_time_min_listing . " CY, On: " . $min_listing_date . "</p>";
	echo "<p>Maximum Listed " . $listed_text . " " . $all_time_max_listing . " CY, On: " . $max_listing_date . "</p>";
	echo "<p>Supply from last data point: " . number_format($last_date_supply) . "</p>";
	//find out if the price has increased or decreased (average) and show that
	if($item_running_average != 0){
		echo "<p>Change in average price from last data point:&nbsp;";
		$average_difference = $item_running_average[1]['average'] - $item_running_average[0]['average'];
		$average_difference = number_format(($average_difference/$item_running_average[0]['average'])*100,2);
		if($average_difference > 0){
			echo "<span class='label label-success'><img src='/img/v2/up_arrow_icon_market.png'>&nbsp;" . $average_difference . "%</span>";
		}else{
			echo "<span class='label label-important'><img src='/img/v2/down_arrow_icon_market.png'>&nbsp;" . abs($average_difference) . "%</span>";
		};
		echo "</p>";
	};
};
?>
@if(!$item_listings)
<div id='market_listing_graph'>
	<p>
		Not enough data to provide a graph.
	</p>
</div>
@endif

@if($item_listings)
<div id='market_listing_graph' style='width:875px;height:350px;'></div>
<div id='market_disclaimer'>
	<p><i><small>Disclaimer: Market data is based on listing prices not sold prices. All values are approximations due to our pulling intervals.<br>Graphs are regenerated daily at 00:00 UTC.</small></i></p>
</div>
<script>
<?php
	echo "var item_graph_range = " . $market_info_javascript_range . " ;";
	echo "var item_graph_average = " . $market_info_javascript_average . " ;";
?>
    $("#market_listing_graph").highcharts({
        title: {
			text: 'Market Data for <?php echo addslashes($rarity); ?> quality <?php echo addslashes($item_name); ?>'
		},
		xAxis: {
			type: 'datetime'
		},
		yAxis: {
			title: {
				text: 'Crystite'
			},
			min: 0
		},
		tooltip: {
			crosshairs: true,
			shared: true,
			valueSuffix: ' CY'
		},
		series: [
			{
				name: <?php echo "\"" . addslashes($plot_name_average) . "\"" ?>,
				data: item_graph_average,
				zIndex: 1,
				marker: {
					fillColor: 'white',
					lineWidth: 2,
					lineColor: Highcharts.getOptions().colors[0]
				}
			},
			{
				name: <?php echo "\"" . addslashes($plot_name_range) . "\"" ?>,
				data: item_graph_range,
				type: 'arearange',
				lineWidth: 0,
				linkedTo: ':previous',
				color: Highcharts.getOptions().colors[0],
				fillOpacity: 0.3,
				zIndex: 0
			}
		]
    });
</script>
<br>
@endif
</div>