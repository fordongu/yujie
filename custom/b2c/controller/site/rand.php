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
 * Description of rand
 *
 * @author Administrator
 */
class b2c_ctl_site_rand extends b2c_frontpage{
    
    private $Rand;
    
    public function __construct() {
        $this->Rand = kernel::single('b2c_ticheng_rand');
    }


    /**
     * 随机获取昵称
     */
    public function nickname(){
        echo $this->Rand->nickname();
    }
    /**
     * 随机获取邀请码
     */
    public function invitecode(){
        echo $this->Rand->invitecode();
    }
}