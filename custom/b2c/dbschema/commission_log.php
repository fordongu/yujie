<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$db['commission_log'] = array(
    'columns' =>
    array(
        'log_id' =>
        array(
            'type' => 'int(10)',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'label' => app::get('b2c')->_('日志ID'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
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
        'goods_id' =>
        array(
            'type' => 'bigint unsigned',
            'required' => true,
            'default' => 0,
            'label' => app::get('b2c')->_('商品ID'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
        ),
        'product_id' =>
        array(
            'type' => 'bigint unsigned',
            'required' => true,
            'default' => 0,
            'label' => app::get('b2c')->_('产品ID'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
        ),
        'tc_id' =>
        array(
            'type' => 'table:ticheng',
            'required' => true,
            'default' => 0,
            'label' => app::get('b2c')->_('提成系列ID'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
        ),
        'member_id' =>
        array(
            'type' => 'int(10)',
            'required' => true,
            'default' => 0,
            'label' => app::get('b2c')->_('会员ID(购买者)'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
        ),
        'parent_id' =>
        array(
            'type' => 'int(10)',
            'required' => true,
            'default' => '0',
            'label' => app::get('b2c')->_('提成会员ID'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
        ),
        'tc_type' =>
        array(
            'type' => array(
                'off' => app::get('b2c')->_('无上级'),
                'one_fen' => app::get('b2c')->_('上上上级/一星分销商'),
                'two_fen' => app::get('b2c')->_('上上级/二星分销商'),
                'thr_fen' => app::get('b2c')->_('上级/三星分销商'),
                'one_dai' => app::get('b2c')->_('一星代理/区代'),
                'two_dai' => app::get('b2c')->_('二星代理/市代'),
                'thr_dai' => app::get('b2c')->_('三星代理/省代'),
            ),
            'required' => true,
            'default' => 'off',
            'label' => app::get('b2c')->_('提成类型'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'commission' =>
        array(
            'type' => 'money',
            'default' => '0',
            'required' => true,
            'editable' => false,
            'label' => app::get('b2c')->_('提成金额'),
            'in_list' => true,
            'default_in_list' => false,
        ),
        'disabled' =>
        array(
            'type' => 'bool',
            'default' => 'false',
            'required' => true,
            'label' => app::get('b2c')->_('成功/失败'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
        ),
        'formula' =>
        array(
            'type' => 'varchar(200)',
            'default' => '',
            'label' => app::get('b2c')->_('提成计算公式'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'label' => app::get('b2c')->_('创建时间'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
        ),
        'remark' =>
        array(
            'type' => 'longtext',
            'editable' => false,
            'label' => app::get('b2c')->_('备注'),
            'in_list' => true,
            'width' => 200,
            'default_in_list' => true,
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
        'order_id' =>
        array(
            'columns' =>
            array(
                0 => 'order_id',
            ),
        ),
    ),
    'version' => '$Rev: 54999922 $',
    'comment' => app::get('b2c')->_('提成日志记录表'),
);
