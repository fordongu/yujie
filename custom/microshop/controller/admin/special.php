<?php
/**
 * ********************************************
 * Description   : 专辑列表数据
 * Filename      : special.php
 * Create time   : 2014-06-11 17:07:45
 * Last modified : 2014-06-18 16:57:40
 * License       : MIT, GPL
 * ********************************************
 */

class microshop_ctl_admin_special extends desktop_controller {

    var $workground = 'b2c.workground.microshop';
    
    /**
     * 列表
     */
    function index() {
        $this->finder('microshop_mdl_special',array(
            'title'=>app::get('microshop')->_('专辑列表'),
            'use_buildin_filter'=>true,
            'use_buildin_export'=>true,
            'actions'=>array(
                array('label'=>app::get('b2c')->_('添加'),'href'=>'index.php?app=microshop&ctl=admin_special&act=edit','target'=>'dialog::{ title:\'编辑专辑\', width:800, height:470}'),
            )
        ));
    }
    
    /*
     *编辑页面
     */
    function edit(){
        $special_id = $_GET['special_id'];
        if(!empty($special_id)){
         
            $info = kernel::single('microshop_mdl_special')->getDetail( $special_id);
            $goods_list = kernel::single('microshop_mdl_special_products')->getList('*',array('special_id'=>$special_id));
            
            if($goods_list){
                $Goods = kernel::single('b2c_mdl_goods');
                foreach ($goods_list as $k => $v) {
                    $goods_info = $Goods->getList('*',array('goods_id'=>$v['goods_id']));
                    $goods_ids[] = $v['goods_id'];
                    $goods_list[$k]['goods_name'] = $goods_info[0]['name'];
                }
                
            }
         //   $this->vd($goods_list);
            $this->pagedata['goods_ids'] = implode(',', $goods_ids);
            $this->pagedata['info'] = $info;
            $this->pagedata['goods_list'] = $goods_list;
      
        }
        
        $this->display('admin/special/edit.html');
        
    }
    
    /*
     *编辑页面
     */
    function toEdit(){
        $goods_ids = $_POST['conditions']['conditions'][0]['value'];
        $special_id  = $_POST['special_id'];
        $member_id = $_POST['member_id'];
        $special_name = $_POST['special_name'];
        
        if(empty($special_name)){
            exit('专辑名称不能为空!');
        }
        
        if(empty($member_id)){
            exit('会员ID不能为空');
        }
        
        if(empty($goods_ids)){
            exit('请选择商品!!');
        }
        
        $Special = $this->app->model("special");
        $SpecialProducts = $this->app->model("special_products");
        
       
        $this->begin();
        
        $aData = array(
            'member_id'=>$member_id,
            'shop_id'=>$member_id,
            'addtime'=>  time(),
            'special_name'=>$special_name
        );
        
        //$this->vd($aData);
        //$this->vd($bData);
        
        if($special_id){
           $aData['special_id'] = $special_id;
           
           //修改时先删除该专辑所以的商品
           $d_info = $SpecialProducts->delete(array('special_id'=>$special_id));
          
           $aResult = $Special->update($aData);
        }else{
            $aData['see_num'] = 0;
            $aData['addtime'] = time();
            
            $aResult = $Special->insert($aData);
            
        }
        
        $special_id = $special_id ? $special_id : $aResult;
        $is_result = FALSE;
        
        foreach ($goods_ids as $k => $v) {
            $bData['goods_id'] = $v; 
            $bData['member_id'] = $member_id;
            $bData['special_id'] = $special_id;
            $bData['product_id'] = 0;
            $bData['addtime'] = time();
            //$this->vd($bData);
            $bResult = $SpecialProducts->insert($bData);
            unset($bData);
            
            if($bResult){
                $is_result = TRUE;
                $result_msg = app::get('b2c')->_('操作成功,');
            }
        }
        
        if($is_result == FALSE){
            $result_msg = app::get('b2c')->_('操作失败,');
        }
        
        $this->end($is_result,$result_msg,'index.php?app=microshop&ctl=admin_special&act=index');
        
    }
    
    /**
    * 浏览器友好的变量输出
    * @param mixed $var 变量
    * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
    * @param string $label 标签 默认为空
    * @param boolean $strict 是否严谨 默认为true
    * @return void|string
    */
   function vd($var, $echo=true, $label=null, $strict=true) {
       $label = ($label === null) ? '' : rtrim($label) . ' ';
       if (!$strict) {
           if (ini_get('html_errors')) {
               $output = print_r($var, true);
               $output = '<pre>' . $label . htmlspecialchars($output, 3) . '</pre>';
           } else {
               $output = $label . print_r($var, true);
           }
       } else {
           ob_start();
           var_dump($var);
           $output = ob_get_clean();
           if (!extension_loaded('xdebug')) {
               $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
               $output = '<pre>' . $label . htmlspecialchars($output, 3) . '</pre>';
           }
       }
       if ($echo) {
           echo($output);
           return null;
       }else
           return $output;
   }
}
