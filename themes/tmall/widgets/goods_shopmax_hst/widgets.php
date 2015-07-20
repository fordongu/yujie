<?php
/**
 * ShopMax licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopMax Technologies Inc. (http://www.shopmaxmb.com)
 */
 

$setting['author']='shopmaxmb.com';
$setting['name']='ShopMax浏览过的商品';
$setting['version']='v1.0.0';
$setting['stime']='2013-5-10';
$setting['catalog']='商品挂件';
$setting['description']='本版块（widget）无需参数设置，添加本版块到模板对应的插槽上即可使用。前台显示的是用户最近所浏览过的5个商品。';
$setting['userinfo'] ='';
$setting['usual']    = '0';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );

?>
