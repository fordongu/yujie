<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_goodscat_ex_virtualcat(&$setting,&$render){
     	     		
			$showvc = $setting['vcid'];

	     		$mdl_virtualcat = app::get('b2c')->model('goods_virtual_cat');


			$data = $mdl_virtualcat->get_virtualcat_list();

			if(in_array('ALL', $showvc)){
				return $data;
			}



			foreach ($data as $key => $value) {
				
				if(in_array($value['cat_id'], $showvc)){
					$_filterData[] = $value;
				}else{
					foreach (explode(',',$value['cat_path']) as $i) {
						if($i!=""&&in_array($i, $showvc))
						 $_filterData[] = $value;
						break;
					}
				}
				
			}

			return $_filterData;
			
          
    }




?>






