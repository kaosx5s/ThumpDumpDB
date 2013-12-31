<?php

class PVPStats_Parser_Controller extends Base_Controller {

    public $restful = true;


    /*
     *  "I'm gonna shut you down"
     *  -Said no function ever.
     *
     */
    public function get_scan_pvpstats()
    {
        register_shutdown_function(function(){
            Controller::call('PVPStats_Parser@scan_pvpstats_files');
        });
        return Response::json(array('TDDB:'=>'PVPStatstime: processing'));
    }


    /*
     *  Scan some market files
     */
    public function get_scan_pvpstats_files()
    {
        set_time_limit(240);
        $base_path = path('public');
        $base_path = str_replace('\\', '/', $base_path);
        $stats_dir =  $base_path . 'addon/storage/dumps/pvpstats/';

        //Find all eligible pvpstats files, delegate based on file name
        $stats_files = glob($stats_dir.'*_*.json');

        if( count($stats_files) < 1 ) {
            return;// Response::json(array('TDDB:'=>'pvpstatstime, no files'));
        }

        //Cache::forever('pvpstats_listings_last_updated', date('Y-m-d H:i:s'));

        foreach ($stats_files as $pvpstats_file)
        {

            //ALL FILES ARE THE SAME FORMAT, SO WE'LL KEY OFF THEIR UNIQUE DATAS
            try {
                //try to load file
                if( !$fh = file_get_contents($pvpstats_file, 'r') ) {
                    throw new Exception("Could not open file: ");
                }

                if( !$json = json_decode($fh) ) {
                    throw new Exception("Could not decode json: ");
                }

                if( !isset( $json->start_index) || 
                    !isset( $json->url) ||
                    !isset( $json->per_page) ||
                    !isset( $json->title) ||
                    !isset( $json->status) ||
                    !isset( $json->total_count) ||
                    !isset( $json->page) ||
                    !isset( $json->results) 
                ){
                    throw new Exception("Json did not contain expected keys: ");
                }

                if( count($json->results) < 1 ) {
                    throw new Exception("Results contained no entries: ");
                }

                //check that the title is one of the allowed types
                $allowed_titles = array(
                    'teamdeathmatch',
                    'harvester',
                    'jetball',
                    'sabotage'
                );

                $title_trimmed = strtolower( str_replace( array('Leaderboard', ' ', '(Preseason)'), '', $json->title) );

                if( !in_array($title_trimmed, $allowed_titles) ) {
                    throw new Exception("Title contained key other than expected, (". $title_trimmed ."): ");
                }


                //init some vars
                $new_datas = array();
                $now = date('Y-m-d H:i:s');

                //alright, let's review some results
                foreach ($json->results as $result)
                {
                    //general check if numeric
                    if( !is_numeric($result->games_played) ||
                        !is_numeric($result->damage_healed) ||
                        !is_numeric($result->guid) ||
                        !is_numeric($result->net_damage) ||
                        !is_numeric($result->kills) ||
                        !is_numeric($result->kda_ratio) ||
                        !is_numeric($result->rating) ||
                        !is_numeric($result->total_pages) ||
                        !is_numeric($result->elo_leaderboard_id) ||
                        !is_numeric($result->losses) ||
                        !is_numeric($result->deaths) ||
                        !is_numeric($result->wins) ||
                        !is_numeric($result->revives) ||
                        !is_numeric($result->id) ||
                        !is_numeric($result->damage_dealt) ||
                        !is_numeric($result->assists) ||
                        !is_numeric($result->passes) ||
                        !is_numeric($result->projectile_hits) ||
                        !is_numeric($result->executes) ||
                        !is_numeric($result->damage_taken) ||
                        !is_numeric($result->points_scored) ||
                        !is_numeric($result->projectile_misses) ||
                        !is_numeric($result->rank) ||
                        !is_numeric( str_replace('%', '', $result->rank) ) 
                    ) {
                        throw new Exception("Expected numeric key was not numeric: ");
                    }


                    if( strlen($result->character_name) > 27 ) {
                        Log::warn("Character name was longer than expected [27]: (".serialize($result->character_name).") ". basename($pvpstats_file_name));
                    }

                    $new_datas[] = array(
                        'guid'              => $result->guid,
                        'games_played'      => $result->games_played,
                        'damage_healed'     => $result->damage_healed,
                        'net_damage'        => $result->net_damage,
                        'win_loss_ratio'    => str_replace('%', '', $result->win_loss_ratio),
                        'kills'             => $result->kills,
                        'kda_ratio'         => $result->kda_ratio,
                        'character_name'    => $result->character_name,
                        'rating'            => $result->rating,
                        'total_pages'       => $result->total_pages,
                        'elo_leaderboard_id'=> $result->elo_leaderboard_id,
                        'losses'            => $result->losses,
                        'deaths'            => $result->deaths,
                        'wins'              => $result->wins,
                        'revives'           => $result->revives,
                        'ff_id'             => $result->id,
                        'damage_dealt'      => $result->damage_dealt,
                        'assists'           => $result->assists,
                        'ff_created_at'     => str_replace(array('T','+00:00'), array(' ', ''), $result->created_at),
                        'ff_updated_at'     => str_replace(array('T','+00:00'), array(' ', ''), $result->updated_at),
                        'passes'            => $result->passes,
                        'projectile_hits'   => $result->projectile_hits,
                        'executes'          => $result->executes,
                        'damage_taken'      => $result->damage_taken,
                        'points_scored'     => $result->points_scored,
                        'projectile_misses' => $result->projectile_misses,
                        'rank'              => $result->rank,
                        'added_on'          => $now,
                        'created_at'        => $now,
                        'updated_at'        => $now
                    );


                    unset($result);
                }


                switch ($title_trimmed) {
                    case 'teamdeathmatch':
                        DB::table('pvpratingstdms')->insert($new_datas);
                        break;
                    case 'harvester':
                        DB::table('pvpratingsharvesters')->insert($new_datas);
                        break;
                    case 'jetball':
                        DB::table('pvpratingsjetballs')->insert($new_datas);
                        break;
                    case 'sabotage':
                        DB::table('pvpratingssabotages')->insert($new_datas);
                        break;
                    default:
                        break;
                }


            }catch (Exception $e) {
                Log::warn($e->getMessage() . basename($pvpstats_file));
            }//caught

            unlink($pvpstats_file);
        }

    }


    public function post_dumps()
    {
        if(isset($_GET['fn'])){
            $fn = $_GET['fn'];
        }else{
            $fn = false;
        }

        $allowed_fns = array(
            'pvp_tdm',
            'pvp_harvester',
            'pvp_jetball',
            'pvp_sabotage'
        );

        $suffix = '_dump';
        if( $fn && in_array($fn, $allowed_fns)){
            if(is_string($fn)) {
                $suffix = $fn;
            }else{
                Log::info('PVPStats dumper sent unexpected filename: ' . $fn);
            }
        }else{
            Log::info('FFMini sent unexpected pvpstats category: ' . $fn);
        }
        $json_raw = @file_get_contents('php://input');

        $server_date = date("Y_m_d_H_i_s");
        file_put_contents('public/addon/storage/dumps/pvpstats/'.$suffix.'.json', $json_raw);
            return Response::json(array('TDDB:'=>'PVPStats Data Received.'));
    }

}
