<?php

/*
 * 订单提成分配
 */

class b2c_order_ticheng {

    //提成计算
    public function commission_js($order_id) {
        if (empty($order_id)) {
            logger::info('分层计算订单ID为空:' . $order_id);
            return false;
        }
        logger::info('ticheng js starting : ' . $order_id);
        $orders = kernel::single('b2c_mdl_orders')->dump(array('order_id' => $order_id), 'member_id');
        $member_id = $orders['member_id'];
        $Members = kernel::single('b2c_mdl_members');


        $filter = array('member_id' => $member_id);
        $member = $Members->getList('daili_path,invite_mem_fid,super_mem_id', $filter);

        $f_mem_id = $member[0]['invite_mem_fid']; //上级会员ID
        logger::info('分层计算订单ID为空:' . var_export($member,1));
        if ($f_mem_id > 0) {
            $fmember = $Members->getList('invite_mem_fid', array('member_id' => $f_mem_id));
            $ff_mem_id = $fmember[0]['invite_mem_fid']; //上上级会员ID            
            if ($ff_mem_id > 0) {

                $ffmember = $Members->getList('invite_mem_fid', array('member_id' => $ff_mem_id));
                $fff_mem_id = $ffmember[0]['invite_mem_fid']; //上上上级会员ID
                if ($fff_mem_id > 0) {//3级齐全
                    $mem = array(
                        'daili_path' => $member[0]['daili_path'],
                        'super_mem_id' => $member[0]['super_mem_id'],
                        'mem_id' => array($member_id, '1' => $f_mem_id, '2' => $ff_mem_id, '3' => $fff_mem_id),
                    );
                    kernel::single('b2c_ticheng_jisuan')->ticheng_jisuan(3, $order_id, $mem);
                } else {//只有上上级会员ID
                    $mem = array(
                        'daili_path' => $member[0]['daili_path'],
                        'super_mem_id' => $member[0]['super_mem_id'],
                        'mem_id' => array($member_id, '1' => $f_mem_id, '2' => $ff_mem_id),
                    );
                    kernel::single('b2c_ticheng_jisuan')->ticheng_jisuan(2, $order_id, $mem);
                }
            } else {//只有上级会员ID
                $mem = array(
                    'daili_path' => $member[0]['daili_path'],
                    'super_mem_id' => $member[0]['super_mem_id'],
                    'mem_id' => array($member_id, '1' => $f_mem_id),
                );
                kernel::single('b2c_ticheng_jisuan')->ticheng_jisuan(1, $order_id, $mem);
            }
        } else {//无上级ID
            $mem = array(
                'daili_path' => '',
                'super_mem_id' => '',
                'mem_id' => array($member_id),
            );
            logger::info($member_id.' wu shang ji ID');
            kernel::single('b2c_ticheng_jisuan')->ticheng_jisuan(0, $order_id, $mem);
            
        }
    }

}
