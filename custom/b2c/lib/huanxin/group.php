<?php

/*
 * 	群组
 * 	
 * 	auth 549224868@qq.com  at 2015-01-20
 *
 */

class b2c_huanxin_group extends b2c_huanxin_start {

    private $Group;
    private $Group_member;

    public function __construct() {
        parent::__construct();
        $this->Group = kernel::single('b2c_mdl_huanxin_group');
        $this->Group_member = kernel::single('b2c_mdl_huanxin_group_member');
    }

    /*    //新建群组
     *  $options['member_id']  
     *  $options['desc']  
     *  $options['public']  
     *  $options['groupname']  
     *  $options['maxusers']  
     *  $options['approval']  
     * $options['status'] 群状态
     * 
     */

    public function add_group($options) {
        if ($options['member_id'] > 0 && !empty($options['groupname'])) {
            $username = $this->huanxin_user->getusername($options['member_id']);
            $public = empty($options['public']) ? false : $options['public'];
            $data = array(
                'groupname' => $options['groupname'],
                'desc' => $options['desc'],
                'public' => $public,
                'owner' => $username, //$options['owner'],
                'maxusers' => empty($options['maxusers']) ? 300 : $options['maxusers'],
                'approval' => empty($options['approval']) ? true : $options['approval'],
            );
            $res = $this->huanxin->createGroups($data);
            $group_id = $res['data']['groupid'];
            if ($res['status'] == 200 && !empty($group_id)) {
                $data['member_id'] = $options['member_id'];
                $data['addtime'] = time();
                $data['group_id'] = $group_id;
                $data['status'] = $options['status'] ? $options['status'] : 1;
                if ($this->Group->insert($data)) {
                    $data = array(
                        'group_id' => $group_id,
                        'member_id' => $options['member_id'],
                        'addtime' => time(),
                        'type' => 1,
                    );
                    $this->Group_member->insert($data);
                    return true;
                } else {
                    //日志记录
                    $data = array(
                        'member_id' => $options['member_id'],
                        'error_type' => 'ecstore',
                        'addtime' => time(),
                        'desc' => json_encode($data),
                        'remark' => 'huanxin_group:数据库新增失败！',
                    );
                    $this->huanxin_log->insert($data);
                }
            } else {
                //日志记录
                $data = array(
                    'member_id' => $options['member_id'],
                    'error_type' => 'error',
                    'addtime' => time(),
                    'desc' => json_encode($data),
                    'remark' => '环信接口添加聊天群失败。失败记录：' . var_export($res, 1),
                );
                $this->huanxin_log->insert($data);
            }
        }
        return false;
    }

    //群组加人
    public function add_group_username($member_id, $jiaru_member_id) {

        if ($member_id > 0 && $jiaru_member_id > 0) {
            $group = $this->Group->dump(array('member_id' => $member_id), 'group_id');
            $username = $this->huanxin_user->getusername($jiaru_member_id);
            $res = $this->huanxin->addGroupsUser($group['group_id'], $username);
            if ($res['status'] == 200) {
                $data = array(
                    'group_id' => $group['group_id'],
                    'member_id' => $jiaru_member_id,
                    'type' => 3,
                    'addtime' => time(),
                );
                if ($this->Group_member->insert($data)){                    
                    return true;
                }else{
                    //日志记录
                    $data = array(
                        'member_id' => $group['group_id'],
                        'error_type' => 'ecstore',
                        'addtime' => time(),
                        'desc' => '环信群组成员数据添加失败，ECstore群组成员数据添加成功',
                        'remark' => '群组ID:' . $group['group_id'] . ' 的成员ID:' . $member_id,
                    );
                    $this->huanxin_log->insert($data);
                }
            } else {
                //日志记录
                $data = array(
                    'member_id' => $group['group_id'],
                    'error_type' => 'huanxin',
                    'addtime' => time(),
                    'desc' => '环信群组成员数据添加失败',
                    'remark' => '群组ID:' . $group['group_id'] . ' 的成员ID:' . $jiaru_member_id,
                );
                $this->huanxin_log->insert($data);
            }
        }
        return false;
    }

}
