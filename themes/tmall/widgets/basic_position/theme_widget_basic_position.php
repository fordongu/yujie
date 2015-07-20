<?php

/**

 * ShopEx licence

 *

 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)

 * @license  http://ecos.shopex.cn/ ShopEx License

 */

 

function theme_widget_basic_position($setting,&$app){

	$data = $GLOBALS['runtime']['path'];

	foreach ($data as $key => $value) {
		if(!$value['title']||$value['title'] == '首页'){
			unset($data[$key]);
		}
	}

    	return $data;

}

?>

