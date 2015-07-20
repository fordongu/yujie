<?php
/*
 *	΢������������
 * @author 549224868@qq.com at 2015-01-30
 */

class weixin_userinfo_weixin {
	private $AppID;
	private $AppSecret;
	private $access_token;
	//��Ҫ������ʼ��
	public function __construct(){
		kernel::single('base_session')->start();//����session
		$weixin = kernel::single('weixin_mdl_bind')->getList('appsecret,appid',array(
			'status'=>'active',
			'weixin_account'=>'meiliqiji02'
		));
		$this->AppID = $weixin[0]['appid'];
		$this->AppSecret = $weixin[0]['appsecret'];
		
		//��ʼ�� access token
		if(empty($_SESSION['weixin']['access_token'])){			
			$token = $this->get_access_token();//{"access_token":"ACCESS_TOKEN","expires_in":7200}			
			$_SESSION['weixin']['access_token'] = $token->access_token;
		}
		$this->access_token = $_SESSION['weixin']['access_token'];
	}

	//ģ��GET���󷽷�
    protected function like_form($url, $header = 0){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 500);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, $url);

		$res = curl_exec($curl);
		curl_close($curl);
		return json_decode($res);		
	}
	
	//��ȡ��ȡaccess token  ��Ҫ��������token  
	public function get_access_token(){
		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->AppID.'&secret='.$this->AppSecret;		
		return $this->like_form($url);
	}
	
	//�û�ͬ����Ȩ����ȡcode
	public function get_user_code($redirect_uri = null){
		$redirect_uri = empty($redirect_uri)?WWW_URL:$redirect_uri;
		$redirect_uri = urlencode($redirect_uri);
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->AppID.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=SCOPE&state=laerya#wechat_redirect';
		
		return $this->like_form($url);
	}
	
	//��ȡ�û���Ϣ
	public function get_user_info($access_token = null,$openid){
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->access_token.'&openid='.$openid.'&lang=zh_CN';
		return $this->like_form($url);
	}
	
	//��ȡjsapi_ticket
	public function get_jsapi_ticket(){
		$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$this->access_token.'&type=jsapi';
		$res = $this->like_form($url);
		$_SESSION['weixin']['jsapi_ticket'] = $res->ticket;
		return $res->ticket;
	}
	
	//��ȡsignature
	public function get_signature($noncestr,$timestamp,$url){
		$str = 'jsapi_ticket='.$this->get_jsapi_ticket().'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url;
		return sha1($str);
	}
	
	function randomkeys($length = 6){
		$pattern='1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
		for($i=0;$i<$length;$i++){
		   $key .= $pattern{mt_rand(0,35)};//����php�����
		}
		return $key;
	}
	
	

}
