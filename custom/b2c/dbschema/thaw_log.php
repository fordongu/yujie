<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$db['thaw_log'] = array(
    'columns' =>
    array(
        'order_id' =>
        array(
            'type' => 'bigint(20) unsigned',
            'required' => true,
            'label' => app::get('b2c')->_('订单ID'),
            'is_title' => true,
            'width' => 110,
            'searchtype' => 'has',
            'editable' => false,
            'filtertype' => 'custom',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'log_id' =>
        array(
            'type' => 'int(10)',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'label' => app::get('b2c')->_('提成冻结日志ID'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'comm_adv_log_id' =>
        array(
            'type' => 'int(10)',
            'required' => true,
            'default' => 0,
            'label' => app::get('b2c')->_('提成记录日志ID'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
        ),
        'result' =>
        array(
            'type' => array(
                'succ' => app::get('b2c')->_('解冻成功'),
                'error' => app::get('b2c')->_('解冻失败'),
            ),
            'required' => true,
            'default' => 'succ',
            'label' => app::get('b2c')->_('结果成功或者失败'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'label' => app::get('b2c')->_('任务执行时间'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
        ),
        'remark' =>
        array(
            'type' => 'longtext',
            'editable' => false,
             'label' => app::get('b2c')->_('备注'),
            'comment' => app::get('b2c')->_('备注'),
             'editable' => false,
            'in_list' => true,
        ),
    ),
    'index' =>
    array(
        'comm_adv_log_id' =>
        array(
            'columns' =>
            array(
                0 => 'comm_adv_log_id',
            ),
        ),
    ),
    'version' => '$Rev: 54999922 $',
    'comment' => app::get('b2c')->_('提成解冻日志记录'),
);
