@layout('layout.site-wrapper')

<?php
function chassisIdToSimpleName($chassis_id)
{
  /*Look at all those frames, those are nice frames.
	Switch logic for background selection

	76336   AA-14 "Recluse"
	76133   AA-9 "Firecat"
	76332   AA-D01 "Rhino"
	76337   AA-E01 "Electron"
	76334   AA-R4 "Raptor"
	76164   Accord Assault Battleframe
	75774   Accord Biotech Battleframe
	75772   Accord Dreadnaught Frame
	75775   Accord Engineer Battleframe
	75773   Accord Recon Battleframe
	76335   OD-B9 "Dragonfly"
	76338   OD-E12 "Bastion"
	76331   OD-M "Mammoth"
	76132   OD-M7 "Tigerclaw"
	76333   OD-R02 "Nighthawk"
	82360	D647 "Arsenal"
  */
	switch($chassis_id){
		//ASSAULT FRAMES
		case 76164:
			$name = "Assault";
			break;
		case 76133:
			$name = "Firecat";
			break;
		case 76132:
			$name = "Tigerclaw";
			break;
		//BIO FRAMES
		case 75774:
			$name = "Biotech";
			break;
		case 76336:
			$name = "Recluse";
			break;
		case 76335:
			$name = "Dragonfly";
			break;
		//DREAD FRAMES
		case 75772:
			$name = "Dreadnaught";
			break;
		case 76331:
			$name = "Mammoth";
			break;
		case 76332:
			$name = "Rhino";
			break;
		case 82360:
			$name = "Arsenal";
			break;
		//ENGINEER FRAMES
		case 75775:
			$name = "Engineer";
			break;
		case 76338:
			$name = "Bastion";
			break;
		case 76337:
			$name = "Electron";
			break;
		//RECON FRAMES
		case 75773:
			$name = "Recon";
			break;
		case 76333:
			$name = "Nighthawk";
			break;
		case 76334:
			$name = "Raptor";
			break;
		default:
			$name = false;
			break;
	};
	return $name;
}

function simpleNameToClassIcon($name)
{
	$lookup = array(
		'Assault'	=> 'AccordAssault',
		'Firecat'	=> 'Firecat',
		'Tigerclaw'	=> 'Tigerclaw',
		'Biotech'	=> 'AccordBiotech',
		'Recluse'	=> 'Recluse',
		'Dragonfly'	=> 'Dragonfly',
		'Dreadnaught' => 'AccordDread',
		'Rhino'		=> 'Rhino',
		'Mammoth'	=> 'Mammoth',
		'Engineer'	=> 'AccordEngineer',
		'Electron'	=> 'Electron',
		'Bastion'	=> 'Bastion',
		'Recon'		=> 'AccordRecon',
		'Nighthawk'	=> 'Nighthawk',
		'Raptor'	=> 'Raptor',
		'Arsenal'	=> 'Arsenal'
	);

	if( array_key_exists((string)$name, $lookup) ){
		return $lookup[$name];
	}else{
		return 'FrameToken';
	}
}

	//Website preferences blowout!
	if(isset($web_prefs['show_loadout'])){
		$show_loadout = $web_prefs['show_loadout'];
	}else{
		$show_loadout = 1;
	};
	if(isset($web_prefs['show_progress'])){
		$show_progress = $web_prefs['show_progress'];
	}else{
		$show_progress = 1;
	};
	if(isset($web_prefs['show_inventory'])){
		$show_inventory = $web_prefs['show_inventory'];
	}else{
		$show_inventory = 1;
	};
	if(isset($web_prefs['show_unlocks'])){
		$show_unlocks = $web_prefs['show_unlocks'];
	}else{
		$show_unlocks = 1;
	};
	if(isset($web_prefs['show_location'])){
		$show_location = $web_prefs['show_location'];
	}else{
		$show_location = 1;
	};
	if(isset($web_prefs['show_pve_stats'])){
		$show_pve_stats = $web_prefs['show_pve_stats'];
	}else{
		$show_pve_stats = 1;
	};
	if(isset($web_prefs['show_pve_events'])){
		$show_pve_events = $web_prefs['show_pve_events'];
	}else{
		$show_pve_events = 1;
	};
	if(isset($web_prefs['show_pve_kills'])){
		$show_pve_kills = $web_prefs['show_pve_kills'];
	}else{
		$show_pve_kills = 1;
	};
	if(!isset($web_prefs['show_workbench'])){
		$web_prefs['show_workbench'] = 1;
	};
	if(!isset($web_prefs['show_craftables'])){
		$web_prefs['show_craftables'] = 0;
	};
	if(!isset($web_prefs['show_market_listings'])){
		$web_prefs['show_market_listings'] = 0;
	};
?>

@section('assets')
{{ HTML::style('css/v2/player-profile.css'); }}
@endsection

@section('nav')
    @include('layout.nav.nav-main')
@endsection

@section('main-content')

<?php
    //convert time to "# ___ ago"
    date_default_timezone_set('UTC');

    $now = strtotime('now');
    $then = strtotime($player->updated_at);
    $diff = ($now - $then);

    //Addon users are within an hour, others, a day
    if($diff < 86400) {
        if($player->addon_user == 1) {
            $diff += 3600;
        }else{
            $day_diff = 86400  - $diff;
            $diff += $day_diff;
        }
    }

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
        $ago = 'within ' .$value.' '.$intervals[1][0].($value > 1 ? 's' : '');
    }
?>


<div class='top-info'>
	<span class='player-name'>
	@unless(empty($player->armytag))
	{{ e($player->armytag) }}&nbsp;
	@endunless
	{{ e($player->name) }}
	</span>

	@unless(empty($army))
	<p class='player-army'>
	ARMY: <a href="{{URL::to_route('army_profile');}}/{{ e($army->armyid) }}">{{e($army->name)}}</a>
	</p>
	@endunless


	<p class='player-loc-info'>
	@unless( empty($player->current_archetype) )
	    @if( $player->current_archetype )
	       <?php
	       $archtype = '';
	            switch($player->current_archetype){
	                    case 'recon':
	                            $archtype = 'Recon';
	                            break;
	                    case 'guardian':
	                            $archtype = 'Dreadnaught';
	                            break;
	                    case 'medic':
	                            $archtype = 'Biotech';
	                            break;
	                    case 'bunker':
	                            $archtype = 'Engineer';
	                            break;
	                    case 'berzerker':
	                            $archtype = 'Assault';
	                            break;
						default:
								$archtype = 'Assault';
								break;
	            }
	            if( !empty($progress) ) {
		            if(isset($progress->chassis_id)){
		                    //we can get the battleframe the player is currently playing!
		                    for($i=0;$i<count($progress->xp);$i++){
		                            if($progress->xp[$i]->sdb_id == $progress->chassis_id){
		                                    //override the archtype
		                                    $archtype = $progress->xp[$i]->name;
		                            };
		                    };
		            }else{
		            	$logdate = date('Y-m-d-H-i-s');
		            	Log::warn('Chassis_id was not set -'.$logdate.'- dbid: '.$player->db_id.'- URI: ' . $_SERVER["REQUEST_URI"] );
		            	@file_put_contents('storage/logs/qa/'.$logdate.'_player_profile', $progress);
		            };
	            }//progress not empty
	        ?>
	        {{ $archtype }}
	    @else
	        N/A
	    @endif
	@endunless
	&mdash;
	{{$ago}}
	@if($nearestloc && $player->addon_user == 1 && $show_location)
	&mdash;
	{{ e($nearestloc) }}
	@endif
	@unless(empty($locations))
	@if($player->addon_user == 1)
	&mdash;
		on: <span class='instance-id'>{{ e($player->instanceid) }}</span>
		@if( $spotter )
			, spotted by {{ e($spotter) }}
		@endif
	@endif
	@endunless



	</p>

	<span class='report-icons'>
		<!--<a><i class="icon-warning-sign"></i></a>
		<hr/>-->
		@unless(empty($progress))
		<a><i class="icon-gift" data-toggle="collapse" data-target="#demo"></i></a>
		@endunless
	</span>

	<div class='player-more-info collapse' id='demo'>
		<div class='player-more-inner'>
		@unless(empty($progress))
		<div id='player_signature'>
			<p>Your Current Frame As A Signature!</p>
			<br/>
			<img src='/sigs/{{e($player->name)}}/sig.png' alt='' />
			<input value="[url={{ URL::current()}}][img]{{URL::base();}}/sigs/{{e($player->name)}}/sig.png[/img][/url]" />
			<img src='/sigs/{{e($player->name)}}/light/barsig.png' alt='' />
			<input value="[url={{ URL::current()}}][img]{{URL::base();}}/sigs/{{e($player->name)}}/light/barsig.png[/img][/url]" />
			<img src='/sigs/{{e($player->name)}}/dark/barsig.png' alt='' />
			<input value="[url={{ URL::current()}}][img]{{URL::base();}}/sigs/{{e($player->name)}}/dark/barsig.png[/img][/url]" />
		</div>
		<p>
			You can change "sig" and "barsig" to the (lowercase) simple name of a specific frame too!<br/>
			ex: {{URL::base();}}/sigs/{{e($player->name)}}/dark/raptor.png<br/>
			ex: {{URL::base();}}/sigs/{{e($player->name)}}/raptor.png<br/>
		</p>
		<hr>
		<p>Link to a specific battleframe like so: {{URL::base();}}/players/{{ e($player->name) }}/battleframe/raptor</p>
		@endunless
		</div>{{-- player-more-inner --}}
	</div>{{-- player-more-info --}}
</div>
{{-- --------------------------------------------------------------- --}}
@unless(empty($progress))
<hr>
<h1><span class='tweet-btn'><a href="https://twitter.com/share" class="twitter-share-button" data-lang="en" data-text='Check out my gear! #Firefall #ThumpDump'>Tweet</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></span>
Battleframe Garage
</h1>
@endunless
<div class='dat-profile'>
@unless(empty($progress))
	@if($show_progress)
	<?php
		$count_progress_xp = count($progress->xp);
		$ol_items = "";
		$div_items = "";
		$num_frames_for_width = 0;

		for($i=0;$i<$count_progress_xp;$i++)
		{
			if($progress->xp[$i]->sdb_id == 77733 || $progress->xp[$i]->sdb_id == 82394 || $progress->xp[$i]->sdb_id == 31334){
				//this is the training frame, we don't want to display it.
				$count_progress_xp--;
			}else{
				//Let's setup some data
				$num_frames_for_width++;
				$current_battle_frame_id = $progress->xp[$i]->sdb_id;
				$is_active = ($current_battle_frame_id !=  $progress->chassis_id) ? '' : 'active';
				$current_battle_frame_simple_name = chassisIdToSimpleName($current_battle_frame_id);
				$current_battle_frame_as_class = strtolower($current_battle_frame_simple_name);
				$current_battle_frame_as_icon = simpleNameToClassIcon($current_battle_frame_simple_name);
				$current_battle_frame_lifetime_xp = number_format($progress->xp[$i]->lifetime_xp);
				$current_battle_frame_xp = number_format($progress->xp[$i]->current_xp);

				//Let's generate some data
				$ol_items .= "<li data-battleframe='$current_battle_frame_id' data-target='#frameCarousel' data-slide-to='". $i ."' rel='tooltip' title='$current_battle_frame_simple_name' class='changeFrame $is_active'><img src='/img/assets/items/64/$current_battle_frame_as_icon.png' alt='' /></li>";
				$div_items .= "<div class='item $current_battle_frame_as_class $is_active'>";
				$div_items .= "<p><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a target='_blank' href='" . URL::to_route('players') ."/".e($player->name)."/battleframe/".strtolower($current_battle_frame_simple_name)."'>Link This Frame</a></p>";
				$div_items .= "<p class='total-xp'><i>$current_battle_frame_simple_name</i><br/>$current_battle_frame_lifetime_xp TOTAL XP</p>";
				$div_items .= "</div>";
			};
		}
	?>

			{{-- THIS OUTPUTS THE CAROUSEL SKIP LINKS --}}
	<div id="frameCarousel" class="carousel slide">
		<ol class="carousel-indicators">
			{{ $ol_items }}
		</ol>
		<div class="carousel-inner">
			{{-- THIS OUTPUTS THE CAROUSEL FRAME DATA COMPUTED ABOVE --}}
				{{ $div_items }}
		</div>
	    <!--<a class="carousel-control left" href="#frameCarousel" data-slide="prev">&lsaquo;</a>
	    <a class="carousel-control right" href="#frameCarousel" data-slide="next">&rsaquo;</a>-->
	</div>
	@endif
@endunless
			@unless(empty($progress))
				@if($show_unlocks)
					<div id='player_unlocks'>
						<div id='player_chassis_unlocks'><span class='syncing'>Syncing with SIN...</span></div>
					</div>
				@endif
			@endunless

			@unless(empty($loadout))
				@if($show_loadout)
					<div id='player_gear'>
						<div id='player_gear_set'><span class='syncing'>Syncing with SIN...</span></div>
					</div>
				@endif
			@endunless

</div>{{-- dat-profile --}}


{{-- ----------------------------------------------- --}}
@if($player->addon_user == 1)
	@if(($web_prefs['show_workbench']) || ($web_prefs['show_craftables']) || ($web_prefs['show_market_listings']))
		<hr />
		<h1>Extended Info</h1>
		<div id='store_page_button_container'>
			@if(($web_prefs['show_workbench']) && (!$web_prefs['show_craftables']) && (!$web_prefs['show_market_listings']))
				<a href="/players/{{ e($player->name) }}/store" class="store_page_button btn btn-info"><i class="icon-white icon-shopping-cart"></i> View Workbench</a>
			@elseif(($web_prefs['show_workbench']) && ($web_prefs['show_craftables'] || $web_prefs['show_market_listings']))
				<a href="/players/{{ e($player->name) }}/store" class="store_page_button btn btn-info"><i class="icon-white icon-shopping-cart"></i> View Workbench &amp; Player Store</a>
			@endif
		</div>
	@endif
@endif
@if($player->addon_user == 1)
	@if(($show_pve_stats == 0) && ($show_pve_events == 0) && ($show_pve_kills == 0))
		{{-- display nothing! --}}
	@else
		<hr />
			<div id='pve_stats_area'>
				<h1>PvE Statistics</h1>
				<ul id='pvp_stats_timeframe_select' class="nav nav-pills">
					<li id='pvp_stats_today_btn' class="active"><a href="javascript:void(0)">Today</a></li>
					<li id='pvp_stats_all_time_btn'><a href="javascript:void(0)">All Time</a></li>
				</ul>
				<div id='pve_stats'></div>
			</div>
		<br style="clear:both;" />
	@endif
@endif
@unless(empty($player_refined_resources))
	@if($show_inventory)
<hr />
	<div id='inventory'>
		<h1>Crystite &amp; Refined Resources</h1>
		<p class='crystite-amt'><img src="/img/assets/items/64/MISC_CRYS_1.png" alt='Crysite' />{{ e( number_format($player_crystite_amount) ) }}</p>
		<div class='refined_resources'>
			<?php
				$minerals = array('Aluminum','Carbon','Ceramics','Copper','Iron','Silicate');
				$organics = array('Anabolics','Biopolymer','Petrochemical','Regenics','Toxins','Xenografts');
				$gases = array('Methine','Nitrine','Octine','Radine');
				$inv_min = $inv_org = $inv_gas = array();

				foreach ($player_refined_resources as $prr)
				{
					if( in_array($prr["name"], $minerals) ) {
						$inv_min[] = $prr;
					}elseif ( in_array($prr["name"], $organics) ) {
						$inv_org[] = $prr;
					}else{
						$inv_gas[] = $prr;
					}
				}
				sort($inv_gas);
				sort($inv_min);
				sort($inv_org);
			?>

			@if(count($inv_gas) > 0)
			<table class='resources gases'>
				<tr>
					<th colspan='3'>Gases</th>
				</tr>
				<tbody>

					<?php $total_res = 0;?>
					@foreach($inv_gas as $res)
					<tr>
						<td><img src='/img/{{ e($res["asset_path"]) }}' alt='' /></td><td>{{ e($res["name"]) }}</td><td>{{ e( number_format($res["amt"]) ) }}</td>
					</tr>
					<?php $total_res += intval($res['amt']); ?>

					@endforeach
					<tr class='sub-total'>
					<td></td><td></td><td>{{ e( number_format($total_res)) }}</td>
					</tr>
				</tbody>
			</table>
			@endif

			@if(count($inv_min) > 0)
			<table class='resources minerals'>
				<tr>
					<th colspan='3'>Minerals</th>
				</tr>
				<tbody>

					<?php $total_res = 0;?>
					@foreach($inv_min as $res)
					<tr>
						<td><img src='/img/{{ e($res["asset_path"]) }}' alt='' /></td><td>{{ e($res["name"]) }}</td><td>{{ e( number_format($res["amt"]) ) }}</td>
					</tr>
					<?php $total_res += intval($res['amt']); ?>

					@endforeach
					<tr class='sub-total'>
					<td></td><td></td><td>{{ e( number_format($total_res)) }}</td>
					</tr>
				</tbody>
			</table>
			@endif


			@if(count($inv_org) > 0)
			<table class='resources organics'>
				<tr>
					<th colspan='3'>Organics</th>
				</tr>
				<tbody>

					<?php $total_res = 0;?>
					@foreach($inv_org as $res)
					<tr>
						<td><img src='/img/{{ e($res["asset_path"]) }}' alt='' /></td><td>{{ e($res["name"]) }}</td><td>{{ e( number_format($res["amt"]) ) }}</td>
					</tr>
					<?php $total_res += intval($res['amt']); ?>

					@endforeach
					<tr class='sub-total'>
					<td></td><td></td><td>{{ e( number_format($total_res)) }}</td>
					</tr>
				</tbody>
			</table>
			@endif


		</div>
	</div>
	@endif
<?php
	/*
		print_r($player_refined_resources);
		echo "<br><br>";
		print_r($player_raw_resources);
	*/
?>
@endunless
<br style="clear:both;" />
@unless(empty($frame_progress_javascript))
@if($show_progress)
<hr/>
<div id='progress_graph'>
	<h1>Frame Progress Graph</h1>
	<div id='frame_xp_graph'></div>
</div>
@endif
@endunless
<br style="clear:both;" />
@endsection


@section('footer-js')
<script type="text/javascript" src='/js/fing.js'></script>
@unless(empty($progress))
	@if($show_progress)
<script type="text/javascript" src="/js/highcharts.js"></script>
<script type="text/javascript" src="/js/highcharts-more.js"></script>
	@endif
@endunless
<script type="text/javascript">
$(document).ready(function(){

var Fing = new FunkyInstanceNameGenerator();
    $('.instance-id').each( function( index ) {
        $(this).html( Fing.GetInstanceName( $(this).html() ) );
    });

$('.player-more-info').find('input').click(function(){
	$(this).select();
});

	function get_pve_data(time_frame){
		$.ajax({
			type: "GET",
			url: "<?php echo $player->name; ?>/pve_stats/"+time_frame,
			context: document.body
		}).done(function(data) {
			$("#pve_stats").replaceWith(data);
		});
	};
	get_pve_data("today");
	
	$('#pvp_stats_today_btn').click(function(){
		$('#pvp_stats_today_btn').attr("class","active");
		$('#pvp_stats_all_time_btn').removeClass("active");
		get_pve_data("today");
	});
	$('#pvp_stats_all_time_btn').click(function(){
		$('#pvp_stats_today_btn').removeClass("active");
		$('#pvp_stats_all_time_btn').attr("class","active");
		get_pve_data("all");
	});
	
@unless(empty($progress))
	@if($show_unlocks)
	function get_unlock_data(chassis_id){
		$.ajax({
			type: "GET",
			url: "<?php echo $player->name; ?>/unlocks/"+chassis_id,
			context: document.body
		}).done(function(data) {
			$("#player_chassis_unlocks").replaceWith(data);
			$('.player_unlocks_link').tooltip({
				trigger: "hover",
				placement: "right"
			});
		});
	};
	@endif
@endunless

@unless(empty($progress))
	@if($show_progress)
	$('#frameCarousel').carousel({
			interval: false
	});

	$(".carousel-indicators").find("li").tooltip({
      'selector': '',
      'placement': 'bottom'
    });

    $(".carousel-indicators").css('width','{{ ($num_frames_for_width*45) }}px');

		$("li.changeFrame").click(function(){
			var chassis = $(this).attr('data-battleframe');
			$("div[id=player_gear_set]").empty().append("<span class='syncing'>Syncing with SIN...</span>");
			@if($show_unlocks)
				$("div[id=player_chassis_unlocks]").empty().append("<span class='syncing'>Syncing with SIN...</span>");
			@endif
			$("."+chassis+"").show();
			@unless(empty($loadout))
				@if($show_loadout)
					get_chassis_data(chassis);
				@endif
			@endunless
			@unless(empty($progress))
				@if($show_unlocks)
					get_unlock_data(chassis);
				@endif
			@endunless
		});


		$("ul[name="+"<?php echo $progress->chassis_id; ?>"+"]").attr("class","battleframe_current");
		@unless(empty($loadout))
			@if($show_loadout)
				get_chassis_data(<?php echo $progress->chassis_id; ?>);
			@endif
		@endunless
		@unless(empty($progress))
			@if($show_unlocks)
				get_unlock_data(<?php echo $progress->chassis_id; ?>);
			@endif
		@endunless
	@endif
@endunless

@unless(empty($loadout))
	@if($show_loadout)
	function get_chassis_data(chassis_id){
		$.ajax({
			type: "GET",
			url: "<?php echo $player->name; ?>/chassis/"+chassis_id,
			context: document.body
		}).done(function(data) {
			$("#player_gear_set").replaceWith(data);
			$('.item_plate_link').popover({
				trigger: "hover",
				html : true,
				content: function() {
					var type = $(this).attr('type');
					var index = $(this).attr('index');
					return $('#'+type+'_'+index+'').html();
				}
			});
		});
	};
	@endif
@endunless

@unless(empty($frame_progress_javascript))
//--- PLOTTING THAT PROGRESS
	@if(($show_progress) && (count($frame_progress_javascript) > 1))
		$("#frame_xp_graph").highcharts({
			title: {
				text: 'Battleframe Progress Graph for {{ e($player->name) }}'
			},
			xAxis: {
				type: 'datetime'
			},
			yAxis: {
				title: {
					text: 'Experience Points'
				},
				min: 0,
				plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
			},
			tooltip: {
				crosshairs: true,
				shared: false,
				valueSuffix: ' XP'
			},
			series: [
<?php
			$counter = 0;
			foreach($frame_progress_javascript as $battle_frame_name=>$xp_data){
?>
				{
					name: <?php echo "\"" . addslashes($battle_frame_name) . "\"" ?>,
					data: <?php echo $xp_data; ?>
				}
				@if($counter != count($frame_progress_javascript))
				,
				@endif
<?php
				$counter++;
			};
?>
			]
		});
	@endif
@endunless
});
</script>
@endsection
