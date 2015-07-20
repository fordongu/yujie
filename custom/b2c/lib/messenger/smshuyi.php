<?php

// +----------------------------------------------------------------------
// |  [ WE CAN DO IT JUST work hard ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-0501 http://www.gangguo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.gangguo.net/ )
// +----------------------------------------------------------------------
// | Author: gangguo <549224868@qq.com>
// +----------------------------------------------------------------------

/**
 * Description of smshuyi
 *
 * @author Administrator
 */
class b2c_messenger_smshuyi {

    private $sendUrl = 'http://106.ihuyi.cn/webservice/sms.php?method=Submit'; //发送地址
    private $sms_user_name = "cf_uli9"; //帐号
    private $sms_password = "admin333"; //密码

    public function __construct() {
        header("Content-type:text/html; charset=UTF-8");
    }

    public function index() {
        echo 'baidu.com';
    }

    /**
     * @description 短信发送
     * @access public
     * @param void
     * @return void
     */
    public function send($contents) {

        $mobile_number = $contents[0]['phones'];
        $content = $contents[0]['content'];

        if (!$mobile_number) {
            return false;
        }

        if (is_array($mobile_number)) {
            foreach ($mobile_number as $v) {
                $this->send_sms($v, $content);
            }
        } else {
            $this->send_sms($mobile_number, $content);
        }
    }

    public function send_sms($mobile, $content) {

        $postData = 'account=' . $this->sms_user_name . '&password=' . $this->sms_password;
        $postData .= '&mobile=' . $mobile . '&content=' . rawurlencode($content);
        $s1 = $this->Post($postData, $this->sendUrl);
       
        return $gets = $this->xmlToArray($s1);
        if($gets['code'] == 2){
            return true;
        }else{
            return false;
        }
        
    }

    private function Post($curlPost, $url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }

    private function xmlToArray($xml) {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

}

?>
