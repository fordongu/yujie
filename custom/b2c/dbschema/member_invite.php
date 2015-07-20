<?php
$db['member_invite']    = array (
    'columns'           => array (        
        'invite_id'     => array (
            'type'      => 'int',
            'required'  => true,
            'pkey'      => true,
            'extra'     => 'auto_increment',
            'label'     => app::get('b2c')->_('会员邀请ID'),
            'width'     => 80,
            'editable'  => false,
        ),
        'member_id'     => array (
            'type'      => 'number',
            'required'  => true,
            'label'     => app::get('b2c')->_('会员ID'),
            'width'     => 80,   
            'editable'  => false,
        ),
        'invite_phone'       => array (
            'type'      => 'varchar(50)',       
            'label'     => app::get('b2c')->_('邀请手机号'),
            'width'     => 70,        
        ),
        'invite_time'       => array (
            'type'      => 'time',
            'label'     => app::get('b2c')->_('邀请时间'),
            'default'   => 0,
            'width'     => 70,        
        ),

    ),
    'index'  => array (
        'member_id' => array (
            'columns'   => array (
                0   => 'member_id',
            ),
        ),
        'invite_phone' => array (
            'columns'   => array (
                0   => 'invite_phone',
            ),
        ),
    ),
    'engine' => 'innodb',
	'version'=>'$Rev: 23423423 $',
    'comment' => app::get('b2c')->_('会员邀请表'),
);
