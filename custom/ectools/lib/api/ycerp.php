<?php
class ectools_api_ycerp{

	/**
	 * 构造方法
	 * @param null
	 * @return null
	 */
    public function __construct(){

        $this->appKey = '2KnI87CRUlHDiKAc5656456'; //U1CITYFXSTEST
        $this->appSecret = "ae36f4ee3946e1cbb98d6965b0b2ff5c584"; //U1CITYFXSTESTBBIOFKD 
		$this->postUrl = "http://erp.hwpartyprince.com/api.rest";
		
        #$this->goodsShowDecimals = app::get('ectools')->getConf('site.decimal_digit.display'); 
		#$this->goodsShowCarryset = app::get('ectools')->getConf('site.decimal_type.display');
    }


 public  function Add_postdata_filed($type,$orders){ 
        $objectarr   = new StdClass;
		
		foreach($orders['goods'] as $k=>$v){
		  $goods[$k]['protitle'] = $v['name'];
		  $goods[$k]['prono'] = $v['goods_sn']; //商品货号
		  $goods[$k]['prosku'] = $v['bn']; //货号
		  $goods[$k]['proprice'] = $v['price']; //单价
		  $goods[$k]['procount'] = $v['nums'];  //数量
		}

		$objectarr->OrderProinfo = $goods;
		
		//拆分地区
		if($orders['ship_area']){
		 $ship_area1 = explode(':',$orders['ship_area']);
		 $ship_area = explode('/',$ship_area1[1]);
		}

	   $array=array(
	   "orderNo"=>$orders['order_id'],
	   "userName"=>$orders['member_id'],
	   "uName"=> $orders['ship_name'],//收件人姓名[必填]
       "province"=>$ship_area[0],//省份[必填]
        "city"=>$ship_area[1],//城市[必填]
        "district"=>$ship_area[2],//区域[必填]
        "address"=>$orders['ship_addr'],//地址[必填]
        "postcode"=>$orders['ship_zip']?$orders['ship_zip']:000,//邮编[必填]
        "mobiTel"=>$orders['ship_mobile'],//手机号码[可填][注：手机号码和电话号码至少填一项]
        "phone"=>$orders['ship_tel'],//电话号码[可填][注：手机号码和电话号码至少填一项]
        "cRemark"=>$orders['memo'],//买家留言[可填]
        "oRemark"=>$orders['mark_text'],//卖家备注[可填]
        "oSumPrice"=>$orders['payed'],//实付订单总金额[必填]
        "expFee"=> $orders['cost_freight'],//实付订单运费[可填][默认为0]
        "expCod"=>$orders['shipping_id']==2?1:0,//是否货到付款[必填][1：货到付款]
        "codFee"=>"0",//货到付款手续费[可填][默认为0]
        "expCodFee"=>"0",//货到付款代收运费[可填][默认为0]
		"OrderPro"=>json_encode($objectarr->OrderProinfo)
		);
		return $this->Urldosrt($type,$array);	
	}


  //向ERP添加订单
 public  function AddOrder($orders){
		
		$apiFormat="json";
		$apiMethod="IOpenAPI.AddOrder";
		$postData=$this->Add_postdata_filed("",$orders);//返回加密数据格式
		$inputStr = strtolower(str_replace(" ","",$this->appSecret.$apiMethod."appKey".$this->appKey.$postData));//转小写	
        $sort=$this->mbstringtoarray($inputStr,'utf-8');
        sort($sort,SORT_STRING); 
		$str="";
		for($i=0;$i<count($sort);$i++){
		 $str.=$sort[$i];
		}
		$paramStr=$str;
		$sToken = md5(iconv('utf-8',"gb2312",$paramStr));
		$post_data_filed=$this->Add_postdata_filed("=",$orders);//返回POST参数格式
		$postData = "user=".$this->appKey."&method=".$apiMethod."&token=".$sToken."&format=".$apiFormat."&appKey=".$this->appKey.$post_data_filed;	
		$response =$this->phpCurlPost($this->postUrl,$postData);	

}


  //读ERP订单
 public  function Get_postdata_filed($type){ 
       $objectarr   = new StdClass;
	  // $startTime = date('Y-m-d 00:00:00',time());
	  // $endTime = date('Y-m-d 00:00:00',strtotime("+2 day"));

	   $array=array(
	  // "orderNo"=>$orders_id, //网店单号
	  
	     // "startTime"=>$startTime,
	      //"endTime"=>$endTime,
	      "orderStatus"=> 6, // 0：全部[默认]，1: 等待分配仓库，2: 已分配仓库，3: 等待仓库拣货，4: 订单发货中，5: 部分发货，6: 仓库已发货
		  "pageSize"=>20
		);

		//排列
		$data ='';
		if($type=="="){
			foreach($array as $key=>$value){
				$data.="&".$key."=".$value;
				}
		}else{
			foreach($array as $key=>$value){
				$data.=$key.$value;
			}	
			
			}

			
        return $data;
	}


  //读ERP订单
 public  function GetOrder(){
		$apiFormat="json";
		$apiMethod="IOpenAPI.GetOrder";
		$postData=$this->Get_postdata_filed("");//返回加密数据格式
		$inputStr = strtolower(str_replace(" ","",$this->appSecret.$apiMethod."appKey".$this->appKey.$postData));//转小写	
        $sort=$this->mbstringtoarray($inputStr,'utf-8');
        sort($sort,SORT_STRING); 
		$str="";
		for($i=0;$i<count($sort);$i++){
		 $str.=$sort[$i];
		}
		$paramStr=$str;
		$sToken = md5(iconv('utf-8',"gb2312",$paramStr));
		$post_data_filed=$this->Get_postdata_filed("=");//返回POST参数格式
		$postData = "user=".$this->appKey."&method=".$apiMethod."&token=".$sToken."&format=".$apiFormat."&appKey=".$this->appKey.$post_data_filed;	
		$response =$this->phpCurlPost($this->postUrl,$postData);	
		return $this->object_array(json_decode($response));

}

  //读ERP产品库存
 public  function GetProductSkuInfo($id){
	    $arrdata =array('proSkuNo'=>$id);
	 
		$apiFormat="json";
		$apiMethod="IOpenAPI.GetProductSkuInfo";
		$postData=$this->Urldosrt("",$arrdata);//返回加密数据格式
		$inputStr = strtolower(str_replace(" ","",$this->appSecret.$apiMethod."appKey".$this->appKey.$postData));//转小写	
        $sort=$this->mbstringtoarray($inputStr,'utf-8');
        sort($sort,SORT_STRING); 
		$str="";
		for($i=0;$i<count($sort);$i++){
		 $str.=$sort[$i];
		}
		$paramStr=$str;
		$sToken = md5(iconv('utf-8',"gb2312",$paramStr));
		$post_data_filed=$this->Urldosrt("=",$arrdata);//返回POST参数格式
		$postData = "user=".$this->appKey."&method=".$apiMethod."&token=".$sToken."&format=".$apiFormat."&appKey=".$this->appKey.$post_data_filed;	
		$response =$this->phpCurlPost($this->postUrl,$postData);	
		return $this->object_array(json_decode($response));
}


// object to array
public  function object_array($array=array()){
	  if(is_object($array)){
		$array = (array)$array;
	  }
	  if(is_array($array)){
		foreach($array as $key=>$value){
		  $array[$key] = $this->object_array($value);
		}
	  }
	  return $array;
} 



  //排列
 public  function Urldosrt($type='',$array=''){
	    $data='';
		if($type=="="){
			foreach($array as $key=>$value){
				$data.="&".$key."=".$value;
			}
		}else{
			foreach($array as $key=>$value){
				$data.=$key.$value;
			}	
		}
    return $data;
 }
	
	
	
 public	function mbstringtoarray($str,$charset) {//分割字符
	  $strlen=mb_strlen($str);
	  while($strlen){
		$array[]=mb_substr($str,0,1,$charset);
		$str=mb_substr($str,1,$strlen,$charset);
		$strlen=mb_strlen($str);
	  }
	  return $array;
  }

 public  function phpCurlPost($url,$postStr = "")//post 提交
  {
	$curl = curl_init($url);
	
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postStr);
	
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_FAILONERROR, false);
	
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	$response = curl_exec($curl) or die("error：".curl_errno($curl));
	curl_close($curl);
	$result = (array)json_decode($response);
	return $response;
  }

  
  
}


