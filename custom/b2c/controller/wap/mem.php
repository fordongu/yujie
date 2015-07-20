<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_wap_mem extends wap_frontpage{

    function __construct(&$app){
        parent::__construct($app);
    }
    /*
     *会员中心首页
     * */
    public function index() {
        echo '1';exit;
        $a=kernel::single('mobileapi_rpc_passport');
        $b=$a->direct_referrals(array('member_id'=>10));
        var_dump($b);exit;
        $this->page('wap/member/index.html');
    }
    
}

?>
