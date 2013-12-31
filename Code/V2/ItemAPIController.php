<?php
class ItemAPIController extends BaseController {

	private $class_name = "ItemAPIController";
	private $version = '2013-06-27 00:00:00';
	
	public function Quality_Control($quality){
		switch($quality){
			case 0:
				return "common";
				break;
			case 1000:
				return "legendary";
				break;
			case $quality >= 901:
				return "epic";
				break;
			case $quality >= 701:
				return "rare";
				break;
			case $quality >= 401:
				return "uncommon";
				break;
			default:
				return "common";
				break;
		};
	}
	
	public function Get_Item_Information($itemtypeid){
		$itemtypeid = urldecode($itemtypeid);
		if(!is_numeric($itemtypeid)){
			return Response::json(array('status' => 'error', 'error' => 'This is not a proper ItemTypeID.'));
		};
		if(strlen($itemtypeid) > 8){
			return Response::json(array('status' => 'error', 'error' => 'This is not a proper ItemTypeID.'));
		};
		//check the cache
		if(Cache::has($this->class_name . "_" . $itemtypeid)){
			//pull from cache
			$itemtypeid_info = Cache::get($this->class_name . "_" . $itemtypeid);
			return json_encode($itemtypeid_info);
		};
		$basic_info = hWebIcon::where(
			function($query) use($itemtypeid){
				$query->where('itemTypeId','=', $itemtypeid);
				$query->where('version','=', $this->version);
			}
		)->first(array(
			'asset_path',
			'abilitymodule_id',
			'backpack_id',
			'basic_id',
			'blueprint_id',
			'chassis_id',
			'consumable_id',
			'craftingcomponent_id',
			'craftingstation_id',
			'craftingsubcomponent_id',
			'framemodule_id',
			'palettemodule_id',
			'powerup_id',
			'resourceitem_id',
			'scopemodule_id',
			'weapon_id',
			'weaponmodule_id'));
			
		if(empty($basic_info)){
			return Response::json(array('status' => 'error', 'error' => 'ItemTypeID not found in db.'));
		};
		//break up the types
		$detailed_info = array();
		$constraint_info = array();
		$weapon_info = array();
		if($basic_info['abilitymodule_id'] != ""){
			$detailed_info = AbilityModule::where('id','=',$basic_info['abilitymodule_id'])->first(array('type','abilityId','craftingTypeId','subTypeId','description','level','name','powerLevel','rarity','durability'));
			$constraint_info = hConstraint::where(
				function($query) use($itemtypeid){
					$query->where('itemTypeId','=', $itemtypeid);
					$query->where('version','=', $this->version);
				}
			)->first(array('cpu','mass','power'));
		};
		if($basic_info['basic_id'] != ""){
			$detailed_info = Basic::where('id','=',$basic_info['basic_id'])->first(array('type','craftingTypeId','subTypeId','description','level','name','rarity'));
		};
		if($basic_info['blueprint_id'] != ""){
			//Note -> blueprint_id = recipe_id
		
		};
		if($basic_info['chassis_id'] != ""){
			//Note -> links to hattributes
			$detailed_info = Chassis::where('id','=',$basic_info['chassis_id'])->first(array('type','craftingTypeId','subTypeId','description','level','name','rarity','progression_item_id','progression_resource_id'));
			$chassis_info = hAttributes::where(
				function($query) use($itemtypeid){
					$query->where('itemTypeId','=', $itemtypeid);
					$query->where('version','=', $this->version);
				}
			)->get(array('description','display_name','value','stat_id'));
		};
		if($basic_info['consumable_id'] != ""){
			$detailed_info = Consumable::where('id','=',$basic_info['consumable_id'])->first(array('type','craftingTypeId','subTypeId','description','level','powerLevel','name','rarity'));
		};
		if($basic_info['craftingcomponent_id'] != ""){
			$detailed_info = CraftingComponent::where('id','=',$basic_info['craftingcomponent_id'])->first(array('type','craftingTypeId','subTypeId','description','level','name','rarity'));
		};
		if($basic_info['craftingsubcomponent_id'] != ""){
			$detailed_info = CraftingSubcomponent::where('id','=',$basic_info['craftingsubcomponent_id'])->first(array('type','craftingTypeId','subTypeId','description','level','name','rarity'));
		};
		if($basic_info['framemodule_id'] != ""){
			$detailed_info = FrameModule::where('id','=',$basic_info['framemodule_id'])->first(array('type','abilityId','craftingTypeId','subTypeId','description','level','name','powerLevel','rarity','durability'));
			$constraint_info = hConstraint::where(
				function($query) use($itemtypeid){
					$query->where('itemTypeId','=', $itemtypeid);
					$query->where('version','=', $this->version);
				}
			)->first(array('cpu','mass','power'));
		};
		if($basic_info['palletmodule_id'] != ""){
			$detailed_info = PaletteModule::where('id','=',$basic_info['palettemodule_id'])->first(array('type','craftingTypeId','subTypeId','description','level','name','rarity'));
		};
		if($basic_info['powerup_id'] != ""){
			$detailed_info = Powerup::where('id','=',$basic_info['powerup_id'])->first(array('type','craftingTypeId','subTypeId','description','level','name','rarity'));
		};
		if($basic_info['resourceitem_id'] != ""){
			$detailed_info = ResourceItem::where('id','=',$basic_info['resourceitem_id'])->first(array('type','craftingTypeId','subTypeId','description','level','name','rarity','resource_color'));
		};
		if($basic_info['weapon_id'] != ""){
			//weapons have hstats also!
			$detailed_info = Weapon::where('id','=',$basic_info['weapon_id'])->first(array('type','craftingTypeId','subTypeId','description','level','name','rarity','durability'));
			$constraint_info = hConstraint::where(
				function($query) use($itemtypeid){
					$query->where('itemTypeId','=', $itemtypeid);
					$query->where('version','=', $this->version);
				}
			)->first(array('cpu','mass','power'));
			$weapon_info = hStat::where(
				function($query) use($itemtypeid){
					$query->where('itemTypeId','=', $itemtypeid);
					$query->where('version','=', $this->version);
				}
			)->first(array('ammoPerBurst','clipSize','damagePerRound','damagePerSecond','healthPerRound','maxAmmo','range','reloadTime','roundsPerBurst','roundsPerMinute','splashRadius','spread'));
		};
		if(empty($detailed_info)){
			return Response::json(array('status' => 'error', 'error' => 'error getting detailed information for this itemtypeid.'));
		};
		
		$detailed_info = $detailed_info->toArray();
		$detailed_info['asset_path'] = $basic_info['asset_path'];
		
		//add contstraint details
		if(!empty($constraint_info)){
			$constraint_info = $constraint_info->toArray();
			$detailed_info['cpu'] = $constraint_info['cpu'];
			$detailed_info['mass'] = $constraint_info['mass'];
			$detailed_info['power'] = $constraint_info ['power'];
		};
		if(!empty($weapon_info)){
			$weapon_info = $weapon_info->toArray();
			foreach($weapon_info as $key => $data){
				$detailed_info[$key] = $data;
			};
		};
		if(!empty($chassis_info)){
			$chassis_info = $chassis_info->toArray();
			foreach($chassis_info as $key => $data){
				$detailed_info[$key] = $data;
			};
		};
		//cache
		//Cache::forever($this->class_name . "_" . $itemtypeid,$detailed_info);
		
		$detailed_info['status'] = 'success';
		
		return json_encode($detailed_info);
	}
}
?>