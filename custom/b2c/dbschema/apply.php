<?php
$db['apply'] = array(
    'columns' =>
    array(
        'apply_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'label' => app::get('b2c')->_('代理申请ID'),
            'width' => 10,
            'hidden' => true,
            'editable' => false,
            'in_list' => true,
        ),
        'faqi_member_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'editable' => true,
            'label' => app::get('b2c')->_('发起申请人(最高级代理)'),
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'is_title' => true,
            'default_in_list' => false,
        ),
        'member_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'editable' => true,
            'label' => app::get('b2c')->_('升级人会员ID'),
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'is_title' => true,
            'default_in_list' => false,
        ),
        'is_check' =>
        array(
            'type' => array(
                'wei'=>app::get('b2c')->_('未处理'),
                'yi'=>app::get('b2c')->_('已查看'),
                'ok'=>app::get('b2c')->_('申请成功'),
                'no'=>app::get('b2c')->_('申请失败'),
            ),
            'searchtype' => 'nequal',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'required' => true,
            'default' => 'wei',
            'label' => app::get('b2c')->_('是否处理'),
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'required' => true,
            'default' => 0,
            'label' => app::get('b2c')->_('申请时间'),
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
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
        'is_check' => array(// 索引名称
            'columns' => array(// 要创建索引的数据库字段名
                0 => 'is_check',
            ),
        )
    ),
    'version' => '$Rev: 54223232 $',
    'comment' => app::get('b2c')->_('代理申请表'),
);
