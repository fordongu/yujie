<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_mdl_ticheng extends dbeav_model{
    
    //获取提成比例
    public function get_bielie($tc_name){
        $res = $this->getList('tc_id',array('tc_name'=>$tc_name));
        $tc_id = $res[0]['tc_id'];        
        $Num = kernel::single('b2c_mdl_ticheng_num');

        $daili = $Num->get_ticheng_daili($tc_id);
        $fenxiao = $Num->get_ticheng_fenxiao($tc_id);
        return array(
            'tc_name'=>$tc_name,
            'tc_id'=>$tc_id,
            'daili'=>$daili,
            'fenxiao'=>$fenxiao,
        );
    }


}
