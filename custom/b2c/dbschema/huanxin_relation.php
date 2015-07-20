<?php
/**
 * 会员好友关系表
 * 
 *  auth 549224868@qq.com  at 2015-01-19
 * 
 */

$db['huanxin_relation']=array (
  'columns' =>
  array (
    'member_id' =>
    array (
      'type' => 'number',
	  'required' => true,
	  'searchtype' => 'has',
      'label' => app::get('b2c')->_('会员ID'),
    ),
	'be_member_id' =>
    array (
      'type' => 'number',
	  'required' => true,
	  'searchtype' => 'has',
      'editable' => true,
      'label' => app::get('b2c')->_('好友会员ID'),
    ),
	'addtime' =>
    array (
      'type' => 'time',
      'depend_col' => 'marketable:true:now',
      'label' => app::get('b2c')->_('添加时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
	'disabled' =>
    array (
      'type' =>
      array (
        'yes' => app::get('b2c')->_('解除好友关系'),
        'no' => app::get('b2c')->_('是好友'),
      ),
      'default' => 'no',
      'required' => true,
      'label' => app::get('b2c')->_('是否失效'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
    ),
	'remark' =>
    array (
      'type' => 'varchar(200)',      
      'default' => '',
      'label' => app::get('b2c')->_('备注'),
      'width' => 310,     
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),	
  ), 
  'index' =>  array (
    'weiyisuoyin' =>array(// 索引名称
      'columns' =>array( // 要创建索引的数据库字段名
        0 => 'member_id',
        1 => 'be_member_id',
      ),
	  'prefix' => 'UNIQUE' // 索引的类型 UNIQUE|FULLTEXT|SPATIAL 如果为空 为一般的索引
    ),
  ),  
  'version' => '$Rev: 23423 $',
  'comment' => app::get('b2c')->_('会员好友关系表'),
);