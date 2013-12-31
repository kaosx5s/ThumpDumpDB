<?php
class Signature_Generator_Controller extends Base_Controller{

    public $restful = true;


    public function chassisIdToSimpleName($chassis_id)
    {

		//Look at all those frames, those are nice frames.
		//Switch logic for background selection

		/*
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
		*/
		switch($chassis_id){
			//ASSAULT FRAMES
			case 76164:
				$background = "Assault";
				break;
			case 76133:
				$background = "Firecat";
				break;
			case 76132:
				$background = "Tigerclaw";
				break;
			//BIO FRAMES
			case 75774:
				$background = "Biotech";
				break;
			case 76336:
				$background = "Recluse";
				break;
			case 76335:
				$background = "Dragonfly";
				break;
			//DREAD FRAMES
			case 75772:
				$background = "Dreadnaught";
				break;
			case 76331:
				$background = "Mammoth";
				break;
			case 76332:
				$background = "Rhino";
				break;
			//ENGINEER FRAMES
			case 75775:
				$background = "Engineer";
				break;
			case 76338:
				$background = "Bastion";
				break;
			case 76337:
				$background = "Electron";
				break;
			//RECON FRAMES
			case 75773:
				$background = "Recon";
				break;
			case 76333:
				$background = "Nighthawk";
				break;
			case 76334:
				$background = "Raptor";
				break;
			case 82360:
				$background = "Arsenal";
				break;
			default:
				$background = false;
				break;
		};

		return $background;
    }

    public function simpleNameToChassisId($name)
    {
		/*
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
		*/
		switch($name){
			//ASSAULT FRAMES
			case "assault":
				$cid = 76164;
				break;
			case "firecat":
				$cid = 76133;
				break;
			case "tigerclaw":
				$cid = 76132;
				break;
			//BIO FRAMES
			case "biotech":
				$cid = 75774;
				break;
			case "recluse":
				$cid = 76336;
				break;
			case "dragonfly":
				$cid = 76335;
				break;
			//DREAD FRAMES
			case "dreadnaught":
				$cid = 75772;
				break;
			case "mammoth":
				$cid = 76331;
				break;
			case "rhino":
				$cid = 76332;
				break;
			//ENGINEER FRAMES
			case "engineer":
				$cid = 75775;
				break;
			case "bastion":
				$cid = 76338;
				break;
			case "electron":
				$cid = 76337;
				break;
			//RECON FRAMES
			case "recon":
				$cid = 75773;
				break;
			case "nighthawk":
				$cid = 76333;
				break;
			case "raptor":
				$cid = 76334;
				break;
			case "arsenal":
				$cid = 82360;
				break;
			default:
				$cid = false;
				break;
		};

		return $cid;
    }

	//Alpha layer hack function
	public function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
	{
		if(!isset($pct)){
			return false;
		}
		$pct/=100;
		$w=imagesx($src_im);
		$h=imagesy($src_im);
		imagealphablending($src_im, false);
		$minalpha=127;
		for($x=0;$x<$w;$x++)
			for($y=0;$y<$h;$y++){
				$alpha=(imagecolorat($src_im, $x, $y)>>24)&0xFF;
				if($alpha<$minalpha){
					$minalpha=$alpha;
				}
			}
			for($x=0;$x<$w;$x++){
				for($y=0;$y<$h;$y++){
					$colorxy=imagecolorat($src_im, $x, $y);
					$alpha=($colorxy>>24)&0xFF;
					if($minalpha!==127){
						$alpha=127+127*$pct*($alpha-127)/(127-$minalpha);
					}else{
						$alpha+=127*$pct;
					}
					$alphacolorxy=imagecolorallocatealpha($src_im, ($colorxy>>16)&0xFF, ($colorxy>>8)&0xFF, $colorxy&0xFF, $alpha);
					if(!imagesetpixel($src_im, $x, $y, $alphacolorxy)){
						return false;
					}
				}
			}
		imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
	}

	//Helper function to center strings inside of a given pixel range
	public function Image_Center_String( &$img, $font, $xMin, $xMax, $y, $str, $col )
	{
		$textWidth = imagefontwidth( $font ) * strlen( $str );
		$xLoc = ( $xMax - $xMin - $textWidth ) / 2 + $xMin + $font;
		imagestring( $img, $font, $xLoc, $y, $str, $col );
	}

//---------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------

    public function get_siggen($name = NULL, $frame_request = NULL){
        //searching by name, null name = no buenos
        if($name == NULL || $frame_request == NULL) {
            return Response::error('404');
        }else{
            $input_name = urldecode($name);
        }

		//check to make sure in valid frame names
		//don't forget "barsig" for current
		$valid_frame_requests = array('sig','arsenal','assault','firecat','tigerclaw','biotech','recluse','dragonfly','dreadnaught','rhino','mammoth','engineer','electron','bastion','recon','raptor','nighthawk');
		if( !in_array($frame_request, $valid_frame_requests) ) {
			return Response::error('404');
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
		if(isset($player->armytag)){
			$army = $player->armytag;
		}else{
			$army = null;
		};

        //escape for output in title
        $playerName = htmlentities($player->name);

		//Get the most recent progress and find out the current frame being played and its total XP
		$progress = Progress::where('db_id','=',$player->db_id)->order_by('updated_at','DESC')->first();
        if($progress){
            $progress = unserialize($progress->entry);
			$cache_key_progress = $player->db_id . "_progress";
			if(isset($progress->xp) && isset($progress->chassis_id)){
				
				//if we're barsig only, current frame, if not check entry for other frame exists
				if($frame_request == 'sig') {
					$current_frame = $progress->chassis_id;
				}else{

					$frame_request_exists = self::simpleNameToChassisId($frame_request);
					if( !$frame_request_exists ) {
						return Response::error('404');
					}else{
						$current_frame = $frame_request_exists;
					}
				}
				
				$frame_exists_in_progress = false;
				for($i=0;$i<count($progress->xp);$i++){
					if($progress->xp[$i]->sdb_id == $current_frame){
						$lifetime_xp = number_format($progress->xp[$i]->lifetime_xp);
						$frame_exists_in_progress = true;
					};
				};
				if( !$frame_exists_in_progress ) {
					return Response::error('404');
				}

			}else{
				//we need this data, abort!
				return Response::error('404');
			};
		}else{
			//We kind of need progress in order to display anything so if the user doesn't have a progress than abort everything.
			return Response::error('404');
		};

		//Cache checking ain't easy
		$cache_key = $playerName . "_sig_" . $frame_request;
		if(!Cache::has($player->db_id .'_sig_' . $frame_request)){
			//THEME DEFAULT SETTING
			$theme = "Default";

			$background = self::chassisIdToSimpleName($current_frame);
			if( !$background ) {
				return Response::error('404');
			}

			$background = imagecreatefrompng('public/img/sig_set/' . $theme . '/' . $background . '.png');
			//START DEFAULT THEME
			if($theme == "Default"){
				//Hey man, lets make this shit look good.
				imageantialias($background, true);
				//Save its alpha channel
				imagesavealpha($background, true);
				$overlay = imagecreatefrompng('public/img/sig_set/Default/Overlay.png');

				//Before we set the overlay, lets write some things on our image
				$text_color = imagecolorallocate($background,255,255,255);
				$stroke_color = imagecolorallocate($background, 0, 0, 0);
				//EuroStyle Condensed H16 / W10
				$font = imageloadfont('public/img/sig_set/Default/tddb_sig_font.gdf');
				//EuroStyle Condensed H14 / W10
				$small_font = imageloadfont('public/img/sig_set/Default/tddb_sig_font_14px.gdf');
				//background_image,font,x-space,y-space,string,color
				if($army != ""){
					//18 character limit for trying to position this beast using $font
					if((strlen($army)+strlen($playerName)+3) > 18){
						//Use a smaller font.
						$input_string = $army . $playerName;
						//Draw the shadow
						self::Image_Center_String($background,$small_font,-11,34,10,$input_string,$stroke_color);
						self::Image_Center_String($background,$small_font,-9,36,10,$input_string,$stroke_color);
						self::Image_Center_String($background,$small_font,-10,35,9,$input_string,$stroke_color);
						self::Image_Center_String($background,$small_font,-10,35,11,$input_string,$stroke_color);
						//Draw the string
						self::Image_Center_String($background,$small_font,-10,35,10,$input_string,$text_color);
					}else{
						$input_string = $army . $playerName;
						//Draw the shadow
						self::Image_Center_String($background,$font,-11,34,10,$input_string,$stroke_color);
						self::Image_Center_String($background,$font,-9,36,10,$input_string,$stroke_color);
						self::Image_Center_String($background,$font,-10,35,9,$input_string,$stroke_color);
						self::Image_Center_String($background,$font,-10,35,11,$input_string,$stroke_color);
						//Draw the string
						self::Image_Center_String($background,$font,-10,35,10,$input_string,$text_color);
					};
				}else{
					//No army name found
					$input_string = $playerName;
					//Draw the shadow
					self::Image_Center_String($background,$font,-11,34,10,$input_string,$stroke_color);
					self::Image_Center_String($background,$font,-9,36,10,$input_string,$stroke_color);
					self::Image_Center_String($background,$font,-10,35,9,$input_string,$stroke_color);
					self::Image_Center_String($background,$font,-10,35,11,$input_string,$stroke_color);

					//Draw the string
					self::Image_Center_String($background,$font,-10,35,10,$input_string,$text_color);
				};
				$input_string = $lifetime_xp . " XP";
				//Draw the shadow
				self::Image_Center_String($background,$font,191,201,10,$input_string,$stroke_color);
				self::Image_Center_String($background,$font,189,198,10,$input_string,$stroke_color);
				self::Image_Center_String($background,$font,190,200,9,$input_string,$stroke_color);
				self::Image_Center_String($background,$font,190,200,11,$input_string,$stroke_color);
				//Draw the string
				self::Image_Center_String($background,$font,190,200,10,$input_string,$text_color);

				//Merge that overlay!
				self::imagecopymerge_alpha($background, $overlay, 0, 0, 0, 0, imagesx($background), imagesy($overlay),65);
			};
			//END DEFAULT THEME
			imagesavealpha($background, true);

			//So we cant actually cache an image, but we can create a cache and use it as a timer to recreate the stored image
			Cache::put($player->db_id .'_sig_' . $frame_request, '1', 5);
			imagepng($background,'storage/cache/sigs/' . $playerName . '_sig_'. $frame_request . '.png',9);
			imagedestroy($background);
		};
		$image_path = 'storage/cache/sigs/' . $cache_key . '.png';
		$name = $cache_key . '.png';

		//Generate the response
		Response::make('', 200, array(
						'Content-Type'              => 'image/png',
						'Content-Transfer-Encoding' => 'binary',
						'Content-Disposition' 		=> 'inline',
						'Expires'                   => 0,
						'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0',
						'Pragma'                    => 'public',
						'Content-Length'            => filesize($image_path)
						)
		)->send_headers();
		readfile($image_path);
		exit;
	}


	public function get_barsiggen($name = NULL, $beer = NULL, $frame_request = NULL)
	{

		//Your arguments must be recognized in Fort Kickass
		if( is_null($beer) || is_null($name) || is_null($frame_request)) {
			return Response::error('404');
		}

		//Light beer vs. Dark beer, the showdown -  Thanks Van.
		if( !in_array($beer, array('light','dark')) ) {
			return Response::error('404');
		}

		//check to make sure in valid frame names
		//don't forget "barsig" for current
		$valid_frame_requests = array('barsig','arsenal','assault','firecat','tigerclaw','biotech','recluse','dragonfly','dreadnaught','rhino','mammoth','engineer','electron','bastion','recon','raptor','nighthawk');
		if( !in_array($frame_request, $valid_frame_requests) ) {
			return Response::error('404');
		}

		//Alright, now that I'm nice and limber.  Let's see if you're even people.
		$input_name = urldecode($name);
        $player = Player::where('name','=',$input_name)
            ->order_by('created_at', 'DESC')
            ->first();

        //If you're not even people (like Xtops), go away
        if( is_null($player) ) {
        	return Response::error('404');
        }


        //STOP.  Hammertime.  Get that data.  Set that data straight.
    	$output_name 		= "";
        $output_xp 			= "";
    	$output_class		= "";
        $output_weapon 		= "";
        	$current_frame  = "";

        //Let's set the player name with army tag if it exists
    	$playerName = htmlentities($player->name);
        if( isset($player->armytag) && strlen($player->armytag) > 1 ) {
        	$output_name = htmlentities( $player->armytag . ' ' . $player->name );
        }else{
        	$output_name = htmlentities( $player->name );
        }


        //Alright, now we're making progress!  (get it?)
        //Naw, let's just copy X5s
		$cache_key = $playerName . "_" . $beer . "_sig_" . $frame_request;
		if(!Cache::has($player->db_id . '_' . $beer . '_sig_' . $frame_request)) {

			$progress = Progress::where('db_id','=',$player->db_id)->order_by('updated_at','DESC')->first();
	        if($progress){
	            $progress = unserialize($progress->entry);
				$cache_key_progress = $player->db_id . "_progress";
				if(isset($progress->xp) && isset($progress->chassis_id)){

					//if we're barsig only, current frame, if not check entry for other frame exists
					if($frame_request == 'barsig') {
						$current_frame = $progress->chassis_id;
					}else{

						$frame_request_exists = self::simpleNameToChassisId($frame_request);
						if( !$frame_request_exists ) {
							return Response::error('404');
						}else{
							$current_frame = $frame_request_exists;
						}
					}

					foreach($progress->xp as $chassy){
						if($chassy->sdb_id == $current_frame){
							$output_xp = $chassy->lifetime_xp;
							break;
						};
					};
				}else{
					//we need this data, abort!
					return Response::error('404');
				};
			}else{
				//We kind of need progress in order to display anything so if the user doesn't have a progress than abort everything.
				return Response::error('404');
			};

			$output_xp = number_format($output_xp / 1000000, 2) . 'M XP';

			//Classy.  But if not, gtfo
			$output_class = strtolower(self::chassisIdToSimpleName($current_frame));
			if( !$output_class ) {
				Log::info('no class: ' . $current_frame);
				return Response::error('404');
			}


			//WEAPON ICONS, ASSEMBLE
			switch($output_class) {
				case 'arsenal':
				case 'assault':
				case 'firecat':
				case 'tigerclaw':
					$output_weapon = 'assault';
					break;

				case 'engineer':
				case 'bastion':
				case 'electron':
					$output_weapon = 'engineer';
					break;

				case 'dreadnaught':
				case 'mammoth':
				case 'rhino':
					$output_weapon = 'dreadnaught';
					break;

				case 'biotech':
				case 'recluse':
				case 'dragonfly':
					$output_weapon = 'biotech';
					break;

				case 'recon':
				case 'raptor':
				case 'nighthawk':
					$output_weapon = 'recon';
					break;

				default:
					$output_weapon = 'assault';
					break;
			}

			// Copy and merge those sexy sexy images
			$background = imagecreatefrompng('public/img/sig_set/Bar/base-' . $beer . '.png');
			$overlay_weapon_icon = imagecreatefrompng('public/img/sig_set/Bar/class_weapons/' . $output_weapon . '.png');
			$overlay_class_icon = imagecreatefrompng('public/img/sig_set/Bar/class_icons/' . $output_class . '.png');
				imagesavealpha($background, true);
				self::imagecopymerge_alpha($background, $overlay_weapon_icon, 305, 2, 0, 0, imagesx($overlay_weapon_icon), imagesy($overlay_weapon_icon),100);
				imagedestroy($overlay_weapon_icon);
			imagesavealpha($background, true);
				self::imagecopymerge_alpha($background, $overlay_class_icon, 5, 2, 0, 0, imagesx($overlay_class_icon), imagesy($overlay_class_icon),100);
				imagedestroy($overlay_class_icon);
			imagesavealpha($background, true);
			imageantialias($background, true);

			//Don't worry text, we didn't forget about you!
			$text_color = ($beer == 'light') ? imagecolorallocate($background,0,0,0) : imagecolorallocate($background,255,255,255);
			imagestring($background, 2, 30, 3, $output_name, $text_color);
			imagestring($background, 1, 240, 6, $output_xp, $text_color);

			// Output and free from memory
			Cache::put($player->db_id . '_' . $beer . '_sig', '1', 5);
			imagepng($background,'storage/cache/sigs/' . $playerName . '_' . $beer . '_sig_' . $frame_request . '.png',9);
			imagedestroy($background);
		}//end cache
		$image_path = 'storage/cache/sigs/' . $playerName . '_' . $beer . '_sig_' . $frame_request . '.png';

		//Generate the response
		Response::make('', 200, array(
						'Content-Type'              => 'image/png',
						'Content-Transfer-Encoding' => 'binary',
						'Content-Disposition' 		=> 'inline',
						'Expires'                   => 0,
						'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0',
						'Pragma'                    => 'public',
						'Content-Length'            => filesize($image_path)
						)
		)->send_headers();
		readfile($image_path);
		exit;


	}//barsiggen

}
?>
