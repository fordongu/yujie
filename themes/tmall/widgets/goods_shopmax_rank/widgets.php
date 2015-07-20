<?php
/**
 * ShopMax licence
 *
 * @copyright  Copyright (c) 2005-2020 ShopEx Technologies Inc. (http://www.shopmaxmb.com)
 * @license  http://www.shopmaxmb.com ShopEx License
 */
$setting['author']='shopmaxmb.com';
$setting['name']='ShopMax商品排行';
$setting['version']='1.0';
$setting['stime']='2012-8';
$setting['catalog']='商品挂件';
$setting['description']= '支持范围选择和精确选择商品，支持排序，支持商品规格展示，自动计算每行不确定高度，自动控制图片尺寸';
$setting['order']='1';
$setting['userinfo']='';
$setting['usual']    = '1';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认'),
                        );




/*初始化配置项*/

$setting['selector'] = 'filter';
$setting['limit'] = 8;
$setting['lline_limit'] = 4;
$setting['item_margin_lr'] = 10;
$setting['item_margin_tb'] = 10;
$setting['item_margin_line_fix'] = 1;
$setting['item_border_width'] = 1;
$setting['gpic_size'] = 'goodsPicS';
$setting['item_pic_border_width'] = 1;
$setting['market_price_title'] = '市场价：';
$setting['price_title'] = '销售价：';
$setting['member_price_title'] = '会员价：';
$setting['discount_title'] = '折扣：';
$setting['show_mktprice'] = 1;
$setting['show_price'] = 1;
$setting['item_pic_maxheight'] = 170;

// $setting['item_pic_auto_size'] = 1;



?>
