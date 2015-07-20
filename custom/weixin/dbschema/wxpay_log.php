<?php
$db['wxpay_log'] = array(
    'columns' => array(
        'id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
            'comment' => app::get('weixin')->_('日志ID'),
        ),
        'order_id' =>
        array(
            'type' => 'varchar(100)',
            'searchtype' => 'has',
            'required' => false,
            'comment' => app::get('weixin')->_('订单ID'),
        ),
        'payment_id' =>
        array(
            'type' => 'varchar(100)',
            'searchtype' => 'has',
            'required' => false,
            'comment' => app::get('weixin')->_('支付流水ID'),
        ),
        'transaction_id' =>
        array(
            'type' => 'varchar(100)',
            'required' => false,
            'default' => '',
            'comment' => app::get('weixin')->_('微信交易号'),
        ),
        'result_code' =>
        array(
            'type' => 'varchar(20)',
            'required' => true,
            'default' => '',
            'comment' => app::get('weixin')->_('微信支付结果'),
        ),
        'err_code' =>
        array(
            'type' => 'varchar(20)',
            'default' => '',
            'comment' => app::get('weixin')->_('错误代码'),
        ),
        'time_end' =>
        array(
            'type' => 'varchar(20)',
            'required' => false,
            'default' => '',
            'comment' => app::get('weixin')->_('支付完成时间'),
        ),
        'wx_data' =>
        array(
            'type' => 'longtext',
            'required' => true,
            'comment' => app::get('weixin')->_('微信支付回调数据'),
        ),
        'create_time' =>
        array(
            'type' => 'varchar(12)',
            'required' => false,
            'comment' => app::get('weixin')->_('创建时间'),
        ),
        'nonce_str' =>
        array(
            'type' => 'char(32)',
            'required' => false,
            'default' => '',
            'comment' => app::get('weixin')->_('回调随机字符串'),
        ),
        'device_info' =>
        array(
            'type' => 'varchar(25)',
            'required' => false,
            'default' => '',
            'comment' => app::get('weixin')->_('支付IP地址'),
        ),
    ),
    'index' => array(
        'id' => array(
            'columns' => array('id'),
            'prefix' => 'UNIQUE'
        ),
    ),
    'version' => '$Rev: 54922 $',
    'comment' => app::get('weixin')->_('微信支付日志表'),
);
