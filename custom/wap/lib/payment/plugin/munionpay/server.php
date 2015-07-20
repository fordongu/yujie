<?php
include_once('Utils.php');
class wap_payment_plugin_munionpay_server extends ectools_payment_app {
	//异步返回方法 和同步的方法一样
    public function callback(&$recv){
        $PubPk = DATA_DIR . '/cert/payment_plugin_munionpay/' . $this->getConf('pub_Pk', substr(__CLASS__, 0, strrpos(__CLASS__, '_')));  #加密请求报文证书

        $recv = file_get_contents('php://input','r');
        $postStr = $GLOBALS ["HTTP_RAW_POST_DATA"];
        $notifyDeal = new NotifyUtils($PubPk);
        $xml = $notifyDeal->analysisYlNotify($recv);

        //$xml ="<upomp application=\"TransNotify.Req\"  version=\"1.0.0\" ><transType>01</transType><merchantId>898000000000001</merchantId><merchantOrderId>22201111011010490000000506</merchantOrderId><merchantOrderAmt>1</merchantOrderAmt><settleDate>0420</settleDate><setlAmt>1</setlAmt><setlCurrency>156</setlCurrency><converRate></converRate><cupsQid>201111011016370201232</cupsQid><cupsTraceNum>020123</cupsTraceNum><cupsTraceTime>1101101637</cupsTraceTime><cupsRespCode>00</cupsRespCode><cupsRespDesc>Success!</cupsRespDesc><sign>j22MYWjysAmnRrWyeNFSU2RWQUJJie3K7o/tCEKpEsSgKvdV4aISwngMaBdlaK2GeV/JZBz86TpoD8RYit2pQbmxDdCgw2oXTmlq0lWI8c19JcPDg+hRaLGmNbg7JIjX7/cvOfKn0fkuUUPrIVT4VA8sOmxRyEKhvDkE1Y0wbIo=</sign><respCode></respCode></upomp>";
        $xmlDeal = new XmlUtils();
        $parse=$xmlDeal->readXml($xml);

        if ($parse) {
            $nodeArray = $xmlDeal->getNodeArray();
            $checkIdentifier = "transType=".$nodeArray['transType'].
            "&merchantId=".$nodeArray['merchantId'].
            "&merchantOrderId=".$nodeArray['merchantOrderId'].
            "&merchantOrderAmt=".$nodeArray['merchantOrderAmt'].
            "&settleDate=".$nodeArray['settleDate'].
            "&setlAmt=".$nodeArray['setlAmt'].
            "&setlCurrency=".$nodeArray['setlCurrency'].
            "&converRate=".$nodeArray['converRate'].
            "&cupsQid=".$nodeArray['cupsQid'].
            "&cupsTraceNum=".$nodeArray['cupsTraceNum'].
            "&cupsTraceTime=".$nodeArray['cupsTraceTime'].
            "&cupsRespCode=".$nodeArray['cupsRespCode'].
            "&cupsRespDesc=".$nodeArray['cupsRespDesc'].
            "&respCode=".$nodeArray['respCode'] ;

            $ret['payment_id'] =$nodeArray['merchantOrderId'];
            $ret['account'] = $nodeArray['merchantId'];
            $ret['bank'] = app::get('munionpay')->_('银联手机');
            $ret['pay_account'] = app::get('unionpay')->_('付款帐号');
            $ret['currency'] = 'CNY';
            $ret['money'] = $nodeArray['setlAmt'] / 100;
            $ret['paycost'] = '0.000';
            $ret['cur_money'] = $nodeArray['setlAmt'] / 100;
            $ret['tradeno'] = $nodeArray['cupsTraceNum'];
            $ret['t_payed'] = time();
            $ret['pay_app_id'] = 'munionpay';
            $ret['pay_type'] = 'online';
            $ret['trade_no'] = $nodeArray['cupsQid'];
            $ret['pay_account'] = $nodeArray['cardNum'];
            $ret['memo'] = 'munionpay';
            //$respCode = SecretUtils::checkSign($checkIdentifier,$PubPk,$nodeArray['sign']);
            $respCode = $nodeArray['cupsRespCode'];
            if($respCode=='0000'){
                if($nodeArray['transType']=="01") {
                    $ret['status'] = 'succ';
                }
            } else {
                if($respCode=='0001'){
                    $ret['status']='failed';
                }
                if($respCode=='9999'){
                    $ret['status']='failed';
                }
            }
            // $fp = fopen("test.txt", 'ab'); 
            // flock($fp, LOCK_EX); 
            // fwrite($fp, '$respCode:'.$respCode);
            // flock($fp, LOCK_UN); 
            // fclose($fp); 
            return $ret;
        } else {
            // $fp = fopen("test.txt", 'ab'); 
            // flock($fp, LOCK_EX); 
            // fwrite($fp, 'no_$parse');
            // flock($fp, LOCK_UN); 
            // fclose($fp);
            $ret['status']='failed';
            return $ret;
        }


    }

}
