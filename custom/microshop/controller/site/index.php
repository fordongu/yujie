<?php
/**
 * ********************************************
 * Description   : 微店列表
 * Filename      : index.php
 * Create time   : 2015-04-15 
 * Last modified : 2014-04-21 
 * License       : MIT, GPL
 * ********************************************
 */
class microshop_ctl_site_index extends b2c_frontpage {
	private $member_id;//登录id
        public  $shop_info;
        public  $PAGE_SIZE = 20;
        function __construct( $app ) {
            header("Content-type:text/html;charset=utf-8");
            parent::__construct($app);
             
            $this->member_id = $userObject = @kernel::single('b2c_user_object')->get_member_id();
            if($this->member_id){
                $member = kernel::single('b2c_user_object')->get_members_data($columns['members']='*',$this->member_id);
                
                if($member){
                    $mobile = $member['account']['mobile'];
                    $member_info = $member['members'];
                    $member_info['mobile'] = $mobile;
                }
                
                $this->pagedata['member'] = $member_info;
            }
            
          /*  
            if (!$this->member_id){//未登录
                $_SESSION['mobile_next_page'] = $this->gen_url(array('app'=>'microshop','ctl'=>'site','act'=>'index'));

                $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'login'));
          
                $this->redirect($url);
                exit();
            }
           * 
           */
            $this->_response->set_header('Cache-Control', 'no-store');
            $this->shopname = app::get('site')->getConf('site.name');
            if( isset($this->shopname) ) {
                $this->title        = app::get('microshop')->_('微店').'_'.$this->shopname;
                $this->keywords     = app::get('microshop')->_('微店').'_'.$this->shopname;
                $this->description  = app::get('microshop')->_('微店').'_'.$this->shopname;
            }    
            $cur = app::get('ectools')->model('currency');
        
            //货币格式输出
            $ret = $cur->getFormat();
         
            $ret =array(
                'decimals'                  => app::get('b2c')->getConf('system.money.decimals'),
                'dec_point'                 => app::get('b2c')->getConf('system.money.dec_point'),
                'thousands_sep'             => app::get('b2c')->getConf('system.money.thousands_sep'),
                'fonttend_decimal_type'     => app::get('b2c')->getConf('system.money.operation.carryset'),
                'fonttend_decimal_remain'   => app::get('b2c')->getConf('system.money.decimals'),
                'sign'                      => $ret['sign']
            );   
            $this->pagedata['money_format'] = json_encode($ret);
            $this->pagedata['headers'][] = '<meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width" />';
            $this->pagedata['headers'][] = '<meta content="telephone=no" name="format-detection" />';
            $this->pagedata['headers'][] = '<meta name="apple-mobile-web-app-capable" content="yes" />';
            $this->pagedata['headers'][] = '<meta name="apple-mobile-web-app-status-bar-style" content="black" />';
            $this->pagedata['login_member_id'] = $this->member_id;
            
    }
    
    /*
     * 微店首页
     * author: lin
     */
    function index($shop_id = null) {
        $this->detail($shop_id);
    }
    
    /*
     * 显示微店设置页面
     * author: lin
     */
    function setShop() {
         $_getParams = $this->_request->get_params();
        $shop_id    = intval($_getParams[0]);
        if ( $shop_id ) {
            $mdl        = $this->app->model('shop');
            $filter     = array (
                'shop_id'   => $shop_id
            );
            $info       = $mdl->getDetail($filter);
            $this->pagedata['title']    = $info['shop_name'].'_'.$this->shopname;
           
               
            $this->pagedata['info']     = $info;

        }
        $this->pagedata['shop_id'] = $shop_id;
        $this->display('site/setShop.html');
    }
    
    /*
     * 显示设置微店的名字页面
     * author: lin
     */
    function setName() {
         $_getParams = $this->_request->get_params();
        $shop_id    = intval($_getParams[0]);
        if ( $shop_id ) {
            $mdl        = $this->app->model('shop');
            $filter     = array (
                'shop_id'   => $shop_id
            );
            $info       = $mdl->getDetail($filter);
            $this->pagedata['title']    = $info['shop_name'].'_'.$this->shopname;
           
               
            $this->pagedata['info']     = $info;

        }
        $this->display('site/setName.html');
    }
    
    /*
     * 修改微店的名字
     * author: lin
     */
    function postname(){
        $shop_name=$_POST['shop_name'];
        $shop_id=$_POST['shop_id'];
        $data['shop_name']=$shop_name;
        $data['shop_id']=$shop_id;
        $shopMdl=  app::get('microshop')->model('shop');
        $url=$this->gen_url(array('app'=>'microshop','ctl'=>'site_index','act'=>'setShop','arg0'=>$shop_id));
       $shopMdl->save($data);
        $this->redirect($url,true);
    
    }
    
    /*
     * 显示设置微信号码页面
     * author: lin
     */
    function setwx() {
         $_getParams = $this->_request->get_params();
        $shop_id    = intval($_getParams[0]);
        if ( $shop_id ) {
            $mdl        = $this->app->model('shop');
            $filter     = array (
                'shop_id'   => $shop_id
            );
            $info       = $mdl->getDetail($filter);
          
            $this->pagedata['info']     = $info;

        }
        $this->display('site/setwx.html');
    }
    
    /*
     * 修改微信号码
     * author: lin
     * 
     */
    function postwx(){
        $shop_name=$_POST['wx_name'];
        $shop_id=$_POST['shop_id'];
        $data['wx_name']=$shop_name;
        $data['shop_id']=$shop_id;
        $shopMdl=  app::get('microshop')->model('shop');
        $url=$this->gen_url(array('app'=>'microshop','ctl'=>'site_index','act'=>'setShop','arg0'=>$shop_id));
       $shopMdl->save($data);
        $this->redirect($url,true);
    
    }
    
    /*
     * 添加专辑产品
     * author: lin
     */
    function add_product() {
      
        $special_id = $_POST['special_id']; 
        $product_id = $_POST['product_id'];
        $goods_id = $_POST['goods_id'];
        $member_id = $this->member_id;
        $ret = array ('status'=>0,'msg'=>'');
        $is_true = TRUE;
        
        if(empty($member_id)){
            $ret['msg'] = '只用登陆才能添加!';
            $is_true = FALSE;
        }
        
        if(empty($special_id)){
            $ret['msg'] = '专辑ID不能为空!';
            $is_true = FALSE;
        }
        if(empty($goods_id)){
            $ret['msg'] = '商品ID不能为空!';
            $is_true = FALSE;
        }
        if(empty($product_id)){
            $ret['msg'] = '产品ID不能为空!';
            $is_true = FALSE;
        }
       
        if($is_true){
            $data   = array (
                'member_id'     => $member_id,
                'special_id'    => $special_id,
                'product_id'    => $product_id,
                'goods_id'      => $goods_id
            );

            $mdl    = $this->app->model('special_products');
            $info   = $mdl->getList('special_id',$data,0,1);
       
            if($info){
                $ret['msg'] = '该产品已经添加,请选择其他产品!';
            }else{
                $data['addtime'] = time();

                if ( $mdl->save($data) ) {
                    $ret['status'] = 1;
                    $ret['msg'] = '添加产品成功!';
                }else{
                    $ret['msg'] = '添加产品失败!';
                }
            }
        }
        
        echo json_encode($ret);
    }
    
    /*
     * 显示微店简介页面
     * author: lin
     */
     function setIntr() {
         $_getParams = $this->_request->get_params();
        $shop_id    = intval($_getParams[0]);
        if ( $shop_id ) {
            $mdl        = $this->app->model('shop');
            $filter     = array (
                'shop_id'   => $shop_id
            );
            $info       = $mdl->getDetail($filter);
            $this->pagedata['title']    = $info['shop_name'].'_'.$this->shopname;
           
               
            $this->pagedata['info']     = $info;

        }
        if ( $mdl->update($data, array('shop_id' => $request['shop_id'], 'desc' => $_POST['desc'])) ) {
                $ret    = array (
                    'code'  => 1,
                    'msg'   => app::get('microshop')->_('专辑修改成功'),
                    'data'  => $data,
                );
            } else {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('microshop')->_('专辑修改失败'),
                );
            }
        $this->display('site/setIntr.html');
    }
    
    /*
     * 修改微店简介
     * author: lin
     */
    function postdesc(){
        $desc=$_POST['desc'];
        $shop_id=$_POST['shop_id'];
        $data['desc']=$desc;
        $data['shop_id']=$shop_id;
        $shopMdl=  app::get('microshop')->model('shop');
        $url=$this->gen_url(array('app'=>'microshop','ctl'=>'site_index','act'=>'setShop','arg0'=>$shop_id));
       $shopMdl->save($data);
        $this->redirect($url,true);
    
    }
    
    /*
     * 获取专辑的所有产品
     * author: lin
     */
    public function get_goods_list($params = array()) {
        $_getParams = $this->_request->get_params();
        $special_id    = intval($_getParams[0]);
        
        //获取微店信息
        $MicroshopSpecial = $this->app->model('special');
        $ms_info = $MicroshopSpecial->getList('*',array('special_id'=>$special_id),0,1);
        
        $Shop        = $this->app->model('shop');
        $s_info = $Shop->getDetail(array('shop_id'   => $ms_info[0]['shop_id']));
        $this->pagedata['shop_info'] = $s_info;
       
        $SpecialProducts= $this->app->model('special_products');
        $list = $SpecialProducts->getList($cols='*',array('special_id'=>$special_id),$start=0,$limit=-1,$orderType=null);
      
        $new_list = array();
        if($list){
            
            $Products = kernel::single('b2c_mdl_products');
            foreach ($list as $k=>$v){
                $product_info = $Products->getOne($v['product_id']);
                if($product_info){
                    $new_list[] = array_merge($v, $product_info[0]);
                }
            }

        }
       
        $this->pagedata['special_id'] = $special_id;
        $this->pagedata['list'] = $new_list;
        $this->display('site/product_list.html');
        
    }
    
     /**
     * 添加专辑
     * author: lin
     */
    function add() {
        $special_name = $_POST['special_name'];
        $shop_id = $_POST['shop_id'];
        $ret    = array ();

        if ( $special_name ) {
            $mdl    = $this->app->model('special');
            $data   = array (
                'member_id'     => $this->member_id,
                'shop_id'       => $shop_id,
                'special_name'  => $special_name,
                'addtime'       => time(),
            );
           
            if ( $mdl->save($data) ) {
                $ret    = array (
                    'stauts'=>1,
                    'msg'   => app::get('microshop')->_('专辑添加成功'),
                );
            } else {
                $ret    = array (
                    'stauts'=>0,
                    'msg'   => app::get('microshop')->_('专辑添加失败'),
                );
            }
        } else {
            $ret    = array (
                'stauts'=>0,
                'msg'   => app::get('microshop')->_('专辑名称不能为空'),
            );
        }
        
        echo json_encode($ret);
       
    }
    
    /*
     * 微店详情
     * author: lin
     */
    function detail($shop_id=null) {
        $_getParams = $this->_request->get_params();
        $shop_id    = intval($_getParams[0]);
        
        if ( $shop_id > 0 ) {
            $mdl        = $this->app->model('shop');
            $filter     = array (
                'shop_id'   => $shop_id
            );
            
            $info       = $mdl->getDetail($filter);
            
            if(empty($info)){
                exit('地址不存在');
            }
            
            $this->shop_info = $info; //微店信息
            $this->pagedata['title']    = $info['shop_name'].'_'.$this->shopname;
	
            if ( $info['is_open'] == 'on' ) {
                // 专辑列表
                $s_mdl  = $this->app->model('special');
                $param      = array (
                    'filter'    => array (
                                        'shop_id'   => $shop_id,
                                    ),
                    'page'      => 1,
                    'limit'     => 50,
                    'orderby'   => 'addtime DESC'
                );
                $spec_list  = $s_mdl->getSpecialList($param);
            
                $this->pagedata['spec_list']    = $spec_list;
                $img_set= app::get('image')->getConf('image.set');
                $big_w  = 138;
                $big_h  = intval($img_set['M']['height'] * $big_w / $img_set['M']['width']);
                $small_w= 44;
                $small_h= intval($img_set['S']['height'] * $small_w / $img_set['S']['width']);
                $this->pagedata['img_size'] = array (
                        'big_w'     => $big_w,
                        'big_h'     => $big_h,
                        'small_w'   => $small_w,
                        'small_h'   => $small_h,
                        );
            } else {
                $info['shop_name']      = app::get('microshop')->_('该微店已关闭');
            }
           
            $this->pagedata['info']     = $info;
           
        }else{
            $info['shop_name']      = app::get('microshop')->_('非法操作!');
        }
       
        $this->display('site/shop.html');
    }


    /**
     * 专辑详情
     * author: lin
     */
    function special() {
        $_getParams = $this->_request->get_params();
        $spec_id    = intval($_getParams[0]);
        $data       = array (
                    'special_id'=>$spec_id,
                    'limit'     => -1,
                    );  
        $mdl    = $this->app->model('special');
        $info   = $mdl->getSpecialInfo($data);
        if ( $info['shop_open'] == 1 ) {
            // 用户信息
            $s_mdl  = $this->app->model('shop');
            $s_info = $s_mdl->getDetail($info);
            $info['follow_num'] = $s_info['follow_num'];
            $info['fans_num']   = $s_info['fans_num'];
            $info['bg_url']     = $s_info['cover'];
            $info['avatar']     = $s_info['avatar'];
            $info['cover']      = $s_info['cover'];
            $info['info']       = $s_info['info'];
            $info['shop_name']  = $s_info['shop_name'];
            $img_set    = app::get('image')->getConf('image.set');
            $big_w      = 148;
            $big_h      = intval($img_set['M']['height'] * $big_w / $img_set['M']['width']);
            $this->pagedata['img_size'] = array (
                'big_w'     => $big_w,
                'big_h'     => $big_h,
            );
        } else {
            $info['shop_name']  = app::get('microshop')->_('该微店已关闭');
        }
        $this->pagedata['info'] = $info;
        $this->pagedata['title']= $info['special_name'].'_'.$this->shopname;
        $this->display('site/special.html');
    }

    /*
     * 二维码
     * author: lin
     */
    function erweima($lu){
        //引入phpqrcode库文件
        include('phpqrcode.php'); 
        // 二维码数据 
        $data = $lu; 
        // 生成的文件名 
        $filename = 'baidu.png'; 
        // 纠错级别：L、M、Q、H 
        $errorCorrectionLevel = 'L';  
        // 点的大小：1到10 
        $matrixPointSize = 4;  
        //创建一个二维码文件 
        QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
        //输入二维码到浏览器
        QRcode::png($data);

    }
    
     //图片上传公共类
    protected function imageUpload($max_size=3,$size=''){
		
        if(empty($size)){
            $size == array('width'=> '400','height'=> '300');
        }
        if ($_FILES['file']['size'] > ($max_size * 1024 * 1024)) {
           $error="文件大小不能超过3M";
        }
          //var_dump($_FILES);exit();
        if ( $_FILES['file']['name'] != "" ) {
            $type = array("png","jpg","gif","jpeg"); //允许上传的文件            
            if(!in_array(strtolower($this->fileext($_FILES['file']['name'])), $type)) {
                $text = implode(",", $type);
                $error="只能上传以下类型文件".$text;
            }
        }
        
        $mdl_img = app::get('image')->model('image');
        
        $image_name = $_FILES['file']['name'];
        //return $_FILES;
       
        $image_id = $mdl_img->store($_FILES['file']['tmp_name'],null,null,$image_name);
        
        //var_dump($image_id);exit();
        $mdl_img->rebuild($image_id,array('l','m','s'));
        $image_src = base_storager::image_path($image_id, 'l');
        //return 'baid.com';
        if(empty($image_id)){
            $error="文件上传失败，请重新上传";
          
            exit();
        }
        return array(
                    'res'=>'success',
                    'image_id'=>$image_id,
                    'image_url'=>$image_src,
                    'image_name'=>$image_name,
                    'error'=>$error,
                    );
    }
    
     /**
     * 修改专辑
     * author: lin
     */
    function edit($special_id, $member_id ) {
        $ret    = array();
        if ( $special_id && $special_id > 0 && $member_id > 0 ) {
            $mdl    = app::get('microshop')->model('special');
            $data   = array (
                'special_name'  => $_POST['special_name'],
            );
            $special_name = $_POST['special_name'];
            $result = $mdl->getList('*',array('member_id'=>$member_id));
            if($result[0]['special_id'] > 0){
                $ret    = array (
                    'code'  => -5,
                    'msg'   => app::get('microshop')->_('专辑名字不能重复'),
                );
            }elseif ( $mdl->update($data, array('special_id' => $special_id)) ) {
                $ret    = array (
                    'code'  => 1,
                    'msg'   => app::get('microshop')->_('专辑修改成功'),
                    'data'  => $data,
                );
            } else {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('microshop')->_('专辑修改失败'),
                );
            }
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('microshop')->_('请确保专辑名称/专辑ID/会员ID都合法'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        $this->display('site/zjedit.html');
     }
   
    /*
     * 显示添加专辑商品列表
     * author: lin
     */
    public function get_all_list(){
        $_getParams = $this->_request->get_params();
        $special_id    = intval($_getParams[0]);
        if(!$special_id) exit('非法操作!');
        $Products = kernel::single('b2c_mdl_products');
        
        $product_list = $Products->getList('product_id',array('marketable'=>true),$start=0,$limit=3,null);
        
        $list = array();
        
        if($product_list){
            foreach ($product_list as $k=>$v){
                $product_info = $Products->getOne($v['product_id']);
                if($product_info){
                    $list[] = $product_info[0];
                }
            }
        }
        
        $this->pagedata['special_id'] = $special_id;
        $this->pagedata['list'] = $list;
        $this->display('site/select.html');
        
    }
    
	
}

