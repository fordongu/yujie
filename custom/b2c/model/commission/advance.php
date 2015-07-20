<?php
/**
 * author 549224868@qq.com
 */


class b2c_mdl_commission_advance extends dbeav_model{


    public function __construct($app){
        parent::__construct($app);
     
    }
    
    public function getlists() {
        
        $this->getList();
    }

    public function details($result){
    	$Commadv = kernel::single('b2c_mdl_commission_log');
    	$member=kernel::single('b2c_mdl_members');
    	$b2c_member_lv = kernel::single('b2c_mdl_member_lv');
    	if(!empty($result)){
    		foreach($result as $key=>$vo){
    			$data=$Commadv->getList('*',array('log_id'=>$vo['comm_log_id']));
    			$data=$data[0];
    			$result[$key]['order_id']=$data['order_id'];
    			$name=$member->getList('*',array('member_id'=>$data['member_id']));

    			$name=$name[0];
    			$member_lv_name=$b2c_member_lv->getList('*',array('member_lv_id'=>$name['member_lv_id']));
    			$result[$key]['nickname']=$name['nickname'];
    			$result[$key]['member_lv_name_on']=$member_lv_name[0]['name'];
    		}
    	}
    	return $result;
    }

}
