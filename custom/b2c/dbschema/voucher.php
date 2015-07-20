<?php
$db['voucher']=array (
  'columns' =>
	  array (
	    'vou_id' =>
	    array (
	      'type' => 'number',
	      'required' => true,
	      'pkey' => true,
	      'extra' => 'auto_increment',
		  'label' => app::get('b2c')->_('代金券ID'),
	      'width' => 110,
	      'hidden' => true,
	      'editable' => false,
	      'in_list' => true,
	    ),
	  	'vou_code'=>array(
	    	'type'=>'varchar(255)',
	  			'default' => '',
	  			'label' => app::get('b2c')->_('代金券号码'),
	  			'editable' => false,
	  			'filterdefault' => true,
	  			'in_list' => true,
	  			'default_in_list' => false,
	    ),
  		'vou_name'=>array(
  				'type'=>'varchar(255)',
  				'default' => '',
  				'label' => app::get('b2c')->_('代金券名称'),
  				'editable' => false,
  				'filterdefault' => true,
  				'in_list' => true,
  				'default_in_list' => false,
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
	    'create_time' =>
	    array (
	      'type' => 'int(11)',
	      'label' => app::get('b2c')->_('代金券产生时间'),
	      'editable' => false,
	      'in_list' => true,
	      'default_in_list' => true,
	    ),
  		'vou_to_time' =>
  		array (
  				'type' => 'int(11)',
  				'label' => app::get('b2c')->_('代金券到期时间'),
  				'editable' => false,
  				'in_list' => true,
  				'default_in_list' => true,
  		),
		'vou_conditions' =>
	    array (
	      'type' => 'mediumint(9)',
	      'label' => app::get('b2c')->_('代金券使用条件'),
	      'editable' => false,
    	  'in_list' => true,
    	  'default_in_list' => true,
	    ),
		'disabled' =>
	    array (
	      'type' => array(
	    		'false'=>app::get('b2c')->_('代金券已失效'),
	      		'true'=>app::get('b2c')->_('代金券没有失效'),
	    	),
	      'default' => 'true',
	      'label' => app::get('b2c')->_('是否失效'),
	      'editable' => false,
	    ),
	  		
  		'vou_isvalid' =>
  		array (
  				'type' => array(
  						'false'=>app::get('b2c')->_('代金券不可用'),
  						'true'=>app::get('b2c')->_('代金券可用'),
  				),
  				'default' => 'true',
  				'label' => app::get('b2c')->_('是否可用'),
  				'editable' => false,
  		),
	),
	  'version' => '$Rev: 23423423 $',
	  'comment' => app::get('b2c')->_('会员摇一摇代金券表'),
);