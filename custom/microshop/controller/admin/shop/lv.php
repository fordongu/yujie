<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class microshop_ctl_admin_shop_lv extends desktop_controller{

    var $workground = 'b2c.workground.microshop';

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
       //header("Content-type:text/html;charset=utf-8");
    }

    function index(){

        $this->finder('microshop_mdl_shop_lv',array(
            'title'=>app::get('b2c')->_('微店等级'),
            'actions'=>array(
                         array('label'=>app::get('b2c')->_('添加微店等级'),'href'=>'index.php?app=microshop&ctl=admin_shop_lv&act=addnew&finder_id='.$_GET['_finder']['finder_id'],'target'=>'_blank'),
                        )
            ));
    }

    function addnew($shop_lv_id=null){
        header("Cache-Control:no-store");
        $this->pagedata['title']    = app::get('microshop')->_('编辑微店');
        if($shop_lv_id){
        $mdl    = $this->app->model('shop_lv');
        $info   = $mdl->dump($shop_lv_id);
        $this->pagedata['info'] = $info;
            
        }
       
        $this->singlepage('admin/shop/lv.html');
    }

    function toAdd() {
        $this->begin('');
        $data   = $_POST;
        $mdl    = $this->app->model('shop_lv');
        if ( $mdl->save($data) ) {
            $this->end(true,    app::get('microshop')->_('操作成功'));
        } else {
            $this->end(true,    app::get('microshop')->_('操作失败'));
        }
    }


}
