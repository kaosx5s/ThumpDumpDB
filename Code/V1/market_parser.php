<?php

class Market_Parser_Controller extends Base_Controller {

    public $restful = true;


    /*
     *  "I'm gonna shut you down"
     *  -Said no function ever.
     *
     */
    public function get_scan_mkt()
    {
        register_shutdown_function(function(){
            Controller::call('Market_Parser@scan_market_files');
        });
        return Response::json(array('TDDB:'=>'Markettime: processing'));
    }


    /*
     *  Scan some market files
     */
    public function get_scan_market_files()
    {
        set_time_limit(240);
        $base_path = path('public');
        $base_path = str_replace('\\', '/', $base_path);
        $mkt_dir =  $base_path . 'addon/storage/dumps/market/';

        //Find all eligible market files, delegate based on file name
        $market_files = glob($mkt_dir.'*_*.json');

        if( count($market_files) < 1 ) {
            return;// Response::json(array('TDDB:'=>'Markettime, no files'));
        }

        Cache::forever('market_listings_last_updated', date('Y-m-d H:i:s'));

        foreach ($market_files as $market_file)
        {
            //get _ "suffix" .json

            $market_file_basename = basename($market_file);
            $market_file_name = str_replace('.json', '', $market_file_basename);
            $market_file_category = explode('_', $market_file_name)[1];

            switch ($market_file_category) {
                case 'Resource':
                    DB::query("UPDATE `marketlistings` SET `active` = ? WHERE `category` = ?", array(0,'Resource'));
                    $resource   = new MarketResource($market_file);
                    break;
                case 'Jumpjets':
                    DB::query("UPDATE `marketlistings` SET `active` = ? WHERE `category` = ?", array(0,'Jumpjet'));
                    $jumpjet    = new MarketJumpjet($market_file);
                    break;
                case 'Plating':
                    DB::query("UPDATE `marketlistings` SET `active` = ? WHERE `category` = ?", array(0,'Plating'));
                    $plating    = new MarketPlating($market_file);
                    break;
                case 'Servos':
                    DB::query("UPDATE `marketlistings` SET `active` = ? WHERE `category` = ?", array(0,'Servo'));
                    $servos     = new MarketServo($market_file);
                    break;
                case 'Weapon':
                    DB::query("UPDATE `marketlistings` SET `active` = ? WHERE `category` = ?", array(0,'Weapon'));
                    $weapon     = new MarketWeapon($market_file);
                    break;
                case 'AbilityModule':
                    DB::query("UPDATE `marketlistings` SET `active` = ? WHERE `category` = ?", array(0,'AbilityModule'));
                    $abilitymodules = new MarketAbilityModule($market_file);
                    break;
                case 'ThumperBeacon':
                    //DB::query("UPDATE `marketlistings` SET `active` = ? WHERE `category` = ?", array(0,'ThumperBeacon'));
                    $thumperbeacons = new MarketThumperBeacon($market_file);
                    break;
                case 'Consumable':
                    DB::query("UPDATE `marketlistings` SET `active` = ? WHERE `category` = ?", array(0,'Consumable'));
                    $consumable = new MarketConsumable($market_file);
                    break;
                case 'Currency':
                    //DB::query("UPDATE `marketlistings` SET `active` = ? WHERE `category` = ?", array(0,'Currency'));
                    $currency = new MarketCurrency($market_file);
                    break;
                case 'CraftingComponents':
                    DB::query("UPDATE `marketlistings` SET `active` = ? WHERE `category` = ?", array(0,'CraftingComponent'));
                    $craftingcomponents = new MarketCraftingComponent($market_file);
                    break;
                default:
                    break;
            }
            unlink($market_file);
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
            '15_Resource',
            '16_Jumpjets',
            '17_Plating',
            '18_Servos',
            '58_Weapon',
            '61_AbilityModule',
            '80_ThumperBeacon',
            '82_Consumable',
            '207_Currency',
            '3321_CraftingComponents'
        );

        $suffix = '_dump';
        if( $fn && in_array($fn, $allowed_fns)){
            if(is_string($fn)) {
                $suffix = $fn;
            }else{
                Log::info('Market dumper sent unexpected filename: ' . $fn);
            }
        }else{
            Log::info('FFMini sent unexpected market category: ' . $fn);
        }

        $suffix = $fn;
        
        $json_raw = @file_get_contents('php://input');

        $server_date = date("Y_m_d_H_i_s");
        file_put_contents('public/addon/storage/dumps/market/'.$suffix.'.json', $json_raw);
            return Response::json(array('TDDB:'=>'Market Data Received.'));
    }

}
