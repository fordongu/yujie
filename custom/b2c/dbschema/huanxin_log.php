<?php

/**
 * 环信 数据同步 错误日志
 *  auth 549224868@qq.com  at 2015-01-21
 * 
 */
$db['huanxin_log'] = array(
    'columns' =>
    array(
        'log_id' =>
        array(
            'type' => 'bigint(18)',
            'extra' => 'auto_increment',
            'pkey' => true,
        ),
        'member_id' =>
        array(
            'type' => 'bigint(18)',
            'required' => true,
            'searchtype' => 'has',
            'label' => app::get('b2c')->_('EC会员ID'),
        ),
        'desc' =>
        array(
            'type' => 'text(500)',
            'required' => true,
            'editable' => false,
            'label' => app::get('b2c')->_('内容描述'),
        ),
        'solve' =>
        array(
            'type' =>
            array(
                'unresolved' => app::get('b2c')->_('未解决'),
                'ok' => app::get('b2c')->_('已解决'),
            ),
            'default' => 'unresolved',
            'required' => true,
            'label' => app::get('b2c')->_('是否解决'),
            'width' => 110,
            'editable' => true,
            'in_list' => true,
        ),
        'addtime' =>
        array(
            'type' => 'time',
            'depend_col' => 'marketable:true:now',
            'label' => app::get('b2c')->_('记录时间'),
            'width' => 110,
            'editable' => false,
            'in_list' => false,
        ),
        'error_type' =>
        array(
            'type' =>
            array(
                'ecstore' => app::get('b2c')->_('ecstore的错误'),
                'huanxin' => app::get('b2c')->_('环信那边的错误'),
                'error' => app::get('b2c')->_('两边都有错误'),
                'success' => app::get('b2c')->_('两边都有Ok'),
            ),
            'default' => 'error',
            'required' => true,
            'label' => app::get('b2c')->_('错误类型'),
            'width' => 110,
            'editable' => true,
            'in_list' => true,
        ),
        'remark' =>
        array(
            'type' => 'text(10000)',
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
    ),
    'version' => '$Rev: 6666 $',
    'comment' => app::get('b2c')->_('错误日志'),
);
