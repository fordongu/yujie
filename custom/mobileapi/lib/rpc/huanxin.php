<?php

/**
 * 	app端 移动端 环信相关接口
 * @author 549224868@qq.com  at 2015-01-21
 *        
 */
class mobileapi_rpc_huanxin extends mobileapi_frontpage {

    private $huanxin_user;
    private $data_post;
    private $page_nums;
    private $offset;
    private $n_page;
    private $where = 'WHERE 1';
    private $default_avatar = 'http://up.ekoooo.com/uploads2/tubiao/7/2008871975753177808.png';

    function __construct($app) {
        parent::__construct($app);
        $this->app = $app;
        $this->verify_member();
        $this->member = $this->get_current_member();
        $this->app->rpcService = kernel::single('base_rpc_service');
        $this->huanxin_user = kernel::single('b2c_mdl_huanxin_user');

        if (!empty($_POST)) {
            $this->data_post = $this->check_input($_POST);
            unset($_POST);
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
     *  过滤POST来的数据,基于安全考虑,会把POST数组中带HTML标签的字符过滤掉
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

    private function checkInt($value) {
        if ($value <= 0) {
            return false;
            eixt();
        }
    }

    private function checkString($value) {
        if (empty($value)) {
            return false;
            eixt();
        }
    }

    private function checkArray($value) {
        if (!is_array($value)) {
            return false;
            eixt();
        }
    }

    /*
     * 	member_id  
     * 	环信登录帐号密码获取
     */

    public function get_huanxin() {
        if ($res = kernel::single('b2c_mdl_huanxin_user')->getList('username,password,nickname', array('member_id' => $this->app->member_id))) {
            return $res[0];
        }
        $this->Rpc->send_user_error('4003', '账号错误，请联系管理员');
    }

    /*
     * 	添加好友
     */

    public function add_friend() {
        $this->checkString($this->data_post['username']);
        $be_member_id = kernel::single('b2c_mdl_member_to_huanxin')->get_member_id($this->data_post['username']);
        if (kernel::single('b2c_huanxin_registhuanxin')->save_relative($this->app->member_id, $be_member_id)) {
            return '添加成功';
        }
        $this->Rpc->send_user_error('4003', '添加失败');
    }

    //搜索好友  user_id 
    public function search_friend() {
        //return $this->data_post['username'];
        if (!empty($this->data_post['username'])) {
            if (mb_strlen($this->data_post['username']) > 20) {
                $this->Rpc->send_user_error('4003', '搜索的字符过长');
                exit();
            }
            $Members = kernel::single('b2c_mdl_members');
            $base = kernel::single('base_storager');
            //判断手机
            if (preg_match("/^1[34578]{1}[0-9]{9}$/", $this->data_post['username'])) {
                $res = $Members->getList('member_id,mobile,nickname,avatar', array('mobile' => $this->data_post['username']));
            }
            if (is_array($res)) {
                $filter = array('member_id|noequal' => $res[0]['member_id'], 'nickname|head' => $this->data_post['username']);
                $res[0]['avatar'] = $base->image_path($res[0]['avatar']);
            } else {
                $res = array();
                $filter = array('nickname|head' => $this->data_post['username']);
            }
            //return $filter;
            $res1 = $Members->getList('member_id,mobile,nickname,avatar', $filter);
            if (is_array($res1)) {
                $data = array_merge($res, $res1);
                foreach ($data as $k => $v) {
                    $data[$k]['avatar'] = $base->image_path($v['avatar']);
                    if (empty($v['avatar'])) {
                        $img = $base->image_path(app::get('b2c')->getConf('site.avatar'));
                    } else {
                        $img = $base->image_path($v['avatar']);
                    }
                    $data[$k]['avatar'] = $img;
                    $data[$k]['username'] = $this->huanxin_user->getusername($v['member_id']);
                }
                return $data;
            } else {
                $msg = '抱歉，没有找到';
            }
        } else {
            $msg = '空值，不能搜索';
        }
        $this->Rpc->send_user_error('4003', $msg);
    }

    /* 根据username 获取 昵称和头像
     * 
     * 
     */

    public function get_userinfo() {        
        if (!empty($this->data_post['data'])) {
            $data_post = rtrim($this->data_post['data'],']');
            $data_post = ltrim($data_post,'[');
            $data_post = explode(',',$data_post);
            $data = array();
            $Members = kernel::single('b2c_mdl_members');
            $base = kernel::single('base_storager');  
            foreach ($data_post as $k => $v) {                
                $member_id = $this->huanxin_user->getmember_id($v);
                if($member_id <= 0){
                    continue; 
                }
                $res = $Members->dump(array('member_id' => $member_id), 'member_id,nickname,mobile,avatar');
                if (empty($res['avatar'])) {
                    $img = $base->image_path(app::get('b2c')->getConf('site.avatar'));
                } else {
                    $img = $base->image_path($res['avatar']);
                }
                $res['username'] = $v;
                $res['avatar'] = $img;
                $data[$k] = $res;
            }
            return $data;
        }
        $this->Rpc->send_user_error('4003', '参数错误');
    }

    /*
     * 	获取 环信username
     */

    public function get_username($member_id) {
        $this->checkInt($member_id);
        return $this->huanxin_user->getusername($member_id);
    }

    /*
     * 	获取 member_id
     */

    public function get_member_id($username) {
        $this->checkString($username);
        return $this->huanxin_user->getmember_id($username);
    }
    
    /*
     * 判断好友关系
     */
    

}

?>
