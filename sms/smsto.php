<?php
/* *
 * 功能：即时到帐接口接入页
 * 版本：3.2
 * 修改日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************注意*************************
 * 如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
 * 1、商户服务中心（https://b.alipay.com/support/helperApply.htm?action=consultationApply），提交申请集成协助，我们会有专业的技术工程师主动联系您协助解决
 * 2、商户帮助中心（http://help.alipay.com/support/232511-16307/0-16307.htm?sh=Y&info_type=9）
 * 3、支付宝论坛（http://club.alipay.com/read-htm-tid-8681712.html）
 * 如果不想使用扩展功能请把扩展功能参数赋空值。
 */

require_once("sms.config.php");
require_once("lib/sms.class.php");
/**************************请求参数--必填参数**************************/

//发送的手机号码
$mobile      = $_POST['mobile'];
//要发送的内容
$content         = $_POST['content'];

//构造一个短信发送类
$sms = new sms($sms_config);

//获得发送时间戳
$timestamp = $sms->get_timestamp();

//构造发送
$conts = array(array('phones'=>$mobile,'content'=>$content));

//构造发送内容
$contents = json_encode($conts);

$send_result = $sms->send_shopex_sms($timestamp,$contents);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<TITLE>短信发送接口</TITLE>
<style>
*{
	margin:0;
	padding:0;
}
ul,ol{
	list-style:none;
}
.title{
    color: black;
    font-size: 14px;
    font-weight: bold;
    padding: 8px 16px 5px 10px;
}
.hidden{
	display:none;
}

.new-btn-login-sp{
	border:1px solid #D74C00;
	padding:1px;
	display:inline-block;
}

.new-btn-login{
    background-color: transparent;
    background-image: url("images/new-btn-fixed.png");
    border: medium none;
}
.new-btn-login{
    background-position: 0 -198px;
    width: 82px;
	color: #FFFFFF;
    font-weight: bold;
    height: 28px;
    line-height: 28px;
    padding: 0 10px 3px;
}
.new-btn-login:hover{
	background-position: 0 -167px;
	width: 82px;
	color: #FFFFFF;
    font-weight: bold;
    height: 28px;
    line-height: 28px;
    padding: 0 10px 3px;
}
.bank-list{
	overflow:hidden;
	margin-top:5px;
}
.bank-list li{
	float:left;
	width:153px;
	margin-bottom:5px;
}

#main{
	width:750px;
	margin:0 auto;
	font-size:14px;
	font-family:'宋体';
}
#logo{
	background-color: transparent;
    
    border: medium none;
	background-position:0 0;
	width:166px;
	height:35px;
    float:left;
}
.red-star{
	color:#f00;
	width:10px;
	display:inline-block;
}
.null-star{
	color:#fff;
}
.content{
	margin-top:5px;
}

.content dt{
	width:100px;
	display:inline-block;
	text-align:right;
	float:left;
	
}
.content dd{
	margin-left:100px;
	margin-bottom:5px;
}
#foot{
	margin-top:10px;
}
.foot-ul li {
	text-align:center;
}
.note-help {
    color: #999999;
    font-size: 12px;
    line-height: 130%;
    padding-left: 3px;
}

.cashier-nav {
    font-size: 14px;
    margin: 15px 0 10px;
    text-align: left;
    height:30px;
    border-bottom:solid 2px #CFD2D7;
}
.cashier-nav ol li {
    float: left;
}
.cashier-nav li.current {
    color: #AB4400;
    font-weight: bold;
}
.cashier-nav li.last {
    clear:right;
}
.sms_link {
    text-align:right;
}
.sms_link a:link{
    text-decoration:none;
    color:#8D8D8D;
}
.sms_link a:visited{
    text-decoration:none;
    color:#8D8D8D;
}
</style>
</head>
<body text=#000000 bgColor=#ffffff leftMargin=0 topMargin=4>
	<div id="main">
		<div id="head">
            <dl class="sms_link">
                <a target="_blank" href="http://sms.shopex.cn/"><span>短信平台首页</span></a>|
                <a target="_blank" href="http://www.ecim-store.com"><span>短信通首页</span></a>
                <!-- <a target="_blank" href="http://help.sms.com/support/index_sh.htm"><span>帮助中心</span></a> -->
            </dl>
            <span class="title">短信发送快速通道</span>
		</div>
        <div class="cashier-nav">
            <ol>
                <li class="last">1、确认发送信息 →</li>
                <li>2、发送 →</li>
                <li class="current">3、发送完成</li>
            </ol>
        </div>
            <div id="body" style="clear:left">
                <dl class="content">
                    <?php 
                    
                    if($send_result['res'] == 'succ'){
                        echo "<span style='color:red;'>发送成功</span></br>";
                        echo "<span style='color:bule;'>发送批次号msgid：".$send_result['info']['msgid'];
                    }else{
                        echo "<span style='color:red;'>发送失败</span>";
                    }
                    ?>
                    
                </dl>
            </div>
        <div id="foot">
			<ul class="foot-ul">
				<li>
					<font class=note-help>如果您点击“确认发送”按钮，即表示您同意向用户发送此短信。 
					  <br/>
					  
					</font>
				</li>
				<li>
					短信平台版权所有 2011-2015 sms.COM 
				</li>
			</ul>
			<ul>
		</div>
	</div>
</body>
</html>