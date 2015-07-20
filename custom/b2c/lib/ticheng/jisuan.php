<?php

/*
 * 订单提成计算
 * auther 549224868@qq.com
 */

class b2c_ticheng_jisuan {

    private $Commlog;
    private $Commadv;
    private $TichengNum;
    private $Members;
    private $Goods;

    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct() {

        $this->Commlog = kernel::single('b2c_mdl_commission_log');
        $this->Commadv = kernel::single('b2c_mdl_commission_advance');
        //$this->Orderitem = kernel::single('b2c_mdl_order_items');
        //$this->Products = kernel::single('b2c_mdl_products');
        $this->TichengNum = kernel::single('b2c_mdl_ticheng_num');
        $this->Members = kernel::single('b2c_mdl_members');
        $this->Goods = kernel::single('b2c_mdl_goods');
    }

    //无上级
    public function ticheng_wu() {
        $member = $this->Members->get_member_type($this->mem_id[0]);
        //logger::info('biadu.com:006:' . var_export($member, 1));
        if ($member['role'] == '1') {//是代理商,默认为三星代理（代理的最高级）
            $bilie = 0;
            foreach ($this->tc_bilie['daili'] as $v) {
                $bilie += $v;
            }
            $commission = $bilie * $this->amount; //三星代理 提成

            $this->save_dai_log($this->mem_id[0], 'thr_dai', $commission, $bilie);
            //$this->insert_data_log($this->mem_id[0], 'thr_dai', $commission, $bilie,$remark);
            //后续处理 ， 直接入账 ，或者冻结 ，或者短信通知等等
            
        } else {//不是代理商
            $data = array_merge($this->data_comm, array(
                'disabled' => 'false',
                'parent_id' => 0,
                'parent_id_type' => 'off',
                'remark' => '自然客户（不是代理，无上级），不进行任何提成计算',
            ));
            $this->Commlog->insert($data);
        }
    }

    private $mem_id;
    private $amount;
    private $data_comm;
    private $daili_path;

    public function ticheng_jisuan($num, $order_id, $mem) {
        if (empty($mem)) {
            logger::warning('commission jisuan error : member info is empty');
            return false;
        }
        //分销关系的初始化
        $this->mem_id = $mem['mem_id'];
        $this->daili_path = $mem['daili_path'];
        $order_goods = kernel::single('b2c_mdl_order_items')->getList('order_id,goods_id,amount,product_id', array('order_id' => $order_id));
        //logger::info('baidu.com:001:' . var_export($order_goods, 1));
        foreach ($order_goods as $product) {
            //获取 该产品的提成系列
            $tc_id = $this->get_tc_bilie($product['goods_id']);
            //获取 该产品的总额
            $this->amount = $product['amount'];
            //初始化 日志记录数据     
            $this->data_comm = array(
                'order_id' => $order_id,
                'tc_id' => $tc_id,
                'goods_id' => $product['goods_id'],
                'product_id' => $product['product_id'],
                'member_id' => $mem['mem_id'][0],
                'create_time' => time(),
                'disabled' => 'true',
            );
            //logger::info('baidu.com:003:' . $tc_id);
            if ($tc_id <= 0) {
                $data = array(
                    'order_id' => $order_id,
                    'goods_id' => $product['goods_id'],
                    'product_id' => $product['product_id'],
                    'member_id' => $mem['mem_id'][0],
                    'create_time' => time(),
                    'disabled' => 'false',
                    'parent_id' => 0,
                    'parent_id_type' => 'off',
                    'remark' => '因相关数据缺失，该订单的产品没有进行任何分层计算',
                );
                $this->Commlog->insert($data);
                continue;
            }else{
                $this->ticheng_switch($num);
            }            
        }
    }

    private function ticheng_switch($num = 0) {
       //logger::info('baidu.com:002:' . var_export($num, 1));
        switch ($num) {
            case 0:
                $this->ticheng_wu();
                break;
            case 1:
                //代理提成计算
                $this->check_daili();
                //分层记录 ：三星分销（上级）计算 记录
                $this->save_fen_log('thr');
                //$this->ticheng_one();
                break;
            case 2:
                //代理提成计算
                $this->check_daili();
                //分层记录 ：三星分销（上级）计算 记录
                $this->save_fen_log('thr');
                // ：二星分销（上上级）计算 记录
                $this->save_fen_log('two');
                //$this->ticheng_two();
                break;
            case 3:
                //代理提成计算
                $this->check_daili();
                //分层记录 ：三星分销（上级）计算 记录
                $this->save_fen_log('thr');
                // ：二星分销（上上级）计算 记录
                $this->save_fen_log('two');
                // ：一星分销（上上上级）计算 记录
                $this->save_fen_log('one');
                // $this->ticheng_san();
                break;
        }
    }

    //根据代理路径判断代理个数
    private function check_daili() {
        //设 A(thr)->B(two)->C(one)  代理层
        $daili = explode('-', $this->daili_path);
        $member = $this->Members->get_member_type($daili[0]);
        $d_thr_commission = 0;
        $d_two_commission = 0;
        $d_one_commission = 0;
        if ($member['role'] == '1') {//A 是代理 (逻辑上是默认成立的)
            $d_thr_bilie = $this->tc_bilie['daili']['thr']; //8%
            $d_thr_commission = $d_thr_bilie * $this->amount;

            $member_1 = $this->Members->get_member_type($daili[1]);
            if ($member_1['role'] == '1' && $member_1['class'] == 'two') {//B 是二星代理
                $d_two_bilie = $this->tc_bilie['daili']['two']; //5%
                $d_two_commission = $d_two_bilie * $this->amount;

                $member_2 = $this->Members->get_member_type($daili[2]);
                if ($member_2['role'] == '1' && $member_2['class'] == 'one') {//C 是一星代理
                    $d_one_bilie = $this->tc_bilie['daili']['one']; //2%
                    $d_one_commission = $d_one_bilie * $this->amount;
                } else {//C 不是一星代理
                    $d_thr_bilie += $this->tc_bilie['daili']['one']; //8%+2%
                    $d_thr_commission = $d_thr_bilie * $this->amount;
                }
            } elseif ($member_1['role'] == '1' && $member_1['class'] == 'one') {//B 是一星代理 即C 不是代理                
                $d_thr_bilie += $this->tc_bilie['daili']['two'];
                $d_two_bilie = $this->tc_bilie['daili']['one']; //2%
                $d_two_commission = $d_two_bilie * $this->amount;
                $d_thr_commission = $d_thr_bilie * $this->amount;
            } else {//B 不是代理  即C 也不是代理
                $d_thr_bilie += ($this->tc_bilie['daili']['two'] + $this->tc_bilie['daili']['one']); //15%
                $d_thr_commission = $d_thr_bilie * $this->amount;
            }
        }
        if ($d_thr_commission > 0) {//三星代理 （最高级代理）有提成
            $this->save_dai_log($daili[0], 'thr_dai', $d_thr_commission, $d_thr_bilie);
        }
        if ($d_two_commission > 0) {//二星代理B （最高级代理的下级）有提成
            $this->save_dai_log($daili[1], 'two_dai', $d_two_commission, $d_two_bilie);
        }
        if ($d_one_commission > 0) {//一星代理 （最高级代理的下级的下级）有提成
            $this->save_dai_log($daili[2], 'one_dai', $d_one_commission, $d_one_bilie);
        }
    }

    private $tc_bilie; //代理分销提成比例存储    

    private function get_tc_bilie($goods_id) {
        $tc_id = $this->Goods->get_tc_id($goods_id);
        //logger::info('baidu.com:005:' . var_export($tc_id, 1));
        if (empty($tc_id)) {
            logger::warning('prduct waring ：goods_id ' . $goods_id . ' without tc_name'); //短信警告 该产品没有设置提成系列 
            return false;
        }
        $this->tc_bilie = array(
            'daili' => $this->TichengNum->get_ticheng_daili($tc_id),
            'fenxiao' => $this->TichengNum->get_ticheng_fenxiao($tc_id),
        );
        //logger::info('baidu.com:004:' . var_export($this->tc_bilie, 1));
        return $tc_id;
    }

    public function save_fen_log($type) {
        switch ($type) {
            case 'one':
                $tc_type = 'one_fen';
                $remark = '一星分销（上上上级）';
                $mem_id = $this->mem_id['3'];
                break;
            case 'two':
                $tc_type = 'two_fen';
                $remark = '二星分销（上上级）';
                $mem_id = $this->mem_id['2'];
                break;
            case 'thr':
                $tc_type = 'thr_fen';
                $remark = '三星分销（上级）';
                $mem_id = $this->mem_id['1'];
                break;
        }
        $commission = $this->tc_bilie['fenxiao'][$type] * $this->amount; //计算提成
        $this->insert_data_log($mem_id, $tc_type, $commission, $this->tc_bilie['fenxiao'][$type],$remark);
    }

    public function save_dai_log($parent_id, $tc_type, $commission, $bilie) {
        switch ($tc_type) {
            case 'one_dai':
                $remark = '黄金代理（最高级代理的下级的下级）';
                break;
            case 'two_dai':
                $remark = '白金代理（最高级代理的下级）';
                break;
            case 'thr_dai':
                $remark = '砖石代理（最高级代理）';
                break;
        }
        $this->insert_data_log($parent_id, $tc_type, $commission, $bilie,$remark);
    }
    

    private function insert_data_log($member_id,$tc_type,$commission,$bilie,$remark) {
        //提成日志记录
        $data = array_merge($this->data_comm, array(
            'parent_id' => $member_id,
            'tc_type' => $tc_type,
            'commission' => $commission,
            'formula' => $bilie . ' x ' . $this->amount . ' = ' . $commission,
            'remark' => $remark . '提成 ' . ($bilie * 100) . '% 成功',
        ));
        $comm_log_id = $this->Commlog->insert($data);
        //提成冻结记录
        $data = array(
            'comm_log_id' => $comm_log_id,
            'member_id' => $member_id,
            'create_time' => time(),
            'commission_nums' => $commission,
            'status' => 'frozen', //冻结
            'remark' => $remark . '提成/优惠:' . $member_id,
        );
        $this->Commadv->insert($data);
        
        //会员提成写入数据 members 提成金额 //冻结
        if($this->Members->add_commission($member_id,$commission)){ 
            logger::info('Ticheng is over');
            $content = '您的某位小伙伴儿刚又给您贡献了 '.$commission.'人民币';
            kernel::single('b2c_huanxin_registhuanxin')->send_message('6_MT2sY',array($member_id),$content,'users');
        }else{
            logger::error('Ticheng advance,commission,advance_freen save is error');
        }
        
    }

}
