<?php
/**
 * 微信手机支付
 * @auther 549224868@qq.com
 * @version 4.0
 * @package ectools.lib.payment.plugin
 */
include_once("WxPayPubHelper.php");
include_once("JsApipub.php");

class weixin_payment_pay4_wxpayjsapi {

    /**
     * 构造方法
     * @param null
     * @return boolean
     */
    public function __construct($app) {
        header("Content-Type: text/html;charset=utf-8");
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function admin_intro() {
        $regIp = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['HTTP_HOST'];
        return '<img src="' . app::get('weixin')->res_url . '/payments/images/WXPAY.jpg"><br /><b style="font-family:verdana;font-size:13px;padding:3px;color:#000"><br>微信支付(JSAPI V3.3.6)是由腾讯公司知名移动社交通讯软件微信及第三方支付平台财付通联合推出的移动支付创新产品，旨在为广大微信用户及商户提供更优质的支付服务，微信的支付和安全系统由腾讯财付通提供支持。</b>
            <br>如果遇到支付问题，请访问：<a href="javascript:void(0)" onclick="top.location = ' . "'http://bbs.ec-os.net/read.php?tid=1007'" . '">http://bbs.ec-os.net/read.php?tid=1007</a>';
    }

    /**
     * 后台配置参数设置
     * @param null
     * @return array 配置参数列表
     */
    public function setting() {
        // 公众账号
        $publicNumbersInfo = app::get('weixin')->model('bind')->getList('appid,name', array('appid|noequal' => ''));
        $publicNumbers = array();
        foreach ($publicNumbersInfo as $row) {
            $publicNumbers[$row['appid']] = $row['name'];
        }
        return array(
            'pay_name' => array(
                'title' => app::get('weixin')->_('支付方式名称'),
                'type' => 'string',
                'validate_type' => 'required',
            ),
            'appId' => array(
                'title' => app::get('weixin')->_('appId'),
                'type' => 'select',
                'options' => $publicNumbers
            ),
//          'appId'=>array(
//              'title'=>app::get('weixin')->_('appId'),
//              'type'=>'string',
//              'validate_type' => 'required',
//          ),
            'Mchid' => array(
                'title' => app::get('weixin')->_('Mchid'),
                'type' => 'string',
                'validate_type' => 'required',
            ),
            'Key' => array(
                'title' => app::get('weixin')->_('Key'),
                'type' => 'string',
                'validate_type' => 'required',
            ),
            'Appsecret' => array(
                'title' => app::get('weixin')->_('Appsecret'),
                'type' => 'string',
                'validate_type' => 'required',
            ),
            'support_cur' => array(
                'title' => app::get('weixin')->_('支持币种'),
                'type' => 'text hidden cur',
                'options' => $this->arrayCurrencyOptions,
            ),
            'pay_desc' => array(
                'title' => app::get('weixin')->_('描述'),
                'type' => 'html',
                'includeBase' => true,
            ),
            'pay_type' => array(
                'title' => app::get('weixin')->_('支付类型(是否在线支付)'),
                'type' => 'radio',
                'options' => array('false' => app::get('weixin')->_('否'), 'true' => app::get('weixin')->_('是')),
                'name' => 'pay_type',
            ),
            'status' => array(
                'title' => app::get('weixin')->_('是否开启此支付方式'),
                'type' => 'radio',
                'options' => array('false' => app::get('weixin')->_('否'), 'true' => app::get('weixin')->_('是')),
                'name' => 'status',
            ),
        );
    }

    /**
     * 前台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function intro() {
        return app::get('weixin')->_('微信支付是由腾讯公司知名移动社交通讯软件微信及第三方支付平台财付通联合推出的移动支付创新产品，旨在为广大微信用户及商户提供更优质的支付服务，微信的支付和安全系统由腾讯财付通提供支持。财付通是持有互联网支付牌照并具备完备的安全体系的第三方支付平台。');
    }

    /**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment) {
        kernel::single('base_session')->start(); //开启session

        //使用jsapi接口
        $jsApi = kernel::single('weixin_payment_pay4_JsApipub');
        //$get_code_url = $jsApi->createOauthUrlForCode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        //=========步骤1：网页授权获取用户openid============
        //通过code获得openid	//获取code码，以获取openid

        $code = $_SESSION['wxpay']['code'];
        unset($_SESSION['wxpay']['code']);
        
        $jsApi->setCode($code);
        $openid = $jsApi->getOpenId();
        //var_dump($code);exit();
        //=========步骤2：使用统一支付接口，获取prepay_id============
        //使用统一支付接口
        $unifiedOrder = new UnifiedOrder_pub();

        //设置统一支付接口参数	//设置必填参数

        $unifiedOrder->setParameter("openid", "$openid"); //商品描述

        $unifiedOrder->setParameter("body", $payment['body']); //商品描述
        //自定义订单号，此处仅作举例
        $timeStamp = time();
        //$out_trade_no = WxPayConf_pub::APPID."$timeStamp";
//        echo '<pre>';
//        print_r($payment);
//        echo '</pre>';

        $unifiedOrder->setParameter("out_trade_no", $payment['order_id']); //商户订单号 $payment['order_id']
        $unifiedOrder->setParameter("total_fee", 100 * $payment['total_amount']); //总金额
        $unifiedOrder->setParameter("notify_url", WxPayConf_pub::NOTIFY_URL); //通知地址 
        $unifiedOrder->setParameter("trade_type", "JSAPI"); //交易类型
        $unifiedOrder->setParameter("spbill_create_ip", $payment['ip']); //设备号 
        //非必填参数，商户可根据实际情况选填
        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号  
        $unifiedOrder->setParameter("device_info", $payment['ip']); //设备号 

        $unifiedOrder->setParameter("attach", $payment['payment_id']); //附加数据 
        $unifiedOrder->setParameter("time_start", date('YmdHis',$payment['t_begin'])); //交易起始时间
        //$unifiedOrder->setParameter("time_expire", time() + 600); //交易结束时间 

        $unifiedOrder->setParameter("goods_tag", $payment['orders'][0]['rel_id']); //rel_id 
        $unifiedOrder->setParameter("product_id", $payment['member_id']); //member_id
        
        $prepay_id = $unifiedOrder->getPrepayId();
        //logger::info('sssssssssssssssss:'.  var_export($prepay_id,1));
        //var_dump($prepay_id);exit();
        //=========步骤3：使用jsapi调起支付============
        $jsApi->setPrepayId($prepay_id);

        $jsApiParameters = $jsApi->getParameters();
        //var_dump($jsApiParameters);exit();
        echo $this->get_html($jsApiParameters);
        exit;
    }

    /**
     * 支付后返回后处理的事件的动作
     * @params array - 所有返回的参数，包括POST和GET
     * @return null
     */
    function callback(&$in) {
        $mch_id = trim($this->getConf('Mchid', __CLASS__));
        $key = trim($this->getConf('Key', __CLASS__));
        $in = $in['weixin_postdata'];
        $insign = $in['sign'];
        unset($in['sign']);

        if ($in['return_code'] == 'SUCCESS' && $in['result_code'] == 'SUCCESS') {
            if ($insign == $this->getSign($in, $key)) {
                $objMath = kernel::single('ectools_math');
                $money = $objMath->number_multiple(array($in['total_fee'], 0.01));
                $ret['payment_id'] = $in['out_trade_no'];
                $ret['account'] = $mch_id;
                $ret['bank'] = app::get('wap')->_('微信支付JSAPI');
                $ret['pay_account'] = $in['openid'];
                $ret['currency'] = 'CNY';
                $ret['money'] = $money;
                $ret['paycost'] = '0.000';
                $ret['cur_money'] = $money;
                $ret['trade_no'] = $in['transaction_id'];
                $ret['t_payed'] = strtotime($in['time_end']) ? strtotime($in['time_end']) : time();
                $ret['pay_app_id'] = "wxpayjsapi";
                $ret['pay_type'] = 'online';
                $ret['memo'] = $in['attach'];
                $ret['status'] = 'succ';
            } else {
                $ret['status'] = 'failed';
            }
        } else {
            $ret['status'] = 'failed';
        }
        return $ret;
    }

    /**
     * 支付成功回打支付成功信息给支付网关
     */
    function ret_result($paymentId) {
        $ret = array('return_code' => 'SUCCESS', 'return_msg' => '');
        $ret = $this->arrayToXml($ret);
        echo $ret;
        exit;
    }

    /**
     * 校验方法
     * @param null
     * @return boolean
     */
    public function is_fields_valiad() {
        return true;
    }

    /**
     * 生成支付表单 - 自动提交
     * @params null
     * @return null
     */
    public function gen_form() {
        return '';
    }

    protected function get_html($jsApiParameters) {
        header("Content-Type: text/html;charset=utf-8");

        //$success_url = app::get('wap')->router()->gen_url(array('app'=>'b2c','ctl'=>'wap_paycenter','act'=>'result_pay','full'=>1,'arg0'=>$this->fields['order_id'],'arg1'=>'true'));
        //$failure_url = app::get('wap')->router()->gen_url(array('app'=>'b2c','ctl'=>'wap_paycenter','act'=>'index','full'=>1,'arg0'=>$this->fields['order_id']));
        $error_url = WWW_URL . 'index.php/wap/member-orders.html';
        $strHtml = <<<Eof
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <title>微信安全支付</title>
	<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
	<script type="text/javascript">		
		function onBridgeReady(){
		   WeixinJSBridge.invoke(	
			   'getBrandWCPayRequest',
			   {$jsApiParameters}, 				
			   function(res){
					WeixinJSBridge.log(res.err_msg);
					//alert(res.err_code+res.err_desc+res.err_msg);
				   if(res.err_msg == "get_brand_wcpay_request:ok" ) {
					   alert('支付成功');
					  window.location.href="{$error_url}"; 
				   }else{
                                          //alert(res.err_code);
					  //alert(res.err_desc);
					  //alert(res.err_msg);
                                            alert('支付失败');
					   window.location.href="{$error_url}"; 
				   }
			   }
		   ); 
		}
		function callpay(){
			//alert(typeof WeixinJSBridge);
			if (typeof WeixinJSBridge == "undefined"){
			   if( document.addEventListener ){
				   document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
			   }else if (document.attachEvent){
				   document.attachEvent('WeixinJSBridgeReady', onBridgeReady); 
				   document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
			   }
			}else{
			   onBridgeReady();
			}
			
		}		
		window.onload = function(){	
			callpay();
		}
	</script>
</head>
</html>	
Eof;
        //var_dump($strHtml);exit();
        return $strHtml;
    }

//↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓公共函数部分↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

    public function get_code($url) {
        if (!empty($url)) {
            $data = $this->moniget($url);
        }
        return false;
    }

    public function moniget($url) {
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOP_TIMEOUT, $this->curl_timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        return json_decode($res, true);
    }

    /**
     *  作用：将xml转为array
     */
    public function xmlToArray($xml) {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    /**
     *  作用：array转xml
     */
    function arrayToXml($arr) {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml.="<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml.="<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     *  作用：以post方式提交xml到对应的接口url
     */
    public function postXmlCurl($xml, $url, $second = 30) {
        $res = kernel::single('base_httpclient')->post($url, $xml);
        return $res;
//      //初始化curl
//      $ch = curl_init();
//      //设置超时
//      curl_setopt($ch, CURLOPT_TIMEOUT, $second);
//      //这里设置代理，如果有的话
//      //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
//      //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
//      curl_setopt($ch,CURLOPT_URL, $url);
//      curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
//      curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
//      //设置header
//      curl_setopt($ch, CURLOPT_HEADER, FALSE);
//      //要求结果为字符串且输出到屏幕上
//      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//      //post提交方式
//      curl_setopt($ch, CURLOPT_POST, TRUE);
//      curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
//      //运行curl
//      $data = curl_exec($ch);
//      curl_close($ch);
//      //返回结果
//      if($data)
//      {
//          curl_close($ch);
//          return $data;
//      }
//      else
//      {
//          $error = curl_errno($ch);
//          echo "curl出错，错误码:$error"."<br>";
//          echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
//          curl_close($ch);
//          return false;
//      }
    }

    /**
     *  作用：设置标配的请求参数，生成签名，生成接口参数xml
     */
    function createXml($parameters) {
        $this->parameters["appid"] = WxPayConf_pub::APPID; //公众账号ID
        $this->parameters["mch_id"] = WxPayConf_pub::MCHID; //商户号
        $this->parameters["nonce_str"] = $this->createNoncestr(); //随机字符串
        $this->parameters["sign"] = $this->getSign($this->parameters); //签名
        return $this->arrayToXml($this->parameters);
    }

    /**
     *  作用：post请求xml
     */
    function postXml() {
        $xml = $this->createXml();
        $this->response = $this->postXmlCurl($xml, $this->url, $this->curl_timeout);
        return $this->response;
    }

    /**
     *  作用：格式化参数，签名过程需要使用
     */
    function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    /**
     *  作用：生成签名
     */
    public function getSign($Obj, $key) {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $key;
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

//↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑公共函数部分↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
    //模拟GET请求方法
    protected function like_form($url, $header = 0) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

}
