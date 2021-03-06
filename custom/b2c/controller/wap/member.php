<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_wap_member extends wap_frontpage{

    function __construct(&$app){
        parent::__construct($app);
        $shopname = app::get('site')->getConf('site.name');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('会员中心').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('会员中心_').'_'.$shopname;
            $this->description = app::get('b2c')->_('会员中心_').'_'.$shopname;
        }
        $this->header .= '<meta name="robots" content="noindex,noarchive,nofollow" />';
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->verify_member();
        $this->pagesize = 10;
        $this->action = $this->_request->get_act_name();
        if(!$this->action) $this->action = 'index';
        $this->action_view = $this->action.".html";
        $this->member = $this->get_current_member();
        /** end **/
    }

    /*
     *本控制器公共分页函数
     * */
    function pagination($current,$totalPage,$act,$arg='',$app_id='b2c',$ctl='wap_member'){ //本控制器公共分页函数
        if (!$arg){
            $this->pagedata['pager'] = array(
                'current'=>$current,
                'total'=>$totalPage,
                'link' =>$this->gen_url(array('app'=>$app_id, 'ctl'=>$ctl,'act'=>$act,'args'=>array(($tmp = time())))),
                'token'=>$tmp,
                );
        }else{
            $arg = array_merge($arg, array(($tmp = time())));
            $this->pagedata['pager'] = array(
                'current'=>$current,
                'total'=>$totalPage,
                'link' =>$this->gen_url(array('app'=>$app_id, 'ctl'=>$ctl,'act'=>$act,'args'=>$arg)),
                'token'=>$tmp,
                );
        }
    }

    function get_start($nPage,$count){
        $maxPage = ceil($count / $this->pagesize);
        if($nPage > $maxPage) $nPage = $maxPage;
        $start = ($nPage-1) * $this->pagesize;
        $start = $start<0 ? 0 : $start;
        $aPage['start'] = $start;
        $aPage['maxPage'] = $maxPage;
        return $aPage;
    }
    // public function down2(){
    //     $this->page('wap/member/down.html');
    // }
    /*
    *绑定微信
    */
    public function callback(){
        if(isset($_GET['code'])){
            
            $weixinbind=app::get('weixin')->model('bind')->dump(array('id'=>1),'*');
            $openid =file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$weixinbind['appid'].'&secret='.$weixinbind['appsecret'].'&code='.$_GET['code'].'&grant_type=authorization_code');
            $openid=json_decode($openid,true);
            
            //是否已绑定微信
            $bindtag=app::get('pam')->model('bind_tag');
            $bindOpenId = $bindtag->getRow('member_id',array('open_id'=>$openid['openid']));
            $bindMember = $bindtag->getRow('member_id',array('member_id'=>$this->app->member_id));
            
            if(!$bindMember && !$bindOpenId){
                $bindWeixinData = array(
                    'member_id' => $this->app->member_id,
                    'open_id' => $openid['openid'],
                    'createtime' => time()
                );
                //查询微信用户信息
                $weixin=file_get_contents('https://api.weixin.qq.com/sns/userinfo?access_token='.$openid['access_token'].'&openid='.$openid['openid'].'&lang=zh_CN');
                $weixin=json_decode($weixin,true);
                //已关注
                if(!isset($weixin['errcode'])){
                   $bindWeixinData['tag_name']= $weixin['nickname'];
                   $b2c_members_model = $this->app->model('members');
                   $member=$b2c_members_model->getRow('avatar,nickname',array('member_id'=>$this->app->member_id));
                    $data=array();
                    //账号是否有昵称
                   if(empty($member['nickname'])){
                        $data['nickname']=$weixin['nickname'];
                   }
                   //账号是否有头像
                   if(empty($member['avatar']) && !empty($weixin['headimgurl'])){
                        $mdl_img = app::get('image')->model('image');
                        $image_id = $mdl_img->store($weixin['headimgurl']);
                        $data['avatar']=$image_id;
                   }
                   if(!empty($data)){
                        $b2c_members_model->update($data,array('member_id'=>$this->app->member_id));
                   }
                }
                $flag = app::get('pam')->model('bind_tag')->insert($bindWeixinData);
            }
            
            
        }
        $url = $this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1));
        $this->redirect($url);
    }
    /*
     *会员中心首页
     * */
    public function index() {
        //面包屑
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $GLOBALS['runtime']['path'] = $this->path;

        //

         // 判断是否微信
        // $Weixin = app::get('pam')->model('bind_tag');
        
        // $bindMember = $Weixin->getRow('member_id',array('member_id'=>$this->app->member_id));
        // $user_agent = $_SERVER['HTTP_USER_AGENT'];
        // if (strpos($user_agent, 'MicroMessenger') === false) {
        // }else if(!$bindMember){
        //     $weixinbind=app::get('weixin')->model('bind')->dump(array('id'=>1),'*');
            
        //     $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$weixinbind['appid'].'&redirect_uri='.urlencode(WWW_URL.'/index.php/wap/member-callback.html').'&response_type=code&scope=snsapi_base#wechat_redirect');        
        // }

        #获取会员等级
        $obj_mem_lv = $this->app->model('member_lv');
        $levels = $obj_mem_lv->getList('name,lv_logo,disabled',array('member_lv_id'=>$this->member['member_lv']));
        if($levels[0]['disabled']=='false'){
            $this->member['levelname'] = $levels[0]['name'];
            $this->member['lv_logo'] = $levels[0]['lv_logo'];
        }
        $oMem_lv = $this->app->model('member_lv');
        $this->pagedata['switch_lv'] = $oMem_lv->get_member_lv_switch($this->member['member_lv']);

        //交易提醒
#        $msgAlert = $this->msgAlert();
#        $this->member = array_merge($this->member,$msgAlert);

        //订单列表
#        $oRder = $this->app->model('orders');//--11sql
#        $aData = $oRder->fetchByMember($this->app->member_id,$nPage=1,array(),5); //--141sql优化点
#        $this->get_order_details($aData, 'member_latest_orders');//--177sql 优化点
#        $this->pagedata['orders'] = $aData['data'];

        //收藏列表
        $obj_member = $this->app->model('member_goods');
        $aData_fav = $obj_member->get_favorite($this->app->member_id,$this->member['member_lv'],$page=1,$num=4);//201sql
        $this->pagedata['favorite'] = $aData_fav['data'];
        #默认图片
#        $imageDefault = app::get('image')->getConf('image.set');
#        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        //会员头像
        $filter=array('member_id'=>$this->member['member_id']);
        $image_id=$this->app->model('members')->dump($filter,'avatar','default');
        $this->pagedata['image_id'] = $image_id['avatar'];
        //输出
        $this->pagedata['member'] = $this->member;
        $this->set_tmpl('member');
        //微店id
        $shop=kernel::single('microshop_mdl_shop')->dump($filter,'shop_id,is_open');
        $this->pagedata['shop']=$shop;
        //未评价商品咨询开关
        $this->pagedata['comment_switch_discuss'] = $this->app->getConf('comment.switch.discuss');
        $this->pagedata['comment_switch_ask'] = $this->app->getConf('comment.switch.ask');
        $this->page('wap/member/index.html');
    }
    /*
        会员交易金额记录
    */
    public function advance($nPage=1){
        $count=$this->app->model('member_advance')->count(array('member_id'=>$this->member['member_id'],'disabled'=>'false'));
        $aPage = $this->get_start($nPage,$count);
        
        $this->pagination($nPage,$aPage['maxPage'],'advance');
        $advance=$this->app->model('member_advance')->getList('message,mtime,money,import_money,explode_money,member_advance',array('member_id'=>$this->member['member_id'],'disabled'=>'false'),$aPage['start'],$this->pagesize);

        $this->pagedata['advance']=$advance;
        $this->page('wap/member/advance.html');
    }

    /*
    *会员中心设置
    */
    public function setting(){

        //输出
        $this->pagedata['member'] = $this->member;
        $filter=array('member_id'=>$this->member['member_id']);
        $image_id=$this->app->model('members')->dump($filter,'avatar','default');
        $this->pagedata['image_id'] = $image_id['avatar'];
        $this->page('wap/member/setting.html');
    }   

     /*
     *会员中心首页交易提醒 (未付款订单,到货通知，未读的评论咨询回复)
     * */
    private function msgAlert(){
        //获取待付款订单数
        $oRder = $this->app->model('orders');//--11sql
        $un_pay_orders = $oRder->count(array('member_id' => $this->member['member_id'],'pay_status' => 0,'status'=>'active'));
        $member['un_pay_orders'] = $un_pay_orders;

        //到货通知
        $member_goods = $this->app->model('member_goods');
        $member['sto_goods_num'] = $member_goods->get_goods($this->app->member_id);

        //评论咨询回复
        $mem_msg = $this->app->model('member_comments');
        $object_type = array('discuss','ask');
        $aData = $mem_msg->getList('*',array('to_id' => $this->app->member_id,'object_type'=> $object_type,'mem_read_status' => 'false','display' => 'true'));
        $un_readAskMsg = 0;
        $un_readDiscussMsg = 0;
        foreach($aData as $val){
            if($val['object_type'] == 'ask'){
                $un_readAskMsg += 1;
            }else{
                $un_readDiscussMsg += 1;
            }
        }
        $member['un_readAskMsg'] = $un_readAskMsg;
        $member['un_readDiscussMsg'] = $un_readDiscussMsg;
        return $member;
    }

    //积分历史
    function point_history($nPage=1){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('积分历史'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $member = $this->app->model('members');
        $member_point = $this->app->model('member_point');
        $obj_gift_link = kernel::service('b2c.exchange_gift');
        if ($obj_gift_link)
        {
            $this->pagedata['exchange_gift_link'] = $obj_gift_link->gen_exchange_link();
        }
        // 额外的会员的信息 - 冻结积分、将要获得的积分
        $obj_extend_point = kernel::servicelist('b2c.member_extend_point_info');
        if ($obj_extend_point)
        {
            foreach ($obj_extend_point as $obj)
            {
                $this->pagedata['extend_point_html'] = $obj->gen_extend_detail_point($this->app->member_id);
            }
        }
        $data = $member->dump($this->app->member_id,'*',array('score/event'=>array('*')));
        $count = count($data['score']['event']);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $member_point->getList('*',array('member_id' => $this->app->member_id),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$params['page'],'point_history');
        $this->pagedata['total'] = $data['score']['total'];
        $this->pagedata['historys'] = $params['data'];
        $this->page('wap/member/point_history.html');
    }

    //我的订单
    public function orders($pay_status='all', $nPage=1)
    {
         $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('我的订单'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $order = $this->app->model('orders');
        $color=1;
        if ($pay_status == 'all')
        {
            $color=4;
            if($_GET['pay_status_mebers'] == 1){
                $order_status['pay_status'] = 0; //0:未支付;
                $order_status['status'] = 'active';
                $color=1;
            }elseif($_GET['pay_status_mebers'] == 5){//退款、售后
                // $order_status['status'] = 'active';
                $order_status['ship_status'] = 1; //发货状态 0:未发货;1:已发货;
                $order_status['pay_status'] = 1;
                $color=5;
            }elseif($_GET['ship_status_mebers'] == 1){
                $order_status['ship_status']=0; //发货状态 0:未发货;1:已发货;
                $order_status['pay_status']=1;
                $order_status['status'] = 'active';
                $color=2;
            }elseif($_GET['ship_status_mebers'] == 2){
                $order_status['ship_status'] = 1; //发货状态 0:未发货;1:已发货;
                $order_status['pay_status'] = 1;
                $order_status['status'] = 'active';
                $color=3;
            }
            //$order_status['pay_status'] = 1; //付款状态 0:未支付;1:已支付;
            //$order_status['ship_status'] = 1; //发货状态 0:未发货;1:已发货;
            //$order_status['status'] = 'active';
            $aData = $order->fetchByMember($this->app->member_id,$nPage-1,$order_status);
        }
        else
        {
            $order_status = array();
            if ($pay_status == 'nopayed')
            {
                $order_status['pay_status'] = 0;
                $order_status['status'] = 'active';
            }
            $aData = $order->fetchByMember($this->app->member_id,$nPage-1,$order_status);
        }
        $this->pagedata['color'] = $color;

        $this->get_order_details($aData,'member_orders');
        $oImage = app::get('image')->model('image');
        $oGoods = app::get('b2c')->model('goods');
        $imageDefault = app::get('image')->getConf('image.set');
        foreach($aData['data'] as $k => &$v) {
            foreach($v['goods_items'] as $k2 => &$v2) {
                $spec_desc_goods = $oGoods->getList('spec_desc',array('goods_id'=>$v2['product']['goods_id']));
                $select_spec_private_value_id = reset($v2['product']['products']['spec_desc']['spec_private_value_id']);
                $spec_desc_goods = reset($spec_desc_goods[0]['spec_desc']);
                if($spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']){
                    list($default_product_image) = explode(',', $spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']);
                    $v2['product']['thumbnail_pic'] = $default_product_image;
                }else{
                    if( !$v2['product']['thumbnail_pic'] && !$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                        $v2['product']['thumbnail_pic'] = $imageDefault['S']['default_image'];
                    }
                }
            }
            if($color==5){
                $product= kernel::single('aftersales_mdl_return_product');
                $v['refund']=$product->dump(array('order_id'=>$v['order_id']),'*');
            }else{
                $v['refund']='1';
            }
        }
        
        $this->pagedata['orders'] = $aData['data'];
        $arr_args = array($pay_status);
        $this->pagination($nPage,$aData['pager']['total'],'orders',$arr_args);
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['is_orders'] = "true";

        $this->page('wap/member/orders.html');
    }

    /**
     * 得到订单列表详细
     * @param array 订单详细信息
     * @param string tpl
     * @return null
     */
    protected function get_order_details(&$aData,$tml='member_orders')
    {
        if (isset($aData['data']) && $aData['data'])
        {
            $objMath = kernel::single('ectools_math');
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
            }

            foreach ($aData['data'] as &$arr_data_item)
            {
                $this->get_order_detail_item($arr_data_item,$tml);
            }
        }
    }

    /**
     * 得到订单列表详细
     * @param array 订单详细信息
     * @param string 模版名称
     * @return null
     */
    protected function get_order_detail_item(&$arr_data_item,$tpl='member_order_detail')
    {
        if (isset($arr_data_item) && $arr_data_item)
        {
            $objMath = kernel::single('ectools_math');
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
            }


            $arr_data_item['goods_items'] = array();
            $obj_specification = $this->app->model('specification');
            $obj_spec_values = $this->app->model('spec_values');
            $obj_goods = $this->app->model('goods');
            $oImage = app::get('image')->model('image');
            if (isset($arr_data_item['order_objects']) && $arr_data_item['order_objects'])
            {
                foreach ($arr_data_item['order_objects'] as $k=>$arr_objects)
                {
                    $index = 0;
                    $index_adj = 0;
                    $index_gift = 0;
                    $image_set = app::get('image')->getConf('image.set');
                    if ($arr_objects['obj_type'] == 'goods')
                    {
                        foreach ($arr_objects['order_items'] as $arr_items)
                        {
                            if (!$arr_items['products'])
                            {
                                $o = $this->app->model('order_items');
                                $tmp = $o->getList('*', array('item_id'=>$arr_items['item_id']));
                                $arr_items['products']['product_id'] = $tmp[0]['product_id'];
                            }

                            if ($arr_items['item_type'] == 'product')
                            {
                                if ($arr_data_item['goods_items'][$k]['product'])
                                    $arr_data_item['goods_items'][$k]['product']['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k]['product']['quantity']));
                                else
                                    $arr_data_item['goods_items'][$k]['product']['quantity'] = $arr_items['quantity'];

                                $arr_data_item['goods_items'][$k]['product'] = $arr_items;
                                $arr_data_item['goods_items'][$k]['product']['name'] = $arr_items['name'];
                                $arr_data_item['goods_items'][$k]['product']['goods_id'] = $arr_items['goods_id'];
                                $arr_data_item['goods_items'][$k]['product']['price'] = $arr_items['price'];
                                $arr_data_item['goods_items'][$k]['product']['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k]['product']['quantity']);
                                $arr_data_item['goods_items'][$k]['product']['amount'] = $arr_items['amount'];
                                $arr_goods_list = $obj_goods->getList('image_default_id,spec_desc', array('goods_id' => $arr_items['goods_id']));
                                $arr_goods = $arr_goods_list[0];
                                // 获取货品关联第一张图片
                                $select_spec_private_value_id = reset($arr_items['products']['spec_desc']['spec_private_value_id']);
                                $spec_desc_goods = reset($arr_goods['spec_desc']);
                                if($spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']){
                                    list($default_product_image) = explode(',', $spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']);
                                    $arr_goods['image_default_id'] = $default_product_image;
                                }else{
                                    if( !$arr_goods['image_default_id'] && !$oImage->getList("image_id",array('image_id'=>$arr_goods['image_default_id']))){
                                        $arr_goods['image_default_id'] = $image_set['S']['default_image'];
                                    }
                                }

                                $arr_data_item['goods_items'][$k]['product']['thumbnail_pic'] = $arr_goods['image_default_id'];
                                $arr_data_item['goods_items'][$k]['product']['link_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_product','act'=>'index','arg0'=>$arr_items['products']['product_id']));;
                                if ($arr_items['addon'])
                                {
                                    $arrAddon = $arr_addon = unserialize($arr_items['addon']);
                                    if ($arr_addon['product_attr'])
                                        unset($arr_addon['product_attr']);
                                    $arr_data_item['goods_items'][$k]['product']['minfo'] = $arr_addon;
                                }else{
                                    unset($arrAddon,$arr_addon);
                                }
                                if ($arrAddon['product_attr'])
                                {
                                    foreach ($arrAddon['product_attr'] as $arr_product_attr)
                                    {
                                        $arr_data_item['goods_items'][$k]['product']['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                    }
                                }

                                if (isset($arr_data_item['goods_items'][$k]['product']['attr']) && $arr_data_item['goods_items'][$k]['product']['attr'])
                                {
                                    if (strpos($arr_data_item['goods_items'][$k]['product']['attr'], $this->app->_(" ")) !== false)
                                    {
                                        $arr_data_item['goods_items'][$k]['product']['attr'] = substr($arr_data_item['goods_items'][$k]['product']['attr'], 0, strrpos($arr_data_item['goods_items'][$k]['product']['attr'], $this->app->_(" ")));
                                    }
                                }
                            }
                            elseif ($arr_items['item_type'] == 'adjunct')
                            {
                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_items['item_type']];
                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);


                                if ($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj])
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity']));
                                else
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity'] = $arr_items['quantity'];

                                if (!$arrGoods['image_default_id'])
                                {
                                    $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                }
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj] = $arr_items;
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['name'] = $arr_items['name'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity']);
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['goods_id'] = $arr_items['goods_id'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['price'] = $arr_items['price'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['link_url'] = $arrGoods['link_url'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['amount'] = $arr_items['amount'];

                                if ($arr_items['addon'])
                                {
                                    $arr_addon = unserialize($arr_items['addon']);

                                    if ($arr_addon['product_attr'])
                                    {
                                        foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                        {
                                            $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                        }
                                    }
                                }

                                if (isset($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr']) && $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'])
                                {
                                    if (strpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], $this->app->_(" ")) !== false)
                                    {
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'] = substr($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], 0, strrpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], $this->app->_(" ")));
                                    }
                                }

                                $index_adj++;
                            }
                            else
                            {
                                // product gift.
                                if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                                {
                                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_items['item_type']];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);

                                    if ($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift])
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']));
                                    else
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity'] = $arr_items['quantity'];

                                    if (!$arrGoods['image_default_id'])
                                    {
                                        $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                    }
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift] = $arr_items;
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['name'] = $arr_items['name'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['goods_id'] = $arr_items['goods_id'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['price'] = $arr_items['price'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity']);
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['link_url'] = $arrGoods['link_url'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['amount'] = $arr_items['amount'];

                                    if ($arr_items['addon'])
                                    {
                                        $arr_addon = unserialize($arr_items['addon']);

                                        if ($arr_addon['product_attr'])
                                        {
                                            foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                            {
                                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                            }
                                        }
                                    }

                                    if (isset($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr']) && $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'])
                                    {
                                        if (strpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], $this->app->_(" ")) !== false)
                                        {
                                            $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'] = substr($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], 0, strrpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], $this->app->_(" ")));
                                        }
                                    }
                                }
                                $index_gift++;
                            }
                        }
                    }
                    else
                    {
                        if ($arr_objects['obj_type'] == 'gift')
                        {
                            if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                            {
                                foreach ($arr_objects['order_items'] as $arr_items)
                                {
                                    if (!$arr_items['products'])
                                    {
                                        $o = $this->app->model('order_items');
                                        $tmp = $o->getList('*', array('item_id'=>$arr_items['item_id']));
                                        $arr_items['products']['product_id'] = $tmp[0]['product_id'];
                                    }

                                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_objects['obj_type']];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);

                                    if (!isset($arr_items['products']['product_id']) || !$arr_items['products']['product_id'])
                                        $arr_items['products']['product_id'] = $arr_items['goods_id'];

                                    if ($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']])
                                        $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']));
                                    else
                                        $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity'] = $arr_items['quantity'];

                                    if (!$arrGoods['image_default_id'])
                                    {
                                        $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                    }

                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['name'] = $arr_items['name'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['goods_id'] = $arr_items['goods_id'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['price'] = $arr_items['price'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['point'] = intval($arr_items['score']*$arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']);
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['nums'] = $arr_items['quantity'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['link_url'] = $arrGoods['link_url'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['amount'] = $arr_items['amount'];

                                    if ($arr_items['addon'])
                                    {
                                        $arr_addon = unserialize($arr_items['addon']);

                                        if ($arr_addon['product_attr'])
                                        {
                                            foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                            {
                                                $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                            }
                                        }
                                    }

                                    if (isset($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr']) && $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'])
                                    {
                                        if (strpos($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], $this->app->_(" ")) !== false)
                                        {
                                            $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'] = substr($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], 0, strrpos($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], $this->app->_(" ")));
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                            {

                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_objects['obj_type']];
                                $arr_data_item['extends_items'][] = $str_service_goods_type_obj->get_order_object($arr_objects, $arr_Goods,$tpl);
                            }
                        }
                    }
                }
            }

        }
    }

    /**
     * Generate the order detail
     * @params string order_id
     * @return null
     */
    public function orderdetail($order_id=0)
    {
        if (!isset($order_id) || !$order_id)
        {
            $this->begin(array('app' => 'b2c','ctl' => 'wap_member', 'act'=>'index'));
            $this->end(false, app::get('b2c')->_('订单编号不能为空！'));
        }

        $objOrder = $this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $sdf_order = $objOrder->dump($order_id, '*', $subsdf);
        $objMath = kernel::single("ectools_math");
        if(!$sdf_order||$this->app->member_id!=$sdf_order['member_id']){
            $this->_response->set_http_response_code(404);
            $this->_response->set_body(app::get('b2c')->_('订单号：') . $order_id . app::get('b2c')->_('不存在！'));
            return;
        }
        if($sdf_order['member_id']){
            $member = $this->app->model('members');
            $aMember = $member->dump($sdf_order['member_id'], 'email');
            $sdf_order['receiver']['email'] = $aMember['contact']['email'];
        }

        // 处理收货人地区
        $arr_consignee_area = array();
        $arr_consignee_regions = array();
        if (strpos($sdf_order['consignee']['area'], ':') !== false)
        {
            $arr_consignee_area = explode(':', $sdf_order['consignee']['area']);
            if ($arr_consignee_area[1])
            {
                if (strpos($arr_consignee_area[1], '/') !== false)
                {
                    $arr_consignee_regions = explode('/', $arr_consignee_area[1]);
                }
            }

            $sdf_order['consignee']['area'] = (is_array($arr_consignee_regions) && $arr_consignee_regions) ? $arr_consignee_regions[0] . $arr_consignee_regions[1] . $arr_consignee_regions[2] : $sdf_order['consignee']['area'];
        }

        // 订单的相关信息的修改
        $obj_other_info = kernel::servicelist('b2c.order_other_infomation');
        if ($obj_other_info)
        {
            foreach ($obj_other_info as $obj)
            {
                $this->pagedata['discount_html'] = $obj->gen_point_discount($sdf_order);
            }
        }
        $this->pagedata['order'] = $sdf_order;

        $order_items = array();
        $gift_items = array();
        $this->get_order_detail_item($sdf_order,'member_order_detail');
        $this->pagedata['order'] = $sdf_order;
// echo "<pre>";print_r($this->pagedata['order']);exit;
        /** 将商品促销单独剥离出来 **/
        if ($this->pagedata['order']['order_pmt'])
        {
            foreach ($this->pagedata['order']['order_pmt'] as $key=>$arr_pmt)
            {
                if ($arr_pmt['pmt_type'] == 'goods')
                {
                    $this->pagedata['order']['goods_pmt'][$arr_pmt['product_id']][$key] =  $this->pagedata['order']['order_pmt'][$key];
                    unset($this->pagedata['order']['order_pmt'][$key]);
                }
            }
        }
        /** end **/

        // 得到订单留言.
        $oMsg = kernel::single("b2c_message_order");
        $arrOrderMsg = $oMsg->getList('*', array('order_id' => $order_id, 'object_type' => 'order'), $offset=0, $limit=-1, 'time DESC');

        $this->pagedata['ordermsg'] = $arrOrderMsg;
        $this->pagedata['res_url'] = $this->app->res_url;

        //我已付款
        $$timeHours = array();
        for($i=0;$i<24;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeHours[$v] = $v;
        }
        $timeMins = array();
        for($i=0;$i<60;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeMins[$v] = $v;
        }
        $this->pagedata['timeHours'] = $timeHours;
        $this->pagedata['timeMins'] = $timeMins;

        // 生成订单日志明细
        //$oLogs =$this->app->model('order_log');
        //$arr_order_logs = $oLogs->getList('*', array('rel_id' => $order_id));
        $arr_order_logs = $objOrder->getOrderLogList($order_id);

        // 取到支付单信息
        $obj_payments = app::get('ectools')->model('payments');
        $this->pagedata['paymentlists'] = $obj_payments->get_payments_by_order_id($order_id);

        // 支付方式的解析变化
        $obj_payments_cfgs = app::get('ectools')->model('payment_cfgs');
        $arr_payments_cfg = $obj_payments_cfgs->getPaymentInfo($this->pagedata['order']['payinfo']['pay_app_id']);
        $this->pagedata['order']['payment'] = $arr_payments_cfg;

        #//物流跟踪安装并且开启
        #$logisticst = app::get('b2c')->getConf('system.order.tracking');
        #$logisticst_service = kernel::service('b2c_change_orderloglist');
        #if(isset($logisticst) && $logisticst == 'true' && $logisticst_service){
        #    $this->pagedata['services']['logisticstack'] = $logisticst_service;
        #}
        $this->pagedata['orderlogs'] = $arr_order_logs['data'];
        // 添加html埋点
        foreach( kernel::servicelist('b2c.order_add_html') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'fetchHtml') ) {
                    $services->fetchHtml($this,$order_id,'site/invoice_detail.html');
                }
            }
        }
        $this->pagedata['controller'] = "orders";
        $this->page('wap/member/orderdetail.html');
    }

    function deposit(){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('预存款充值'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $oCur = app::get('ectools')->model('currency');
        $currency = $oCur->getDefault();
        $this->pagedata['currencys'] = $currency;
        $this->pagedata['currency'] = $currency['cur_code'];
        $opay = app::get('ectools')->model('payment_cfgs');
        $aOld = $opay->getList('*', array('status' => 'true', 'platform'=>array('iscommon','iswap'), 'is_frontend' => true));

        #获取默认的货币
        $obj_currency = app::get('ectools')->model('currency');
        $arr_def_cur = $obj_currency->getDefault();
        $this->pagedata['def_cur_sign'] = $arr_def_cur['cur_sign'];

        $aData = array();
        foreach($aOld as $val){
            if(($val['app_id']!='deposit') && ($val['app_id']!='offline')){
                $aData[] = $val;
            }
        }

        $this->pagedata['total'] = $this->member['advance'];
        $this->pagedata['payments'] = $aData;
        $this->pagedata['member_id'] = $this->app->member_id;
        $this->pagedata['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'balance'));

        $this->page('wap/member/deposit.html');
    }


    //预存款交易记录
    public function balance($nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的预存款'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $member = $this->app->model('members');
        $mem_adv = $this->app->model('member_advance');
        $items_adv = $mem_adv->get_list_bymemId($this->app->member_id);
        $count = count($items_adv);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $mem_adv->getList('*',array('member_id' => $this->app->member_id),$aPage['start'],$this->pagesize,'mtime desc');
        $params['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$params['page'],'balance');
        $this->pagedata['advlogs'] = $params['data'];
        $data = $member->dump($this->app->member_id,'advance');
        $this->pagedata['total'] = $data['advance']['total'];
        // errorMsg parse.
        $this->pagedata['errorMsg'] = json_decode($_GET['errorMsg']);
        $this->page('wap/member/balance.html');
    }

    function favorite($nPage=1){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('商品收藏'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $aData = kernel::single('b2c_member_fav')->get_favorite($this->app->member_id,$this->member['member_lv'],$nPage);
        $imageDefault = app::get('image')->getConf('image.set');
        $aProduct = $aData['data'];
        foreach($aProduct as $k=>$v){
            if($v['nostore_sell']){
                $aProduct[$k]['store'] = 999999;
                $aProduct[$k]['product_id'] = $v['spec_desc_info'][0]['product_id'];
            }else{
                foreach((array)$v['spec_desc_info'] as $value){
                    $aProduct[$k]['product_id'] = $value['product_id'];
                    $aProduct[$k]['spec_info'] = $value['spec_info'];
                    $aProduct[$k]['price'] = $value['price'];
                    if(is_null($value['store']) ){
                        $aProduct[$k]['store'] = 999999;
                        break;
                    }elseif( ($value['store']-$value['freez']) > 0 ){
                        $aProduct[$k]['store'] = $value['store']-$value['freez'];
                        break;
                    }else{
                        $aProduct[$k]['store'] = false;
                    }
                }
            }
        }
        $this->pagedata['favorite'] = $aProduct;
        $this->pagination($nPage,$aData['page'],'favorite');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        $this->pagedata['current_page'] = $nPage;
        /** 接触收藏的页面地址 **/
        $this->pagedata['fav_ajax_del_goods_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'ajax_del_fav','args'=>array('goods')));
        $this->page('wap/member/favorite.html');
    }

    /*
     *删除商品收藏
     * */
     function ajax_del_fav($gid=null,$object_type='goods'){
        if(!$gid){
            $this->splash('error',null,app::get('b2c')->_('参数错误！'));
        }
        if (!kernel::single('b2c_member_fav')->del_fav($this->app->member_id,$object_type,$gid,$maxPage)){
            $this->splash('error',null,app::get('b2c')->_('移除失败！'));
        }else{
            $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);

            $current_page = $_POST['current'];
            if ($current_page > $maxPage){
                $current_page = $maxPage;
                $reload_url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'favorite','args'=>array($current_page)));
                $this->splash('success',$reload_url,app::get('b2c')->_('成功移除！'));
            }
            $aData = kernel::single('b2c_member_fav')->get_favorite($this->app->member_id,$this->member['member_lv'],$current_page);
            $aProduct = $aData['data'];

            $oImage = app::get('image')->model('image');
            $imageDefault = app::get('image')->getConf('image.set');
            foreach($aProduct as $k=>$v) {
                if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                    $aProduct[$k]['image_default_id'] = $imageDefault['S']['default_image'];
                }
            }
            $this->pagedata['favorite'] = $aProduct;
            $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
            $reload_url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'favorite'));
            $this->splash('success',$reload_url,app::get('b2c')->_('成功移除！'));
        }
    }

    function ajax_fav() {
        $object_type = $_POST['type'];
        $nGid = $_POST['gid'];
        if (!kernel::single('b2c_member_fav')->add_fav($this->app->member_id,$object_type,$nGid)){
            $this->splash('failed', app::get('b2c')->_('商品收藏添加失败！'), '', '', true);
        }else{
            $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);
            $this->splash('success',$url,app::get('b2c')->_('商品收藏添加成功'));
        }
    }

    //收获地址
    function receiver(){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('收货地址'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $objMem = $this->app->model('members');
        $this->pagedata['receiver'] = $objMem->getMemberAddr($this->app->member_id);
        $this->pagedata['is_allow'] = (count($this->pagedata['receiver'])<10 ? 1 : 0);
        $this->pagedata['num'] = count($this->pagedata['receiver']);
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->page('wap/member/receiver.html');
    }


    /*
     * 设置和取消默认地址，$disabled 2为设置默认1为取消默认
     */
    function set_default($addrId=null,$disabled=2){
        // $addrId = $_POST['addr_id'];
        if(!$addrId) $this->splash('failed',null, app::get('b2c')->_('参数错误'),true);
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'receiver'));
        $obj_member = $this->app->model('members');
        $member_id = $this->app->member_id;
        if($obj_member->check_addr($addrId,$member_id)){
            if($obj_member->set_to_def($addrId,$member_id,$message,$disabled)){
                $this->splash('success',$url,$message);
            }else{
                $this->splash('failed',$url,$message);
            }
        }else{
            $this->splash('failed', 'back', app::get('b2c')->_('参数错误'));
        }
    }

    /*
     *添加、修改收货地址
     * */
    function modify_receiver($addrId=null){
        if(!$addrId){
            echo  app::get('b2c')->_("参数错误");exit;
        }
        $obj_member = $this->app->model('members');
        if($obj_member->check_addr($addrId,$this->app->member_id)){
            if($aRet = $obj_member->getAddrById($addrId)){
                $aRet['defOpt'] = array('0'=>app::get('b2c')->_('否'), '1'=>app::get('b2c')->_('是'));
                 $this->pagedata = $aRet;
            }else{
                $this->_response->set_http_response_code(404);
                $this->_response->set_body(app::get('b2c')->_('修改的收货地址不存在！'));
                exit;
            }
            $this->page('wap/member/modify_receiver.html');
        }else{
            echo  app::get('b2c')->_("参数错误");exit;
        }
    }

    //修改用户昵称
    function nickname(){
        $this->pagedata['mem'] =$this->member;
        $this->page('wap/member/nickname.html');
    }
    function save_setting(){
        if(empty($_POST['nickname'])){
            $this->splash('failed',null,app::get('b2c')->_('昵称不能为空！'),true); 
        }

        $url = $this->gen_url(array('app'=>'b2c','ctl'=>"wap_member",'act'=>"setting"));
        $member_model = $this->app->model('members');
        $data = array('nickname'=>strip_tags(trim($_POST['nickname'])));
        $filter = array('member_id'=>$this->app->member_id);
        if($member_model->update($data,$filter)){
            kernel::single('b2c_huanxin_registhuanxin')->edit_nickname($this->app->member_id,$_POST['nickname']);
            $this->splash('success', $url , app::get('b2c')->_('修改成功'),true);
        }else{
            $this->splash('failed',null , app::get('b2c')->_('修改失败'),true);
        }
    }
    //确认收货
    function dofinsh($order_id=false){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'orders')).'?ship_status_mebers=2';
        if(!$order_id){
            $this->splash('success',$url,app::get('b2c')->_('参数错误！'),true);
        }
        $orders=$this->app->model('orders');
        $filter=array('order_id'=>$order_id);
        $member=$orders->dump($filter,'member_id');

        $reship=$this->app->model('reship');

        if(!empty($member) && $member['member_id']==$this->member['member_id']){
            //开启事务
            $db = kernel::database();
            $transaction_status = $db->beginTransaction();

            $data=array('status'=>'finish','confirm'=>'Y');
            $datas=array('status'=>'succ','t_confirm'=>time());
            if($orders->update($data,$filter) && $reship->update($datas,$filter)){
                $db->commit($transaction_status);
                $this->splash('success', $url , app::get('b2c')->_('修改成功'),true);
            }else{
                $db->rollBack();
                $this->splash('failed',null , app::get('b2c')->_('修改失败'),true);
            }
        }else{
            $this->splash('success',$url,app::get('b2c')->_('订单错误'),true);
        }
    }

    // 修改头像
    function avatar(){
        // $memMdl=  app::get('b2c')->model('members');
        // $members=$memMdl->getList('*',array('member_id'=>$this->app->member_id));
      
        // $this->pagedata['avatar']=$members[0]['avatar'];
        $filter=array('member_id'=>$this->member['member_id']);
        $image_id=$this->app->model('members')->dump($filter,'avatar','default');
        $this->pagedata['image_id'] = $image_id['avatar'];
        $this->page('wap/member/avatar.html');
    }
    
   function save_avatar(){
        if(empty($_FILES['file']['name'])){
            $url = kernel::single('wap_controller')->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'avatar'));
            echo '<script type="text/javascript">alert("您至少选张图片吧");window.location.href="'.$url.'"; </script>';exit();
        }

       $data=array();
       $size = array(
            'width'   => '160',
            'height'  => '160',
        );  // 尺寸

        $image = $this->imageUpload(3,$size);
       if($image['error']){
           $msg=$image['error'];
           $this->splash('failed',null,$msg,'','',true); 
       }
       $data['avatar']=$image['image_id'];
      
       $memMdl=  app::get('b2c')->model('members');
       if($memMdl->update($data,array('member_id'=>$this->app->member_id))){
          
            $msg=  app::get('b2c')->_('修改成功');
            $url = kernel::single('wap_controller')->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'setting'));
            header("location:".$url);

       }else{
           
            //$msg=  app::get('b2c')->_('修改失败');  
            $url = kernel::single('wap_controller')->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'avatar'));
            echo '<script type="text/javascript">alert("修改失败");window.location.href="'.$url.'"; </script>';
            //$this->splash('failed',null,$msg,'','',true); 
       }
       
   } 
   
   //图片上传公共类
    protected function imageUpload($max_size=3,$size=''){
            
        if(empty($size)){
            $size == array('width'=> '400','height'=> '300');
        }
        if ($_FILES['file']['size'] > ($max_size * 1024 * 1024)) {
            $msg= '上传文件不能超过3M！';
        }
        
        if ( $_FILES['file']['name'] != "" ) {
            $type = array("png","jpg","gif","jpeg"); //允许上传的文件
            
            if(!in_array(strtolower($this->fileext($_FILES['file']['name'])), $type)) {
                $text = implode(",", $type);
                                $msg= '您只能上传以下类型文件'.$text;
                
            }
        }
        
        $mdl_img = app::get('image')->model('image');
        
        $image_name = $_FILES['file']['name'];
        
        $image_id = $mdl_img->store($_FILES['file']['tmp_name'],null,null,$image_name);
        $mdl_img->rebuild($image_id,array('l','m','s'));
        $image_src = base_storager::image_path($image_id, 'l'); 
        //return 'baid.com';
        if(empty($image_id)){
                    $msg= '文件上传失败，请重新上传！';
            exit();
        }
        return array(
                    'res'=>'success',
                    'image_id'=>$image_id,
                    'image_url'=>$image_src,
                    'image_name'=>$image_name,
                    'error'=>$msg
                    );
    }
    private function fileext($filename)
    {
        return substr(strrchr($filename, '.'), 1);
    }

    /*
     *保存收货地址
     * */
    function save_rec(){
        if(!$_POST['def_addr']){
            $_POST['def_addr'] = 0;
        }
        $save_data = kernel::single('b2c_member_addrs')->purchase_save_addr($_POST,$this->app->member_id,$msg);
        if(!$save_data){
            $this->splash('failed',null,$msg,'','',true);
        }
        $this->splash('success',$this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'receiver')),app::get('b2c')->_('保存成功'),'','',true);
    }

    //添加收货地址
    function add_receiver(){
        $obj_member = $this->app->model('members');
        if($obj_member->isAllowAddr($this->app->member_id)){
            $this->page('wap/member/modify_receiver.html');
        }else{
            $this->splash('failed',$this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'receiver')),app::get('b2c')->_('最多添加10个收货地址'),'','',true);
        }
    }



    //删除收货地址
    function del_rec($addrId=null){
        if(!$addrId) $this->splash('failed', 'back', app::get('b2c')->_('参数错误'),'','',true);
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'receiver'));
        $obj_member = $this->app->model('members');
        if($obj_member->check_addr($addrId,$this->app->member_id))
        {
            if($obj_member->del_rec($addrId,$message,$this->app->member_id))
            {
                $this->splash('success',$url,$message,'','',true);
            }
            else
            {
                $this->splash('failed',$url,$message,'','',true);
            }
        }
        else
        {
            $this->splash('failed', 'null', app::get('b2c')->_('操作失败'),'','',true);
        }
    }




    /*
        过滤POST来的数据,基于安全考虑,会把POST数组中带HTML标签的字符过滤掉
    */
    function check_input($data){
        $aData = $this->arrContentReplace($data);
        return $aData;
    }

    function arrContentReplace($array){
        if (is_array($array)){
            foreach($array as $key=>$v){
                $array[$key] =     $this->arrContentReplace($array[$key]);
            }
        }
        else{
            $array = strip_tags($array);
        }
        return $array;
    }

    /*
     * 获取评论咨询的数据
     *
     * */
    public function getComment($nPage=1,$type='discuss'){
        //获取评论咨询基本数据
        $comment = kernel::single('b2c_message_disask');
        $aData = $comment->get_member_disask($this->app->member_id,$nPage,$type);
        $gids = array();
        $productGids = array();
        foreach((array)$aData['data'] as $k => $v){
            if($v['type_id'] && !in_array($v['type_id'],$gids) ){
                $gids[] = $v['type_id'];
            }
            if(!$v['product_id'] && !in_array($v['type_id'],$productGids) ){
                $productGids[] = $v['type_id'];
            }

            if($v['items']){//统计回复未读的数量
                $unReadNum = 0;
                foreach($v['items'] as $val){
                    if($val['mem_read_status'] == 'false' ){
                        $unReadNum += 1;
                    }
                }
            }
            $aData['data'][$k]['unReadNum'] = $unReadNum;
        }

        //获取货品ID
        $productData = $productGids ? $this->app->model('products')->getList('goods_id,product_id',array('goods_id'=>$productGids,'is_default'=>'true')) : array();
        foreach((array) $productData as $p_row){
            $productList[$p_row['goods_id']] = $p_row['product_id'];
        }
        $this->pagedata['productList'] = $productList;

        //评论咨询商品信息
        $goodsData = $gids ? $this->app->model('goods')->getList('goods_id,name,price,thumbnail_pic,udfimg,image_default_id',array('goods_id'=>$gids)) : null;
        if($goodsData){
            foreach($goodsData as $row){
                $goodsList[$row['goods_id']] = $row;
            }
        }
        $this->pagedata['goodsList'] = $goodsList;

        //评论咨询私有的数据
        if($type == 'discuss'){
            $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
            if($this->pagedata['point_status'] == 'on'){//如果开启评分则获取评论评分
                $objPoint = $this->app->model('comment_goods_point');
                $goods_point = $objPoint->get_single_point_arr($gids);
                $this->pagedata['goods_point'] = $goods_point;
            }
        }else{
            $gask_type = unserialize($this->app->getConf('gask_type'));//咨询类型
            foreach((array)$gask_type as $row){
                $gask_type_list[$row['type_id']] = $row['name'];
            }
            $this->pagedata['gask_type'] = $gask_type_list;
        }
        return $aData;
    }

    function comment($nPage=1){
        //面包屑
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('评论与咨询'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $comment = $this->getComment($nPage,'discuss');
        $this->pagedata['commentList'] = $comment['data'];
        $this->pagination($nPage,$comment['page'],'comment');
        $this->output();
    }

    function ask($nPage=1){
        //面包屑
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('评论与咨询'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $this->pagedata['controller'] = "comment";
        $comment = $this->getComment($nPage,'ask');
        $this->pagedata['commentList'] = $comment['data'];
        $this->pagedata['commentType'] = 'ask';
        $this->pagination($nPage,$comment['page'],'ask');
        $this->action_view = 'comment.html';
        $this->output();
    }

    /*
     *未评论商品
     **/
    public function nodiscuss($nPage=1){
        //面包屑
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('未评论商品'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        //获取会员已发货的商品日志
        $sell_logs = $this->app->model('sell_logs')->getList('order_id,product_id,goods_id',array('member_id'=>$this->app->member_id));
        //获取会员已评论的商品
        $comments = $this->app->model('member_comments')->getList('order_id,product_id',array('author_id'=>$this->app->member_id,'object_type'=>'discuss','for_comment_id'=>'0'));
        $data = array();
        if($comments){
            foreach((array)$comments as $row){
                if($row['order_id'] && $row['product_id']){
                    $data[$row['order_id']][$row['product_id']] = $row['product_id'];
                }
            }
        }

        foreach((array)$sell_logs as $key=>$log_row){
            if($data && $data[$log_row['order_id']][$log_row['product_id']] == $log_row['product_id']){
                unset($sell_logs[$key]);
            }else{
                $filter['order_id'][] = $log_row['order_id'];
                $filter['product_id'][] = $log_row['product_id'];
                $filter['item_type|noequal'] = 'gift';
            }
        }

        $orderItemModel = app::get('b2c')->model('order_items');
        $limit = $this->pagesize;
        $start = ($nPage-1)*$limit;
        $i = 0;
        $nogift = $orderItemModel->getList('order_id,product_id',$filter);
        if($nogift){
            foreach($nogift as $row){
                $tmp_nogift_order_id[] = $row['order_id'];
                $tmp_nogift_product_id[] = $row['product_id'];
            }
        }
        foreach((array)$sell_logs as $key=>$log_row){
            if(in_array($log_row['order_id'],$tmp_nogift_order_id) && in_array($log_row['product_id'],$tmp_nogift_product_id) ){//剔除赠品,赠品不需要评论
                if($i >= $start && $i < ($nPage*$limit) ){
                    $sell_logs_data[] = $log_row;
                    $gids[] = $log_row['goods_id'];
                }
                if($nogift){
                    $i += 1;
                }
            }
        }
        $totalPage = ceil($i/$limit);
        if($nPage > $totalPage) $nPage = $totalPage;

        $this->pagedata['list'] = $sell_logs_data;
        $this->pagination($nPage,$totalPage,'nodiscuss');

        if($gids){
            //获取商品信息
            $goodsData = $this->app->model('goods')->getList('goods_id,name,image_default_id',array('goods_id'=>$gids));
            $goodsList = array();
            foreach((array)$goodsData as $goods_row){
                $goodsList[$goods_row['goods_id']]['name'] = $goods_row['name'];
                $goodsList[$goods_row['goods_id']]['image_default_id'] = $goods_row['image_default_id'];
            }
            $this->pagedata['goods'] = $goodsList;

            $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
            $this->pagedata['verifyCode'] = $this->app->getConf('comment.verifyCode');
            if($this->pagedata['point_status'] == 'on'){
                //评分类型
                $comment_goods_type = $this->app->model('comment_goods_type');
                $this->pagedata['comment_goods_type'] = $comment_goods_type->getList('*');
                if(!$this->pagedata['comment_goods_type']){
                    $sdf['type_id'] = 1;
                    $sdf['name'] = app::get('b2c')->_('商品评分');
                    $addon['is_total_point'] = 'on';
                    $sdf['addon'] = serialize($addon);
                    $comment_goods_type->insert($sdf);
                    $this->pagedata['comment_goods_type'] = $comment_goods_type->getList('*');
                }
            }

        $this->pagedata['submit_comment_notice'] = $this->app->getConf('comment.submit_comment_notice.discuss');
        }
        $this->page('wap/member/nodiscuss.html');
    }

    //我的优惠券
    function coupon($nPage=1) {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的优惠券'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $oCoupon = kernel::single('b2c_coupon_mem');
        $aData = $oCoupon->get_list_m($this->app->member_id,$nPage);
        if ($aData) {
            foreach ($aData as $k => $item) {
                if ($item['coupons_info']['cpns_status'] !=1) {
                    $aData[$k]['coupons_info']['cpns_status'] = false;
                    $aData[$k]['memc_status'] = app::get('b2c')->_('此种优惠券已取消');
                    continue;
                }

                $member_lvs = explode(',',$item['time']['member_lv_ids']);
                if (!in_array($this->member['member_lv'],(array)$member_lvs)) {
                    $aData[$k]['coupons_info']['cpns_status'] = false;
                    $aData[$k]['memc_status'] = app::get('b2c')->_('本级别不准使用');
                    continue;
                }

                $curTime = time();
                if ($curTime>=$item['time']['from_time'] && $curTime<$item['time']['to_time']) {
                    if ($item['memc_used_times']<$this->app->getConf('coupon.mc.use_times')){
                        if ($item['coupons_info']['cpns_status']){
                            $aData[$k]['memc_status'] = app::get('b2c')->_('可使用');
                        }else{
                            $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券已作废');
                        }
                    }else{
                        $aData[$k]['coupons_info']['cpns_status'] = false;
                        $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券次数已用完');
                    }
                }else{
                    $aData[$k]['coupons_info']['cpns_status'] = false;
                    $aData[$k]['memc_status'] = app::get('b2c')->_('还未开始或已过期');
                }
            }
        }

        $total = $oCoupon->get_list_m($this->app->member_id);
        $this->pagination($nPage,ceil(count($total)/$this->pagesize),'coupon');
        $this->pagedata['coupons'] = $aData;
        $this->page('wap/member/coupon.html');
    }


    /**
     * 添加留言
     * @params string order_id
     * @params string message type
     */
    public function add_order_msg( $order_id , $msgType = 0 ){
        $timeHours = array();
        for($i=0;$i<24;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeHours[$v] = $v;
        }
        $timeMins = array();
        for($i=0;$i<60;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeMins[$v] = $v;
        }
        $this->pagedata['orderId'] = $order_id;
        $this->pagedata['msgType'] = $msgType;
        $this->pagedata['timeHours'] = $timeHours;
        $this->pagedata['timeMins'] = $timeMins;

        $this->page('wap/member/add_order_msg.html');
    }

    /**
     * 订单留言提交
     * @params null
     * @return null
     */
    public function toadd_order_msg()
    {
        if(!$_POST['msg']['orderid']){
            $this->splash(false,app::get('b2c')->_('参数错误'),true);
        }

        $obj_filter = kernel::single('b2c_site_filter');
        $_POST = $obj_filter->check_input($_POST);

        $_POST['to_type'] = 'admin';
        $_POST['author_id'] = $this->app->member_id;
        $_POST['author'] = $this->member['uname'];
        $is_save = true;
        $obj_order_message = kernel::single("b2c_order_message");
        if ($obj_order_message->create($_POST)){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'orderdetail','arg0'=>$_POST['msg']['orderid']));
            $this->splash('success',$url,app::get('b2c')->_('留言成功'),'','',true);
        }else{
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'add_order_msg','arg0'=>$_POST['msg']['orderid'],'arg1'=>1));
            $this->splash(false,$url,app::get('b2c')->_('留言失败'),'','',true);
        }
    }

    /*
     *会员中心 修改密码页面
     * */
    function security($type = ''){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('修改密码'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->page('wap/member/modify_password.html');
    }

    /*
     *保存修改密码
     * */
    function save_security(){
        $passport_login = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'login'));
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'logout','arg0'=>$passport_login));
        $userPassport = kernel::single('b2c_user_passport');
        $result = $userPassport->save_security($this->app->member_id,$_POST,$msg);
        if($result){
            $this->splash('success',$url,$msg,'','',true);
        }else{
            $this->splash('failed',null,$msg,'','',true);
        }
    }
    

    //意见反馈
    function message($nMsgId=false, $status='send') { //给管理员发信件

        $num=$this->get_msg_num(); //方法
        if($nMsgId){
            $objMsg = kernel::single('b2c_message_msg');
            $init =  $objMsg->dump($nMsgId);
            if($init['author_id'] == $this->app->member_id)
            {
                $this->pagedata['init'] = $init;
                $this->pagedata['msg_id'] = $nMsgId;
            }
        }
        if($status === 'reply'){
            $this->pagedata['reply'] = 1;
        }
        $this->pagedata['controller'] = "inbox";
        //$this->output();
        $this->page('wap/member/member_message.html');//文件路径
    }

    /*
     *获取收件箱未读信息数量
     * */
    function get_msg_num(){
        $oMsg = kernel::single('b2c_message_msg');
        $row = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
        $this->pagedata['inbox_num'] = count($row)?count($row):0;
        $row = $oMsg->getList('*',array('has_sent' => 'false','author_id' => $this->app->member_id));
        $this->pagedata['outbox_num'] = count($row)?count($row):0;
    }

    /*
     *发送站内信
     * */
    function send_msg(){
        if(!isset($_POST['msg_to']) || $_POST['msg_to'] == '管理员'){
            $_POST['to_type'] = 'admin';
            $_POST['msg_to'] = 0;
        }else{
            $userObject = kernel::single('b2c_user_object');
            $to_id = $userObject->get_id_by_uname($_POST['msg_to']);
            if(!$to_id){
                $this->splash('failed',null,app::get('b2c')->_('收件人不存在'),$_POST['response_json']);
            }
            $_POST['to_id'] = $to_id;
        }
        if($_POST['subject'] && $_POST['comment']) {
            $objMessage = kernel::single('b2c_message_msg');
            $_POST['has_sent'] = $_POST['has_sent'] == 'false' ? 'false' : 'true';
            $_POST['member_id'] = $this->app->member_id;
            $_POST['uname'] = $this->member['uname'];
            $_POST['contact'] = $this->member['email'];
            $_POST['ip'] = $_SERVER["REMOTE_ADDR"];
            $_POST['subject'] = strip_tags($_POST['subject']);
            $_POST['comment'] = strip_tags($_POST['comment']);
            if($_POST['comment_id']){
                //$data['comment_id'] = $_POST['comment_id'];
                $_POST['comment_id'] = '';//防止用户修改comment_id
            }
            if( $objMessage->send($_POST) ) {
            if($_POST['has_sent'] == 'false'){
                $this->splash('success','member/index.html',app::get('b2c')->_('保存到草稿箱成功'),$_POST['response_json']);
            }else{
                $this->splash('success','member/index.html',app::get('b2c')->_('发送成功'),$_POST['response_json']);
            }
            } else {
                $this->splash('failed','wap/member/member_message.html',app::get('b2c')->_('发送失败'),$_POST['response_json']);
            
            }
        }
        else {
            $this->splash('failed','member-message.html',app::get('b2c')->_('必填项不能为空'),$_POST['response_json']);
        
        }
        
    }


    function cancel($order_id){
        $this->pagedata['cancel_order_id'] = $order_id;
        $this->page('wap/member/order_cancel_reason.html');

    }

    function docancel(){
        $arrMember = kernel::single('b2c_user_object')->get_current_member(); //member_id,uname
        //开启事务处理
        $db = kernel::database();
        $transaction_status = $db->beginTransaction();

        $order_cancel_reason = $_POST['order_cancel_reason'];

        $error_url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'cancel','arg0'=>$order_cancel_reason['order_id']));
        if($order_cancel_reason['reason_type'] == 7 && !$order_cancel_reason['reason_desc'])
        {
            $this->splash('failed',$error_url,'请输入详细原因',true);
        }
        if(strlen($order_cancel_reason['reason_desc'])>150)
        {
            $this->splash('failed',$error_url,'详细原因过长，请输入50个字以内',true);
        }
        if($order_cancel_reason['reason_type'] != 7 && strlen($order_cancel_reason['reason_desc']) > 0)
        {
            $order_cancel_reason['reason_desc'] = '';
        }
        $order_cancel_reason = utils::_filter_input($order_cancel_reason);
        $order_cancel_reason['cancel_time'] = time();
        $mdl_order = app::get('b2c')->model('orders');
        $sdf_order_member_id = $mdl_order->getRow('member_id', array('order_id'=>$order_cancel_reason['order_id']));
        if($sdf_order_member_id['member_id'] != $arrMember['member_id'])
        {
            $db->rollback();
            $this->splash('failed',$error_url,"请勿取消别人的订单",true);
            return;
        }

        $mdl_order_cancel_reason = app::get('b2c')->model('order_cancel_reason');
        $result = $mdl_order_cancel_reason->save($order_cancel_reason);
        if(!$result)
        {
            $db->rollback();
            $this->splash('failed',$error_url,"订单取消原因记录失败",true);
        }
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_cancel($order_cancel_reason['order_id'],'',$message))
        {
            $db->rollback();
            $this->splash('failed',$error_url,$message,true);
        }

        $sdf['order_id'] = $order_cancel_reason['order_id'];
        $sdf['op_id'] = $arrMember['member_id'];
        $sdf['opname'] = $arrMember['uname'];
        $sdf['account_type'] = 'member';

        $b2c_order_cancel = kernel::single("b2c_order_cancel");
        if ($b2c_order_cancel->generate($sdf, $this, $message))
        {
            if($order_object = kernel::service('b2c_order_rpc_async')){
                $order_object->modifyActive($sdf['order_id']);
            }
            $db->commit($transaction_status);
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'index'));
            $this->splash('success',$url,"订单取消成功",true);
        }
        else
        {
            $db->rollback();
            $this->splash('failed',$error_url,"订单取消失败",true);
        }
    }

    //我的合伙人
    public function partner(){
        $data=kernel::single('mobileapi_rpc_passport');
        $this->pagedata['partner']=$data->direct_referrals(array('member_id'=>$this->member['member_id']));

        $this->page('wap/member/partner.html');
    }
    //下级合伙人
    public function partner_list($member_id){
        $filter=array('member_id'=>$member_id);
        $image_id=$this->app->model('members')->dump($filter,'avatar','default');
        $this->pagedata['image_id'] = $image_id['avatar'];
        $data=kernel::single('mobileapi_rpc_passport');
        $this->pagedata['partner']=$data->direct_referrals(array('member_id'=>$member_id));
        $this->page('wap/member/partner_list.html');
    }
    //物流
    public function logistics($order_id){
        //查物流单号
        $logi=$this->app->model('delivery');
        $delivery=$logi->dump(array('order_id'=>$order_id),'delivery_id');
        if(!empty($delivery)){
            $data=kernel::single('logisticstrack_ctl_site_tracker');
            $logistics=$data->logistics_array($delivery['delivery_id']);
        }else{
            $logistics=array(
                'data'=>array(),
                'msg'=>'订单错误',
            );
        }
        $this->pagedata['logistics']=$logistics;
        $this->page('wap/member/logistics.html');
    }

    //退货页面
    public function refund($order_id){
        if($order_id){
            $data=kernel::single('mobileapi_rpc_aftersales');
            $refund=$data->add(array('order_id'=>$order_id));
        }else{
           echo  app::get('b2c')->_("参数错误");exit;
        }
        if(!empty($refund['list'])){
            foreach($refund['list'] as &$vo){
                $arr=array();
                for($i=1;$i<=$vo['nums'];$i++){
                    $arr[$i]=$i;
                }
                $vo['arrnums']=$arr;
            }
        }
        $this->pagedata['refund']=$refund;
        $this->page('wap/member/refund.html');
    }
    //退货处理
    public function refund_save(){
        if(empty($_POST)){
            echo  app::get('b2c')->_("访问出错");exit;
        }
        $data=kernel::single('mobileapi_rpc_aftersales');
        $refund=$data->return_wap_save();
        if(isset($refund['error'])){
            $reload_url=$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'refund','arg'=>$_POST['order_id']));
            $this->splash('failed',$reload_url,app::get('b2c')->_($refund['error']));
        }else{
            $reload_url=$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'orders')).'?pay_status_mebers=5';
            $this->splash('success',$reload_url,app::get('b2c')->_($refund['success']));
            
        }
        
    }
    /**
     * 我要提现
     */
    public function withdrawal($param = array(), $rpcService) {
        
        //获取会员绑定提现账户
        $MemberBank = app::get('b2c')->model('member_bank');
        $bank_list = $MemberBank->getList('*',array('member_id'=>$this->app->member_id));
        $data['bank_list'] = $bank_list;
        
        //会员总余额
        $member = app::get('b2c')->model('members');
        $total = $member->dump($this->app->member_id, 'advance');
        $data['total'] = $total['advance']['total'];
        
        $this->pagedata['withdrawals'] = $data;
        $this->page('wap/member/withdrawal.html');
        
    }

    /**
     * 申请提现
     */
    public function submit_withdrawal() {
        $money = $_POST['money'];
        $bank = $_POST['bank'];
        //验证提现账户
        if(empty($bank)){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'showBank'));
            echo '<meta http-equiv="refresh" content="2;'.$url.'">';
            $this->splash('failed',$url,app::get('b2c')->_('您还没有绑定提现的账户,请先绑定!'));
        }
        
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'withdrawal'));
        echo '<meta http-equiv="refresh" content="2;'.$url.'">';
        
        // 验证
        if (!preg_match('/^\d*$/', $money) || $money <= 0 || ($money % 100) != 0) {
            $this->splash('failed',null,app::get('b2c')->_('提交的金额不是数字或者金额小于0了！以佰元为单位！'));
        }
        
        //验证会员账户金额
        $member = app::get('b2c')->model('members');
        $total = $member->dump($this->app->member_id, 'advance');
        if (($total['advance']['total'] - $money) < 0) {
            $this->splash('failed',null,app::get('b2c')->_('您当前的预存款余额不足'));
        }
        
        //插入数据
        $mem_wit = app::get('b2c')->model('member_withdrawal');
        $arr['member_id'] = $this->app->member_id;
        $arr['amount'] = $money;
        $arr['create_time'] = time();
        $arr['has_op'] = 'false';
        $arr['alipay_user'] = trim($bank);

        if ($mem_wit->insert($arr)) {
            $message = app::get('b2c')->_('申请成功，请等待管理员操作!');
            $this->splash('success',$url,$message);
           
        } else {
            
            $this->splash('failed',null,app::get('b2c')->_('申请错误稍后再试'));
        }
    }
    /*
     * 显示绑定提现账户页面
     * 
     */
    public function showBank() {
        
        //获取银行
        $bank_list = app::get('b2c')->getConf('system.bank.name');
        $bank_list = explode(':', $bank_list);
        $this->pagedata['bank_list'] = $bank_list;
        $bank=$this->app->model('member_bank')->getList('*',array('member_id'=>$this->app->member_id));
        $this->pagedata['banks']=$bank;

        $this->page('wap/member/showBank.html');
    }

    /*
     * 绑定银行账户
     */
    public function submitBank(){
        $bank=$this->app->model('member_bank');
        $data=$_POST;
        $data['member_id']=$this->app->member_id;
        $data['create_time']=time();
        if($bank->save($data)){
            $this->splash('success',$this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'showBank')),app::get('b2c')->_('添加成功'));
        }else{
            $this->splash('failed',null,app::get('b2c')->_('添加失败，请重试'));
        }
    }
    /*
    *删除银行账户
    */
    public function detelebank($member_bank_id){
        $url=$this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'showBank'));
        if($member_bank_id){
            $bank=$this->app->model('member_bank');
            $filter=array('member_bank_id'=>$member_bank_id);
            if($bank->delete($filter)){
                $this->splash('success',$url,app::get('b2c')->_('成功'));
            }else{
                $this->splash('failed',$url,app::get('b2c')->_('失败'));
            }
        }else{
            $this->splash('failed',$url,app::get('b2c')->_('参数错误'));
        }
    }
    /*
    *分享
    */
    public function share(){
        $this->pagedata['member_id']=$this->app->member_id;
        $this->page('wap/member/share.html');

    }
    /*
    *二维码分享
    */
    public function share_code(){
        $member_id = $this->app->member_id;
        $member_id = 12001;
        $len = strlen($member_id);
        $dir_total = 1000;  //每个目录存放的文件总数
        $g_len = ($len-strlen($dir_total)) <= 0 ? 1 : ($len-3);
        $rest = substr($member_id,0,$g_len)+1;
        $m_dir = 'codedir'.$rest.'/';
        $codedir = 'public/images/code/'.$m_dir;
        if(!file_exists($codedir)){
            mkdir($codedir, 0777);
        }
     
        $value= WWW_URL.'index.php/wap/passport-signup-'.$member_id.'.html';
       
        $QR = $codedir.$member_id.'log.png';
        $QRLOG = $codedir.$member_id.'.png';
      
        if(!file_exists($QRLOG)){
            require './public/php/phpqrcode.php';
            $errorCorrectionLevel = 'L';
            $matrixPointSize = 10;
            QRcode::png($value,$QR, $errorCorrectionLevel, $matrixPointSize, 2);
            $logo = './themes/zoutao/images/ok2.png';
          
            if(file_exists($logo)){
                $QR = imagecreatefromstring(file_get_contents($QR));
                $logo = imagecreatefromstring(file_get_contents($logo));
                $QR_width = imagesx($QR);
                $QR_height = imagesy($QR);
                $logo_width = imagesx($logo);
                $logo_height = imagesy($logo);
                $logo_qr_width = $QR_width / 5;
                $scale = $logo_width / $logo_qr_width;
                $logo_qr_height = $logo_height / $scale;
                $from_width = ($QR_width - $logo_qr_width) / 2;
                imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
                imagepng($QR,$QRLOG);
              
            }
            
        }
        
        if(file_exists($QR)){
            unlink($QR);
        }
        
        $this->pagedata['code'] = WWW_URL.$QRLOG;
        $this->page('wap/member/share_code.html');
    }
    //二维码关注微信
    public function attention(){
        $this->page('wap/member/attention.html');
    }
}
