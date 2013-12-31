<?php

class Player_Stores_Controller extends Base_Controller {

    public $restful = true;
	
	public function get_player_certs($name = NULL){
        //searching by name, null name = no buenos
        if($name == NULL) {
            return Response::error('404');
        }else{
            $input_name = urldecode($name);
        }

        //Look up the player, latest to account for deletes with the same name
        $player = Player::where('name','=',$input_name)
            ->order_by('created_at', 'DESC')
            ->first();

        //no player found, why not search?
        if(!$player) {
            return Response::error('404');
        }
		
        //Does the player have an army?
        $army = Army::where('armyId','=',$player->armyid)
            ->order_by('created_at', 'DESC')
            ->first();

        //escape for output in title
        $playerName = htmlentities($player->name);

		//Player website preferences
		$web_prefs = WebsitePref::where('db_id','=',$player->db_id)->first();
		if(isset($web_prefs->attributes)){
			$web_prefs = $web_prefs->attributes;
		};
		
		//website prefs we care about - show_workbench, show_craftables, show_market_listings
		if(!isset($web_prefs['show_workbench'])){
			$web_prefs['show_workbench'] = 1;
		};
		if(!isset($web_prefs['show_craftables'])){
			$web_prefs['show_craftables'] = 0;
		};
		if(!isset($web_prefs['show_market_listings'])){
			$web_prefs['show_market_listings'] = 0;
		};
		
		if($web_prefs['show_market_listings']){
			//find out what this player has currently listed on the market
			$cache_key_player_market = $player->db_id . "_market_listings";
			if(Cache::has($cache_key_player_market)){
				//pull from cache
				$cached_market_listings = Cache::get($cache_key_player_market);
				$item_stats_lookup = $cached_market_listings['stats'];
				$market_listings = $cached_market_listings['data'];
			}else{
				$market_listings = MarketListing::where('active','=','1')->where('character_guid','=',$player->db_id)->get(array('item_sdb_id','expires_at','price_cy','price_per_unit','rarity','quantity','title','icon','ff_id','category'));

				if(!empty($market_listings)){
					$market_stat_ids = array();
					$market_stat_cats = array();
					foreach($market_listings as $listing){
						$market_stat_ids[] = $listing->attributes['ff_id'];
						$market_stat_cats[] = $listing->attributes['category'];
					};
					if(count($market_stat_cats) < 1) {
						$stats = false;
					}
					$market_stat_cats_unique = array_unique($market_stat_cats);

					$item_stats_lookup = false;
					foreach ($market_stat_cats_unique as $statcat)
					{
						switch ($statcat) {
							case 'AbilityModule':
								$stats = MarketStatAbilityModule::where_in('marketlisting_id', $market_stat_ids)->get();
								foreach ($stats as $stat)
								{
									$temp = '<table>';
									$ustats = (array) unserialize($stat->stats);
									ksort($ustats);
									foreach ($ustats as $key => $value)
									{
										$key = str_replace('_', ' ', $key);
										$key = ucwords($key);
										$value = number_format($value,2);
										$temp .= '<tr><td>'.htmlentities($key) . ': </td><td>' . htmlentities($value) . '</td></tr>';
									}
									$temp .= '</table>';
									$item_stats_lookup[(string) $stat->marketlisting_id] = $temp;
								}
								break;
							case 'CraftingComponent':
								$stats = MarketStatCraftingComponent::where_in('marketlisting_id', $market_stat_ids)->get();
								foreach ($stats as $stat)
								{
									$temp = '';
									$key_lookup = array(
										'mass' => 'MASS',
										'power' => 'PWR',
										'cpu' => 'CPU'
									);
									ksort($stat->attributes);
									foreach ($stat->attributes as $key => $value)
									{
										if($value > 0 && array_key_exists($key, $key_lookup)) {
											$temp .= htmlentities($key_lookup[$key]) . ': ' . htmlentities($value) . '<br>';
										}                        
									}
									$item_stats_lookup[(string) $stat->marketlisting_id] = $temp;
								}
								break;
							case 'Jumpjet':
								$stats = MarketStatJumpjet::where_in('marketlisting_id', $market_stat_ids)->get();
								foreach ($stats as $stat)
								{
									$temp = '<table>';
									$key_lookup = array('id','updated_at','created_at','marketlisting_id');
									ksort($stat->attributes);
									foreach ($stat->attributes as $key => $value)
									{
										if(!in_array($key, $key_lookup)) {
											$key = str_replace('_', ' ', $key);
											$key = ucwords($key);
											$value = number_format($value,2);
											$temp .= '<tr><td>'.htmlentities($key) . ': </td><td>' . htmlentities($value) . '</td></tr>';
										}
									}
									$temp .= '</table>';
									$item_stats_lookup[(string) $stat->marketlisting_id] = $temp;
								}
								break;
							case 'Plating':
								$stats = MarketStatPlating::where_in('marketlisting_id', $market_stat_ids)->get();
								foreach ($stats as $stat)
								{
									$temp = '<table>';
									$key_lookup = array('id','updated_at','created_at','marketlisting_id');
									ksort($stat->attributes);
									foreach ($stat->attributes as $key => $value)
									{
										if(!in_array($key, $key_lookup)) {
											$key = str_replace('_', ' ', $key);
											$key = ucwords($key);
											$value = number_format($value,2);
											$temp .= '<tr><td>'.htmlentities($key) . ': </td><td>' . htmlentities($value) . '</td></tr>';
										}
									}
									$temp .= '</table>';
									$item_stats_lookup[(string) $stat->marketlisting_id] = $temp;
								}
								break;
							case 'Resource':
								$stats = MarketStatResource::where_in('marketlisting_id', $market_stat_ids)->get();     
								foreach ($stats as $stat)
								{
									$temp = '<table>';
									$key_lookup = array(
										1 => 'Thermal Resistance',
										2 => 'Conductivity',
										3 => 'Malleability',
										4 => 'Density',
										5 => 'Toughness',
										'quality' => 'Quality'
									);
									$temp_ar = array();
									foreach ($stat->attributes as $key => $value)
									{
										if(array_key_exists($key, $key_lookup)) {
											$temp_ar[$key] = '<tr><td>'.htmlentities($key_lookup[$key]) . ': </td><td>' . htmlentities($value) . '</td></tr>';
										}
									}
										//because resources are special, we alpha order them numerically
										$temp .= $temp_ar[2];
										$temp .= $temp_ar[4];
										$temp .= $temp_ar[3];
										$temp .= $temp_ar[1];
										$temp .= $temp_ar[5];
										$temp .= $temp_ar['quality'];
									$temp .= '</table>';
									$item_stats_lookup[(string) $stat->marketlisting_id] = $temp;
								}
								break;
							case 'Servo':
								$stats = MarketStatServo::where_in('marketlisting_id', $market_stat_ids)->get();
								foreach ($stats as $stat)
								{
									$temp = '<table>';
									$key_lookup = array('id','updated_at','created_at','marketlisting_id');
									ksort($stat->attributes);
									foreach ($stat->attributes as $key => $value)
									{
										if(!in_array($key, $key_lookup)) {
											$key = str_replace('_', ' ', $key);
											$key = ucwords($key);
											$value = number_format($value,2);
											$temp .= '<tr><td>'.htmlentities($key) . ': </td><td>' . htmlentities($value) . '</td></tr>';
										}
									}
									$temp .= '</table>';
									$item_stats_lookup[(string) $stat->marketlisting_id] = $temp;
								}
								break;
							case 'Weapon':
								$stats = MarketStatWeapon::where_in('marketlisting_id', $market_stat_ids)->get();
								foreach ($stats as $stat)
								{
									$temp = '<table>';
									$key_lookup = array('id','updated_at','created_at','marketlisting_id');
									ksort($stat->attributes);
									foreach ($stat->attributes as $key => $value)
									{
										if(!in_array($key, $key_lookup)) {
											$key = str_replace('_', ' ', $key);
											$key = ucwords($key);
											$value = number_format($value,2);
											$temp .= '<tr><td>'.htmlentities($key) . ': </td><td>' . htmlentities($value) . '</td></tr>';
										}
									}
									$temp .= '</table>';
									$item_stats_lookup[(string) $stat->marketlisting_id] = $temp;
								}
								break;
							default:
								break;
						}
					}
					//Cache
					$cached_market_listings = array();
					$cached_market_listings['data'] = $market_listings;
					$cached_market_listings['stats'] = $item_stats_lookup;
					Cache::put($cache_key_player_market,$cached_market_listings,30);
				}
			};
		};
		
		if($web_prefs['show_craftables']){
			if(isset($web_prefs['show_progress'])){
				if($web_prefs['show_progress']){
					$progress = Progress::where('db_id','=',$player->db_id)->order_by('updated_at','DESC')->first();
				}else{
					$progress = null;
				};
			}else{
				$progress = Progress::where('db_id','=',$player->db_id)->order_by('updated_at','DESC')->first();
			};
			
			if(!empty($progress)){
				$cache_key_progress = $player->db_id . "_progress";
				$progress = $progress->entry;
				$progress_md5 = md5($progress);
				if(Cache::Get($cache_key_progress . "_md5_cc") != $progress_md5){
					$progress = unserialize($progress);
					if(isset($progress->unlocks)){
						$master_cert_list = array();
						
						//all odd number certs from 799 to 1245
						//category names from 766 to 783
						//arsenal = 1398
						/*
							766 = base assault
							767 = base biotech
							768 = base dreadnaught
							769 = base engineer
							770 = base recon
							
							774 = firecat
							775 = tigerclaw
							776 = dragonfly
							777 = recluse
							778 = rhino
							779 = mammoth
							1398 = arsenal
							780 = electron
							781 = bastion
							782 = nighthawk
							783 = raptor
						*/
						//certs we don't care about
						$useless_certs = array(784,785,786,788,789,790,791,792,794,1154,1359,1366,1367,1371,1372,1378,1379,1380,1381,1382,1383,1384,1385,1386,1387,1388,1389,1390,1391,1392);

						$unlocked_base_assault = 0;
						$unlocked_base_biotech = 0;
						$unlocked_base_dreadnaught = 0;
						$unlocked_base_engineer = 0;
						$unlocked_base_recon = 0;
						
						$unlocked_firecat = 0;
						$unlocked_tigerclaw = 0;
						$unlocked_dragonfly = 0;
						$unlocked_recluse = 0;
						$unlocked_rhino = 0;
						$unlocked_arsenal = 0;
						$unlocked_mammoth = 0;
						$unlocked_electron = 0;
						$unlocked_bastion = 0;
						$unlocked_nighthawk = 0;
						$unlocked_raptor = 0;
						
						foreach($progress->unlocks as $progress_certs){
							//Make a master list of certs, note that certs are in numerical sorted order so we are able to do a fast merge
							for($i=0;$i<count($progress_certs);$i++){
								if(!in_array($progress_certs[$i],$master_cert_list)){
									if(!in_array($progress_certs[$i],$useless_certs)){
										//unlock flags
										if($progress_certs[$i] == 766){
											$unlocked_base_assault = 1;
										};
										if($progress_certs[$i] == 767){
											$unlocked_base_biotech = 1;
										};
										if($progress_certs[$i] == 768){
											$unlocked_base_dreadnaught = 1;
										};
										if($progress_certs[$i] == 769){
											$unlocked_base_engineer = 1;
										};
										if($progress_certs[$i] == 770){
											$unlocked_base_recon = 1;
										};
										if($progress_certs[$i] == 774){
											$unlocked_firecat = 1;
										};
										if($progress_certs[$i] == 775){
											$unlocked_tigerclaw = 1;
										};
										if($progress_certs[$i] == 776){
											$unlocked_dragonfly = 1;
										};
										if($progress_certs[$i] == 777){
											$unlocked_recluse = 1;
										};
										if($progress_certs[$i] == 778){
											$unlocked_rhino = 1;
										};
										if($progress_certs[$i] == 779){
											$unlocked_mammoth = 1;
										};
										if($progress_certs[$i] == 1398){
											$unlocked_arsenal = 1;
										};
										if($progress_certs[$i] == 780){
											$unlocked_electron = 1;
										};
										if($progress_certs[$i] == 781){
											$unlocked_bastion = 1;
										};
										if($progress_certs[$i] == 782){
											$unlocked_nighthawk = 1;
										};
										if($progress_certs[$i] == 783){
											$unlocked_raptor = 1;
										};
									
										if(($progress_certs[$i] > 764 && $progress_certs[$i] < 798) || ($progress_certs[$i] > 1246)){
											//add to the master list
											$master_cert_list[] = $progress_certs[$i];
										}else if(($progress_certs[$i] > 797 && $progress_certs[$i] < 1246) && ($progress_certs[$i] % 2 == 0)){
											//add to the master list
											$master_cert_list[] = $progress_certs[$i];
										};
									};
								};
							};
						};
						//free up some memory
						$progress = null;
						sort($master_cert_list);
						
						$cert_info = Certification::where(
							function($query) use($master_cert_list){
								$query->where_in('id', $master_cert_list);
							}
						)->get(array('id','name'));
						
						//look at all these arrays
						//jumpjets, secondary weapons {Assault rifle, burst rifle, grenade launcher, SMG, shotgun}, servos
						$base_shared_certs = array(910,982,984,986,988,990,992,1222,1224,1226,1228,1230,1242,1244,1345,1346,1347,1348,1349,1355,1356);
						//accord absorbtion plating, afterburners, bombs away, burn jets, crater, overcharge, plasma cannon, shockwave, assault plating
						$base_assault_certs = array(787,793,795,798,800,802,806,828,996,1016,1018,1020,1022,1024,1026,1028,1202,1204,1232,1246,1247,1248,1249,1250,1335,1336,1350);
						//accord chemical sprayer, accord siphoning plating, healing generator, healing wave, needler, poison ball, poison trail, triage, biotech plating
						$base_biotech_certs = array(804,808,812,814,824,830,832,1008,1010,1054,1056,1058,1060,1062,1064,1210,1212,1236,1256,1264,1265,1266,1267,1268,1339,1340,1352);
						//Absorption Bomb, Accord Explosive Rounds, Accord Mortar Arcfold, Accord Repulsor Blast, Heavy Armor, Turret Mode, Heavy Machine Gun, Resilient Plating
						$base_dreadnaught_certs = array(862,866,872,880,940,998,1012,1014,1090,1092,1094,1096,1098,1100,1206,1208,1234,1281,1282,1283,1284,1285,1286,1337,1338,1351);
						//Anti-Personnel Turret, Charged Pulse Generator, Claymore, Deployable Shield, Heavy Turret, Supply Station, Accord Nanite Deployment, engineer plating, Sticky Grenade Launcher
						$base_engineer_certs = array(942,944,946,948,950,994,1004,1006,1126,1128,1130,1132,1134,1136,1214,1216,1238,1299,1300,1301,1302,1303,1304,1341,1342,1353);
						//Accord Artillery Strike, Cryo Grenade, Decoy, Proximity Reponse, Resonating Bolts, SIN Beacon, Accord Regenerative Plating, R36 Assault Rifle
						$base_recon_certs = array(904,918,922,928,932,934,936,1000,1002,1166,1168,1170,1172,1174,1176,1218,1220,1240,1317,1318,1319,1320,1321,1322,1343,1344,1354);
						
						//ADV frames
						//fuel air bomb, immolate, incinerator, inferno dash, thermal cannon, thermal wave
						$base_firecat_certs = array(810,816,818,820,822,826,1030,1032,1034,1036,1038,1040,1251,1252,1253,1254,1255,1257);
						//fusion cannon, Disruption, Missile Shot, Tether Field, Trailblaze, Auxiliary Tanks
						$base_tigerclaw_certs = array(834,838,842,844,848,852,1042,1044,1046,1048,1050,1052,1258,1259,1260,1261,1262,1263);
						//bio rifle, Emergency Response, Healing Ball, Healing Dome, Healing Pillar, Rally
						$base_dragonfly_certs = array(836,846,850,856,860,864,1066,1068,1070,1072,1074,1076,1269,1270,1271,1272,1273,1274);
						//bio crossbow, creeping death, evacuate, kinetic shot, necrosis, necrotic poison
						$base_recluse_certs = array(874,878,884,888,890,898,1078,1080,1082,1084,1086,1088,1275,1276,1277,1278,1279,1280);
						//Charge!, Dreadfield, Gravity Field Grenade, Sundering Wave, Heavy Laser MG, Personal Shield
						$base_rhino_certs = array(912,916,920,924,926,930,1114,1116,1118,1120,1122,1124,1293,1294,1295,1296,1297,1298,1393,1394,1395);
						//Shield Wall, Teleport Shot, Thunderdome, Tremors, Imminent Threat, Heavy Plasma MG
						$base_mammoth_certs = array(892,896,900,902,906,908,938,1102,1104,1106,1108,1110,1112,1287,1288,1289,1290,1291,1292,1357,1358);
						//Boomerang Shot, Bulwark, Electrical Storm, Overclocking Station, Shock Rail, Fail-Safe
						$base_arsenal_certs = array(1399,1400,1403,1406,1407,1408,1409,1410,1411,1416,1417,1418,1421,1422,1423,1426,1427,1428,1436,1437,1438);
						//EMP, Particle Beam, Rocket Jump, Shoulder Rockets, Combat shotgun, light machine gun
						$base_electron_certs = array(964,966,968,970,972,974,1150,1152,1156,1158,1162,1164,1311,1312,1313,1314,1315,1316);
						//Energy Wall, Fortify, Multi Turret, Sentinel Pod, Tesla Rifle, Overseer
						$base_bastion_certs = array(952,954,956,958,960,962,1138,1140,1142,1144,1146,1148,1305,1306,1307,1308,1309,1310);
						//Eruption Rounds, Execute Shot, Remote Explosive, Smoke Screen, Sniper Rifle, Ambush
						$base_nighthawk_certs = array(870,876,882,886,894,914,980,1178,1180,1182,1184,1186,1188,1323,1324,1325,1326,1327,1328);
						//Overload, Power Field, SIN Scrambler, Teleport Beacon, Conduit, Charge Rifle
						$base_raptor_certs = array(840,854,858,868,976,978,1190,1192,1194,1196,1198,1200,1329,1330,1331,1332,1333,1334);

						$base_shared_items = array();
						$base_assault_items = array();
						$base_biotech_items = array();
						$base_dreadnaught_items = array();
						$base_engineer_items = array();
						$base_recon_items = array();
						
						$base_firecat_items = array();
						$base_tigerclaw_items = array();
						$base_dragonfly_items = array();
						$base_recluse_items = array();
						$base_rhino_items = array();
						$base_mammoth_items = array();
						$base_arsenal_items = array();
						$base_electron_items = array();
						$base_bastion_items = array();
						$base_nighthawk_items = array();
						$base_raptor_items = array();

						for($i=0;$i<count($cert_info);$i++){
							//base items
							if(in_array($cert_info[$i]->id,$base_shared_certs)){
								$base_shared_items[] = $cert_info[$i]->name;
							};
							if($unlocked_base_assault){
								if(in_array($cert_info[$i]->id,$base_assault_certs)){
									$base_assault_items[] = $cert_info[$i]->name;
								};
							};
							if($unlocked_base_biotech){
								if(in_array($cert_info[$i]->id,$base_biotech_certs)){
									$base_biotech_items[] = $cert_info[$i]->name;
								};
							};
							if($unlocked_base_dreadnaught){
								if(in_array($cert_info[$i]->id,$base_dreadnaught_certs)){
									$base_dreadnaught_items[] = $cert_info[$i]->name;
								};
							};
							if($unlocked_base_engineer){
								if(in_array($cert_info[$i]->id,$base_engineer_certs)){
									$base_engineer_items[] = $cert_info[$i]->name;
								};
							};
							if($unlocked_base_recon){
								if(in_array($cert_info[$i]->id,$base_recon_certs)){
									$base_recon_items[] = $cert_info[$i]->name;
								};
							};
							//ASSAULT ADV
							if($unlocked_firecat){
								if(in_array($cert_info[$i]->id,$base_firecat_certs)){
									$base_firecat_items[] = $cert_info[$i]->name;
								};
							};
							if($unlocked_tigerclaw){
								if(in_array($cert_info[$i]->id,$base_tigerclaw_certs)){
									$base_tigerclaw_items[] = $cert_info[$i]->name;
								};
							};
							//BIOTECH ADV
							if($unlocked_dragonfly){
								if(in_array($cert_info[$i]->id,$base_dragonfly_certs)){
									$base_dragonfly_items[] = $cert_info[$i]->name;
								};
							};
							if($unlocked_recluse){
								if(in_array($cert_info[$i]->id,$base_recluse_certs)){
									$base_recluse_items[] = $cert_info[$i]->name;
								};
							};
							//DREADNAUGHT ADV
							if($unlocked_rhino){
								if(in_array($cert_info[$i]->id,$base_rhino_certs)){
									$base_rhino_items[] = $cert_info[$i]->name;
								};
							};
							if($unlocked_mammoth){
								if(in_array($cert_info[$i]->id,$base_mammoth_certs)){
									$base_mammoth_items[] = $cert_info[$i]->name;
								};
							};
							if($unlocked_arsenal){
								if(in_array($cert_info[$i]->id,$base_arsenal_certs)){
									$base_arsenal_items[] = $cert_info[$i]->name;
								};
							};
							//ENGINEER ADV
							if($unlocked_electron){
								if(in_array($cert_info[$i]->id,$base_electron_certs)){
									$base_electron_items[] = $cert_info[$i]->name;
								};
							};
							if($unlocked_bastion){
								if(in_array($cert_info[$i]->id,$base_bastion_certs)){
									$base_bastion_items[] = $cert_info[$i]->name;
								};
							};
							//RECON ADV
							if($unlocked_nighthawk){
								if(in_array($cert_info[$i]->id,$base_nighthawk_certs)){
									$base_nighthawk_items[] = $cert_info[$i]->name;
								};
							};
							if($unlocked_raptor){
								if(in_array($cert_info[$i]->id,$base_raptor_certs)){
									$base_raptor_items[] = $cert_info[$i]->name;
								};
							};
						};
						asort($base_shared_items);
						asort($base_assault_items);
						asort($base_biotech_items);
						asort($base_dreadnaught_items);
						asort($base_engineer_items);
						asort($base_recon_items);
						asort($base_firecat_items);
						asort($base_tigerclaw_items);
						asort($base_dragonfly_items);
						asort($base_recluse_items);
						asort($base_rhino_items);
						asort($base_mammoth_items);
						asort($base_arsenal_items);
						asort($base_electron_items);
						asort($base_bastion_items);
						asort($base_nighthawk_items);
						asort($base_raptor_items);
						
						//cache it, cache it good
						$cached_can_craft = array();
						$cached_can_craft['base_shared_items'] = $base_shared_items;
						$cached_can_craft['base_assault_items'] = $base_assault_items;
						$cached_can_craft['base_biotech_items'] = $base_biotech_items;
						$cached_can_craft['base_dreadnaught_items'] = $base_dreadnaught_items;
						$cached_can_craft['base_engineer_items'] = $base_engineer_items;
						$cached_can_craft['base_recon_items'] = $base_recon_items;
						$cached_can_craft['base_firecat_items'] = $base_firecat_items;
						$cached_can_craft['base_tigerclaw_items'] = $base_tigerclaw_items;
						$cached_can_craft['base_dragonfly_items'] = $base_dragonfly_items;
						$cached_can_craft['base_recluse_items'] = $base_recluse_items;
						$cached_can_craft['base_rhino_items'] = $base_rhino_items;
						$cached_can_craft['base_mammoth_items'] = $base_mammoth_items;
						$cached_can_craft['base_electron_items'] = $base_electron_items;
						$cached_can_craft['base_bastion_items'] = $base_bastion_items;
						$cached_can_craft['base_nighthawk_items'] = $base_nighthawk_items;
						$cached_can_craft['base_raptor_items'] = $base_raptor_items;
						$cached_can_craft['base_arsenal_items'] = $base_arsenal_items;
						Cache::forever($cache_key_progress . "_can_craft",$cached_can_craft);
						Cache::forever($cache_key_progress . "_md5_cc",$progress_md5);
						//set progress to 1 for the view control params.
						$progress = 1;
					}else{
						//this player does not have progress->unlocks so we can't even check for craftables; assume null.
						$progress = null;
					};
				}else{
					//load it all from cache
					$cached_can_craft = Cache::Get($cache_key_progress . "_can_craft");
					//set all those variables
					$base_shared_items = $cached_can_craft['base_shared_items'];
					$base_assault_items = $cached_can_craft['base_assault_items'];
					$base_biotech_items = $cached_can_craft['base_biotech_items'];
					$base_dreadnaught_items = $cached_can_craft['base_dreadnaught_items'];
					$base_engineer_items = $cached_can_craft['base_engineer_items'];
					$base_recon_items = $cached_can_craft['base_recon_items'];
					$base_firecat_items = $cached_can_craft['base_firecat_items'];
					$base_tigerclaw_items = $cached_can_craft['base_tigerclaw_items'];
					$base_dragonfly_items = $cached_can_craft['base_dragonfly_items'];
					$base_recluse_items = $cached_can_craft['base_recluse_items'];
					$base_rhino_items = $cached_can_craft['base_rhino_items'];
					$base_mammoth_items = $cached_can_craft['base_mammoth_items'];
					$base_arsenal_items = $cached_can_craft['base_arsenal_items'];
					$base_electron_items = $cached_can_craft['base_electron_items'];
					$base_bastion_items = $cached_can_craft['base_bastion_items'];
					$base_nighthawk_items = $cached_can_craft['base_nighthawk_items'];
					$base_raptor_items = $cached_can_craft['base_raptor_items'];
				};
			};
		};
		
		
		if($web_prefs['show_workbench']){
			//Workbench Stuff
			$workbench_info = array();
			if( Cache::has($player->db_id . '_printer') ) {
				$workbench_info = Cache::get($player->db_id . '_printer');
			}else{
				$wbinfo = Printer::where('db_id','=',$player->db_id)->get();
				foreach ($wbinfo as $wb)
				{
					$workbench_info[] = $wb->original;
				}
				Cache::forever($player->db_id . '_printer', $workbench_info);
			}
			if(!empty($workbench_info)){
				$workbench_info_blueprints = array();
				for($i=0;$i<count($workbench_info);$i++){
					$workbench_info_blueprints[] = $workbench_info[$i]['blueprint_id'];
				};
				$workbench_results = Recipe::with(array('outputs'))->where_in('itemtypeid',$workbench_info_blueprints)->get(array('itemtypeid','description','name'));
				
				$workbench_output_ids = array();
				$workbench_output_names = array();
				//at this point we will always have something in the workbench so don't worry about null
				foreach($workbench_results as $workbench_result){
					if($workbench_result->relationships['outputs'] != null){
						//add the output itemtypeid to the list
						print_r($workbench_result->relationships['outputs']);
					}else{
						//add the blueprint_id as an itemtypeid because its a cert
						$workbench_output_ids[] = $workbench_result->attributes['itemtypeid'];
						if(strstr($workbench_result->attributes['description'],"Research")){
							$workbench_output_names[ $workbench_result->attributes['itemtypeid'] ] = $workbench_result->attributes['description'];
						}else{
							$workbench_output_names[ $workbench_result->attributes['itemtypeid'] ] = $workbench_result->attributes['name'];
						};
					};
				};
				unset($workbench_results);
				
				//get the icons
				$wb_icons = hWebIcon::where(
					function($query) use($workbench_output_ids){
						$query->where('version','=', Base_Controller::getVersionDate());
						$query->where_in('itemTypeId',$workbench_output_ids);
					}
				)->get(array('itemtypeid','asset_path'));
				foreach ($wb_icons as $wb)
				{
					$workbench_output_icons[ $wb->itemtypeid ] = $wb->asset_path;
				}
				unset($wb_icons);
			};
		};
		
		//ShipIt(TM)
		$response =  View::make('player.player_profile_store')
			->with('title', 'Player Store for ' . $playerName)
			->with(compact('web_prefs'))
			->with(compact('player'))
			->with(compact('army'));
			
		if(!empty($progress)){
			$response->progress = $progress;
			$response->base_shared_items = $base_shared_items;
			$response->base_assault_items = $base_assault_items;
			$response->base_biotech_items = $base_biotech_items;
			$response->base_dreadnaught_items = $base_dreadnaught_items;
			$response->base_engineer_items = $base_engineer_items;
			$response->base_recon_items = $base_recon_items;
			$response->base_firecat_items = $base_firecat_items;
			$response->base_tigerclaw_items = $base_tigerclaw_items;
			$response->base_dragonfly_items = $base_dragonfly_items;
			$response->base_recluse_items = $base_recluse_items;
			$response->base_rhino_items = $base_rhino_items;
			$response->base_mammoth_items = $base_mammoth_items;
			$response->base_arsenal_items = $base_arsenal_items;
			$response->base_electron_items = $base_electron_items;
			$response->base_bastion_items = $base_bastion_items;
			$response->base_nighthawk_items = $base_nighthawk_items;
			$response->base_raptor_items = $base_raptor_items;
		};
		if(!empty($market_listings)){
			$response->market_listings = $market_listings;
			if(!empty($item_stats_lookup)){
				$response->stats = $item_stats_lookup;
			};
		};
		if(!empty($workbench_info)){
			$response->workbench_info = $workbench_info;
			$response->workbench_output_icons = $workbench_output_icons;
			$response->workbench_output_names = $workbench_output_names;
		};
		return $response;
	}
}
?>