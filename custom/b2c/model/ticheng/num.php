<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_mdl_ticheng_num extends dbeav_model{
    
    //获取代理提成比例
    public function get_ticheng_daili($tc_id){
        $filter = array(
            'tc_id'=>$tc_id,
            'type'=>1,
        );
        return $this->get_ticheng_bilie($filter);
    }
    
    //获取分销的提成比列
    public function get_ticheng_fenxiao($tc_id){
        $filter = array(
            'tc_id'=>$tc_id,
            'type'=>2,
        );
        return $this->get_ticheng_bilie($filter);
    }
    
    private function get_ticheng_bilie($filter){
       return $this->dump($filter,'one,two,thr');
    }

}
