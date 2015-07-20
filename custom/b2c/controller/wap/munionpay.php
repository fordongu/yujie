<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_wap_munionpay extends wap_frontpage{

    function __construct($app){
        parent::__construct($app);

    }

    function index()
    {
        $order_id = trim($_GET['order_id']);

        $objOrder = $this->app->model('orders');
        $sdf = $objOrder->dump($order_id);

        if(!$sdf){
            $msg = app::get('b2c')->_('订单号：'). $order_id . app::get('b2c')->_('不存在！');
        }

        if ($sdf['pay_status'] == '0') {
            $msg = '订单支付失败';
        }

        $this->pagedata['msg'] = $msg;
        $this->page('wap/munionpay/callback.html');
    }

}
