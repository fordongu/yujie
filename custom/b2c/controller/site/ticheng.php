<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class b2c_ctl_site_ticheng extends b2c_frontpage{
    
	function __construct(&$app){
         parent::__construct($app);
		 header('Content-type:text/html;charset=utf-8');
    }
	
	public function clean(){
		if(cachemgr::clean()){
			echo '缓存清除成功!';
		}else{
			echo '缓存清除失败!';
		}
	}
	public function wt_goods(){
		$sql = 'SELECT name FROM sdb_b2c_products where store = 0 group by goods_id';
		$res  = kernel::database()->select($sql);
		echo '共 '.count($res).' 个<hr>';
		foreach($res as $k=>$v){
			echo $v['name'].'<hr>';
		}
		exit();
	}
	
    function task(){
		echo app::get('b2c')->getConf('sign.days');
		exit();
		//$res = cachemgr::enable();
		echo $key = 'uuuuuuuuuuuu';//'baidu.comabcd';
		$content1= array(
			'ttt'=>1111111,
			'ttt33'=>222222222222222,
			'ttt333'=>'lllllllllll',
			'ttt22'=>'fsdfsdfsdfsd',
		);
		$content = 'dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd';
		//$res1 = cachemgr::set($key, $content1, $params=array());
		//$res2 = cachemgr::get($key,$jjjj);
		$res3 = cachemgr::status($hhh);
		
		//var_dump(cachemgr::clean());
		//echo mt_rand(20,80);
		exit();
		$Goods = kernel::single('b2c_mdl_goods');
		$goods_ids = $Goods->getList('goods_id',array('buy_count'=>33));
		foreach($goods_ids as $v){
			$data = array('buy_count'=>mt_rand(0,80));
			$filter = array('goods_id'=>$v['goods_id']);
			$res = $Goods->update($data,$filter);
			unset($data);unset($filter);
			var_dump($res);
			if(!$res){
				echo $v['goods_id'].' error!<hr>';
			}else{
				echo 'ok : ';
			}
		}
		
		exit('buy_count');
		
			list($s1, $s2) = explode(' ', microtime()); 
			echo ($s1*1000).'<hr>';
			echo $s2.'<hr>';
			$time = (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000); 
			
		var_dump($time);
		exit();
		$money = 0;
		for($ii = 7;$ii > 0;$ii--){
			$money += $ii;
		}
		echo $money;exit();
		$sql = 'SELECT name FROM `sdb_b2c_products` where cost = 0 and price != 0 group by goods_id';
		//$res = kernel::single('b2c_mdl_products')->getList('name',array('cost'=>0,'price|noequal'=>0));
		$res = kernel::database()->select($sql);
		foreach($res as $k=>$v){
			echo ($k+1).',产品名称: '.$v['name'].'<hr>';
		}
		exit();
		$Sign = kernel::single('b2c_mdl_sign');
		$yesd = strtotime(date("Y-m-d",strtotime("-1 day")));
		$tod = strtotime(date('Y-m-d',time()));
		$res = $Sign->getList('sign_id',array('create_time|than'=>$yesd,'create_time|lthan'=>$tod,'member_id'=>115061));
		var_dump($res);
		exit();
	echo date('Y-m-d H:i:s',strtotime(date("Y-m-d",strtotime("-1 day"))));exit();		
	echo date('Y-m-d H:i:s',strtotime(date("Y-m-d H:i:s",strtotime("-1 day"))));exit();		
		$email = 'wanggang5161@163.com';

		//提成既时结算
	$res = kernel::single('b2c_order_ticheng')->demo();
		
		var_dump($res);
		exit();
   }
   

   
}

