<?php

/*
 * 	初始化
 * 	
 * 	auth 549224868@qq.com  at 2015-01-20
 *
 */
require_once 'easmob.php';

class b2c_huanxin_start {

    protected $huanxin;
    protected $options;
    protected $huanxin_log;
    protected $huanxin_user;

    public function __construct() {
        $this->options = array(
            'client_id' => CLIENT_ID,
            'client_secret' => CLIENT_SECRET,
            'org_name' => ORG_NAME,
            'app_name' => APP_NAME
        );
        $this->huanxin_user = kernel::single('b2c_mdl_huanxin_user');
        $this->huanxin = new b2c_huanxin_easmob($this->options);
        $this->huanxin_log = kernel::single('b2c_mdl_huanxin_log');
    }

}
