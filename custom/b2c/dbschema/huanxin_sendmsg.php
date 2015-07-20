<?php

/**
 * 环信 消息推送记录
 * 
 *  auth 549224868@qq.com  at 2015-01-19
 * 
 */
$db['huanxin_sendmsg'] = array(
    'columns' =>
    array(
        'send_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'extra' => 'auto_increment',
            'pkey' => true,
            'label' => app::get('b2c')->_('消息发送ID'),
        ),
        'send_mem_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'searchtype' => 'has',
            'editable' => false,
            'label' => app::get('b2c')->_('发送人的会员ID'),
        ),
        'send_username' =>
        array(
            'type' => 'varchar(50)',
            'required' => true,
            'label' => app::get('b2c')->_('发送人的username'),
            'editable' => false,
            'in_list' => false,
        ),
        'content' =>
        array(
            'type' => 'text(5000)',
            'required' => false,
            'searchtype' => 'has',
            'editable' => true,
            'label' => app::get('b2c')->_('消息内容'),
            'in_list' => true,
        ),
        'addtime' =>
        array(
            'type' => 'time',
            'depend_col' => 'marketable:true:now',
            'label' => app::get('b2c')->_('发送时间'),
            'width' => 110,
            'editable' => false,
            'in_list' => false,
        ),
        'receive_mem_id' =>
        array(
            'type' => 'text(1000)',
            'label' => app::get('b2c')->_('接收会员ID'),
            'width' => 110,
            'editable' => true,
            'in_list' => true,
        ),
        'receive_username' =>
        array(
            'type' => 'text(1000)',
            'label' => app::get('b2c')->_('接收环信username'),
            'width' => 110,
            'editable' => true,
            'in_list' => true,
        ),
        'status' =>
        array(
            'type' => array(
                'success' => app::get('b2c')->_('成功'),
                'failure' => app::get('b2c')->_('失败'),
            ),
            'default' => 'success',
            'label' => app::get('b2c')->_('消息发生状态'),
            'width' => 310,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'index' => array(
        'send_username' => array(
            'columns' => array(
                0 => 'send_username',
            ),
        ),
    ),
    'version' => '$Rev: 5555 $',
    'comment' => app::get('b2c')->_('环信消息推送记录表'),
);
