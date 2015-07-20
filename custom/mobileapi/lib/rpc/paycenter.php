<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class mobileapi_rpc_paycenter extends mobileapi_frontpage {

    var $noCache = true;
    private $data_post;

    public function __construct(&$app) {

        parent::__construct($app);

        $this->objMath = kernel::single("ectools_math");
        $this->app = app::get('b2c');
        $this->verify_member();
        if (!empty($_POST)) {
            $this->data_post = $this->check_input($_POST);
            //初始化分页处理
            if ($this->data_post['page_no'] > 0) {
                $this->data_post['n_page'] = $this->data_post['page_no'];
            }
            $this->page_nums = $this->data_post['page_nums'] ? $this->data_post['page_nums'] : 2;
            $this->n_page = $this->data_post['n_page'] ? $this->data_post['n_page'] : 1;
            $this->offset = ($this->n_page - 1) * $this->page_nums;
            if ($this->data_post['order'] == 'null') {
                $this->data_post['orderby'] = '';
            }
        }
    }

    /*
     * 支付中心页面
     *
     * */

    public function index($order_id, $newOrder = false) {
        $objOrder = $this->app->model('orders');
        $sdf = $objOrder->dump($order_id);

        if (!$sdf) {
            $this->_response->set_body(app::get('b2c')->_('订单号：') . $order_id . app::get('b2c')->_('不存在！'));
            kernel::single('site_router')->http_status(404);
            return;
            return;
        }

        if ($sdf['pay_status'] == '1' || $sdf['pay_status'] == '2') {
            $url = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_paycenter', 'act' => 'result_pay', 'arg0' => $order_id, 'arg1' => 'true'));
            header('Location:' . $url);
            exit;
        }

        $member_money = app::get('b2c')->model('members')->getList('advance', array('member_id' => $sdf['member_id']));
        $this->pagedata['deposit_money'] = $member_money[0]['advance'] ? $member_money[0]['advance'] : 0;

        // 校验订单的会员有效性.
        $is_verified = ($this->_check_verify_member($sdf['member_id'])) ? $this->_check_verify_member($sdf['member_id']) : false;

        // 校验订单的有效性.
        if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token') . $order_id) && !$is_verified) {
            $this->begin();
            $this->end(false, app::get('b2c')->_('订单无效！'), $this->gen_url(array('app' => 'site', 'ctl' => 'default', 'act' => 'index')));
        }

        $sdf['cur_money'] = $this->objMath->number_minus(array($sdf['cur_amount'], $sdf['payed']));

        $this->pagedata['order'] = $sdf;
        $order_quantity = app::get('b2c')->model('order_items')->getList('sum(nums) as nums', array('order_id' => $order_id));
        $this->pagedata['order']['quantity'] = $order_quantity[0]['nums'];

        $opayment = app::get('ectools')->model('payment_cfgs');
        $this->pagedata['payments'] = $opayment->getListByCode(); //
        $system_money_decimals = $this->app->getConf('system.money.decimals');
        $system_money_operation_carryset = $this->app->getConf('system.money.operation.carryset');
        $pay_online = false;
        foreach ($this->pagedata['payments'] as $key => &$arrPayments) {

            //判断是否有在线支付方式
            if (!$pay_online && $arrPayments['app_id'] != 'deposit' && $arrPayments['app_pay_type'] == 'true') {
                $pay_online = true;
            }

            if ($arrPayments['app_id'] == $this->pagedata['order']['payinfo']['pay_app_id']) {
                $this->pagedata['order']['payinfo']['pay_name'] = $arrPayments['app_name'];
                $this->pagedata['order']['payinfo']['pay_des'] = $arrPayments['app_des'];
                $this->pagedata['order']['payinfo']['platform'] = $arrPayments['app_platform'];
                $arrPayments['cur_money'] = $this->objMath->formatNumber($this->pagedata['order']['cur_money'], $system_money_decimals, $system_money_operation_carryset);
                $arrPayments['total_amount'] = $this->objMath->formatNumber($this->pagedata['order']['total_amount'], $system_money_decimals, $system_money_operation_carryset);
            } else {
                $arrPayments['cur_money'] = $this->pagedata['order']['cur_money'];
                if ($this->pagedata['order']['payinfo']['pay_app_id'] == 'deposit' && $arrPayments['app_id'] != 'deposit') {
                    $temp_cur_money = $this->objMath->number_minus(array($arrPayments['cur_money'], $this->pagedata['deposit_money']));
                    $arrPayments['cur_money'] = $temp_cur_money ? $temp_cur_money : 0;
                }
                $cur_discount = $this->objMath->number_multiple(array($sdf['discount'], $this->pagedata['order']['cur_rate']));

                if ($this->pagedata['order']['payinfo']['cost_payment'] > 0) {
                    if ($this->pagedata['order']['cur_money'] > 0)
                        $cost_payments_rate = $this->objMath->number_div(array($arrPayments['cur_money'], $this->objMath->number_plus(array($this->pagedata['order']['cur_money'], $this->pagedata['order']['payed']))));
                    else
                        $cost_payments_rate = 0;

                    $cost_payment = $this->objMath->number_multiple(array($this->objMath->number_multiple(array($this->pagedata['order']['payinfo']['cost_payment'], $this->pagedata['order']['cur_rate'])), $cost_payments_rate));
                    $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
                    $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cost_payment));
                    $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $this->objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']))));
                    $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
                }
                else {
                    $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
                    $cost_payment = $this->objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']));
                    $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cost_payment));
                    $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
                }

                $arrPayments['total_amount'] = $this->objMath->formatNumber($this->objMath->number_div(array($arrPayments['cur_money'], $this->pagedata['order']['cur_rate'])), $system_money_decimals, $system_money_operation_carryset);
                $arrPayments['cur_money'] = $this->objMath->formatNumber($arrPayments['cur_money'], $system_money_decimals, $system_money_operation_carryset);
                if ($this->pagedata['order']['payinfo']['pay_app_id'] == 'deposit' && $arrPayments['app_id'] != 'deposit') {
                    $arrPayments['cur_money'] = $this->objMath->number_div(array($arrPayments['cur_money'], $this->pagedata['order']['cur_rate']));
                    $payed = $this->objMath->number_div(array($this->pagedata['order']['payed'], $this->pagedata['order']['cur_rate']));
                    $arrPayments['total_amount'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $payed, $this->pagedata['deposit_money']));
                }
            }
        }

        //将订单金额转换为基准货币值
        $this->pagedata['order']['cur_money'] = $this->objMath->number_div(array($this->pagedata['order']['cur_money'], $this->pagedata['order']['cur_rate']));
        $this->pagedata['order']['payed'] = $this->objMath->number_div(array($this->pagedata['order']['payed'], $this->pagedata['order']['cur_rate']));
        $this->pagedata['order']['total_amount'] = $this->objMath->number_plus(array($this->pagedata['order']['cur_money'], $this->pagedata['order']['payed']));
        //end

        if ($this->pagedata['order']['payinfo']['pay_app_id'] == '-1') {
            $this->pagedata['order']['payinfo']['pay_name'] = app::get('b2c')->_('货到付款');
        }

        $this->pagedata['combination_pay'] = 'false';
        if ($this->pagedata['order']['payinfo']['pay_app_id'] == 'deposit') {
            $this->pagedata['combination_pay'] = $pay_online ? app::get('b2c')->getConf('site.combination.pay') : 'false';
        }

        $objCur = app::get('ectools')->model('currency');
        $aCur = $objCur->getFormat($this->pagedata['order']['currency']);
        $this->pagedata['order']['cur_def'] = $aCur['sign'];

        $this->pagedata['return_url'] = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_paycenter', 'act' => 'result_pay'));
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['form_action'] = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_paycenter', 'act' => 'dopayment', 'arg0' => 'order'));
        $obj_order_payment_html = kernel::servicelist('b2c.order.pay_html');
        $app_id = 'b2c';
        if ($obj_order_payment_html) {
            foreach ($obj_order_payment_html as $obj) {
                $obj->gen_data($this, $app_id);
            }
        }
        $this->pagedata['newOrder'] = $newOrder;
        $this->set_tmpl('order_index');
        $this->page('site/paycenter/payments.html', false, $app_id);
    }

   
    /**
     * 生成支付单据处理
     * @params string - pay_object ('order','recharge','joinfee')
     * @return null
     */
    public function dopayment() {

        $pay_object = empty($_POST['pay_object']) ? 'order' : $_POST['pay_object'];
        $combination_pay = false;

        $payment['order_id'] = trim($_POST['payment_order_id']);
        $payment['def_pay']['cur_money'] = trim($_POST['payment_cur_money']);
        $payment['def_pay']['pay_app_id'] = trim($_POST['payment_pay_app_id']);
        $payment['memo'] = trim($_POST['payment_memo']);

        
        //统一下单结尾

        unset($_POST);

        $_POST['payment'] = $payment;

        if ($pay_object) {
            $member_id = kernel::single('b2c_user_object')->get_member_id();
            $objOrders = $this->app->model('orders');
            $objPay = kernel::single('ectools_pay');
            //return json_encode($objPay);
            $objMath = kernel::single('ectools_math');
            // 得到商店名称
            $shopName = app::get('site')->getConf('site.name');
            // Post payment information.
            $sdf = $_POST['payment'];

            unset($sdf['def_pay']);
            unset($sdf['other_online']);
            if ($combination_pay == true) {
                if ($_POST['payment']['other_online']) {
                    $sdf['pay_app_id'] = $_POST['payment']['other_online']['pay_app_id'];
                    $sdf['cur_money'] = $_POST['payment']['other_online']['cur_money'] ? $_POST['payment']['other_online']['cur_money'] : 0;
                }
            } else {
                if ($_POST['payment']['def_pay']) {
                    $sdf['pay_app_id'] = $_POST['payment']['def_pay']['pay_app_id'];
                    $sdf['cur_money'] = $_POST['payment']['def_pay']['cur_money'] ? $_POST['payment']['def_pay']['cur_money'] : 0;
                }
            }

            if ($member_id)
                $sdf['member_id'] = $member_id;

            if (!$sdf['pay_app_id']) {
//                 $this->splash('failed',null, app::get('b2c')->_('支付方式不能为空！'));
                kernel::single('base_rpc_service')->send_user_error('pay_app_id_null', '支付方式不能为空!');
            }

            $payment_cfgs = app::get('ectools')->model('payment_cfgs');
            if (!$payment_cfgs->check_payment_open($sdf['pay_app_id'])) {
//                 $this->splash('failed',null,app::get('b2c')->_('不支持此支付方式'));
                kernel::single('base_rpc_service')->send_user_error('pay_app_id_error', '不支持此支付方式!');
            }

            $order_items = app::get('b2c')->model('order_items')->getList('name', array('order_id' => $sdf['order_id']), 0, 1);

            $sdf['body'] = $order_items[0]['name'];
            $sdf['pay_object'] = $pay_object;
            $sdf['shopName'] = $shopName;


            switch ($sdf['pay_object']) {
                case 'order':
                    $orderMemberId = app::get('b2c')->model('orders')->getList('member_id', array('order_id' => $sdf['order_id']));
                    if (!$orderMemberId[0]['member_id'] || $orderMemberId[0]['member_id'] != $member_id) {
                        //$this->splash('failed',null,app::get('b2c')->_('无效订单，不能支付'));

                        kernel::single('base_rpc_service')->send_user_error('order_id_error', '无效订单，不能支付');
                    }
                    $arrOrders = $objOrders->dump($sdf['order_id'], '*');


                    if ($arrOrders['payinfo']['pay_app_id'] != $sdf['pay_app_id']) {
                        $class_name = "";
                        $obj_app_plugins = kernel::servicelist("ectools_payment.ectools_mdl_payment_cfgs");
                        foreach ($obj_app_plugins as $obj_app) {
                            $app_class_name = get_class($obj_app);
                            $arr_class_name = explode('_', $app_class_name);
                            if (isset($arr_class_name[count($arr_class_name) - 1]) && $arr_class_name[count($arr_class_name) - 1]) {
                                if ($arr_class_name[count($arr_class_name) - 1] == $sdf['pay_app_id']) {
                                    $pay_app_ins = $obj_app;
                                    $class_name = $app_class_name;
                                }
                            } else {
                                if ($app_class_name == $sdf['pay_app_id']) {
                                    $pay_app_ins = $obj_app;
                                    $class_name = $app_class_name;
                                }
                            }
                        }
                        $strPaymnet = app::get('ectools')->getConf($class_name);
                        $arrPayment = unserialize($strPaymnet);

                        $sdf['cur_money'] = $objMath->number_multiple(array($sdf['cur_money'], $arrOrders['cur_rate']));
                        $cost_payment = $objMath->number_multiple(array($objMath->number_minus(array($objMath->number_minus(array($arrOrders['total_amount'], $arrOrders['payinfo']['cost_payment'])), $arrOrders['payed'])), $arrPayment['setting']['pay_fee']));
                        $total_amount = $objMath->number_plus(array($objMath->number_minus(array($arrOrders['total_amount'], $arrOrders['payinfo']['cost_payment'])), $cost_payment));
                        $cur_money = $objMath->number_multiple(array($total_amount, $arrOrders['cur_rate']));

                        // 更新订单支付信息
                        $arr_updates = array(
                            'order_id' => $sdf['order_id'],
                            'payinfo' => array(
                                'pay_app_id' => $sdf['pay_app_id'],
                                'cost_payment' => $objMath->number_multiple(array($cost_payment, $arrOrders['cur_rate'])),
                            ),
                            'currency' => $arrOrders['currency'],
                            'cur_rate' => $arrOrders['cur_rate'],
                            'total_amount' => $total_amount,
                            'cur_amount' => $cur_money,
                        );

                        $changepayment_services = kernel::servicelist('b2c_order.changepayment');
                        foreach ($changepayment_services as $changepayment_service) {
                            $changepayment_service->generate($arr_updates);
                        }

                        $objOrders->save($arr_updates);

                        $arrOrders = $objOrders->dump($sdf['order_id'], '*');
                        /** 需要想中心发送支付方式修改的动作 * */
                        $obj_b2c_pay = kernel::single('b2c_order_pay');
                        $obj_b2c_pay->order_payment_change($arrOrders);
                    }

                    //ajx 防止恶意修改支付金额，导致支付状态异常
                    if ($pay_object == 'order' && (!isset($_POST['payment']['other_online']) || $combination_pay == true)) {
                        $orders = $objOrders->dump($sdf['order_id']);
                        $sdf['cur_amount'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                        $orders['total_amount'] = $objMath->number_div(array($orders['cur_amount'], $orders['cur_rate']));
                        $sdf['money'] = floatval($orders['total_amount'] - $orders['payed']);
                        $sdf['currency'] = $orders['currency'];
                        $sdf['cur_money'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                        $sdf['cur_rate'] = $orders['cur_rate'];
                    }

                    // 检查是否能够支付
                    $obj_checkorder = kernel::service('b2c_order_apps', array('content_path' => 'b2c_order_checkorder'));
                    $sdf_post = $sdf;
                    $sdf_post['money'] = $sdf['cur_money'];
                    if (!$obj_checkorder->check_order_pay($sdf['order_id'], $sdf_post, $message)) {
                        //$this->result($arrOrders,$message);
                        return $this->result($arrOrders, $message);
                    }

                    if ($sdf['pay_app_id'] == 'offline') {
                        return $this->result_pay($arrOrders['order_id'], true);
                    }

                    if (!$sdf['pay_app_id'])
                        $sdf['pay_app_id'] = $arrOrders['payinfo']['pay_app_id'];

                    $sdf['currency'] = $arrOrders['currency'];
                    $sdf['total_amount'] = $arrOrders['total_amount'];
                    $sdf['payed'] = $arrOrders['payed'] ? $arrOrders['payed'] : '0.000';
                    $sdf['money'] = $objMath->number_div(array($sdf['cur_money'], $arrOrders['cur_rate']));

                    $sdf['payinfo']['cost_payment'] = $arrOrders['payinfo']['cost_payment'];

                    // 相关联的id.
                    $sdf['rel_id'] = $sdf['order_id'];
                    break;
                case 'recharge':
                    // 得到充值信息
                    $sdf['rel_id'] = $sdf['member_id'];
                    break;
                case 'joinfee':
                    // 得到加盟费信息
                    break;
                default:
                    // 其他的卡充值
                    $sdf['rel_id'] = $sdf['rel_id'];
                    break;
            }
            
            $payment_id = $sdf['payment_id'] = $objPay->get_payment_id($sdf['rel_id']);
            if ($sdf['pay_app_id'] == 'deposit') {
                $member_info = app::get('b2c')->model('members')->getList('advance,advance_freeze', array('member_id' => $member_id));
                $sdf['return_url'] = "";
            } else {
                
            }

            $sdf['status'] = 'ready';
            // 需要加入service给其他实体和虚拟卡
            $obj_prepaid = kernel::service('b2c.prepaidcards.add');
            $is_save_prepaid = false;
            if ($obj_prepaid) {
                $is_save_prepaid = $obj_prepaid->gen_charge_log($sdf);
            }
            if ($_POST['payment']['def_pay']['pay_app_id'] === 'wxpay') {
                $sdf['pay_app_type'] = 'mobile';
            }
            //return 'kkkkkkkkkkkkkk';
            $is_payed = $objPay->generate($sdf, $this, $msg);
           
            if ($is_payed == 'mwxpay' && $sdf['pay_app_id'] == 'wxpay') {
                return $this->wxpay_zhifu($sdf);
                exit();
            }
            if ($is_save_prepaid && $is_payed) {
                $obj_prepaid->update_charge_log($sdf);
            }

            if ($sdf['pay_app_id'] == 'deposit') {
                if (!$combination_pay && $sdf['combination_pay'] == 'true') {
                    $this->dopayment('order', true);
                }

                if ($is_payed) {
                    //提成既时结算
                    logger::info($sdf['order_id'] . ' Ticheng is starting ');
                    kernel::single('b2c_order_ticheng')->commission_js($sdf['order_id']);

                    return $this->result_pay($arrOrders['order_id'], true);
                    //return $arrOrders;
                } else {
                    return $this->result($arrOrders, $msg);
                }
            }

            return $is_payed;
        }
    }

    public function get_payment_money() {
        $pay_app_id = $_POST['payment']['other_online']['pay_app_id'];
        $cur_money = $_POST['payment']['other_online']['cur_money'];
        $payments = app::get('ectools')->model('payment_cfgs')->getPaymentInfo($pay_app_id);
        $objMath = kernel::single('ectools_math');
        $cost_payment = $objMath->number_multiple(array($cur_money, $payments['pay_fee']));
        $data['cur_money'] = $objMath->number_plus(array($cost_payment, $cur_money));
        $data['total_amount'] = $objMath->number_plus(array($data['cur_money'], $_POST['payment']['def_pay']['cur_money']));
        $data['cur_money'] = app::get('ectools')->model('currency')->changer($data['cur_money']);
        $data['total_amount'] = app::get('ectools')->model('currency')->changer($data['total_amount']);
        echo json_encode($data);
    }

    public function result_pay($data_id, $type = false) {
        $this->objMath = kernel::single("ectools_math");
        $arrOrders = array();
        if ($type) {
            $order_id = $data_id;
        } else {
            $billList = app::get('ectools')->model('order_bills')->getList('*', array('bill_id' => $data_id));
            $order_id = $billList[0]['rel_id'];
        }
        $arrOrders = app::get('b2c')->model('orders')->getList('order_id,payed,final_amount,cur_rate,currency,total_amount,ship_area,ship_addr,ship_zip,ship_time,ship_mobile,ship_name,pay_status', array('order_id' => $order_id));
        $arrOrders = $arrOrders[0];
        $order_quantity = app::get('b2c')->model('order_items')->getList('sum(nums) as nums', array('order_id' => $arrOrders['order_id']));
        $arrOrders['quantity'] = $order_quantity[0]['nums'];
        $this->pagedata['order'] = $arrOrders;

        $this->pagedata['msg'] = '支付成功';

        $this->pagedata['site_checkout_receivermore_open'] = $this->app->getConf('site.checkout.receivermore.open');

        return $this->pagedata;
    }

    public function result($arrOrders, $msg) {
        $arrOrders = app::get('b2c')->model('orders')->getList('order_id,payed,final_amount,cur_rate,currency,total_amount,ship_area,ship_addr,ship_zip,ship_time,ship_mobile,ship_name,pay_status', array('order_id' => $arrOrders['order_id']));
        $arrOrders = $arrOrders[0];
        $order_quantity = app::get('b2c')->model('order_items')->getList('sum(nums) as nums', array('order_id' => $arrOrders['order_id']));
        $arrOrders['quantity'] = $order_quantity[0]['nums'];

        $this->pagedata['order'] = $arrOrders;
        $this->pagedata['msg'] = $msg;

        return $this->pagedata;

//         $this->set_tmpl('splash');
//         $this->page('site/paycenter/result_failure.html');
    }

    /*     * ***-------2014-11-22,二次开发，549224868@qq.com--------------------start----------------------*** */

    public function congzhi_pay_buy() {
        $this->data_post = $this->check_input($_POST);
        if ($this->data_post['recharge_amount'] <= 0) {
            return '金额错误';
            exit();
        }
        if (empty($this->data_post['payment']['pay_app_id'])) {
            return '支付方式未选择';
            exit();
        }
        $pay_app = json_decode($this->data_post['payment']['pay_app_id']);

        $data['pay_recharge_id'] = time() . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9);
        $data['recharge_status'] = 'ready';
        $data['recharge_type'] = 'buymoney';
        /* 			return 'jjjsss77777777777';
          $data['pay_name'] = $pay_app['payment_name'];
          return 'jjjsssiiiiiii';
          $data['pay_app_id'] = $pay_app['pay_app_id'];
          return 'jjjsssklklklklk';
          $data['recharge_amount'] = $this->data_post['recharge_amount'];
          return 'jjjsssffffff';
          $data['member_id'] = $this->app->member_id;
          $data['recharge_time'] = time();
          //$data['return_url'] = '/index.php/member-balance.html';
          //$data['pay_object'] = 'recharge'; */
        return $data['pay_recharge_id'];
        if (kernel::single('microshop_mdl_pay_recharge')->insert($data)) {
            return $data;
        }
        return false;
    }

    //购物宝充值
    public function do_recharge_pay($pay_object = 'order', $combination_pay = false) {
        unset($pay_object);
        $pay_object = null;
        $pay_object = 'recharge';
        if ($this->data_post['recharge'] == 'recharge') {

            unset($_POST);
            $_POST = null;

            $_POST['payment']['member_id'] = $this->app->member_id;
            $_POST['payment']['money'] = $this->data_post['money'];
            $_POST['payment']['pay_app_id'] = $this->data_post['pay_app_id'];
            $_POST['payment']['return_url'] = '/index.php/member-balance.html';
        } else {
            return false;
        }
        if ($pay_object) {
            $member_id = kernel::single('b2c_user_object')->get_member_id();
            $objOrders = $this->app->model('orders');
            $objPay = kernel::single('ectools_pay');
            $objMath = kernel::single('ectools_math');
            // 得到商店名称
            $shopName = app::get('site')->getConf('site.name');
            // Post payment information.
            $sdf = $_POST['payment'];
            unset($sdf['def_pay']);
            unset($sdf['other_online']);

            if ($combination_pay == true) {
                if ($_POST['payment']['other_online']) {
                    $sdf['pay_app_id'] = $_POST['payment']['other_online']['pay_app_id'];
                    $sdf['cur_money'] = $_POST['payment']['other_online']['cur_money'] ? $_POST['payment']['other_online']['cur_money'] : 0;
                }
            } else {
                if ($_POST['payment']['def_pay']) {
                    $sdf['pay_app_id'] = $_POST['payment']['def_pay']['pay_app_id'];
                    $sdf['cur_money'] = $_POST['payment']['def_pay']['cur_money'] ? $_POST['payment']['def_pay']['cur_money'] : 0;
                }
            }

            if ($member_id)
                $sdf['member_id'] = $member_id;

            if (!$sdf['pay_app_id']) {
                $this->splash('failed', null, app::get('b2c')->_('支付方式不能为空！'));
            }

            $payment_cfgs = app::get('ectools')->model('payment_cfgs');
            if (!$payment_cfgs->check_payment_open($sdf['pay_app_id'])) {
                $this->splash('failed', null, app::get('b2c')->_('不支持此支付方式'));
            }
            //预售修改,在原有的基础上加上 product_id 字段获取商品id以便下面查询预售信息
            $order_items = app::get('b2c')->model('order_items')->getList('name,product_id', array('order_id' => $sdf['order_id']), 0, 1);
            $product_id = $order_items[0]['product_id'];

            $sdf['body'] = $order_items[0]['name'];
            $sdf['pay_object'] = $pay_object;
            $sdf['shopName'] = $shopName;

            switch ($sdf['pay_object']) {
                case 'order':
                    $orderMemberId = app::get('b2c')->model('orders')->getList('member_id', array('order_id' => $sdf['order_id']));
                    if (!$orderMemberId[0]['member_id'] || $orderMemberId[0]['member_id'] != $member_id) {
                        $this->splash('failed', null, app::get('b2c')->_('无效订单，不能支付'));
                    }
                    $arrOrders = $objOrders->dump($sdf['order_id'], '*');
                    //预售信息
                    $preparesell_is_actived = app::get('preparesell')->getConf('app_is_actived');
                    if ($preparesell_is_actived == 'true') {
                        $preparesell = app::get('preparesell')->model('preparesell_goods');
                        $prepare = $preparesell->getRow('*', array('product_id' => $product_id));
                        $promotion_type = $arrOrders['promotion_type'];
                    }
                    //echo '<pre>';print_r($promotion_type);exit();
                    //线下支付
                    if ($sdf['pay_app_id'] == 'offline') {
                        if (isset($sdf['member_id']) && $sdf['member_id'])
                            $this->begin(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'orderdetail', 'arg0' => $sdf['order_id']));
                        else
                            $this->begin(array('app' => 'b2c', 'ctl' => 'site_order', 'act' => 'index', 'arg0' => $sdf['order_id']));
                    }

                    if ($arrOrders['payinfo']['pay_app_id'] != $sdf['pay_app_id']) {
                        $class_name = "";
                        $obj_app_plugins = kernel::servicelist("ectools_payment.ectools_mdl_payment_cfgs");
                        foreach ($obj_app_plugins as $obj_app) {
                            $app_class_name = get_class($obj_app);
                            $arr_class_name = explode('_', $app_class_name);
                            if (isset($arr_class_name[count($arr_class_name) - 1]) && $arr_class_name[count($arr_class_name) - 1]) {
                                if ($arr_class_name[count($arr_class_name) - 1] == $sdf['pay_app_id']) {
                                    $pay_app_ins = $obj_app;
                                    $class_name = $app_class_name;
                                }
                            } else {
                                if ($app_class_name == $sdf['pay_app_id']) {
                                    $pay_app_ins = $obj_app;
                                    $class_name = $app_class_name;
                                }
                            }
                        }
                        $strPaymnet = app::get('ectools')->getConf($class_name);
                        $arrPayment = unserialize($strPaymnet);

                        $sdf['cur_money'] = $objMath->number_multiple(array($sdf['cur_money'], $arrOrders['cur_rate']));
                        $cost_payment = $objMath->number_multiple(array($objMath->number_minus(array($objMath->number_minus(array($arrOrders['total_amount'], $arrOrders['payinfo']['cost_payment'])), $arrOrders['payed'])), $arrPayment['setting']['pay_fee']));
                        $total_amount = $objMath->number_plus(array($objMath->number_minus(array($arrOrders['total_amount'], $arrOrders['payinfo']['cost_payment'])), $cost_payment));
                        $cur_money = $objMath->number_multiple(array($total_amount, $arrOrders['cur_rate']));

                        // 更新订单支付信息
                        $arr_updates = array(
                            'order_id' => $sdf['order_id'],
                            'payinfo' => array(
                                'pay_app_id' => $sdf['pay_app_id'],
                                'cost_payment' => $objMath->number_multiple(array($cost_payment, $arrOrders['cur_rate'])),
                            ),
                            'currency' => $arrOrders['currency'],
                            'cur_rate' => $arrOrders['cur_rate'],
                            'total_amount' => $total_amount,
                            'cur_amount' => $cur_money,
                        );

                        $changepayment_services = kernel::servicelist('b2c_order.changepayment');
                        foreach ($changepayment_services as $changepayment_service) {
                            $changepayment_service->generate($arr_updates);
                        }

                        $objOrders->save($arr_updates);

                        $arrOrders = $objOrders->dump($sdf['order_id'], '*');
                        /** 需要想中心发送支付方式修改的动作 * */
                        $obj_b2c_pay = kernel::single('b2c_order_pay');
                        $obj_b2c_pay->order_payment_change($arrOrders);
                    }

                    //ajx 防止恶意修改支付金额，导致支付状态异常
                    if ($pay_object == 'order' && (!isset($_POST['payment']['other_online']) || $combination_pay == true)) {
                        $orders = $objOrders->dump($sdf['order_id']);
                        //echo '<pre>';print_r($prepare['end_time']);exit();
                        //预售商品价格
                        if ($promotion_type == 'prepare' && $prepare['preparesell_price'] < $prepare['promotion_price']) {
                            if ($prepare['status'] != 'true') {
                                $this->splash('failed', null, app::get('b2c')->_('该商品的预售没有开启！'));
                            }
                            if ($prepare['begin_time'] < time() && time() < $prepare['end_time'] && $prepare['status'] == 'true') {
                                $sdf['cur_amount'] = $objMath->number_minus(array($prepare['preparesell_price'], $orders['payed']));
                                $orders['total_amount'] = $objMath->number_div(array($prepare['preparesell_price'], $orders['cur_rate']));
                                $sdf['money'] = floatval($prepare['preparesell_price'] - $orders['payed']);
                                $sdf['currency'] = $orders['currency'];
                                $sdf['cur_money'] = $objMath->number_minus(array($prepare['preparesell_price'], $orders['payed']));
                                $sdf['cur_rate'] = $orders['cur_rate'];
                            } elseif ($prepare['begin_time_final'] < time() && time() < $prepare['end_time_final'] && $prepare['status'] == 'true') {
                                $sdf['cur_amount'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                                $orders['total_amount'] = $objMath->number_div(array($orders['cur_amount'], $orders['cur_rate']));
                                $sdf['money'] = floatval($orders['total_amount'] - $orders['payed']);
                                $sdf['currency'] = $orders['currency'];
                                $sdf['cur_money'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                                $sdf['cur_rate'] = $orders['cur_rate'];
                            } elseif (($prepare['begin_time'] > time() || time() > $prepare['end_time'] ) && $prepare['status'] == 'true') {
                                $this->splash('failed', null, app::get('b2c')->_('支付订金时间还没有到，或者支付订金时间已过！'));
                            } elseif (($prepare['begin_time_final'] < time() || time() < $prepare['end_time_final'] ) && $prepare['status'] == 'true') {
                                $this->splash('failed', null, app::get('b2c')->_('支付尾款时间还没有到，或者尾款订金时间已过！'));
                            } else {
                                $this->splash('failed', null, app::get('b2c')->_('支付时间还没有到，或者支付时间已过！'));
                            }
                        } elseif ($promotion_type == 'prepare' && $prepare['preparesell_price'] == $prepare['promotion_price']) {
                            if ($prepare['status'] != 'true') {
                                $this->splash('failed', null, app::get('b2c')->_('该商品的预售没有开启！'));
                            }
                            if ($prepare['begin_time'] < time() && time() < $prepare['end_time'] && $prepare['status'] == 'true') {
                                $sdf['cur_amount'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                                $orders['total_amount'] = $objMath->number_div(array($orders['cur_amount'], $orders['cur_rate']));
                                $sdf['money'] = floatval($orders['total_amount'] - $orders['payed']);
                                $sdf['currency'] = $orders['currency'];
                                $sdf['cur_money'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                                $sdf['cur_rate'] = $orders['cur_rate'];
                            } else {
                                $this->splash('failed', null, app::get('b2c')->_('支付时间还没有到，或者支付时间已过！'));
                            }
                        }
                        //以前的
                        else {
                            $sdf['cur_amount'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                            $orders['total_amount'] = $objMath->number_div(array($orders['cur_amount'], $orders['cur_rate']));
                            $sdf['money'] = floatval($orders['total_amount'] - $orders['payed']);
                            $sdf['currency'] = $orders['currency'];
                            $sdf['cur_money'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                            $sdf['cur_rate'] = $orders['cur_rate'];
                        }
                    }

                    // 检查是否能够支付
                    $obj_checkorder = kernel::service('b2c_order_apps', array('content_path' => 'b2c_order_checkorder'));
                    $sdf_post = $sdf;
                    $sdf_post['money'] = $sdf['cur_money'];
                    if (!$obj_checkorder->check_order_pay($sdf['order_id'], $sdf_post, $message)) {
                        $this->result($arrOrders, $message);
                        return;
                    }

                    if ($sdf['pay_app_id'] == 'offline') {
                        $this->result_pay($arrOrders['order_id'], true);
                        return;
                    }

                    if (!$sdf['pay_app_id'])
                        $sdf['pay_app_id'] = $arrOrders['payinfo']['pay_app_id'];

                    $sdf['currency'] = $arrOrders['currency'];
                    $sdf['total_amount'] = $arrOrders['total_amount'];
                    $sdf['payed'] = $arrOrders['payed'] ? $arrOrders['payed'] : '0.000';
                    $sdf['money'] = $objMath->number_div(array($sdf['cur_money'], $arrOrders['cur_rate']));

                    $sdf['payinfo']['cost_payment'] = $arrOrders['payinfo']['cost_payment'];

                    // 相关联的id.
                    $sdf['rel_id'] = $sdf['order_id'];
                    break;
                case 'recharge':
                    // 得到充值信息
                    $sdf['rel_id'] = $sdf['member_id'];
                    break;
                case 'joinfee':
                    // 得到加盟费信息
                    break;
                default:
                    // 其他的卡充值
                    $sdf['rel_id'] = $sdf['rel_id'];
                    break;
            }

            $payment_id = $sdf['payment_id'] = $objPay->get_payment_id($sdf['rel_id']);
            if ($sdf['pay_app_id'] == 'deposit') {
                $member_info = app::get('b2c')->model('members')->getList('advance,advance_freeze', array('member_id' => $member_id));
                $sdf['return_url'] = "";
            } else {
                if (!$sdf['return_url'])
                    $sdf['return_url'] = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_paycenter', 'act' => 'result_pay', 'arg0' => $payment_id));
            }

            $sdf['status'] = 'ready';
            // 需要加入service给其他实体和虚拟卡
            $obj_prepaid = kernel::service('b2c.prepaidcards.add');
            $is_save_prepaid = false;
            if ($obj_prepaid) {
                $is_save_prepaid = $obj_prepaid->gen_charge_log($sdf);
            }

            $is_payed = $objPay->generate($sdf, $this, $msg);
            if ($is_save_prepaid && $is_payed) {
                $obj_prepaid->update_charge_log($sdf);
            }

            if ($sdf['pay_app_id'] == 'deposit') {
                if (!$combination_pay && $sdf['combination_pay'] == 'true') {
                    $this->dopayment('order', true);
                }

                if ($is_payed) {
                    $this->result_pay($arrOrders['order_id'], true);
                } else
                    $this->result($arrOrders, $msg);
            }
        }
    }

    //预存余额充值到购物宝。
    public function yue_to_recharge_pay() {

        if ($this->data_post['member_id'] == $this->app->member_id && $this->data_post['recharge'] == 'yu_e') {
            if ($this->data_post['money'] <= 0) {
                $this->splash('failed', null, app::get('b2c')->_('交易金额出错!'));
            }

            $yu_e = kernel::single('b2c_mdl_members')->getList('advance', array('member_id' => $this->app->member_id));

            if ($yu_e[0]['advance'] < $this->data_post['money']) {
                $this->splash('failed', null, app::get('b2c')->_('预存款余额不足!'));
            }
//return $this->data_post['money'];	
            $data['member_id'] = $this->app->member_id;
            $data['mtime'] = time();
            $data['message'] = '预存款充值到购物宝';
            $data['explode_money'] = $this->data_post['money'];
            $data['money'] = $this->data_post['money'];
            $data['member_advance'] = $yu_e[0]['advance'] - $this->data_post['money'];
            //$this->begin();
            $res = kernel::single('b2c_mdl_member_advance')->insert($data);
            $res1 = kernel::single('b2c_mdl_members')->update(array('advance' => $data['member_advance']), array('member_id' => $this->app->member_id));

            $Microshop = kernel::single('microshop_mdl_shop');
            $buymoney = $Microshop->getList('buymoney', array('member_id' => $this->app->member_id));
            $res2 = $Microshop->update(array('buymoney' => $buymoney[0]['buymoney'] + $this->data_post['money']), array('member_id' =>
                $this->app->member_id));
            if ($res1 && $res && $res2) {
                return '充值成功';
            }
        } else {
            return false;
        }
    }

    /*
      过滤POST来的数据,基于安全考虑,会把POST数组中带HTML标签的字符过滤掉
     */

    private function check_input($data) {
        $aData = $this->arrContentReplace($data);
        return $aData;
    }

    private function arrContentReplace($array) {
        if (is_array($array)) {
            foreach ($array as $key => $v) {
                $array[$key] = $this->arrContentReplace($array[$key]);
            }
        } else {
            $array = strip_tags($array);
        }
        return $array;
    }

    protected function wxpay_zhifu($sdf) {
        if (!empty($sdf)) {
            //重组支付数据
            $package = $this->get_package($sdf);
            $app_signature = $this->get_app_signature($package);
            $data = array(
                'traceid' => $this->app->member_id,
                'noncestr' => $this->get_rand_str(),
                'package' => $package,
                'timestamp' => time(),
                'app_signature' => $app_signature,
                'sign_method' => 'sha1',
            );
            return array_merge($data, $this->get_wxpay_info(), $sdf);
        }
        return 'error';
    }

    public function get_wxpay_info() {
        return app::get('weixin')->getConf('weixin_open_setting');
    }

    public function get_rand_str($length = 12) {
        // 密码字符集，可任意添加你需要的字符  
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $password;
    }

    //获取package
    protected function get_package($sdf) {

        $open_weixin = $this->get_wxpay_info();
        return $data = array(
            'bank_type' => 'WX',
            'body' => $sdf['body'],
            'fee_type' => 1,
            'input_charset' => 'UTF-8',
            'notify_url' => 'http://www.zfjk800.com/index.php/openapi/weixin/wxpay',
            'out_trade_no' => $sdf['order_id'],
            'partner' => $open_weixin['partnerid'],
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
            'total_fee' => $sdf['money'] * 100,
            'trade_type' => 'APP',
            'attach'=>$sdf['payment_id'],
        );
        $str = '';
        foreach ($data as $key => $val) {
            $str .= $key . '=' . $val . '&';
        }
        $partnerKey = 'key=' . $open_weixin['partnerKey'];
        $sign = strtoupper(md5($str . $partnerKey));
        $string = '';
        foreach ($data as $k => $v) {
            $string .= $k . '=' . urlencode($v) . '&';
        }
        return $string . 'sign=' . $sign;
    }

    //获取app_signature
    public function get_app_signature($package) {
        $open_weixin = $this->get_wxpay_info();
        $data = array(
            'appid' => $open_weixin['appid'],
            'noncestr' => $this->get_rand_str(),
            'package' => $package,
            'appkey' => $open_weixin['paysignkey'],
            'timestamp' => time(),
            'traceid' => $this->app->member_id,
        );
        $str = 'appid=' . $open_weixin['appid'] . '&';
        $str .= 'appkey=' . $open_weixin['paysignkey'] . '&';
        $str .= 'noncestr=' . $this->get_rand_str() . '&';
        $str .= 'package=' . $package . '&';
        $str .= 'timestamp=' . time() . '&';
        $str .= 'traceid=' . $this->app->member_id;
        return $paysign = sha1($str);
    }

    /*     * ***-------2014-11-22,二次开发，549224868@qq.com--------------------end----------------------*** */
}
