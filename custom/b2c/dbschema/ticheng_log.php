<?php

$db['ticheng_log'] = array(
    'columns' =>
    array(
        'log_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'label' => app::get('b2c')->_('提成错误日志ID'),
        ),
        'order_id' =>
        array(
            'type' => 'varchar(20)',
            'required' => true,
            'editable' => true,
            'label' => app::get('b2c')->_('订单号'),
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'is_title' => true,
            'default_in_list' => false,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'required' => true,
            'default' => 0,
            'label' => app::get('b2c')->_('创建时间'),
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'remark' =>
        array(
            'type' => 'varchar(255)',
            'default' => '',
            'label' => app::get('b2c')->_('备注'),
            'editable' => true,
            'in_list' => true,
            'default_in_list' => false,
        ),
    ),
    'index' => array(
        'order_id' => array(// 索引名称
            'columns' => array(// 要创建索引的数据库字段名
                0 => 'order_id',
            ),
            //'prefix' => 'UNIQUE' // 索引的类型 UNIQUE|FULLTEXT|SPATIAL 如果为空 为一般的索引
        )
    ),
    'version' => '$Rev: 54223232 $',
    'comment' => app::get('b2c')->_('提成系列/比例表'),
);
