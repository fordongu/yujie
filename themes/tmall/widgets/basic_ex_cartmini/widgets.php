<?php
/**
 * ShopMax licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopMax Technologies Inc. (http://www.shopmax.com)
 */

$setting['author']='shopmaxmb.com';
$setting['name']='迷你购物车';
$setting['version']='1.0';
$setting['catalog']='系统基础挂件';
$setting['description']= '支持显示当前购物车商品数量,支持展开购物车概览';
$setting['usual'] = '0';
$setting['userinfo']='购物车挂件，可显示商品数量';
$setting['stime']='2012-08';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );



/*default setting*/

$setting['title_str'] = "购物车";
$setting['show_cart_count'] = 1;
$setting['show_cart_num'] = 1;
$setting['show_cart_total'] = 1;
$setting['mini_container_width'] = 200;
