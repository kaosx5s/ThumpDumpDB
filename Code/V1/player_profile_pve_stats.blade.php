<?php
//cast to arrays to check if empty
$check_empty_kills = (array) $pve_stats_data['kills'];
if( empty($check_empty_kills) ) {
	$pve_stats_data['kills'] = $check_empty_kills;
}
$check_empty_stats = (array) $pve_stats_data['stats'];
if( empty($check_empty_stats) ) {
	$pve_stats_data['stats'] = $check_empty_stats;
}
$check_empty_events = (array) $pve_stats_data['events'];
if( empty($check_empty_events) ) {
	$pve_stats_data['events'] = $check_empty_events;
}


if(($web_prefs['show_pve_kills']) && !empty($pve_stats_data['kills'])){
	//Look at all those kills, better find out who they belong to.
	$bandit_id_list = array(512,513,514,529,530,532,592,593,594,595,611,761,762,903,962,969,970);
	$melded_id_list = array(270,314,409,528,554,562,565,602);
	$chosen_id_list = array(281,326,327,391,392,409,453,543,548,572,708,709,1012);
	$aranha_id_list = array(239,241,243,244,347,348,386,387,430,433,436,448,449,452,505,506,507,508,584,588,590,596,599,641,644,689,690,693,696,888,923,927,929,931,935,937,938,940,942,949,955,1011);
	
	//counter
	$bandit_mob_counter = 0;
	$melded_mob_counter = 0;
	$chosen_mob_counter = 0;
	$aranha_mob_counter = 0;

	foreach($pve_stats_data['kills'] as $data){
		if(!empty($data)){
			//for each un-empty tier find out where the mobs go
			foreach($data as $mob_id => $amount){
				if(in_array($mob_id,$bandit_id_list)){
					//this is a bandit mob!
					$bandit_mob_counter = $bandit_mob_counter + $amount;
				};
				if(in_array($mob_id,$melded_id_list)){
					//this is a melded mob!
					$melded_mob_counter = $melded_mob_counter + $amount;
				};
				if(in_array($mob_id,$chosen_id_list)){
					//this is a chosen mob!
					$chosen_mob_counter = $chosen_mob_counter + $amount;
				};
				if(in_array($mob_id,$aranha_id_list)){
					//this is a aranha mob!
					$aranha_mob_counter = $aranha_mob_counter + $amount;
				};
			};
		};
	};
	
	//run each counter through the number formatting function.
	$total_mob_kills = stats_number_formatting($bandit_mob_counter + $chosen_mob_counter + $aranha_mob_counter + $melded_mob_counter);
	$bandit_mob_counter = stats_number_formatting($bandit_mob_counter);
	$melded_mob_counter = stats_number_formatting($melded_mob_counter);
	$chosen_mob_counter = stats_number_formatting($chosen_mob_counter);
	$aranha_mob_counter = stats_number_formatting($aranha_mob_counter);
};

//do some math on accuracy (total shots fired / accuracy)
if(!empty($pve_stats_data['stats'])){

	$total_shots = $pve_stats_data['stats']->primary_weapon_shots_fired + $pve_stats_data['stats']->secondary_weapon_shots_fired;
	if($pve_stats_data['stats']->accuracy != 0){
		$pve_stats_data['stats']->accuracy = number_format((($total_shots / $pve_stats_data['stats']->accuracy)*100),0);
		if($pve_stats_data['stats']->accuracy > 100){
			//the accuracy is more than 100%, assume burst weapon / aoe use; the average burst weapon uses 3 shots per round.
			$pve_stats_data['stats']->accuracy = number_format(($pve_stats_data['stats']->accuracy / 3),0);
			if($pve_stats_data['stats']->accuracy > 100){
				//the average splash radius is 3.5 but that seems a bit harsh, if we are still over 100% then do a division by 2.25.
				$pve_stats_data['stats']->accuracy = number_format(($pve_stats_data['stats']->accuracy / 2.25),0);
			};
		};
	}else{
		$pve_stats_data['stats']->accuracy = 0;
	};

	//convert all numbers to smaller format if larger than 10,000. (we only need to do this for stats that are prone to going over)
	$pve_stats_data['stats']->damage_done = stats_number_formatting($pve_stats_data['stats']->damage_done);
	$pve_stats_data['stats']->damage_taken = stats_number_formatting($pve_stats_data['stats']->damage_taken);
	$pve_stats_data['stats']->primary_weapon_shots_fired = stats_number_formatting($pve_stats_data['stats']->primary_weapon_shots_fired);
	$pve_stats_data['stats']->secondary_weapon_shots_fired = stats_number_formatting($pve_stats_data['stats']->secondary_weapon_shots_fired);
	$pve_stats_data['stats']->primary_reloads = stats_number_formatting($pve_stats_data['stats']->primary_reloads);
	$pve_stats_data['stats']->secondary_reloads = stats_number_formatting($pve_stats_data['stats']->secondary_reloads);
	$pve_stats_data['stats']->healed = stats_number_formatting($pve_stats_data['stats']->healed);
};

if(!empty($pve_stats_data['events'])){
	//format those race numbers
	//sunken -> copa
	if($pve_stats_data['events']->lgv_fastest_time_sunken_copa == 999){
		$pve_stats_data['events']->lgv_fastest_time_sunken_copa = "N/A";
	}else{
		$pve_stats_data['events']->lgv_fastest_time_sunken_copa = number_format($pve_stats_data['events']->lgv_fastest_time_sunken_copa, 2) . "s";
	};
	//thump dump -> copa
	if($pve_stats_data['events']->lgv_fastest_time_thump_copa == 999){
		$pve_stats_data['events']->lgv_fastest_time_thump_copa = "N/A";
	}else{
		$pve_stats_data['events']->lgv_fastest_time_thump_copa = number_format($pve_stats_data['events']->lgv_fastest_time_thump_copa, 2) . "s";
	};
	//copa -> trans
	if($pve_stats_data['events']->lgv_fastest_time_copa_trans == 999){
		$pve_stats_data['events']->lgv_fastest_time_copa_trans = "N/A";
	}else{
		$pve_stats_data['events']->lgv_fastest_time_copa_trans = number_format($pve_stats_data['events']->lgv_fastest_time_copa_trans, 2) . "s";
	};
	//copa -> thump dump
	if($pve_stats_data['events']->lgv_fastest_time_copa_thump == 999){
		$pve_stats_data['events']->lgv_fastest_time_copa_thump = "N/A";
	}else{
		$pve_stats_data['events']->lgv_fastest_time_copa_thump = number_format($pve_stats_data['events']->lgv_fastest_time_copa_thump, 2) . "s";
	};
	//trans -> sunken
	if($pve_stats_data['events']->lgv_fastest_time_trans_sunken == 999){
		$pve_stats_data['events']->lgv_fastest_time_trans_sunken = "N/A";
	}else{
		$pve_stats_data['events']->lgv_fastest_time_trans_sunken = number_format($pve_stats_data['events']->lgv_fastest_time_trans_sunken, 2) . "s";
	};
};
?>
<div id='pve_stats'>
	<div id='pve_stats_data_container'>
		<table class='pve_stats_life'>
			<tr>
				<th colspan='2'>General</th>
			</tr>
			<tbody>
				@if($web_prefs['show_pve_stats'] && !empty($pve_stats_data['stats']))
					<tr><td>Revives</td><td><?php echo $pve_stats_data['stats']->revives; ?></td></tr>
					<tr><td>Revived</td><td><?php echo $pve_stats_data['stats']->revived; ?></td></tr>
					<tr><td>Incapacitated</td><td><?php echo $pve_stats_data['stats']->incapacitated; ?></td></tr>
					<tr><td>Deaths</td><td><?php echo $pve_stats_data['stats']->deaths; ?></td></tr>
					<tr><td>Suicides</td><td><?php echo $pve_stats_data['stats']->suicides; ?></td></tr>
					<tr><td>Drowned</td><td><?php echo $pve_stats_data['stats']->drowned; ?></td><tr>
				@else
					<tr><td>Revives</td><td>N/A</td></tr>
					<tr><td>Revived</td><td>N/A</td></tr>
					<tr><td>Incapacitated</td><td>N/A</td></tr>
					<tr><td>Deaths</td><td>N/A</td></tr>
					<tr><td>Suicides</td><td>N/A</td></tr>
					<tr><td>Drowned</td><td>N/A</td><tr>
				@endif
			</tbody>
		</table>
		<table class='pve_stats_kills'>
			<tr>
				<th colspan='2'>Kills &amp; Damage</th>
			</tr>
			<tbody>
				@if($web_prefs['show_pve_kills'] && !empty($pve_stats_data['kills']))
					<tr><td>Mob Kills</td><td><?php echo $total_mob_kills ?></td></tr>
					<tr><td class='indented'>Bandit</td><td><?php echo $bandit_mob_counter; ?></td></tr>
					<tr><td class='indented'>Chosen</td><td><?php echo $chosen_mob_counter; ?></td></tr>
					<tr><td class='indented'>Gaea (Aranha)</td><td><?php echo $aranha_mob_counter; ?></td></tr>
					<tr><td class='indented'>Melding</td><td><?php echo $melded_mob_counter; ?></td></tr>
				@else
					<tr><td>Mob Kills</td><td>N/A</td></tr>
					<tr><td class='indented'>Bandit</td><td>N/A</td></tr>
					<tr><td class='indented'>Chosen</td><td>N/A</td></tr>
					<tr><td class='indented'>Gaea (Aranha)</td><td>N/A</td></tr>
					<tr><td class='indented'>Melding</td><td>N/A</td></tr>
				@endif
				@if($web_prefs['show_pve_stats'] && !empty($pve_stats_data['stats']))
					<tr><td>Damage Done</td><td><?php echo $pve_stats_data['stats']->damage_done; ?></td></tr>
					<tr><td>Damage Taken</td><td><?php echo $pve_stats_data['stats']->damage_taken; ?></td></tr>
					<tr><td>Healed</td><td><?php echo $pve_stats_data['stats']->healed; ?></td></tr>
					<tr><td>Headshots</td><td><?php echo $pve_stats_data['stats']->headshots; ?></td></tr>
					<tr><td>Scanhammer Kills</td><td><?php echo $pve_stats_data['stats']->scanhammer_kills; ?></td></tr>
				@else
					<tr><td>Damage Done</td><td>N/A</td></tr>
					<tr><td>Damage Taken</td><td>N/A</td></tr>
					<tr><td>Healed</td><td>N/A</td></tr>
					<tr><td>Headshots</td><td>N/A</td></tr>
					<tr><td>Scanhammer Kills</td><td>N/A</td></tr>
				@endif
			</tbody>
		</table>
		<table class='pve_stats_weapons'>
			<tr>
				<th colspan='2'>Weapon Use</th>
			</tr>
			<tbody>
				@if($web_prefs['show_pve_stats'] && !empty($pve_stats_data['stats']))
					<tr><td>Primary Shots Fired</td><td><?php echo $pve_stats_data['stats']->primary_weapon_shots_fired; ?></td></tr>
					<tr><td>Secondary Shots Fired</td><td><?php echo $pve_stats_data['stats']->secondary_weapon_shots_fired; ?></td></tr>
					<tr><td>Primary Reloads</td><td><?php echo $pve_stats_data['stats']->primary_reloads; ?></td></tr>
					<tr><td>Secondary Reloads</td><td><?php echo $pve_stats_data['stats']->secondary_reloads; ?></td></tr>
					<tr><td>Accuracy</td><td><?php echo $pve_stats_data['stats']->accuracy; ?>&#37;</td></tr>
				@else
					<tr><td>Primary Shots Fired</td><td>N/A</td></tr>
					<tr><td>Secondary Shots Fired</td><td>N/A</td></tr>
					<tr><td>Primary Reloads</td><td>N/A</td></tr>
					<tr><td>Secondary Reloads</td><td>N/A</td></tr>
					<tr><td>Accuracy</td><td>N/A</td></tr>
				@endif
			</tbody>
		</table>
		<table class='pve_stats_event_other'>
			<tr>
				<th colspan='2'>Non-Scaled Events</th>
			</tr>
			<tbody>
				@if($web_prefs['show_pve_events'] && !empty($pve_stats_data['stats']))
					<tr><td>Crashed LGV's</td><td><?php echo $pve_stats_data['events']->crashed_lgvs; ?></td></tr>
					<tr><td>Crashed Thumpers</td><td><?php echo $pve_stats_data['events']->crashed_thumpers; ?></td></tr>
					<tr><td>Holmgang Tech</td><td><?php echo $pve_stats_data['events']->holmgang_tech_completed; ?></td></tr>
					<tr><td>Outposts Defended</td><td><?php echo $pve_stats_data['events']->outposts_defended; ?></td></tr>
					<tr><td>Watchtowers Defended</td><td><?php echo $pve_stats_data['events']->watchtowers_defended; ?></td></tr>
					<tr><td>Watchtowers Recaptured</td><td><?php echo $pve_stats_data['events']->watchtowers_retaken; ?></td></tr>
					<tr><td>Raider Squads Defeated</td><td><?php echo $pve_stats_data['events']->raider_squads_defeated; ?></td></tr>
					@if(isset($pve_stats_data['events']->chosen_death_squads))
						<tr><td>Chosen Death Squads Defeated</td><td><?php echo $pve_stats_data['events']->chosen_death_squads; ?></td></tr>
					@endif
				@else
					<tr><td>Crashed LGV's</td><td>N/A</td></tr>
					<tr><td>Crashed Thumpers</td><td>N/A</td></tr>
					<tr><td>Holmgang Tech</td><td>N/A</td></tr>
					<tr><td>Outposts Defended</td><td>N/A</td></tr>
					<tr><td>Watchtowers Defended</td><td>N/A</td></tr>
					<tr><td>Watchtowers Recaptured</td><td>N/A</td></tr>
					@if(isset($pve_stats_data['events']->chosen_death_squads))
						<tr><td>Chosen Death Squads Defeated</td><td>N/A</td></tr>
					@endif
				@endif
			</tbody>
		</table>
	</div>
	@if($web_prefs['show_pve_events'] && !empty($pve_stats_data['events']))
		<span class='pve_stats_header_two'>Scaled Events</span>
		<div id='pve_stats_events_container'>
			<table class='pve_stats_event_ares'>
				<tbody>
					<tr><td>Ares Missions</td><td><?php echo $pve_stats_data['events']->ares_missions_0 + $pve_stats_data['events']->ares_missions_1; ?></td></tr>
					<tr><td class='indented'>Stage 1</td><td><?php echo $pve_stats_data['events']->ares_missions_0; ?></td></tr>
					<tr><td class='indented'>Stage 2</td><td><?php echo $pve_stats_data['events']->ares_missions_1; ?></td></tr>
				</tbody>
			</table>
			<table class='pve_stats_event_strike_teams'>
				<tbody>
					<tr><td>Chosen Strike Teams</td><td><?php echo $pve_stats_data['events']->strike_teams_0 + $pve_stats_data['events']->strike_teams_1 + $pve_stats_data['events']->strike_teams_2 + $pve_stats_data['events']->strike_teams_3; ?></td></tr>
					<tr><td class='indented'>Stage 1</td><td><?php echo $pve_stats_data['events']->strike_teams_0; ?></td></tr>
					<tr><td class='indented'>Stage 2</td><td><?php echo $pve_stats_data['events']->strike_teams_1; ?></td></tr>
					<tr><td class='indented'>Stage 3</td><td><?php echo $pve_stats_data['events']->strike_teams_2; ?></td></tr>
					<tr><td class='indented'>Stage 4</td><td><?php echo $pve_stats_data['events']->strike_teams_3; ?></td></tr>
				</tbody>
			</table>
			<table class='pve_stats_event_incursions'>
				<tbody>
					<tr><td>Incursions</td><td><?php echo $pve_stats_data['events']->warbringers_3 + $pve_stats_data['events']->warbringers_4; ?></td></tr>
					<tr><td class='indented'>Stage 3</td><td><?php echo $pve_stats_data['events']->warbringers_3; ?></td></tr>
					<tr><td class='indented'>Stage 4</td><td><?php echo $pve_stats_data['events']->warbringers_4; ?></td></tr>
				</tbody>
			</table>
			<table class='pve_stats_event_invasions'>
				<tbody>
					<tr><td>Invasions</td><td><?php echo $pve_stats_data['events']->thump_dump_invasions_completed + $pve_stats_data['events']->sunken_harbor_invasions_completed; ?></td></tr>
					<tr><td class='indented'>Thump Dump</td><td><?php echo $pve_stats_data['events']->thump_dump_invasions_completed; ?></td></tr>
					<tr><td class='indented'>Sunken Harbor</td><td><?php echo $pve_stats_data['events']->sunken_harbor_invasions_completed; ?></td></tr>
				</tbody>
			</table>
			<table class='pve_stats_event_lgv_races'>
				<tbody>
					<tr><td>LGV Races</td><td><?php echo $pve_stats_data['events']->lgv_races; ?></td></tr>
					<tr><td class='indented'>SH -> Copa</td><td><?php echo $pve_stats_data['events']->lgv_fastest_time_sunken_copa; ?></td></tr>
					<tr><td class='indented'>TD -> Copa</td><td><?php echo $pve_stats_data['events']->lgv_fastest_time_thump_copa; ?></td></tr>
					<tr><td class='indented'>Copa -> TH</td><td><?php echo $pve_stats_data['events']->lgv_fastest_time_copa_trans; ?></td></tr>
					<tr><td class='indented'>Copa -> TD</td><td><?php echo $pve_stats_data['events']->lgv_fastest_time_copa_thump; ?></td></tr>
					<tr><td class='indented'>TH -> SH</td><td><?php echo $pve_stats_data['events']->lgv_fastest_time_trans_sunken; ?></td></tr>
				</tbody>
			</table>
			<table class='pve_stats_event_tornados'>
				<tbody>
					<tr><td>Tornados</td><td><?php echo $pve_stats_data['events']->tornados_3 + $pve_stats_data['events']->tornados_4; ?></td></tr>
					<tr><td class='indented'>Stage 3</td><td><?php echo $pve_stats_data['events']->tornados_3; ?></td></tr>
					<tr><td class='indented'>Stage 4</td><td><?php echo $pve_stats_data['events']->tornados_4; ?></td></tr>
				</tbody>
			</table>
		</div>
	@endif
</div>