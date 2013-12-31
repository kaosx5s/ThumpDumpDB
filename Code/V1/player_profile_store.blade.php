@layout('layout.site-wrapper')

@section('assets')
	{{ HTML::style('css/v2/raw-table.css'); }}
@endsection

@section('nav')
    @include('layout.nav.nav-main')
@endsection

@section('main-content')
<style>
.player-name {
	font:bold 32px 'Play','Arial';
	color: #c00505;
    color: #222;
    font-family: Tahoma;
    padding: 5px 20px 10px 12px;
    text-shadow: 0px 2px 3px #555;
    text-align: left;
    -moz-border-radius: 10px;
    -webkit-border-radius: 10px;
    border-radius: 10px;
}
.player-army {
	font: normal 17px 'Oswald';
	padding: 10px 0 10px 20px;
	color: #BD8243;
}
#player_store h1{
	margin-bottom:5px;
	font-size: 17px;
	text-align: right;
	margin-bottom: 5px;
	color: #000;
	font-style: italic;
	letter-spacing: 1px;
	text-transform: uppercase;
	font-family: 'Play';
	position: relative;
}
#player_store h2{
	font: normal 16px 'Oswald';
	text-align: left;
	background: #E2E2E2;
	padding-left: 6px;
	border-bottom:1px solid #000;
}
#base_frame{
	float:left;
	margin-right:15px;
}
#base_frame ul{
	list-style:none;
	font-size:0.95em;
	min-width:135px;
}
#base_frame li{
	background-color:#fff;
	border-bottom:1px solid #E0E0E0;
	padding:4px 5px;
}
.market_listing_table{
	width:100%;
}
.market_listing_table tbody{
	border-color: #7F92BD;
	font: normal 15px 'Play';
}
.market_listing_table th{
	background-color:#ccc;
	padding: 4px 0;
	border-bottom:1px solid #000;
}
.market_listing_table td{
	text-align:center;
	background-color:#fff;
	border-bottom:1px solid #000;
	color:#000;
	padding: 4px 0;
}
.item-icon img {width:32px;height:32px;}
.popover-content {padding: 9px 14px;}
.popover-content table tbody td {font: normal 14px 'Arial';}
.legendary_plate{background: #a77a22;}
.epic_plate{background: #9922a7;}
.rare_plate{background: #225ba7;}
.uncommon_plate{background: #458c39;}
.common_plate{background: #ababab;}
.salvage_plate{background: #c1c1c1;}

.workbench_container{
	width:600px;
	margin:0 auto;
}
.workbench{
	float:left;
	width:275px;
	border-radius:5px;
	border:1px solid #000;
	height:98px;
	margin-right:15px;
    background: rgb(255,255,255);
    background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2ZmZmZmZiIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNlNWU1ZTUiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
    background: -moz-linear-gradient(top, rgb(255,255,255) 0%, rgb(229,229,229) 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgb(255,255,255)), color-stop(100%,rgb(229,229,229)));
    background: -webkit-linear-gradient(top, rgb(255,255,255) 0%,rgb(229,229,229) 100%);
    background: -o-linear-gradient(top, rgb(255,255,255) 0%,rgb(229,229,229) 100%);
    background: -ms-linear-gradient(top, rgb(255,255,255) 0%,rgb(229,229,229) 100%);
    background: linear-gradient(to bottom, rgb(255,255,255) 0%,rgb(229,229,229) 100%);
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#e5e5e5',GradientType=0 );
}
.workbench_icon_area{
	display:block;
	float:left;
	width:50px;
	height:50px;
	margin:5px;
	border:1px solid #000;
	border-radius:10px;
	background-color:#fff;
}
.workbench_item_name{
	width:250px;
	padding:5px;
	margin-top:18px;
}
.workbench_progress{
    clear:both;
    margin:1px;
    height:12px;
    margin-left:5px;
    margin-right:5px;
    border: 1px solid #000;
}
.workbench_time_left{
	margin-top:3px;
	width:275px;
	text-align: center;
}
.workbench_small_item_name{
	font-size:0.85em;
}
.workbench_percent{
    color:#000 !important;
}
.button_container{
	float:right;
}
#base_frame li{
	cursor:pointer;
}
.popover_cert_table{
	overflow:auto;
	min-width:550px;
}
.cert_recipe{
	display:none;
}
.cert_recipe_table td{
	text-align:center;
	padding:5px;
	border:1px solid #000;
}
.cert_recipe_table th{
	text-align:center;
	padding:5px;
	border:1px solid #000;
}
</style>
<div id='player_store'>
	<span class='player-name'>
	@unless(empty($player->armytag))
	{{ e($player->armytag) }}&nbsp;
	@endunless
	{{ e($player->name) }}
	</span>
	<div class='button_container'>
		<a href='/players/{{ e($player->name) }}' class="store_page_button btn btn-info"><i class="icon-white icon-arrow-left"></i> Back to main profile</a>&nbsp;
		<a href='/m/{{ e($player->name) }}/printer' class="store_page_button btn btn-info"><i class="icon-white icon-edit"></i> Mobile Friendly Version</a>
	</div>
	@if(($web_prefs['show_workbench'] == 0) && ($web_prefs['show_market_listings'] == 0) && ($web_prefs['show_craftables'] == 0))
		<hr>
		<div id='player_store_disabled'>
			<p>This players store is currently closed.</p>
		</div>
	@endif
	@if($web_prefs['show_workbench'])
	<div id='player_workbenches'>
		<hr>
		<h1>Workbenches</h1>
		@if(!empty($workbench_info))
		<div class='workbench_container'>
			@for($i=0;$i<count($workbench_info);$i++)
			<div class='workbench'>
				<div class='workbench_icon_area'>
					{{ HTML::image('img/' . $workbench_output_icons[ $workbench_info[$i]['blueprint_id'] ], '') }}
				</div>
				<div class='workbench_item_name'>
					@if(strlen($workbench_output_names[ $workbench_info[$i]['blueprint_id'] ]) > 26)
						<p class='workbench_small_item_name'>{{ e($workbench_output_names[ $workbench_info[$i]['blueprint_id'] ]) }}</p>
					@else
						<p>{{ e($workbench_output_names[ $workbench_info[$i]['blueprint_id'] ]) }}</p>
					@endif
				</div>
				<div class="workbench_progress progress progress-striped active">
					<div id='workbench_<?php echo $i; ?>_progress' class="bar bar-success workbench_percent" style="width: 100%;"></div>
				</div>
				<div id='workbench_<?php echo $i; ?>_timer' class='workbench_time_left'>
				</div>
			</div>
			@endfor
		</div>
		@else
		<p>Workbenches are empty.</p>
		@endif
	</div>
	<br style="clear:both;">
	@endif
	@if(!empty($progress))
	<div id='player_can_craft'>
		<hr>
		<h1>Craftable Components - Base Frames</h1>
		@if(!empty($base_shared_items))
			<div id='base_frame'>
				<h2>Common</h2>
				<ul>
				@foreach($base_shared_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_assault_items))
			<div id='base_frame'>
				<h2>Assault</h2>
				<ul>
				@foreach($base_assault_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_biotech_items))
			<div id='base_frame'>
				<h2>Biotech</h2>
				<ul>
				@foreach($base_biotech_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_dreadnaught_items))
			<div id='base_frame'>
				<h2>Dreadnaught</h2>
				<ul>
				@foreach($base_dreadnaught_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_engineer_items))
			<div id='base_frame'>
				<h2>Engineer</h2>
				<ul>
				@foreach($base_engineer_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_recon_items))
			<div id='base_frame'>
				<h2>Recon</h2>
				<ul>
				@foreach($base_recon_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		<br style='clear:both;'>
		<hr>
		<h1>Craftable Components - Advanced Frames</h1>
		@if(!empty($base_firecat_items))
			<div id='base_frame'>
				<h2>Firecat</h2>
				<ul>
				@foreach($base_firecat_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_tigerclaw_items))
			<div id='base_frame'>
				<h2>Tigerclaw</h2>
				<ul>
				@foreach($base_tigerclaw_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_dragonfly_items))
			<div id='base_frame'>
				<h2>Dragonfly</h2>
				<ul>
				@foreach($base_dragonfly_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_recluse_items))
			<div id='base_frame'>
				<h2>Recluse</h2>
				<ul>
				@foreach($base_recluse_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_rhino_items))
			<div id='base_frame'>
				<h2>Rhino</h2>
				<ul>
				@foreach($base_rhino_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_mammoth_items))
			<div id='base_frame'>
				<h2>Mammoth</h2>
				<ul>
				@foreach($base_mammoth_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_arsenal_items))
			<div id='base_frame'>
				<h2>Arsenal</h2>
				<ul>
				@foreach($base_arsenal_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_electron_items))
			<div id='base_frame'>
				<h2>Electron</h2>
				<ul>
				@foreach($base_electron_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_bastion_items))
			<div id='base_frame'>
				<h2>Bastion</h2>
				<ul>
				@foreach($base_bastion_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_nighthawk_items))
			<div id='base_frame'>
				<h2>Nighthawk</h2>
				<ul>
				@foreach($base_nighthawk_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
		@if(!empty($base_raptor_items))
			<div id='base_frame'>
				<h2>Raptor</h2>
				<ul>
				@foreach($base_raptor_items as $index => $item)
					<li id='cert_item'>{{ e($item) }}</li>
				@endforeach
				</ul>
			</div>
		@endif
	</div>
	<div id='cert_recipe' style='display:none;'>
		<table class='cert_recipe_table'></table>
	</div>
	<br style='clear:both;'>
	@endif
	@if((empty($market_listings) && $web_prefs['show_market_listings']))
	<div id='market_listings'>
		<hr>
		<h1>Market Listings</h1>
		<p><i>No listed items or TDDB does not know of your listed items yet, please check back in 30min.</i></p>
	</div>
	@endif
	@if(!empty($market_listings))
	<div id='market_listings'>
		<hr>
		<h1>Market Listings ({{ count($market_listings) }} / 8)</h1>
<?php
	function convert_time($input){
		//convert time to "# ___ ago"
		date_default_timezone_set('UTC');

		$now = strtotime('now');
		$then = strtotime($input);
		$diff = ($then - $now);

		$ago = 'Just now';
		if($diff == 0) {
			 $ago = 'Just now';
			 break;
		}else{

			$intervals = array
			(
				1                   => array('year',    31556926),
				$diff < 31556926    => array('month',   2628000),
				$diff < 2629744     => array('week',    604800),
				$diff < 604800      => array('day',     86400),
				$diff < 86400       => array('hour',    3600),
				$diff < 3600        => array('minute',  60),
				$diff < 60          => array('second',  1)
			);

			$value = floor($diff/$intervals[1][1]);
			$ago = $value.' '.$intervals[1][0].($value > 1 ? 's' : '');
		}
		if($ago < 0){
			$ago = "Expired";
		};
		return $ago;
	}
?>
		<table class='market_listing_table'>
			<tbody>
				<tr><th>Icon</th><th>Item Name</th><th>Quantity</th><th>Price</th><th>Rarity</th><th>Expires</th></tr>
				@for($i=0;$i<count($market_listings);$i++)
				<tr>
					<td class='item-icon'>
						@if(isset($stats[$market_listings[$i]->attributes['ff_id']]))
							{{ HTML::image('img/assets/items/64/'.$market_listings[$i]->attributes['icon'].'.png', '', array('class'=>e($market_listings[$i]->attributes['rarity']).'_plate rounded hoverimg', 'data-content'=>$stats[$market_listings[$i]->attributes['ff_id']], 'data-placement'=>'left', 'data-trigger'=>'hover', 'data-html'=>'true')); }}
						@else
							{{ HTML::image('img/assets/items/64/'.$market_listings[$i]->attributes['icon'].'.png', '', array('class'=>e($market_listings[$i]->attributes['rarity']).'_plate rounded hoverimg')); }}
						@endif
					</td>
					<td>
						{{ e(str_replace('^Q','',$market_listings[$i]->attributes['title'])); }}
					</td>
					<td>
						{{ e(number_format($market_listings[$i]->attributes['quantity'])); }}
					</td>
					@if($market_listings[$i]->attributes['quantity'] == '1')
						<td>
							{{ e(number_format($market_listings[$i]->attributes['price_cy'])); }}
						</td>
					@else
						<td>
							({{ e(number_format($market_listings[$i]->attributes['price_per_unit'],2)); }} / unit)
						</td>
					@endif
					<td>
						{{ e($market_listings[$i]->attributes['rarity']); }}
					</td>
					<td>
						{{ e(convert_time($market_listings[$i]->attributes['expires_at'])); }}
					</td>
				</tr>
				@endfor
			</tbody>
		</table>
	</div>
	@endif
</div>
@endsection

@section('footer-js')
<script type="text/javascript">
$(document).ready(function(){
	$(".hoverimg").popover();
	
	$("#base_frame li").mouseenter(function() {
		el = $(this);
		var item_name = $(this).html();
		$.getJSON("/api/helper/recipeinput/?item_name="+item_name, function(response) {
			el.popover({
				content:
					function(){
						var table_contents = "<tr><th>Attribute Name</th><th>Item Name</th><th>Quantity</th><th>Required</th></tr>";
						$.each(response, function(index, info){
							if(index !== "status" && index !== "recipe_id"){
								var attribute_name;
								var required;
								if(info.attribute_name === undefined){
									attribute_name = "";
								}else{
									attribute_name = info.attribute_name;
								};
								if(info.required == 1){
									required = "Yes";
								}else{
									required = "No";
								};
								table_contents = table_contents+"<tr><td>"+attribute_name+"</td><td>"+info.item+"</td><td>"+info.quantity+"</td><td>"+required+"</td></tr>";
							};
						})
						$("#cert_recipe table").html(table_contents);
						return $('#cert_recipe').html();
					},
				html: true,
				template: '<div class="popover popover_cert_table"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>'
			}).popover('show');
		});
	});
	$("#base_frame li").bind('mouseout',function(){
	   var e=$(this);
	   e.popover('hide'); 
	});
<?php
	if(!empty($workbench_info)){
		for($i=0;$i<count($workbench_info);$i++){
			echo "var workbench_" . $i . "_start = new Date(" . $workbench_info[$i]['started_at']*1000 . ");";
			echo "var workbench_" . $i . "_end = new Date(" . $workbench_info[$i]['ready_at']*1000 . ");";
			echo "countdown($('#workbench_" . $i . "_timer'), workbench_" . $i . "_end, workbench_" . $i . "_start, " . $i . ");";
			echo "setInterval(function () { countdown($('#workbench_" . $i . "_timer'), workbench_" . $i . "_end, workbench_" . $i . "_start, " . $i . "); }, 1000);";
		};
	};
?>
	function countdown($display, end_date, start_date, bench_index) {
		var now = new Date();
		var difference = (end_date - now.getTime());
		var percent = Math.floor(((now.getTime() - start_date)/(end_date - start_date))*100);
		var seconds = Math.floor(difference/1000) % 60;
			seconds = ( seconds > 1 ) ? seconds + ' Secs' : seconds + ' Sec';
		var minutes = Math.floor((difference/1000)/60) % 60;
			minutes = ( minutes > 1 ) ? minutes + ' Mins' : minutes + ' Min';
		var hours = Math.floor((difference/1000)/3600) % 24;
			hours = ( hours > 1 ) ? hours + ' Hrs' : hours + ' Hr';
		var days = Math.floor((difference/1000)/(60*60*24));
		if(difference < 0){
			$display.html('<p>Complete!</p>');
			$('#workbench_' + bench_index + '_progress').css('width','100%');
			$('#workbench_' + bench_index + '_progress').html('100%');
		}else{
			if(days > 0){
				$display.html('<p>' + days + ' day(s), ' + hours + ' ' + minutes + ' ' + seconds + '</p>');
			}else{
				$display.html('<p>' + hours + ' ' + minutes + ' ' + seconds + '</p>');
			};
			$('#workbench_' + bench_index + '_progress').css('width',percent + '%');
			$('#workbench_' + bench_index + '_progress').html(percent + '%');
		};
	}
});
</script>
@endsection