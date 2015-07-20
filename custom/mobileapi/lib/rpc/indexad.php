<?php
/**
 *
 * @author iegss
 *        
 */
class mobileapi_rpc_indexad {
	
	/**
	 */
	function __construct($app) {
		$this->app = $app;
	}
	
	function get_all_list() {
		
		$db = kernel::database();
		
		$sql = "select *  FROM `sdb_mobileapi_indexad_group` where disabled = 'false' order by ordernum asc, group_id asc";
		$indexads_group = $db->select($sql);
		$now_time = time(); 
		
		foreach ($indexads_group as $key => $v){
			$sql = "select *  FROM `sdb_mobileapi_indexad` where disabled = 'false' and group_id = '".$v['group_id']."' order by ordernum asc, ad_id asc";
			$indexads = $db->select($sql);
			$groupad = array();
			foreach ($indexads as $key1 => $v){
				$v['ad_img'] = base_storager::image_path($v['ad_img']);
				$groupad[] = $v;
			}
			$indexads_group[$key]['items'] = $groupad;
			$indexads_group[$key]['system_time'] = $now_time; 
		}
		return $indexads_group; 
	}
	

	

	
	/****----   gangguo.二次开放。2014-12-12。549224868@qq.com------------start----****/
	//app版本跟新 shop--商城APP    super--分销神器APP
	public function get_app_version(){
		if($_POST['app_name'] == 'shop' || $_POST['app_name'] == 'super' || !empty($_POST['app_type'])){
			if($_POST['app_name'] == 'shop'){
				$appname = '商城APP';
			}
			if($_POST['app_name'] == 'super'){
				$appname = '分销神器';
			}
			$apptype = $_POST['app_type'];
			$sql = "select * from sdb_system_fx_version where app_name = '{$appname}' AND app_type = '{$apptype}' order by update_time desc limit 1";
			$res = kernel::database()->select($sql);		
			return $res[0];
		}
		return '访问出错!';
	}
	
	
	
	public function baiduqq(){
	
		return 'This is test!';
	}
	
	/****----   gangguo.二次开放。2014-12-12。549224868@qq.com------------end----****/
	
	
}

?>
