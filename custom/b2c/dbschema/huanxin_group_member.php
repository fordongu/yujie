<?php

/**
 * 环信 群组 成员
 *  auth 549224868@qq.com  at 2015-01-21
 * 
 */
$db['huanxin_group_member'] = array(
    'columns' =>
    array(
        'group_id' =>
        array(
            'type' => 'bigint(18)',
            'required' => true,
            'searchtype' => 'has',
            'label' => app::get('b2c')->_('组ID'),
        ),
        'member_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'searchtype' => 'has',
            'label' => app::get('b2c')->_('会员ID'),
        ),
        'type' =>
        array(
            'type' => array(
                '1' => app::get('b2c')->_('群主'),
                '2' => app::get('b2c')->_('管理员'),
                '3' => app::get('b2c')->_('普通成员'),
                '4' => app::get('b2c')->_('其他'),
            ),
            'default' => 3,
            'required' => true,
            'searchtype' => 'has',
            'label' => app::get('b2c')->_('群成员类型/角色'),
        ),
        'addtime' =>
        array(
            'type' => 'time',
            'depend_col' => 'marketable:true:now',
            'label' => app::get('b2c')->_('加入时间'),
            'width' => 110,
            'editable' => false,
            'in_list' => false,
        ),
    ),
    'index' => array(
        'member_id' => array(
            'columns' => array(
                0 => 'member_id',
            )
        ),
        'group_id' => array(
            'columns' => array(
                0 => 'group_id',
            )
        ),
        'weiyi' => array(
            'columns' => array(
                0 => 'group_id',
                1 => 'member_id',
            ),
            'prefix' => 'UNIQUE'
        ),
    ),
    'version' => '$Rev: 33333 $',
    'comment' => app::get('b2c')->_('环信群组成员表'),
);
