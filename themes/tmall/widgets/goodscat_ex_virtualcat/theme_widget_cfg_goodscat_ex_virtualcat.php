
<?php 
	
	function theme_widget_cfg_goodscat_ex_virtualcat(){

		$mdl_virtualcat = app::get('b2c')->model('goods_virtual_cat');

		$data = $mdl_virtualcat->get_virtualcat_list(true);

		foreach ($data as $key => $value) {
			$value['cat_path'] = explode(',',$value['cat_path']);
			$data[$key] = $value;
		}

		return $data;
	}
?>





