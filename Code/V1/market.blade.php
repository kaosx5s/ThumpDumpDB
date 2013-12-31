@layout('layout.site-wrapper')

@section('assets')
	{{ HTML::style('css/v2/raw-table.css'); }}
	<style type="text/css">
		.table.raw tbody td.item-icon {width:44px;}
		.raw .item-icon img {width:32px;height:32px;}
		.table.raw tbody {border-color: #7F92BD;font: normal 15px 'Play';}
		.table.raw tbody tr + tr {border-color: #D8D4CD;}
		.table.raw tbody td {padding:4px;}

		.popover-content {padding: 9px 14px;}
		.popover-content table tbody td {font: normal 14px 'Arial';}
        .submit-market {margin:-10px 0 0 5px;height:30px;}

		.legendary_plate{background: #a77a22;}
		.epic_plate{background: #9922a7;}
		.rare_plate{background: #225ba7;}
		.uncommon_plate{background: #458c39;}
		.common_plate{background: #ababab;}
		.salvage_plate{background: #c1c1c1;}
		#market_graph{display:none;}
		#td_clicked.selected_row{background-color:#dddddd;}
	</style>
@endsection

@section('nav')
    @include('layout.nav.nav-main')
@endsection

@section('main-content')
<h2>Market Listings</h2>

{{Form::open( URL::to_route('market_search'), 'GET');}}
{{Form::text('query');}}
{{Form::submit('Search Listings', array('class'=>'submit-market'));}}
{{Form::close();}}
@if(!empty($listings->results))
<div id='tooltip'></div>
<div id='market_graph'></div>
<?php
//make icons into lookup table
$icon_itemtypeid_lookup = array();
if($icons) {
	foreach ($icons as $icon)
	{
		$icon_itemtypeid_lookup[$icon->itemtypeid] = $icon->asset_path;
	}
}
?>

<?php echo $listings->links(); ?>

    <?php

        //convert time to "# ___ ago"
        date_default_timezone_set('UTC');

        $now = strtotime('now');
        $then = strtotime( Cache::get('market_listings_last_updated') );
        $diff = ($now - $then);

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
    ?>
    <p>Updated: {{$ago}} ago</p>

<p>Resources with quality &lt; 100 are now prefixed with a 0.  ie: Aluminum^33 will be Aluminum^033</p>

<table class='table raw'>
<thead>
	<?php
		$current_cat = ( $category ) ? $category : '' ;
	?>
    <tr>
        <th></th>
        <th>Name <a href="{{URL::to_route('market') .'/'. $current_cat;}}?sort=name&amp;order=asc">&#9650;</a><a href="{{URL::to_route('market') .'/'. $current_cat;}}?sort=name&amp;order=desc">&#9660;</a></th>
        <th>Price Per Unit <a href="{{URL::to_route('market') .'/'. $current_cat;}}?sort=ppu&amp;order=asc">&#9650;</a><a href="{{URL::to_route('market') .'/'. $current_cat;}}?sort=ppu&amp;order=desc">&#9660;</a></th>
        <th>Price <a href="{{URL::to_route('market') .'/'. $current_cat;}}?sort=price&amp;order=asc">&#9650;</a><a href="{{URL::to_route('market') .'/'. $current_cat;}}?sort=price&amp;order=desc">&#9660;</a></th>
        <th>Quantity <a href="{{URL::to_route('market') .'/'. $current_cat;}}?sort=quantity&amp;order=asc">&#9650;</a><a href="{{URL::to_route('market') .'/'. $current_cat;}}?sort=quantity&amp;order=desc">&#9660;</a></th>
        <th>Expires <a href="{{URL::to_route('market') .'/'. $current_cat;}}?sort=expiration&amp;order=asc">&#9650;</a><a href="{{URL::to_route('market') .'/'. $current_cat;}}?sort=expiration&amp;order=desc">&#9660;</a></th>
    </tr>
</thead>
<tbody>

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
		return $ago;
	}
?>
@foreach($listings->results as $listing)
<tr class='item_row' item_sdb_id='<?php echo $listing->item_sdb_id; ?>' item_quality='<?php echo $listing->rarity; ?>'>

	@if($stats)
    <td class='item-icon'>{{ HTML::image("img/".$icon_itemtypeid_lookup[$listing->item_sdb_id], '', array('class'=>e($listing->rarity).'_plate rounded hoverimg', 'data-content'=>$stats[$listing->ff_id], 'data-placement'=>'left', 'data-trigger'=>'hover', 'data-html'=>'true')); }}</td>
    @else
    <td class='item-icon'>{{ HTML::image("img/".$icon_itemtypeid_lookup[$listing->item_sdb_id], '', array('class'=>e($listing->rarity).'_plate rounded')); }}</td>
    @endif
    <td>{{e( str_replace( array('^CY','^Q'), '', $listing->title) )}}</td>
    <td>{{e( number_format($listing->price_per_unit,2) )}}</td>
    <td>{{e( number_format($listing->price_cy) )}}</td>
    <td>{{e( $listing->quantity )}}</td>
    <td>{{e(convert_time($listing->expires_at))}}</td>

</tr>
@endforeach

</tbody>
</table>

<?php echo $listings->links(); ?>



@else
<p>No market listings found that haven't expired already.</p>
@endif
@endsection

@section('footer-js')
<script type="text/javascript" src="/js/highcharts.js"></script>
<script type="text/javascript" src="/js/highcharts-more.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$(".hoverimg").popover();
	$(".item_row").click(function() {
		var quality = $(this).attr('item_quality');
		var item_sdb_id = $(this).attr('item_sdb_id');
		//DEEEDEEEDEEE DEEEE DEEE DEEEEEEE
		$(this).addClass('selected_row');
		$('#td_clicked').removeClass('selected_row')
		$('#td_clicked').removeAttr('id');
		$(this).attr('id','td_clicked');
		//$(this).after($("#market_graph"));
		$.ajax({
			type: "GET",
			url: "/market/"+item_sdb_id+"/"+quality+"/graph",
			context: document.body
		}).done(function(data) {
			$("#market_graph").replaceWith(data);
			$("#market_graph").show();
			$('html, body').animate({ scrollTop: 0 }, 'slow');
		});
	});
});
</script>
@endsection
