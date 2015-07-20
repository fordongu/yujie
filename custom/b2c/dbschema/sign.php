<?php
$db['sign']=array (
  'columns' =>
	  array (
	    'sign_id' =>
	    array (
	      'type' => 'number',
	      'required' => true,
	      'pkey' => true,
	      'extra' => 'auto_increment',
		  'label' => app::get('b2c')->_('签到ID'),
	      'width' => 110,
	      'hidden' => true,
	      'editable' => false,
	      'in_list' => true,
	    ),
	    'member_id' =>
	    array (
	      'type' => 'number',
		  'label' => app::get('b2c')->_('关联会员id'),
    	  'filtertype' => 'normal',
    	  'filterdefault' => 'true',
    	  'in_list' => true,
    	  'is_title'=>true,
    	  'default_in_list' => false,
	    ),
	    'sign_type' =>
	    array (
	      'type'      => array(
				'app'	=>app::get('b2c')->_('app端'),
                'pc'    =>app::get('b2c')->_('pc电脑端'),
                'wx'    =>app::get('b2c')->_('微信端'),
                'wap'   =>app::get('b2c')->_('wap浏览器端'),
                'other' =>app::get('b2c')->_('其他'),
            ),
	      'label' => app::get('b2c')->_('签到来源'),
	      'editable' => true,
	      'filtertype' => 'normal',
	      'filterdefault' => 'true',
	      'in_list' => true,
	      'is_title'=>true,
	      'default_in_list' => false,
	    ),
		 'creattime' =>
	    array (
	      'type' => 'char(1)',
	    ),
	    'create_time' =>
	    array (
	      'type' => 'char(11)',
	      'default' => 0,
	      'label' => app::get('b2c')->_('会员签到时间'),
	      'editable' => false,
	      'in_list' => true,
	      'default_in_list' => true,
	    ),
		'year' =>
	    array (
	      'type' => 'char(4)',
	      'default' => 0,
	      'label' => app::get('b2c')->_('年'),
	      'editable' => false,
	    ),
		'months' =>
	    array (
	      'type' => 'char(2)',
	      'default' => 0,
	      'label' => app::get('b2c')->_('月'),
	      'editable' => false,
	    ),
		'day' =>
	    array (
	      'type' => 'char(2)',
	      'default' => 0,
	      'label' => app::get('b2c')->_('日'),
	      'editable' => false,
	    ),
	    'remark' =>
			array (
			  'type' => 'varchar(255)',
			  'default' => '',
			  'label' => app::get('b2c')->_('备注'),
			  'editable' => false,
			  'in_list' => true,
			  'default_in_list' => false,
			),
	),
	  'index' =>
	  array (
		'member_id' =>
		array (
		  'columns' =>
		  array (
			0 => 'member_id',
		  ),
		),
		'year' =>
		array (
		  'columns' =>
		  array (
			0 => 'year',
		  ),
		),
		'months' =>
		array (
		  'columns' =>
		  array (
			0 => 'months',
		  ),
		),
		'day' =>
		array (
		  'columns' =>
		  array (
			0 => 'day',
		  ),
		),
		'creattime' =>
		array (
		  'columns' =>
		  array (
			0 => 'creattime',
		  ),
		),
	  ),
	  'version' => '$Rev: 54922 $',
	  'comment' => app::get('b2c')->_('会员签到表'),
);
