<?php
	require 'application/views/layout/helper/statlookup.php';
	echo "<div id='player_gear_set'>";
	if(isset($player_item_gear[0]['asset_path'])){
?>
		@for($i=0;$i<count($player_item_gear);$i++)
			<div id='item_plate' class='ability-rounded {{ e($player_item_gear[$i]["quality_color"]); }}_plate'>
				<ul class='gear_item'>
					<li class='gear_icon'>{{ HTML::image('img/'.$player_item_gear[$i]['asset_path'],'Wat'); }}</li>
						<li class='gear_name'><a href='#' type='gear' index='<?php echo $i; ?>' class='item_plate_link'>{{ e(str_replace('^Q','',$player_item_gear[$i]['name'])); }}</a></li>
				</ul>
			</div>

				<div id='gear_<?php echo $i; ?>' class='item_tooltip_container'>
					<div class='item_tooltip'>
						<div class='item_tooltip_inset'>
							<p class='tooltip_item_name'><span class='{{ e($player_item_gear[$i]["quality_color"]); }}'>{{ e(str_replace('^Q','',$player_item_gear[$i]['name'])); }}</span></p>
							<ul class='tooltip_item_attributes'>
								@if(isset($player_item_gear[$i]['durability']->current))
									<li><span class='tooltip_little'>Durability (Pool):</span> {{ e($player_item_gear[$i]['durability']->current); }} / 1000 ({{ e($player_item_gear[$i]['durability']->pool); }})
								@else
									<li><span class='tooltip_little'>Durability (Pool):</span> &infin;
								@endif
								<li><span class='tooltip_little'>Quality:</span> {{ e($player_item_gear[$i]['quality']); }}
								<?php
									foreach((array)$player_item_gear[$i]['attribute_modifiers'] as $data => $value){
										//remove mass, power and cores from our listing
										if(!in_array($data,array(950,951,952,953)) && isset($stat_name_lookup[$data])){
											echo "<li><span class='tooltip_little'>". $stat_name_lookup[$data] .":</span> ";
											switch($stat_name_lookup[$data]){
												case "Health Regen":
													$value = number_format($value,2)." HP/s";
													break;
												case "Jet Energy Recharge":
													$value = number_format($value,2)." /s";
													break;
												case "Energy":
													$value = number_format($value,2);
													break;
												case "Jump Height":
													$value = number_format($value,2)." m";
													break;
												case "Run Speed":
													$value = number_format($value,2)." m/s";
													break;
												case "Debuff Duration":
													$value = number_format($value,2)." s";
													break;
												case "Health Threshold":
													$value = number_format($value,2);
													break;
												default:
													$value = $value;
													break;
											};
											echo $value;
											echo "</li>";
										};
									};
								?>
							</ul>
							<p class='tooltip_req_divider'>Requirements</p>
							<ul class='tooltip_item_requirements'>
								<li><span class='tooltip_little'>Mass:</span>
								<?php
									if(isset($player_item_gear[$i]['attribute_modifiers']->{951})){
										echo floor(abs($player_item_gear[$i]['attribute_modifiers']->{951}));
									}else{
										echo floor(abs($player_item_gear[$i]['base_constraint_info']['mass']));
									};
								?>
								<li><span class='tooltip_little'>Power:</span>
								<?php
									if(isset($player_item_gear[$i]['attribute_modifiers']->{952})){
										echo floor(abs($player_item_gear[$i]['attribute_modifiers']->{952}));
									}else{
										echo floor(abs($player_item_gear[$i]['base_constraint_info']['power']));
									};
								?>
								<li><span class='tooltip_little'>Cores:</span>
								<?php
									if(isset($player_item_gear[$i]['attribute_modifiers']->{953})){
										echo floor(abs($player_item_gear[$i]['attribute_modifiers']->{953}));
									}else{
										echo floor(abs($player_item_gear[$i]['base_constraint_info']['cpu']));
									};
								?>
							</ul>
						</div>
					</div>
				</div>
				@endfor
<?php
			};
			if(isset($player_item_weapons[0]['asset_path'])){
?>
				@for($i=0;$i<count($player_item_weapons);$i++)
				<div id='item_plate' class='ability-rounded {{ e($player_item_weapons[$i]["quality_color"]); }}_plate'>
					<ul class='gear_weapon'>
						<li class='gear_icon'>{{ HTML::image('img/'.$player_item_weapons[$i]['asset_path'],'Wat'); }}</li>
						<li class='gear_name'><a href='#' type='weapon' index='<?php echo $i; ?>' class='item_plate_link'>{{ e(str_replace('^Q','',$player_item_weapons[$i]['name'])); }}</a></li>
					</ul>
				</div>
				<div id='weapon_<?php echo $i; ?>' class='item_tooltip_container'>
					<div class='item_tooltip'>
						<div class='item_tooltip_inset'>
							<p class='tooltip_item_name'><span class='{{ e($player_item_weapons[$i]["quality_color"]); }}'>{{ e(str_replace('^Q','',$player_item_weapons[$i]['name'])); }}</span></p>
							<ul class='tooltip_item_attributes'>
								@if(isset($player_item_weapons[$i]['durability']->current))
									<li><span class='tooltip_little'>Durability (Pool):</span> {{ e($player_item_weapons[$i]['durability']->current); }} / 1000 ({{ e($player_item_weapons[$i]['durability']->pool); }})
								@else
									<li><span class='tooltip_little'>Durability (Pool):</span> &infin;
								@endif
								<li><span class='tooltip_little'>Quality:</span> {{ e($player_item_weapons[$i]['quality']); }}
								<li><span class='tooltip_little'>Allocated Power:</span> {{ e($player_item_weapons[$i]['allocated_power']); }}
								<li><span class='tooltip_little'>Reload:</span> {{ e(number_format($player_item_weapons[$i]['base_info']['reloadtime'],2)); }}
								<li><span class='tooltip_little'>Range:</span> {{ e(number_format($player_item_weapons[$i]['base_info']['range'],2)); }}
								<li><span class='tooltip_little'>Max Ammo:</span> {{ e(number_format($player_item_weapons[$i]['base_info']['maxammo'],2)); }}
								<li><span class='tooltip_little'>DPS:</span> {{ e(number_format($player_item_weapons[$i]['base_info']['damagepersecond'],2)); }}
								<?php
									foreach((array)$player_item_weapons[$i]['attribute_modifiers'] as $data => $value){
										//remove mass, power and cores from our listing
										if(!in_array($data,array(950,951,952,953)) && isset($stat_name_lookup[$data])){
											echo "<li><span class='tooltip_little'>". $stat_name_lookup[$data] .":</span> ";
											switch($stat_name_lookup[$data]){
												case "Weapon Splash Radius":
													$value = number_format($value,2)." m";
													break;
												case "Damage Per Round":
													$value = number_format($value,2);
													break;
												case "Weapon Spread":
													$value = number_format($value,2);
													break;
												case "Reload Speed":
													$value = number_format($value,2);
													break;
												default:
													$value = $value;
													break;
											};
											echo $value;
											echo "</li>";
										};
									};
								?>
							</ul>
							<p class='tooltip_req_divider'>Requirements</p>
							<ul class='tooltip_item_requirements'>
								<li><span class='tooltip_little'>Mass:</span>
								<?php
									if(isset($player_item_weapons[$i]['attribute_modifiers']->{951})){
										echo floor(abs($player_item_weapons[$i]['attribute_modifiers']->{951}));
									}else{
										echo floor(abs($player_item_weapons[$i]['base_constraint_info']['mass']));
									};
								?>
								<li><span class='tooltip_little'>Power:</span>
								<?php
									if(isset($player_item_weapons[$i]['attribute_modifiers']->{952})){
										echo floor(abs($player_item_weapons[$i]['attribute_modifiers']->{952}));
									}else{
										echo floor(abs($player_item_weapons[$i]['base_constraint_info']['power']));
									};
								?>
								<li><span class='tooltip_little'>Cores:</span>
								<?php
									if(isset($player_item_weapons[$i]['attribute_modifiers']->{953})){
										echo floor(abs($player_item_weapons[$i]['attribute_modifiers']->{953}));
									}else{
										echo floor(abs($player_item_weapons[$i]['base_constraint_info']['cpu']));
									};
								?>
							</ul>
						</div>
					</div>
				</div>
				@endfor
<?php
			};
			if(isset($player_item_abilities[0]['asset_path'])){
?>
				@for($i=0;$i<count($player_item_abilities);$i++)
				<div id='item_plate' class='ability-rounded {{ e($player_item_abilities[$i]["quality_color"]); }}_plate'>
					<ul class='gear_ability'>
						<li class='gear_icon'>{{ HTML::image('img/'.$player_item_abilities[$i]['asset_path'],'Wat'); }}</li>
						<li class='gear_name'><a href='#' type='ability' index='<?php echo $i; ?>' class='item_plate_link'>{{ e(str_replace('^Q','',$player_item_abilities[$i]['name'])); }}</a></li>
					</ul>
				</div>
				<div id='ability_<?php echo $i; ?>' class='item_tooltip_container'>
					<div class='item_tooltip'>
						<div class='item_tooltip_inset'>
							<p class='tooltip_item_name'><span class='{{ e($player_item_abilities[$i]["quality_color"]); }}'>{{ e(str_replace('^Q','',$player_item_abilities[$i]['name'])); }}</span></p>
							<ul class='tooltip_item_attributes'>
								@if(isset($player_item_abilities[$i]['durability']->current))
									<li><span class='tooltip_little'>Durability (Pool):</span> {{ e($player_item_abilities[$i]['durability']->current); }} / 1000 ({{ e($player_item_abilities[$i]['durability']->pool); }})
								@else
									<li><span class='tooltip_little'>Durability (Pool):</span> &infin;
								@endif
								<li><span class='tooltip_little'>Quality:</span> {{ e($player_item_abilities[$i]['quality']); }}
								<li><span class='tooltip_little'>Allocated Power:</span> {{ e($player_item_abilities[$i]['allocated_power']); }}
								<?php
									foreach((array)$player_item_abilities[$i]['attribute_modifiers'] as $data => $value){
										//remove mass, power and cores from our listing
										if(!in_array($data,array(950,951,952,953)) && isset($stat_name_lookup[$data])){
											echo "<li><span class='tooltip_little'>". $stat_name_lookup[$data] .":</span> ";
											switch($stat_name_lookup[$data]){
												case "Recharge":
													$value = number_format($value,2). "s";
													break;
												case "Effect Radius":
													$value = number_format($value,2). " m";
													break;
												case "Damage":
													$value = round($value);
													break;
												case "Recharge":
													$value = number_format($value,2). "s";
													break;
												case "Damage Duration":
													$value = number_format($value,2). "s";
													break;
												case "Effect Radius":
													$value = number_format($value,2). " m";
													break;
												case "Charge Speed":
													$value = number_format($value,2);
													break;
												case "Damage Reduction":
													$value = number_format($value,2);
													break;
												case "Duration":
													$value = number_format($value,2). "s";
													break;
												case "Turret Mode Rate of Fire Bonus":
													$value = number_format($value,2);
													break;
												case "Turret Mode Accuracy Bonus":
													$value = number_format($value,2);
													break;
												case "Turret Mode Damage Bonus":
													$value = number_format($value,2);
													break;
												default:
													$value = $value;
													break;
											};
											echo $value;
											echo "</li>";
										};
									};
								?>
							</ul>
							<p class='tooltip_req_divider'>Requirements</p>
							<ul class='tooltip_item_requirements'>
								<li><span class='tooltip_little'>Mass:</span>
								<?php
									if(isset($player_item_abilities[$i]['attribute_modifiers']->{951})){
										echo floor(abs($player_item_abilities[$i]['attribute_modifiers']->{951}));
									}else{
										echo floor(abs($player_item_abilities[$i]['base_constraint_info']['mass']));
									};
								?>
								<li><span class='tooltip_little'>Power:</span>
								<?php
									if(isset($player_item_abilities[$i]['attribute_modifiers']->{952})){
										echo floor(abs($player_item_abilities[$i]['attribute_modifiers']->{952}));
									}else{
										echo floor(abs($player_item_abilities[$i]['base_constraint_info']['power']));
									};
								?>
								<li><span class='tooltip_little'>Cores:</span>
								<?php
									if(isset($player_item_abilities[$i]['attribute_modifiers']->{953})){
										echo floor(abs($player_item_abilities[$i]['attribute_modifiers']->{953}));
									}else{
										echo floor(abs($player_item_abilities[$i]['base_constraint_info']['cpu']));
									};
								?>
							</ul>
						</div>
					</div>
				</div>
				@endfor
<?php
			};
			echo "</div>";
?>
