<?php
$db['shop']             = array (
    'columns'           => array (
        'shop_id'       => array (
            'type'      => 'number',
            'required'  => true,
            'label'     => app::get('microshop')->_('微店ID'),
            'width'     => 80,
            'editable'  => false,
        ),
        'shop_name'     => array (
            'type'      => 'char(40)',
            'required'  => false,
            'label'     => app::get('microshop')->_('微店名称'),
            'width'     => 100,
            'in_list'   => true,
            'searchtype'=> 'has',
            'default_in_list'   => true,
            'filtertype'        => 'normal',
            'filterdefault'     => 'true',
        ),
        'member_id'     => array (
            'type'      => 'number',
            'required'  => true,
            'label'     => app::get('microshop')->_('会员ID'),
            'width'     => 80,
            'default'   => 0,
            'editable'  => false,
             'in_list'   => true,
            'default_in_list'   => false,
        ),
        'see_num'       => array (
            'type'      => 'number',
            'label'     => app::get('microshop')->_('查看数'),
            'width'     => 110,
            'default'   => 0,
            'hidden'    => true,
            'in_list'   => true,
            'default_in_list'   => true,
        ),
        'addtime'       => array (
            'type'      => 'time',
            'required'  => true,
            'label'     => app::get('microshop')->_('添加时间'),
            'width'     => 70,
            'editable'  => false,
            'in_list'   => true,
            'default_in_list'   => true,
            'filtertype'        => 'time',
            'filterdefault'     => true,
        ),
        'is_open'       => array (
            'type'      => array(
                 'off'    => app::get('microshop')->_('微店关闭'),
                 'on'    => app::get('microshop')->_('微店开启'),
            ),
            'label'     => app::get('microshop')->_('微店状态'),
            'default'   => 'off',
        ),
        'shop_lvl'       => array (
			'type'      => 'number',
            'required'  => true,
            'label'     => app::get('microshop')->_('微店等级ID'),
            'width'     => 70,
			'default'   => 0,
            'editable'  => false,
            'in_list'   => true,
            'default_in_list'   => false,            
        ),
          'wx_name'     => array (
            'type'      => 'char(50)',        
            'label'     => app::get('microshop')->_('微信号码'),
            'width'     => 100,
            'in_list'   => true,
            'default' => ' ',
            'default_in_list'   => true,
        ),
         'descs'     => array (
            'type'      => 'text',        
            'label'     => app::get('microshop')->_('微店简介'),
            'width'     => 100,
            'in_list'   => true,
			  'default' => ' ',
            'default_in_list'   => true,
        ),
        
    ),
    'index'  => array (
        'member_id' => array (
            'columns'   => array (
                0   => 'member_id',
            ),
        ),
         'shop_id' => array (
            'columns'   => array (
                0   => 'shop_id',
            ),
            'prefix' => 'UNIQUE' 
        ),
    ),
    'engine' => 'innodb',
    'version'=>'$Rev: 23423423 $',
    'comment' => app::get('microshop')->_('微店专辑表'),
);
