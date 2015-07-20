<?php

/**
 * 分销相关的APP 接口
 * at 2015-03-03  author 549224868@qq.com
 */
class mobileapi_rpc_fenxiao extends mobileapi_frontpage {

    private $data_post; //POST 数组
    private $page_nums; //每页多少条
    private $offset;    //查询条数的偏移量
    private $n_page;    //第几页
    private $where = 'WHERE 1'; //sql where条件语句

    public function __construct($app) {
        parent::__construct($app);
        $this->app = $app;
        $this->verify_member(); //登录验证
        $this->member = $this->get_current_member();
        $this->app->rpcService = kernel::single('base_rpc_service');
        $this->pagesize = 10;

        if (!empty($_POST)) {
            unset($_POST['sign']);
            unset($_POST['direct']);
            unset($_POST['method']);
            unset($_POST['date']);
            $this->data_post = $this->check_input($_POST);
            //初始化分页处理
            if ($this->data_post['page_no'] > 0) {
                $this->data_post['n_page'] = $this->data_post['page_no'];
            }
            $this->page_nums = $this->data_post['page_nums'] ? $this->data_post['page_nums'] : 10;
            $this->n_page = $this->data_post['n_page'] ? $this->data_post['n_page'] : 1;
            $this->offset = ($this->n_page - 1) * $this->page_nums;
            if ($this->data_post['order'] == 'null') {
                $this->data_post['orderby'] = '';
            }
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

    //获取提成记录
    public function get_royalty_records($order_by) {
        $Commadv = kernel::single('b2c_mdl_commission_advance');
        $filter = array('member_id' => $this->app->member_id);
        if ($order_by['order_by'] == 'order_no') {
            $filter['status'] = 'frozen';
        }
        if ($order_by['order_by'] == 'order_yes') {
            $filter['status'] = 'activation';
        }
        $result = $Commadv->getList('*', $filter, $this->offset, $this->page_nums);

        $data = $Commadv->details($result);
        return $data;
    }

    //获取提成记录详情
    public function get_royalty_detail() {
        if ($this->data_post['comm_log_id'] > 0 && $this->data_post['log_id']) {
            $result = kernel::single('b2c_mdl_commission_log')->getList('*', array('log_id' => $this->data_post['comm_log_id']));
            return $result[0];
        }
        return false;
    }

    /**
     * 移动端用户签到功能
     * @param string 
     */
    public function signin() {
        //实例化一个sdb_b2c_sign类
        $signModel = app::get('b2c')->model('sign');
        $data = array(
            'sign_type' => 'app',
            'create_time' => time(),
            'year' => date('Y', time()),
            'months' => date('m', time()),
            'day' => date('d', time()),
            'member_id' => $this->app->member_id,
        );
        $fff = array(
            'year' => date('Y', time()),
            'months' => date('m', time()),
            'member_id' => $this->app->member_id,
            'day' => date('d', time()),
            'create_time|than' => strtotime("today"),
            'create_time|lthan' => strtotime(date('Y-m-d', strtotime('+1 day'))),
        );
        if ($signModel->getList('sign_id', $fff)) {
            return '每天只能签到一次!';
        } else {
            if ($signModel->insert($data)) {
                //$res = kernel::single('b2c_order_ticheng')->sign_jilu($this->app->member_id);
                return 'ok';
            }
            $this->Rpc->send_user_error('4003', '参数错误');
        }
    }

    //签到图获取 array year  months
    public function get_sign_tu() {
        $res = $this->signin();
        if ($res == 'ok') {
            $data['is_sign'] = $res;
        }
        $y = $this->data_post['year']; //指定的年
        $m = $this->data_post['months']; //指定的月
        if ($y <= 2014 && $m <= 0 && $m > 12 && $y > date('Y')) {
            return 'time error';
        }
        $d = date('j', mktime(0, 0, 1, ($m == 12 ? 1 : $m + 1), 1, ($m == 12 ? $y + 1 : $y)) - 24 * 3600);
        $this->data_post;
        $where = ' WHERE  `year` = ' . $y;
        $where .= ' AND  `months` = ' . $m;
        $where .= ' AND  `member_id` = ' . $this->app->member_id;
        $where .= ' AND `create_time` > ' . strtotime($y . '-' . $m . '-1') . ' AND `create_time` < ' . strtotime($y . '-' . $m . '-' . $d);
        $sql = 'SELECT `create_time` FROM sdb_b2c_sign ' . $where;
        $result = kernel::database()->select($sql);
        if ($result) {
            foreach ($result as $val) {
                $data['date'][] = date('d', $val['create_time']);
            }
            $res = kernel::single('b2c_mdl_members')->getList('sign_num', array('member_id' => $this->app->member_id));
            $money = 0;
            for ($ii = $res[0]['sign_num']; $ii > 0; $ii--) {
                $money += $ii;
            }
            $data['presentMoney'] = $res[0]['sign_num'];
            $data['lastdays'] = app::get('b2c')->getConf('sign.days') - $res[0]['sign_num'];
            $data['totalMoney'] = $money;
            return $data;
        }
        return 'kong';
    }

    //签到规则
    public function get_sign_rule() {
        $res = kernel::single('content_mdl_article_bodys')->getList('content', array('article_id' => 97));
        if ($res) {
            return $res[0];
        }
        return false;
    }


    /* 代理申请接口
     * 
     * 
     */

    public function daili_apply() {
        if(!empty($this->data_post['remark']) && !empty($this->data_post['mobile'])){
            $mem = kernel::single('pam_mdl_members')->dump(array('login_account'=>$this->data_post['mobile']),'member_id');
            if($mem['member_id'] != $this->app->member_id){
                if($mem['member_id'] > 0 ){
                    $Apply = kernel::single('b2c_mdl_apply');
                    if(!$Apply->dump(array('faqi_member_id'=>$this->app->member_id,'member_id'=>$mem['member_id'],'is_check|noequal'=>'ok'),'apply_id')){
                        $data = array(
                            'faqi_member_id' =>  $this->app->member_id,
                            'member_id' => $mem['member_id'],
                            'remark' => $this->data_post['remark'].' : '.$this->data_post['mobile'],
                            'create_time' => time(),
                        );
                        if($Apply->insert($data)){
                            return '提交申请成功';
                        }else{
                            $msg = '申请失败，请重试';
                        }         
                    }else{
                        $msg = '您的申请正在处理中，请耐心等待';
                    }
                }else{
                   $msg = '申请的手机号不存在';
                }            
            }else{
                $msg = '自己不能申请自己';
            }
        }else{
            $msg = '参数错误';
        }
        $this->Rpc->send_user_error('4003', $msg);
    }
    
    /* 代理申请列表 
     *  
     */
    public function get_apply_list(){
        $res = kernel::single('b2c_mdl_apply')->getList('*',array('faqi_member_id'=>$this->app->member_id));
        if(is_array($res)){
            $Members = kernel::single('b2c_mdl_members');
            foreach ($res as $k=>$v){
                $res1 = $Members->dump(array('member_id'=>$v['member_id']),'nickname,mobile');
                $res[$k]['nickname'] = $res1['nickname'];
                $res1 = $Members->dump(array('member_id'=>$v['faqi_member_id']),'nickname,mobile');
                $res[$k]['dai_nickname'] = $res1['nickname'];
                switch ($v['is_check']){
                    case 'wei': $res[$k]['status'] = '未查看'; break;
                    case 'yi': $res[$k]['status'] = '已查看'; break;
                    case 'ok': $res[$k]['status'] = '申请成功'; break;
                    case 'no': $res[$k]['status'] = '申请失败'; break;
                }
            }
        }
        return $res;
    }
    /*
     * 获取积分列表
     */
    public function get_point_list(){
        $points = kernel::single('b2c_mdl_member_point')->getList('point,addtime,change_point,reason',array('member_id'=>$this->app->member_id),$this->offset, $this->page_nums);
    
        return $points;
    }
    /*
     * 未购买的下线用户
     */
    public function not_to_buy_member(){
        $filter = array('invite_mem_fid'=>$this->app->member_id,'order_num'=>0);
        $members = kernel::single('b2c_mdl_members')->getList('nickname,member_id,mobile',$filter);
        return $members;
    }

    /*
     * 获取银行类型
     */
    public function get_bank_names(){
        
        return explode(':', app::get('b2c')->getConf('system.bank.name'));
    }
}

?>