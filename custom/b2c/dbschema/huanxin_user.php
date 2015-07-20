<?php

/**
 * 环信 会员数据对应表
 * 
 *  auth 549224868@qq.com  at 2015-01-19
 * 
 */
$db['huanxin_user'] = array(
    'columns' =>
    array(
        'member_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'searchtype' => 'has',
            'pkey' => true,
            'label' => app::get('b2c')->_('EC会员ID'),
        ),
        'username' =>
        array(
            'type' => 'varchar(110)',
            'required' => true,
            'searchtype' => 'has',
            'editable' => false,
            'label' => app::get('b2c')->_('环信用户名,环信ID'), //登录名称
        ),
        'password' =>
        array(
            'type' => 'varchar(50)',
            'required' => true,
            'label' => app::get('b2c')->_('环信用户密码'),
            'editable' => false,
            'in_list' => false,
        ),
        'nickname' =>
        array(
            'type' => 'varchar(110)',
            'required' => false,
            'searchtype' => 'has',
            'editable' => true,
            'label' => app::get('b2c')->_('环信用户昵称'),
            'in_list' => true,
        ),
        'addtime' =>
        array(
            'type' => 'time',
            'depend_col' => 'marketable:true:now',
            'label' => app::get('b2c')->_('数据同步时间'),
            'width' => 110,
            'editable' => false,
            'in_list' => false,
        ),
        'user_type' =>
        array(
            'type' =>
            array(
                'dai_thr' => app::get('b2c')->_('最高级代理/三星代理'),
                'dai_two' => app::get('b2c')->_('二级代理/二星代理'),
                'dai_one' => app::get('b2c')->_('三级代理/一星代理'),
                'fenxiao' => app::get('b2c')->_('普通用户'),
                'qita' => app::get('b2c')->_('其他'),
            ),
            'default' => 'fenxiao',
            'required' => true,
            'label' => app::get('b2c')->_('会员类型'),
            'width' => 110,
            'editable' => true,
            'in_list' => true,
        ),
        'disanble' =>
        array(
            'type' =>
            array(
                'yes' => app::get('b2c')->_('存活'),
                'no' => app::get('b2c')->_('禁用'),
            ),
            'default' => 'no',
            'required' => true,
            'label' => app::get('b2c')->_('是否禁用'),
            'width' => 110,
            'editable' => true,
            'in_list' => true,
        ),
        'remark' =>
        array(
            'type' => 'varchar(200)',
            'default' => '',
            'label' => app::get('b2c')->_('备注'),
            'width' => 310,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'index' => array(
        'username' => array(
            'columns' => array(
                0 => 'username',
            ),
            'prefix' => 'UNIQUE'
        ),
    ),
    'version' => '$Rev: 5555 $',
    'comment' => app::get('b2c')->_('EC会员和环信的用户对应表'),
);
