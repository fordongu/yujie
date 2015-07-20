<?php
$db['shop_lv']    = array (
    'columns'       => array (
         'shop_lv_id'       => array (
            'type'      => 'int',
            'extra' => 'auto_increment',
            'pkey' => true,
            'label'     => app::get('microshop')->_('等级id'),
             'width'     => 80,
             'in_list' => true,
            'default_in_list' => true,

        ),
        'lv_name'     => array (
            'type'      => 'varchar(32)',
            'required'  => true,
            'label'     => app::get('microshop')->_('等级名称'),
            'width'     => 120,
            'editable'  => true,
            'in_list' => true,
            'default_in_list' => true,
        ),

        'point'  => array (
            'type'      => 'float(10,2)',
            'label'     => app::get('microshop')->_('每月销售额'),
            'width'     => 80,
            'editable'  => true,
            'in_list' => true,
            'default_in_list' => true,

        ),
        'mem_invite_nums'       => array (
            'type'      => 'int',
             'width'     => 100,                             
            'editable'  => true,
            'in_list' => true,
            'default_in_list' => true,   
             'label'     => app::get('microshop')->_('所需会员数'),

        ),
        'ticheng'       => array (
            'type'      => 'float(10,2)',
            'label'     => app::get('microshop')->_('提成比例'),
            'width'     => 110,
            'editable'  => true,
            'in_list' => true,
            'default_in_list' => true,
        ),

    ),
    'index'  => array (

     ),
    'engine' => 'innodb',
	'version'=>'$Rev: 23423423 $',
    'comment' => app::get('microshop')->_('微店等级表'),
);
