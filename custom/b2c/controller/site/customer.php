<?php

// +----------------------------------------------------------------------
// |  [ WE CAN DO IT JUST work hard ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-0501 http://www.gangguo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.gangguo.net/ )
// +----------------------------------------------------------------------
// | Author: gangguo <549224868@qq.com>
// +----------------------------------------------------------------------

/**
 * Description of customer
 *
 * @author gangguo
 */
class b2c_ctl_site_customer extends b2c_frontpage {
    
    
    public function index(){
        echo 'This is customer system';
        
        //$url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
        //$this->redirect(WWW_URL.'customer/index/');
        
        
    }
    
}
