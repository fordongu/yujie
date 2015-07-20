<?php
/* *
 * 类名：sms
 * 功能：短息平台通知处理类
 * 详细：处理短信平台各接口通知返回
 * 日期：2011-03-25
 * 说明：
 * 该代码仅供学习和研究短信平台接口使用，只是提供一个参考

 *************************注意*************************
 * 调试通知返回时，可查看或改写log日志的写入TXT里的数据，来检查通知返回是否正常
 */

require_once("Snoopy.class.php");
class sms {
    var $snoopy;
    var $sms_config;
    var $appKey;
    var $appSecret;
    var $source;
    var $token;
    var $version;
    var $sms_send_url;
    var $format;
    function __construct($sms_config){
        $this->snoopy = new Snoopy();
        $this->sms_config = $sms_config;
        $this->appKey = $sms_config['appKey'];
        $this->appSecret = $sms_config['appSecret'];
        $this->source = $sms_config['source'];
        $this->token = $sms_config['token'];
        $this->version = $sms_config['version'];
        $this->sms_send_url = $sms_config['sms_send_url'];
        $this->format = $sms_config['format'];
    }

    /*
    * 短信平台发送接口
    */
    function send_shopex_sms($timestamp,$contents){
       $smsparams['certi_app'] = 'sms.send';
       $smsparams['appKey'] = $this->appKey;
       $smsparams['appSecret'] = $this->appSecret;
       $smsparams['source'] = $this->source;
       $smsparams['sendType'] = 'notice';
       $smsparams['contents'] = $contents;
       $smsparams['version'] = $this->version;
       $smsparams['format'] = $this->format;
       $smsparams['timestamp'] = $timestamp;
       $smsparams['certi_ac'] = $this->make_shopex_sms_ac($smsparams,$this->token);
       $this->snoopy->submit($this->sms_send_url,$smsparams);
       $_sms = $this->snoopy->results;
       $sms = json_decode($_sms,TRUE);
       if($sms['res'] == 'succ'){
           return $sms;
       }
   }

    /*
    * 短信平台获得时间戳接口
    */
    function get_timestamp(){
       $timeParams['certi_app'] = 'sms.servertime';
       $timeParams['appKey'] = $this->appKey;
       $timeParams['appSecret'] = $this->appSecret;
       $timeParams['source'] = $this->source;
       $timeParams['version'] = $this->version;
       $timeParams['format'] = $this->format;
       $timeParams['certi_ac'] = $this->make_shopex_sms_ac($timeParams,$this->token);
       $this->snoopy->submit($this->sms_send_url,$timeParams);
       $_time = $this->snoopy->results;
       $time = json_decode($_time,TRUE);
       if($time['res'] == 'succ'){
           return $time['info'];
       }
   }

   /**
    *短信certi_ac算法
    */
   function make_shopex_sms_ac($arr,$token=''){
        ksort($arr);
        $str = '';
        foreach($arr as $key=>$value){
            if($key!='certi_ac') {
                $str.= $value;
            }
        }
        return strtolower(md5($str.strtolower(md5($token))));
   }

}
?>
