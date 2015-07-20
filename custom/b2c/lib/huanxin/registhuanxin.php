<?php

/*
 * 	会员注册，环信同步类
 * 	
 * 	auth 549224868@qq.com  at 2015-01-20
 *
 */

class b2c_huanxin_registhuanxin extends b2c_huanxin_start {

    public function __construct() {
        parent::__construct();
    }

    //注册会员处理
    public function saveuser($member_id = 0, $nickname = null) {
        if ($member_id > 0) {
            if (empty($nickname)) {
                $nickname = kernel::single('b2c_mdl_members')->getList('nickname', array('member_id' => $member_id));
                $nickname = $nickname[0]['nickname'];
            }

            $data = array(
                'username' => $this->username($member_id),
                'password' => $this->password($member_id),
                'nickname' => $nickname,
            );
            //return $data;
            if ($this->huanxin->accreditRegister($data)) {
                $data['member_id'] = $member_id;
                $data['addtime'] = time();
                if ($this->huanxin_user->insert($data)) {
                    $this->save_relative($member_id, 6); //添加系统通知 账号
                    kernel::single('b2c_huanxin_group')->add_group(array(
                        'member_id' => $member_id,
                        'desc' => $nickname.'的小伙伴儿们',
                        'public' => true,
                        'groupname' => $nickname.'伙伴儿群',
                        'approval' => true,
                        'maxusers' => 300,                        
                    )); //添加伙伴儿群
                    return true;
                } else {
                    //日志记录
                    $this->huanxin_log->insert(array(
                        'member_id' => $member_id,
                        'error_type' => 'ecstore',
                        'addtime' => time(),
                        'desc' => '环信用户数据添加成功，ECstore和环信的对应关系数据加入失败',
                    ));
                }
            } else {
                //日志记录
                $this->huanxin_log->insert(array(
                    'member_id' => $member_id,
                    'error_type' => 'error',
                    'addtime' => time(),
                    'desc' => '环信用户数据添加失败，ECstore和环信的对应关系数据添加失败',
                ));
            }
        }
        return false;
    }

    //添加好友关系
    public function save_relative($member_id, $be_member_id) {
        if ($member_id > 0 && $be_member_id > 0) {
            $username = $this->huanxin_user->getusername($member_id);
            $be_username = $this->huanxin_user->getusername($be_member_id);
            if ($this->huanxin->addFriend($username, $be_username)) {
                //环信添加好友成功
                $data = array(
                    'member_id' => $member_id,
                    'be_member_id' => $be_member_id,
                    'addtime' => time(),
                    'remark' => '',
                );
                return kernel::single('b2c_mdl_huanxin_relation')->insert($data);
            } else {
                //环信添加好友失败
                //日志记录
                $data = array(
                    'member_id' => $member_id,
                    'error_type' => 'huanxin',
                    'addtime' => time(),
                    'desc' => '环信好友数据添加失败',
                    'remark' => '会员ID:' . $member_id . ' 的好友会员ID:' . $be_member_id,
                );
                $this->huanxin_log->insert($data);
            }
        }
        return false;
    }

    /*
     * 修改该用户昵称
     *  $options['username'] 
     * $options['nickname']
     * 
     */

    public function edit_nickname($member_id, $nickname) {
        $options = array(
            'username' => $this->huanxin_user->getusername($member_id),
            'nickname' => $nickname,
        );
        $data = array('nickname'=>$nickname);
        $filter = array('member_id'=>$member_id);
        $this->huanxin_user->update($data,$filter);
        return $this->huanxin->editNickname($options);
    }

    /*
     * 消息推送
     * $from_user @string
     * $receive @array
     * $content @string
     * $target_type @string
     */

    public function send_message($from_user, $receive, $content, $target_type) {
        //6_MT2sY   系统通知
        if (is_array($receive)) {
            $username = array();
            foreach ($receive as $k => $v) {
                $username[$k] = $this->huanxin_user->getusername($v);
            }
            return $this->huanxin->yy_hxSend($from_user, $username, $content, $target_type);
        }
        return false;
    }

    //获取环信username
    private function username($member_id) {
        if ($member_id > 0) {
            return $member_id . '_' . $this->get_rand(5);
        }
        return false;
    }

    //获取环信password
    private function password($str) {
        return substr(md5($str), 1, 10);
    }

    //随机字符
    private function get_rand($length) {
        $string = '1234567890abcdefghijklmnopqrstuvwxyz'; //ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $name = '';
        $length = empty($length) ? mt_rand(3, 6) : $length;
        for ($num = 0; $num < $length; $num++) {
            $name .= $string[mt_rand(0, 35)];
        }
        return $name;
    }

}
