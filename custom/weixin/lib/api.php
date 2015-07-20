<?php

class weixin_api {

    public function __construct($app) {
        $this->wechat = kernel::single('weixin_wechat');
        $this->weixinObject = kernel::single('weixin_object');
    }

    public function api() {
        //签名验证，消息有效性验证
        if (!empty($_GET) && $this->doget()) {
            echo $_GET["echostr"];
        }

        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)) {
            $this->dopost($postStr);
        } else {
            echo "";
        }
    }

    /**
     * 处理微信消息有效验证
     */
    public function doget() {
        //获取到token
        $token = $this->weixinObject->get_token($_GET['eid']);
        //验证
        if ($this->wechat->checkSignature($_GET["signature"], $_GET["timestamp"], $_GET["nonce"], $token)) {
            return true;
        } else {
            return false;
        }
    }

    public function dopost($postXml) {
        $postArray = kernel::single('site_utility_xml')->xml2array($postXml);
        $postData = $postArray['xml'];

        //公众账号ID获取
        $weixin_id = $postData['ToUserName'];
        $bind = app::get('weixin')->model('bind')->getList('id,eid', array('weixin_id' => $weixin_id, 'status' => 'active'));
        if (!empty($bind)) {
            $postData['bind_id'] = $bind[0]['id'];
            $postData['eid'] = $bind[0]['eid'];
        } else {
            $this->weixinObject->send('');
        }

        switch ($postData['MsgType']) {
            case 'event':
                /**
                 * subscribe(订阅)、unsubscribe(取消订阅)
                 * scan 带参数二维码事件
                 * location 上报地理位置事件
                 * click 自定义菜单事件
                 * view  点击菜单跳转链接时的事件推送
                 * */
                $method = strtolower($postData['Event']);
                if (method_exists($this->wechat, $method)) {
                    $this->wechat->$method($postData);
                } else {
                    $this->weixinObject->send('');
                }
                break;
            default:
                $this->wechat->commonMsg($postData);
        }
    }

    // 微信支付回调地址
    function wxpay() {
        $postData = array();
        $httpclient = kernel::single('base_httpclient');
        $callback_url = kernel::openapi_url('openapi.ectools_payment/parse/weixin/weixin_payment_plugin_wxpay', 'callback');
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postArray = kernel::single('site_utility_xml')->xml2array($postStr);
        $postData['weixin_postdata'] = $postArray['xml'];
        $nodify_data = array_merge($_GET, $postData);
        //logger::info('weixin zhifu :'.  var_export($nodify_data,1));
        $response = $httpclient->post($callback_url, $nodify_data);
        $data = array(
            'roder_id' => $nodify_data['out_trade_no'],
            'payment_id' => $nodify_data['attach'],
            'transaction_id' => $nodify_data['transaction_id'],
            'trade_state' => $nodify_data['trade_state'],
            'time_end' => $nodify_data['time_end'],
            'member_id' => $nodify_data,
            'wx_data' => json_encode($nodify_data),
            'create_time' => time(),
            'nonce_str' => $nodify_data['nonce_str'],
            'device_info' => $nodify_data['device_info'],
        );
        if(kernel::single('weixin_mdl_wxpay_log')->insert($data)){    
            echo 'success';
            exit; 
        }

    }

    // 维权通知接口
    public function safeguard() {
        //logger::info('3333333333333333333333333333');
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postArray = kernel::single('site_utility_xml')->xml2array($postStr);
        $postData = $postArray['xml'];
        #$postData = array (
        #    'OpenId' => 'ow1l7t6coRbI3aBBNztBc6qT8F4w',
        #    'AppId' => 'wxfdd2db839d9e8984',
        #    'TimeStamp' => '1403080919',
        #    'MsgType' => 'request',
        #    'FeedBackId' => '13221259825330037179',
        #    'TransId' => '1219419901201406183166617972',
        #    'Reason' => '商品质量有问题',
        #    'Solution' => '退款，并不退货',
        #    'ExtInfo' => '我是备注 1391000000',
        #    'AppSignature' => '5f0dba6a6ba427cf523f22c815f6600cfbe1c365',
        #    'SignMethod' => 'sha1',
        #);
        $signData = array(
            'OpenId' => $postData['OpenId'],
            'TimeStamp' => $postData['TimeStamp'],
        );
        if (!weixin_util::verifySignatureShal($signData, $postData['AppSignature'])) {
            return false;
        }

        $saveData['openid'] = $postData['OpenId'];
        $saveData['appid'] = $postData['AppId'];
        $saveData['msgtype'] = $postData['MsgType'];
        $saveData['feedbackid'] = $postData['FeedBackId'];
        $saveData['transid'] = $postData['TransId'];
        $saveData['reason'] = $postData['Reason'];
        $saveData['solution'] = $postData['Solution'];
        $saveData['extinfo'] = $postData['ExtInfo'];
        $saveData['picurl'] = $postData['PicUrl'];
        $saveData['timestamp'] = $postData['TimeStamp'];
        $safeguardModel = app::get('weixin')->model('safeguard');
        $row = $safeguardModel->getRow('id', array('feedbackid' => $saveData['feedbackid']));
        if ($row) {
            if ($saveData['msgtype'] == 'confirm') {
                $status = '3';
                $safeguardModel->update(array('msgtype' => $saveData['msgtype'], 'status' => $status), array('id' => $row['id']));
            } else {
                $saveData['status'] = '1';
                $safeguardModel->update($saveData, array('id' => $row['id']));
            }
        } else {
            $bindData = app::get('weixin')->model('bind')->getRow('id', array('appid' => $saveData['appid']));
            $res = kernel::single('weixin_wechat')->get_basic_userinfo($bindData['id'], $saveData['openid']);
            $saveData['weixin_nickname'] = $res['nickname'];
            if (!$safeguardModel->save($saveData)) {
                logger::info(var_export($saveData, 1), '维权信息记录失败');
            }
        }
    }

    // 微信告警通知接口
    public function alert() {
        //logger::info('666666666666666666666666666666666666');
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postArray = kernel::single('site_utility_xml')->xml2array($postStr);
        $postData = $postArray['xml'];

        $insertData = array(
            'appid' => $postData['AppId'],
            'errortype' => $postData['ErrorType'],
            'description' => $postData['Description'],
            'alarmcontent' => $postData['AlarmContent'],
            'timestamp' => $postData['TimeStamp'],
        );
        app::get('weixin')->model('alert')->save($insertData);

        // 微信需要返回页面编码也未gbk的success
        echo "<meta charset='GBK'>";
        $rs = 'success';
        echo iconv('UTF-8', 'GBK//TRANSLIT', $rs);
        exit;
        /* 告警通知数据格式
          $postData=array(
          'AppId'=>'wxf8b4f85f3a794e77',
          'ErrorType'=>'1001',
          'Description'=>'错误描述',
          'AlarmContent'=>'错误详情',
          'TimeStamp'=>'1393860740',
          'AppSignature'=>'f8164781a303f4d5a944a2dfc68411a8c7e4fbea',
          'SignMethod'=>'sha1'
          ); */
    }

    protected function like_post($url, $data) {
        //初始化curl        
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOP_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        //运行curl
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode($data);
    }

}
