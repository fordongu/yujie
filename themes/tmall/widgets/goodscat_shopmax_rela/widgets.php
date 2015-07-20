<?php
/**
 * ShopMax licence
 *
 * @copyright  Copyright (c) 2005-2020 ShopEx Technologies Inc. (http://www.shopmaxmb.com)
 * @license  http://www.shopmaxmb.com ShopMax License
 */
 
/*基础配置项*/
$setting['author']='www.shopmaxmb.com';
$setting['version']='v1.0';
$setting['name']='关联商品分类';
$setting['order']=0;
$setting['stime']='2012-08';
$setting['catalog']='商品分类挂件';
$setting['description'] = '';
$setting['userinfo'] = '';
$setting['usual']    = '0';
$setting['tag']    = 'auto';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );

/*首次默认配置项*/
$setting['show_cat_lv2'] 		= 1;
$setting['show_cat_lv3'] 		= 1;
$setting['show_cat_sale']		= 1;
$setting['show_cat_brand'] 		= 1;
$setting['box_flex'] 			= 1;
$setting['box_border_width']		= 1;//px
$setting['container_width'] 		= 200; //px
$setting['cat_lv2_width'] 		= 30;  //%
$setting['box_width']	 		= 700; //px
$setting['box_link_width'] 		= 35;// %
$setting['brand_logo_maxwidth'] 	= 75;//px
$setting['sales_title'] 	= "相关促销";
$setting['brand_title'] 	= "相关品牌";


?>
