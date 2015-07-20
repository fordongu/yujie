<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$db['commission_advance'] = array(
    'columns' =>
    array(
        'log_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'label' => app::get('b2c')->_('日志ID'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'comm_log_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'label' => app::get('b2c')->_('提成日志ID'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
        ),
        'member_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'label' => app::get('b2c')->_('会员ID'),
            'is_title' => true,
            'width' => 110,
            'searchtype' => 'has',
            'editable' => false,
            'filtertype' => 'custom',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'required' => true,
            'label' => app::get('b2c')->_('提成时间'),
            'is_title' => true,
            'width' => 110,
            'editable' => false,
            'filtertype' => 'custom',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'commission_nums' =>
        array(
            'type' => 'money',
            'required' => true,
            'label' => app::get('b2c')->_('提成金额'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
        ),
        'status' =>
        array(
            'type' => array(
                'frozen' => app::get('b2c')->_('冻结'),
                'activation' => app::get('b2c')->_('激活'),
                'deduction' => app::get('b2c')->_('扣除'),
                'return' => app::get('b2c')->_('退货'),
            ),
            'default' => 'frozen',
            'required' => true,
            'default_in_list' => true,
            'label' => app::get('b2c')->_('资金状态'),
            'is_title' => true,
            'width' => 110,
            'searchtype' => 'has',
            'editable' => false,
            'filtertype' => 'custom',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'remark' =>
        array(
            'type' => 'longtext',
            'editable' => false,
            'label' => app::get('b2c')->_('备注说明'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'last_modify' =>
        array(
            'type' => 'last_modify',
            'label' => app::get('b2c')->_('修改时间'),
            'width' => 110,
            'in_list' => true,
            'orderby' => true,
        ),
    ),
    'index' =>
    array(
        'member_id' =>
        array(
            'columns' =>
            array(
                0 => 'member_id',
            ),
        ),
        'comm_log_id' =>
        array(
            'columns' =>
            array(
                0 => 'comm_log_id',
            ),
        ),
    ),
    'version' => '$Rev: 54999922 $',
    'comment' => app::get('b2c')->_('提成金额冻结/激活过渡表'),
);
