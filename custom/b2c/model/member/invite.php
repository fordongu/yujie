<?php

/**
 * author  549224868@qq.com
 */
class b2c_mdl_member_invite extends dbeav_model {

    public function check_phone($phonenumber) {

        if (preg_match("/1[345789]{1}\d{9}$/", $phonenumber)) {
            return true;
        } else {
            return false;
        }
    }

    public function is_check($phonenumber) {
        if($this->check_phone($phonenumber)){
            $filter = array(
                'invite_phone'=>$phonenumber,
                'invite_time|than'=>(time() - 7*24*3600),
            );
            $res = $this->dump($filter,'member_id');
            return $res['member_id'];
        }
        return false;
    }

}
