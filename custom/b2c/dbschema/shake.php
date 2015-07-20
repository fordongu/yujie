<?php
$db['shake']=array (
  'columns' =>
	  array (
	    'shake_id' =>
	    array (
	      'type' => 'number',
	      'required' => true,
	      'pkey' => true,
	      'extra' => 'auto_increment',
		  'label' => app::get('b2c')->_('摇奖记录ID'),
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
	  	'shake_num'=>
	  	array(
	      'type' => 'number',
	  	  'default'=>0,
		  'label' => app::get('b2c')->_('摇奖次数'),
    	  'filtertype' => 'normal',
    	  'filterdefault' => 'true',
    	  'in_list' => true,
    	  'is_title'=>true,
    	  'default_in_list' => false,
	    ),
	    'create_time' =>
	    array (
	      'type' => 'int(11)',
	      'label' => app::get('b2c')->_('摇一摇记录产生时间'),
	      'editable' => false,
	      'in_list' => true,
	      'default_in_list' => true,
	    ),
	),
	  'version' => '$Rev: 23423423 $',
	  'comment' => app::get('b2c')->_('会员摇一摇代次数记录'),
);