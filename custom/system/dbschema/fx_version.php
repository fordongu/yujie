<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['fx_version']=array (
    'columns' => array (
        'fx_id' => array (
            'type' => 'int(10)',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'comment' => app::get('system')->_('ID'),
        ),
        'app_name' => array (
            'type' => 'varchar(100)',
            'comment' => app::get('system')->_('APP名称'),
            'label' => app::get('system')->_('APP名称'),
            'required' => true,
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'update_time'=>array(
            'type' => 'time',
            'required' => true,
            'comment' => app::get('system')->_('跟新时间'),
            'label' => app::get('system')->_('跟新时间'),
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'version_num'=>array(
            'type' => 'varchar(255)',
            'required' => true,
            'comment' => app::get('system')->_('版本号'),
            'label' => app::get('system')->_('版本号'),
            'in_list'=>true,
			'in_list'=>true,
            'default_in_list'=>true,
        ),
		'app_type' =>
			array (
			  'type' =>
				  array (
					'ios' => app::get('b2c')->_('苹果系统'),
					'android' => app::get('b2c')->_('安卓系统'),
					'other' => app::get('b2c')->_('其它系统'),
				  ),
			  'default' => 'android',
			  'required' => true,
			  'label' => app::get('b2c')->_('app类型'),
			  'width' => 70,
			  'editable' => true,
			  'hidden' => false,
			  'in_list' => true,
		),
        'down_url' => array (
            'type' => 'varchar(255)',
            'default' => 0,
            'comment' => app::get('system')->_('下载地址'),
            'label' => app::get('system')->_('下载地址'),
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'remark' => array(
            'type' => 'longtext',
            'default' => 0,
            'comment' => app::get('system')->_('备注'),
            'label' => app::get('system')->_('备注'),
            'in_list'=>true,
            'default_in_list'=>false,
        ),
        'download_num' => array (
            'type' => 'int',
            'default' => 0,
            'comment' => app::get('system')->_('下载量'),
            'label' => app::get('system')->_('下载量'),
            'in_list'=>true,
            'default_in_list'=>false,
        ),
    ),
    'index' => array (
        'ind_get' => 
        array (
            'columns' => 
            array (
                0 => 'fx_id'
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 40912 $',
    'ignore_cache' => true,
    'comment' => app::get('system')->_('队列-mysql实现表'),
);

