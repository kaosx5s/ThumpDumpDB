<div id='player_chassis_unlocks'>
	<p class='unlock-title mass'>Mass</p>
	<ul class='player_unlocks_mass'>
		@for($i=0;$i<10;$i++)
			@if(isset($current_battle_frame_unlocks['mass_icon'][$i]))
				@if($i<5)
					<li class='player_unlock_icon_first_five'>
				@elseif($i>=5 && $i<7)
					<li class='player_unlock_icon_six_seven'>
				@elseif($i>=7 && $i<9)
					<li class='player_unlock_icon_eight_nine'>
				@elseif($i==9)
					<li class='player_unlock_icon_ten'>
				@endif
				<a href='#' class='player_unlocks_link' title='<?php echo $current_battle_frame_unlocks['mass_name'][$i]; ?>'>
					{{ HTML::image('img/assets/progression_icons/'.$current_battle_frame_unlocks['mass_icon'][$i].'.png','Wat', array('style'=>'width:36px;height:36px;')); }}
				</a>
			@else
				@if($i<5)
					<li class='player_unlock_icon_first_five_faded'>
				@elseif($i>=5 && $i<7)
					<li class='player_unlock_icon_six_seven_faded'>
				@elseif($i>=7 && $i<9)
					<li class='player_unlock_icon_eight_nine_faded'>
				@elseif($i==9)
					<li class='player_unlock_icon_ten_faded'>
				@endif
				{{ HTML::image('img/assets/progression_icons/Mass_'.($i+1).'.png','Wat', array('style'=>'width:36px;height:36px;')); }}
			@endif
			</li>
		@endfor
	</ul>
	<p class='unlock-title pwr'>PWR</p>
	<ul class='player_unlocks_power'>
		@for($i=0;$i<10;$i++)
			@if(isset($current_battle_frame_unlocks['power_icon'][$i]))
				@if($i<5)
					<li class='player_unlock_icon_first_five'>
				@elseif($i>=5 && $i<7)
					<li class='player_unlock_icon_six_seven'>
				@elseif($i>=7 && $i<9)
					<li class='player_unlock_icon_eight_nine'>
				@elseif($i==9)
					<li class='player_unlock_icon_ten'>
				@endif
				<a href='#' class='player_unlocks_link' title='<?php echo $current_battle_frame_unlocks['power_name'][$i]; ?>'>
					{{ HTML::image('img/assets/progression_icons/'.$current_battle_frame_unlocks['power_icon'][$i].'.png','Wat', array('style'=>'width:36px;height:36px;')); }}
				</a>
			@else
				@if($i<5)
					<li class='player_unlock_icon_first_five_faded'>
				@elseif($i>=5 && $i<7)
					<li class='player_unlock_icon_six_seven_faded'>
				@elseif($i>=7 && $i<9)
					<li class='player_unlock_icon_eight_nine_faded'>
				@elseif($i==9)
					<li class='player_unlock_icon_ten_faded'>
				@endif
				{{ HTML::image('img/assets/progression_icons/Power_'.($i+1).'.png','Wat', array('style'=>'width:36px;height:36px;')); }}
			@endif
			</li>
		@endfor
	</ul>
	<p class='unlock-title cpu'>CPU</p>
	<ul class='player_unlocks_cpu'>
		@for($i=0;$i<10;$i++)
			@if(isset($current_battle_frame_unlocks['cpu_icon'][$i]))
				@if($i<5)
					<li class='player_unlock_icon_first_five'>
				@elseif($i>=5 && $i<7)
					<li class='player_unlock_icon_six_seven'>
				@elseif($i>=7 && $i<9)
					<li class='player_unlock_icon_eight_nine'>
				@elseif($i==9)
					<li class='player_unlock_icon_ten'>
				@endif
				<a href='#' class='player_unlocks_link' title='<?php echo $current_battle_frame_unlocks['cpu_name'][$i]; ?>'>
					{{ HTML::image('img/assets/progression_icons/'.$current_battle_frame_unlocks['cpu_icon'][$i].'.png','Wat', array('style'=>'width:36px;height:36px;')); }}
				</a>
			@else
				@if($i<5)
					<li class='player_unlock_icon_first_five_faded'>
				@elseif($i>=5 && $i<7)
					<li class='player_unlock_icon_six_seven_faded'>
				@elseif($i>=7 && $i<9)
					<li class='player_unlock_icon_eight_nine_faded'>
				@elseif($i==9)
					<li class='player_unlock_icon_ten_faded'>
				@endif
				{{ HTML::image('img/assets/progression_icons/CPU_'.($i+1).'.png','Wat', array('style'=>'width:36px;height:36px;')); }}
			@endif
			</li>
		@endfor
	</ul>
</div>
