<?php

/**
 * 环信 群组 
 *  auth 549224868@qq.com  at 2015-01-21
 * 
 */
$db['huanxin_group'] = array(
    'columns' =>
    array(
        'group_id' =>
        array(
            'type' => 'bigint(18)',
            'pkey' => true,
        ),
        'member_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'searchtype' => 'has',
            'label' => app::get('b2c')->_('群组管理员会员ID'),
        ),
        'groupname' =>
        array(
            'type' => 'varchar(200)',
            'required' => true,
            'searchtype' => 'has',
            'label' => app::get('b2c')->_('群组名称'),
        ),
        'desc' =>
        array(
            'type' => 'text(500)',
            'required' => false,
            'editable' => false,
            'label' => app::get('b2c')->_('群组描述'),
        ),
        'public' =>
        array(
            'type' =>
            array(
                'true' => app::get('b2c')->_('公开'),
                'false' => app::get('b2c')->_('私有'),
            ),
            'default' => 'true',
            'required' => false,
            'label' => app::get('b2c')->_('是否是公开群'),
            'width' => 110,
            'editable' => true,
            'in_list' => true,
        ),
        'status' =>
        array(
            'type' => array(
                '1' => app::get('b2c')->_('开启'),
                '2' => app::get('b2c')->_('关闭'),
                '3' => app::get('b2c')->_('影藏/不显示'),
                '4' => app::get('b2c')->_('其他'),
            ),
            'default' => 1,
            'required' => true,
            'label' => app::get('b2c')->_('群状态'),
        ),
        'addtime' =>
        array(
            'type' => 'time',
            'depend_col' => 'marketable:true:now',
            'label' => app::get('b2c')->_('创建时间'),
            'width' => 110,
            'editable' => false,
            'in_list' => false,
        ),
        'maxusers' =>
        array(
            'type' => 'number',
            'default' => '200',
            'label' => app::get('b2c')->_('群组成员最大数'), //群组成员最大数(包括群主), 值为数值类型,默认值200'
            'width' => 310,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'approval' =>
        array(
            'type' => 'varchar(20)',
            'default' => 'true',
            'label' => app::get('b2c')->_('加入公开群是否需要批准'),
            'width' => 310,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'owner' =>
        array(
            'type' => 'varchar(100)',
            'default' => 'true',
            'required' => false,
            'label' => app::get('b2c')->_('群组的管理员环信ID'), //username
            'width' => 310,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
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
        'member_id' => array(
            'columns' => array(
                0 => 'member_id',
            )
        ),
        'owner' => array(
            'columns' => array(
                0 => 'owner',
            )
        ),
    ),
    'version' => '$Rev: 88888 $',
    'comment' => app::get('b2c')->_('环信聊天群组表'),
);
