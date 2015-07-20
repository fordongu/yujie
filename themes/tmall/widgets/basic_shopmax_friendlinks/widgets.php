<?php
/**
 * ShopMax licence
 *
 * @copyright  Copyright (c) 2005-2020 ShopMax Technologies Inc. (http://www.shopmaxmb.com)
 * @license  http://www.shopmaxmb.com/ ShopEx License
 */
 
$setting['author']='litie@shopex';
$setting['name']='友情连接';
$setting['version']='1.0';
$setting['catalog']='系统基础挂件';
$setting['usual']    = '0';
$setting['description'] = '此挂件将展示“系统》站点》友情连接”内容';
$setting['userinfo'] ='*'; 
$setting['stime']='2012-9';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
?>
