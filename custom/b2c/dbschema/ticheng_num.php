<?php

$db['ticheng_num'] = array(
    'columns' =>
    array(
        'tc_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'label' => app::get('b2c')->_('提成比例ID'),
            'width' => 10,
            'hidden' => true,
            'editable' => false,
            'in_list' => true,
        ),
        'type' =>
        array(
            'type' => array(
                '1' => app::get('b2c')->_('代理'),
                '2' => app::get('b2c')->_('分销'),
            ),
            'default' => '2',
            'required' => true,
            'label' => app::get('b2c')->_('提成类型'),
        ),
        'one' =>
        array(
            'type' => 'decimal(20,2)',
            'default' => '0.00',
            'required' => true,
            'editable' => true,
            'label' => app::get('b2c')->_('一星等级(上上上级)'),
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'is_title' => true,
            'default_in_list' => false,
        ),
        'two' =>
        array(
            'type' => 'decimal(20,2)',
            'required' => true,
            'default' => '0.00',
            'editable' => true,
            'label' => app::get('b2c')->_('二星等级(上上级)'),
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'is_title' => true,
            'default_in_list' => false,
        ),
        'thr' =>
        array(
            'type' => 'decimal(20,2)',
            'default' => '0.00',
            'required' => true,
            'editable' => true,
            'label' => app::get('b2c')->_('三星等级(上级)'),
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'is_title' => true,
            'default_in_list' => false,
        ),
    ),
    'index' => array(
         'tc_id' => array(// 索引名称
            'columns' => array(// 要创建索引的数据库字段名
                0 => 'tc_id',
              //  1 => 'type',
            ),
           // 'prefix' => 'UNIQUE' // 索引的类型 UNIQUE|FULLTEXT|SPATIAL 如果为空 为一般的索引
        ),
         'type' => array(// 索引名称
            'columns' => array(// 要创建索引的数据库字段名
                0 => 'tc_id',
                1 => 'type',
            ),
            'prefix' => 'UNIQUE' // 索引的类型 UNIQUE|FULLTEXT|SPATIAL 如果为空 为一般的索引
        ),
    ),
    'version' => '$Rev: 3333333 $',
    'comment' => app::get('b2c')->_('提成系列/比例表'),
);
