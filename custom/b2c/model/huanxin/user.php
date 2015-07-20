<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_mdl_huanxin_user extends dbeav_model{

    public function getusername($member_id){
        $res = $this->dump(array('member_id'=>$member_id),'username');
        return $res['username'];
    }
    
    public function getmember_id($username){
        $username = str_replace(array(" ","　","\t","\n","\r"), '', $username);
        //$res = $this->dump(array('username'=>$username),'member_id');
        $res = explode('_', $username);
        return $res[0];//['member_id'];
    }
}
