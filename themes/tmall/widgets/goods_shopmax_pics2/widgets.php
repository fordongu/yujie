<?php
/**
 * ShopMax licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopMax Technologies Inc. (http://www.shopmaxmb.com)
 */
 
/*基础配置项*/
$setting['author']='www.shopmaxmb.com';
$setting['version']='v1.0';
$setting['name']='ShopMax楼层式商品列表2';
$setting['order']=0;
$setting['stime']='2012-08';
$setting['catalog']='商品挂件';
$setting['description'] = '展示模板使用的商品配图展示挂件';
$setting['userinfo'] = '';
$setting['usual']    = '1';
$setting['tag']    = 'auto';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
/*初始化配置项*/
$setting['selector'] = 'filter';
$setting['limit'] = 8;
?>
