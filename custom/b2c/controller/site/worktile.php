<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class b2c_ctl_site_worktile {

    public function __construct() {
        header("Content-type:text/html;charset=utf-8");
        $arr = array('127.0.0.1', '120.25.223.104', '14.120.232.232');
        if (in_array($arr, $_SERVER['REMOTE_ADDR'])) {
            kernel::single('desktop_controller')->splash();
            exit();
        }
    }

    public function index() {
        die('页面不存在');
    }

    public function renwu_thaw() {
        $Memadv = kernel::single('b2c_mdl_member_advance');
        $Members = kernel::single('b2c_mdl_members');
        $Thawlog = kernel::single('b2c_mdl_thaw_log');
        $Commadv = kernel::single('b2c_mdl_commission_advance');
        $Commlog = kernel::single('b2c_mdl_commission_log');
        $Orders = kernle::single('b2c_mdl_orders');
        $days = time(); //- 7 * 3600;
        $filter = array(
            'status' => 'frozen',
            'create_time|lthan' => $days,
        );
        $orders = $Commadv->getList('commission_nums,member_id,comm_log_id,log_id,remark', $filter);
        if ($orders) {
            foreach ($orders as $order) {
                //判断订单是否完成
                $order_id = $Commlog->dump(array('log_id'=>$order['comm_log_id']),'order_id,tc_type');
                $order_id = $order_id['order_id'];
                $order_status = $Orders->dump(array('order_id'=>$order_id),'status');
                if($order_status['status'] != 'finish'){
                    //解冻记录
                    $data = array(
                        'comm_adv_log_id' => $order['log_id'],
                        'order_id'=>$order_id,
                        'result' => 'error',
                        'create_time' => time(),
                        'remark' => '任务时间:' . date('Y-m-d H:i:s') . ' 该订单没有完成,不能进行解冻操作',
                    );
                    $Thawlog->insert($data);
                    echo '订单号:'.$order_id.' 该订单没有完成,不能进行解冻操作<hr/>';
                }else{
                    $advance = $Memadv->get($order['member_id']); //会员余额
                    $shop_adv = $Memadv->get_shop_advance(); //商店余额
                    //会员资金记录
                    $data = array(
                        'member_id' => $order['member_id'],
                        'money' => $order['commission_nums'],
                        'mtime' => time(),
                        'message' => '提成('.$order_id['tc_type'].')',
                        'payment_id' => $order['log_id'],
                        'order_id' => $order_id,
                        'memo' => json_encode($order),
                        'import_money' => $order['commission_nums'],
                        'member_advance' => $advance + $order['commission_nums'],
                        'shop_advance' => $shop_adv + $order['commission_nums'],
                    );
                    $Memadv->insert($data);   
                    
                     //会员余额 和 冻结资金 修改
                    $filter = array('member_id' => $order['member_id']);
                    $Members->add_commission($order['member_id'],(0 - $order['commission_nums']));
                    
                    //解冻记录
                    $data = array(
                        'comm_adv_log_id' => $order['log_id'],
                        'order_id'=>$order_id,
                        'result' => 'error',
                        'create_time' => time(),
                        'remark' => '会员余额修改失败,提成解冻失败',
                    );
                    $Thawlog->insert($data);
                    echo '订单号:'.$order_id.' 该订单解冻成功！<hr/>';
                }   
            }
        } else {
            //解冻记录
            $data = array(
                'comm_adv_log_id' => 0,
                'order_id' => $order_id,
                'result' => 'succ',
                'create_time' => time(),
                'remark' => '任务时间:' . date('Y-m-d H:i:s') . ' 没有需要解冻的提成资金',
            );
            $Thawlog->insert($data);
            echo 'success,There is no need to defrost the percentage of funds !<hr>';
        }
    }

}

//end
