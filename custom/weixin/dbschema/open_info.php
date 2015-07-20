<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['open_info']=array (
    'columns' => array (
        'id' =>
        array(
          'type' => 'int unsigned',
          'required' => true,
          'pkey' => true,
          'extra' => 'auto_increment',
          'editable' => false,
          'comment' => app::get('weixin')->_('ID'),
        ),
        'appname' => 
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'is_title' => 'true',
            'label' => app::get('weixin')->_('app应用名称'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'appid' => 
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'comment' => app::get('weixin')->_('开放平台app应用AppID'),
        ),
        'appsecret' => 
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'comment' => app::get('weixin')->_('开放平台app应用AppSecret'),
        ),
		'partnerkey' => 
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'comment' => app::get('weixin')->_('初始密钥(PartnerKey)'),
        ),
		'banknum' => 
        array (
            'type' => 'varchar(100)',
            'required' => false,
            'default' => '',
            'comment' => app::get('weixin')->_('银行账号'),
        ),
        'partnerid' => 
        array (
            'type' => 'varchar(20)',
            'required' => true,
            'default' => '',
            'label' => app::get('weixin')->_('商户号(PartnerID)'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'status' =>
        array (
            'type' =>
            array (
                'active' => app::get('b2c')->_('启用'),
                'disabled' => app::get('b2c')->_('禁用'),
            ),
            'default' => 'active',
            'required' => true,
            'label' => app::get('b2c')->_('状态'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'email' => 
        array (
            'type' => 'varchar(30)',
            'required' => false,
            'label' => app::get('weixin')->_('登录邮箱'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'notify_url' =>
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'comment' => app::get('weixin')->_('通知URL'),
        ),
    ),
    'index' => array (
        'appname' => array (
			'columns' => array ('appname'),
			'prefix' => 'UNIQUE'
		),
    ),
    'version' => '$Rev: 54922 $',
    'comment' => app::get('weixin')->_('微信开放平台app应用参数设置'),
);