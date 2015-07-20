<?php
/**
 * ShopMax licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopMax Technologies Inc. (http://www.shopmaxmb.com)
 */
 

$setting['author']='www.shopmaxmb.com';
$setting['version']='1.0';
$setting['order']=18;
$setting['name']='ShopMax文字+广告图片组';
$setting['catalog'] = '广告挂件';
$setting['description'] = '可自定义展示多张图片,并且可以为其加上连接，当鼠标移动到某一张时，其他相邻广告出现遮罩效果';
$setting['usual'] = '1';
$setting['stime'] ='2012-8';
$setting['userinfo'] = '图片地址可使用上传图片，也可使用远程图片，更可使用%THEME%/images/***.jpg写法调用模板内部图片。';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );

?>
