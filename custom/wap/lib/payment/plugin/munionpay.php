<?php
include_once('munionpay/Utils.php');
/**
 * 中国银联WAP支付
 * @auther 凌云 758238751@qq.com
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class wap_payment_plugin_munionpay extends ectools_payment_app implements ectools_interface_payment_app{
    /**
     * @var string 支付方式名称
     */
    public $name = '中国银联手机支付';
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '中国银联手机支付';
     /**
     * @var string 支付方式key
     */
    public $app_key = 'munionpay';
    /**
     * @var string 中心化统一的key
     */
    public $app_rpc_key = 'munionpay';
    /**
     * @var string 统一显示的名称
     */
    public $display_name = '中国银联手机支付';
    /**
     * @var string 货币名称
     */
    public $curname = 'CNY';
    /**
     * @var string 当前支付方式的版本号
     */
    public $ver = '1.0';
    /**
     * @var string 当前支付方式所支持的平台
     */
    public $platform = 'iswap';

    /**
     * @var array 扩展参数
     */
    public $supportCurrency = array("CNY"=>"01");


    /**
     * @var string 当前支付方式所支持的平台
     */

    function is_fields_valiad()
    {
        return true;
    }

    /**
     * 前台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    function intro()
    {
        return '<b><h3>'.app::get('ectools')->_('中国银联为手机支付提供简单高效的解决方案。用户通过手机在中国银联合上进行订单确认支付后，将接收中国银联发送的验证码，回复验证码并确认即可完成订单支付。').'</h3></b>';
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    function admin_intro()
    {
        return app::get('ectools')->_('中国银联手机支付（UnionPay）是银联电子支付服务有限公司主要从事以互联网等新兴渠道为基础的网上支付。');
    }

    public function __construct($app)
    {
        parent::__construct($app);

        $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/wap/wap_payment_plugin_munionpay_server', 'callback');
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->notify_url, $matches)){
            $this->notify_url = str_replace('http://','',$this->notify_url);
            $this->notify_url = preg_replace("|/+|","/", $this->notify_url);
            $this->notify_url = "http://" . $this->notify_url;
        } else {
            $this->notify_url = str_replace('https://','',$this->notify_url);
            $this->notify_url = preg_replace("|/+|","/", $this->notify_url);
            $this->notify_url = "https://" . $this->notify_url;
        }
        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/wap_payment_plugin_munionpay', 'callback');
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->callback_url, $matches)){
            $this->callback_url = str_replace('http://','',$this->callback_url);
            $this->callback_url = preg_replace("|/+|","/", $this->callback_url);
            $this->callback_url = "http://" . $this->callback_url;
        }else{
            $this->callback_url = str_replace('https://','',$this->callback_url);
            $this->callback_url = preg_replace("|/+|","/", $this->callback_url);
            $this->callback_url = "https://" . $this->callback_url;
        }

        // 按照相应要求请求接口网关改为一下地址
        // $this->submit_url = 'http://58.246.226.99/UpopWeb/api/Pay.action';
        $this->submit_url = 'http://upwap.bypay.cn/gateWay/gate.html';
        $this->front_url = app::get('wap')->router()->gen_url(array('app'=>'b2c','ctl'=>'wap_munionpay','act'=>'index', 'full'=>1));
        $this->back_end_url = $this->notify_url;
        $this->submit_method = 'POST';
        $this->submit_charset = 'utf-8';
    }



    public function setting()
    {
        return array(
                'pay_name'=>array(
                    'title'=>app::get('ectools')->_('支付方式名称'),
                    'type'=>'string',
                    'validate_type' => 'required',
                ),
                'mer_id'=>array(
                    'title'=>app::get('chinapay')->_('客户号'),
                    'type'=>'string',
                    'validate_type' => 'required',
                ),
                'pub_Pk'=>array(
                    'title'=>app::get('chinapay')->_('解密后台通知证书'),
                    'type'=>'file',
                ),
                'mer_key'=>array(
                    'title'=>app::get('chinapay')->_('加密请求报文证书'),
                    'type'=>'file',
                ),
                'pay_fee'=>array(
                    'title'=>app::get('ectools')->_('交易费率'),
                    'type'=>'pecentage',
                    'validate_type' => 'number',
                ),
                'support_cur'=>array(
                    'title'=>app::get('ectools')->_('支持币种'),
                    'type'=>'text hidden cur',
                    'options'=>$this->arrayCurrencyOptions,
                ),
                'pay_desc'=>array(
                    'title'=>app::get('ectools')->_('描述'),
                    'type'=>'html',
                    'includeBase' => true,
                ),
                'pay_type'=>array(
                    'title'=>app::get('wap')->_('支付类型(是否在线支付)'),
                    'type'=>'radio',
                    'options'=>array('true'=>app::get('wap')->_('是')),
                    'name' => 'pay_type',
                    'validate_type' => 'requiredradio',
                ),
                'status'=>array(
                    'title'=>app::get('ectools')->_('是否开启此支付方式'),
                    'type'=>'radio',
                    'options'=>array('false'=>app::get('ectools')->_('否'),'true'=>app::get('ectools')->_('是')),
                    'name' => 'status',
                ),
        );
    }

    /**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment){
        $merId = $this->getConf('mer_id', __CLASS__);//客户号
        $PubPk = DATA_DIR . '/cert/payment_plugin_munionpay/' . $this->getConf('pub_Pk', __CLASS__); #解密后台通知证书
        $MerPrk = DATA_DIR . '/cert/payment_plugin_munionpay/' . $this->getConf('mer_key', __CLASS__);  #加密请求报文证书

        $args=array(
            "orderId" => $payment['order_id'],
            "merchantName" => $payment['shopName'],                     ## 商户名称
            "merchantId" => $merId,                         ## 商户代码
            "merchantOrderId" => $payment['payment_id'],    ## 商户订单号
            "merchantOrderTime" => date("YmdHis"),      ## 商户订单时间
            "merchantOrderAmt" => $payment['cur_money'] * 100,   ## 商户订单金额
            "merchantOrderDesc" => '商品订单号：'.$payment['order_id'].' 产品名称：'.$payment['body'],  ## 商户订单描述
            "transTimeout" => date("YmdHis",time()+60*30),                ## 交易超时时间
        );

        $xml = $this->getXml($args, $PubPk, $MerPrk, '123456');
        $postDeal = new PostUtils($PubPk, $MerPrk, '123456', $merId);
        $recv = $postDeal->submitByPost($this->submit_url, $xml);

        $recv_a= explode("|",$recv);
        if ($recv_a[0]=="1") {
            $recv = str_replace(" ","+",$recv);
            $d_res = $postDeal->analysisYlResult($recv);
            $xmlDeal = new XmlUtils();
            $parse= $xmlDeal->readXml($d_res);
            $nodeArray = $xmlDeal->getNodeArray();
            if("0000" == $nodeArray['respCode']) {
                //header("Location:".$nodeArray['gwInvokeCmd']);
                //header("Content-Type: text/html;charset=".$this->submit_charset);
                $strHtml ="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
        <html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-US\" lang=\"en-US\" dir=\"ltr\">
        <head>
        </head><body><div>正在提交支付到银联，请等待……</div>";
                $strHtml .= '<form action="' . $nodeArray['gwInvokeCmd'] . '" method="' . $this->submit_method . '" name="pay_form" id="pay_form">';
                $strHtml .= '<input type="submit" name="btn_purchase" value="'.app::get('ectools')->_('购买').'" style="display:none;" />';
                $strHtml .= '</form><script type="text/javascript">
                        window.onload=function(){
                            document.getElementById("pay_form").submit();
                        }
                    </script>';
                $strHtml .= '</body></html>';
                echo $strHtml;exit();

            } else {
                echo $nodeArray['respDesc'];
            }
        } else {
             $a= explode("|",$recv);
             foreach($a as $b) {
                header("Content-Type:text/html;charset=utf-8");
                echo base64_decode($b)."<br>";;
            
             }exit;
        }

    }

    function getXml($params, $public_key, $prikey_key, $prikey_password)
    {
        $merchantPublicCert = SecretUtils::getPublicKeyBase64($public_key);
        $strForSign = $this->format_str($params);
        $sign = SecretUtils::sign($strForSign, $prikey_key, $prikey_password);

        $attrArray = array("application" => "MGw.Req", "version" => "1.0.0","sendTime"=>$params['merchantOrderTime'],"sendSeqId"=>$params['merchantOrderId']);
        $nodeArray = $params;
        $nodeArray['backUrl'] = $this->back_end_url;
        $nodeArray['frontUrl'] = $this->front_url.'?order_id='.$params['orderId'];
        $nodeArray['gwType'] = '01';
        return XmlUtils::writeXml($attrArray, $nodeArray);

    }

    function format_str($params)
    {
        ksort($params);
        $sign_str = "";
        $i = 1;
        foreach($params as $key => $val)
        {
            if ($i == count($params)) {
                $sign_str .= sprintf("%s=%s", $key, $val);
                continue;
            }
            $sign_str .= sprintf("%s=%s&", $key, $val);
            $i++;
        }
        return $sign_str;
    }

    //回调函数: 接受银联返回来的数据
    public function callback(&$recv){
        $objMath = kernel::single('ectools_math');
        $money=$objMath->number_multiple(array($recv['orderAmount'], 0.01));
        $merid = $this->getConf('mer_id', __CLASS__);//客户号
        $mer_key=$this->getConf('mer_key', __CLASS__);//私钥
        $sign=$recv['signature'];  //银联返回来的签名      
        $sign_method=$recv['signMethod'];//银联返回来的签名方法 
        //$arrs:银联返回来的数组  
        $arrs=array(
            "version"=>$recv['version'],//消息版本号
            "charset"=>$recv['charset'],//字符编码
            "transType"=>$recv['transType'],//交易类型
            "respCode"=>$recv['respCode'],//响应码
            "respMsg"=>$recv['respMsg'],//响应信息
            "merAbbr"=>$recv['merAbbr'],//商户名称
            "merId"=>$recv['merId'],//商户代码
            "orderNumber"=>$recv['orderNumber'],//订单号
            "traceNumber"=>$recv['traceNumber'],//系统跟踪号
            "traceTime"=>$recv['traceTime'],//系统跟踪时间
            "qid"=>$recv['qid'],//交易流水号
            "orderAmount"=>$recv['orderAmount'],//交易金额
            "orderCurrency"=>$recv['orderCurrency'],//交易币种
            "respTime"=>$recv['respTime'],//交易完成时间
            "settleCurrency"=>$recv['settleCurrency'],//清算币种
            "settleDate"=>$recv['settleDate'],//清算日期
            "settleAmount"=>$recv['settleAmount'],//清算金额
            "exchangeDate"=>$recv['exchangeDate'],//兑换日期
            "exchangeRate"=>$recv['exchangeRate'],//清算汇率
            "cupReserved"=>$recv['cupReserved'],//系统保留域
        );
        //生成签名
        $chkvalue = $this->sign($arrs, $sign_method,$mer_key);
        $ret['payment_id'] =$arrs['orderNumber'];
        $ret['account'] = $arrs['merId'];
        $ret['bank'] = app::get('munionpay')->_('银联');
        $ret['pay_account'] = app::get('unionpay')->_('付款帐号');
        $ret['currency'] = 'CNY';
        $ret['money'] = $money;
        $ret['paycost'] = '0.000';
        $ret['cur_money'] =$money;
        $ret['tradeno'] = $recv['traceNumber'];
        // $ret['t_payed'] = strtotime($recv['settleDate']) ? strtotime($recv['settleDate']) : time();
        $ret['t_payed'] = time();
        $ret['pay_app_id'] = 'unionpay';
        $ret['pay_type'] = 'online';
        $ret['memo'] = 'unionpay';
        //校验签名
        if ($sign==$chkvalue && $recv['respCode']==00) {
             $ret['status'] = 'succ';
        }else{
            $ret['status']='failed';
        }
        return $ret;
    }

  
    public function gen_form()
    {
      $tmp_form='<a href="javascript:void(0)" onclick="document.applyForm.submit();">'.app::get('unionpay')->_('立即申请').'</a>';
      $tmp_form.="<form name='applyForm' method='".$this->submit_method."' action='" . $this->submit_url . "' target='_blank'>";
      // 生成提交的hidden属性
      foreach($this->fields as $key => $val)
      {    
            $tmp_form.="<input type='hidden' name='".$key."' value='".$val."'>";
      }

      $tmp_form.="</form>";

      return $tmp_form;

    }
}
