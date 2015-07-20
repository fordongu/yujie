<?php
	include_once("SDKRuntimeException.php");
	include_once("WxPay.pub.config.php");
	include_once("WxPayPubHelper.php");
/**
* JSAPI支付——H5网页端调起支付接口
*/
class weixin_payment_pay4_JsApipub extends Common_util_pub
{
	var $code;//code码，用以获取openid
	var $openid;//用户的openid
	var $parameters;//jsapi参数，格式为json
	var $prepay_id;//使用统一支付接口得到的预支付id
	var $curl_timeout;//curl超时时间
	public $appId;
	

	function __construct() 
	{
		//设置curl超时时间
		$this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
		$this->appId = WxPayConf_pub::APPID;
	}

	//统一支付接口，可接叐 JSAPI/NATIVE/APP 下预支付订单，返回预支付订单号
	public function get_prepay_id($order){
		
		
	}
	/*
		
	*/
	public function get_package(){
		
		return 'sssss';
	}
	/*
		Sign 签名生成方法
		appid=wxd930ea5d5a258f4f
		auth_code=123456
		body=test
		device_info=123
		mch_id=1900000109
		nonce_str=960f228109051b9969f76c82bde183ac
		out_trade_no=1400755861
		spbill_create_ip=127.0.0.1
		total_fee=1
		key=8934e7d15453e97507ef794cf7b0519d
	*/
	public function get_paySign($array){
		if(is_array($array)){
			$string = '';
			foreach($array as $key=>$val){
				$string .= $key.'='.$val.'&';
			}
			$string .= 'key='.WxPayConf_pub::KEY;
			
			return strtolower(md5($string));
		}
		return false;
	}
	/**
	 * 	作用：生成可以获得code的url
	 */
	function createOauthUrlForCode($redirectUrl)
	{
		$urlObj["appid"] = WxPayConf_pub::APPID;
		$urlObj["redirect_uri"] = "$redirectUrl";
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->formatBizQueryParaMap($urlObj, false);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
	}

	/**
	 * 	作用：生成可以获得openid的url
	 */
	function createOauthUrlForOpenid()
	{
		$urlObj["appid"] = WxPayConf_pub::APPID;
		$urlObj["secret"] = WxPayConf_pub::APPSECRET;
		$urlObj["code"] = $this->code;
		$urlObj["grant_type"] = "authorization_code";
		//var_dump($urlObj);exit();
		$bizString = $this->formatBizQueryParaMap($urlObj, false);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
	}
	
	
	/**
	 * 	作用：通过curl向微信提交code，以获取openid
	 */
	function getOpenid()
	{
		$url = $this->createOauthUrlForOpenid();
		//var_dump($url);exit();
        //初始化curl
       	$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOP_TIMEOUT, $this->curl_timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//运行curl，结果以jason形式返回
        $res = curl_exec($ch);
		curl_close($ch);
		//取出openid
		$data = json_decode($res,true);
		$this->openid = $data['openid'];
		return $this->openid;
	}

	/**
	 * 	作用：设置prepay_id
	 */
	function setPrepayId($prepayId)
	{
		$this->prepay_id = $prepayId;
	}

	/**
	 * 	作用：设置code
	 */
	function setCode($code_)
	{
		$this->code = $code_;
	}

	/**
	 * 	作用：设置jsapi的参数
	 */
	public function getParameters()
	{
            $jsApiObj["appId"] = WxPayConf_pub::APPID;
            $timeStamp = time();
	    $jsApiObj["timeStamp"] = "$timeStamp";
	    $jsApiObj["nonceStr"] = $this->createNoncestr();
           // $UnifiedOrderPub = new UnifiedOrder_pub();
           // $prepay_id = $UnifiedOrderPub->getPrepayId();
            $jsApiObj["package"] = "prepay_id=$this->prepay_id";
	    $jsApiObj["signType"] = "MD5";
	    $jsApiObj["paySign"] = $this->getSign($jsApiObj);
	    $this->parameters = json_encode($jsApiObj);
		
            return $this->parameters;
	}
	function randomkeys($length = 6){
		$pattern='1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
		for($i=0;$i<$length;$i++){
		   $key .= $pattern{mt_rand(0,35)};//生成php随机数
		}
		return $key;
	}
}
?>