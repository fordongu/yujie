<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
function theme_widget_basic_shopmax_friendlinks(&$setting,&$render){
	$mdl_Links = app::get('site')->model('link');
	$data = $mdl_Links ->getList('*',array('hidden' =>'false' ),0,-1,'orderlist asc');
    	return $data ;
}
?>
