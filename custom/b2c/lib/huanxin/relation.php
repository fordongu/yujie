<?php

/*
 * 	添加好友类	
 * 	auth 549224868@qq.com  at 2015-01-20
 *
 */

class b2c_huanxin_relation extends b2c_huanxin_start {

    private $Relation;
    public function __construct() {
        parent::__construct();
        $this->Relation = kernel::single('b2c_mdl_huanxin_relation');
        
    }
    
    //添加好友关系
    public function save_relative($member_id, $be_member_id) {  
         $username = $this->huanxin_user->getusername($member_id);
            $be_username = $this->huanxin_user->getusername($be_member_id);
             return $this->huanxin->addFriend($username, $be_username);
                exit();
        if ($member_id > 0 && $be_member_id > 0) {
            $data = array(
                'member_id' => $member_id,
                'be_member_id' => $be_member_id,
                'addtime' => time(),
                'remark' => '',
            );
            if (!kernel::single('b2c_mdl_huanxin_relation')->insert($data)) {
                //日志记录
                $this->huanxin_log->insert(array(
                    'member_id' => $member_id,
                    'error_type' => 'ecstore',
                    'addtime' => time(),
                    'desc' => '环信好友数据添加失败，ECstore好友数据添加成功',
                    'remark' => '会员ID:' . $member_id . ' 的好友会员ID:' . $be_member_id,
                ));
                return false;
                exit;
            }
            $username = $this->huanxin_user->getusername($member_id);
            $be_username = $this->huanxin_user->getusername($be_member_id);
            if ($username && $be_username) {
                if ($this->huanxin->addFriend($username, $be_username)) {
                    return true;
                } else {
                    //日志记录
                    $this->huanxin_log->insert(array(
                        'member_id' => $member_id,
                        'error_type' => 'huanxin',
                        'addtime' => time(),
                        'desc' => '环信好友数据添加失败，ECstore好友数据添加成功',
                        'remark' => '会员ID:' . $member_id . ' 的好友会员ID:' . $be_member_id,
                    ));
                }
            } else {
                //日志记录
                $this->huanxin_log->insert(array(
                    'member_id' => $member_id,
                    'error_type' => 'error',
                    'addtime' => time(),
                    'desc' => '给会员ID:' . $member_id . ' 或则会员ID:' . $be_member_id . ' 没有对应的环信ID',
                    'remark' => '会员ID:' . $member_id . ' 的好友会员ID:' . $be_member_id,
                ));
            }
        }
        return false;
    }
    

}
