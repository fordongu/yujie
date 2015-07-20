<?php
$db['member_bank']    = array (
    'columns'           => array (
         'member_bank_id'     => array (
            'type'      => 'number',
             'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'label'     => app::get('b2c')->_('会员银行卡id'),
            'width'     => 80,
            'editable'  => false,
        ),
        'member_id'     => array (
            'type'      => 'table:members',
            'required'  => true,
            'label'     => app::get('b2c')->_('会员ID'),
            'width'     => 80,
            'default'   => 0,
            'editable'  => false,
        ),
        'bank_num'     => array (
            'type'      => 'varchar(32)',
            'label'     => app::get('b2c')->_('银行卡号'),
            'width'     => 80,
            
        ),
        'real_name'       => array (
            'type'      => 'varchar(30)',           
            'label'     => app::get('b2c')->_('真实姓名'),
            'width'     => 70,
    
        ),
         'bank_type'       => array (
            'type'      => 'varchar(30)',           
            'label'     => app::get('b2c')->_('银行卡类型'),
            'width'     => 70,
    
        ),
          'bank_name'       => array (
            'type'      => 'varchar(50)',           
            'label'     => app::get('b2c')->_('银行名字'),
            'width'     => 70,
    
        ),
         'create_time'       => array (
            'type'      => 'time',           
            'label'     => app::get('b2c')->_('绑定时间'),
            'width'     => 70,    
        ),
    ),
    'index'  => array (
        'member_id' => array (
            'columns'   => array (
                0   => 'member_id',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version'=>'$Rev: 23423423 $',
    'comment' => app::get('b2c')->_('会员绑定银行卡列表'),
);
