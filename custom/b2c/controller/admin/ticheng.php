<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class b2c_ctl_admin_ticheng extends desktop_controller {

    public function __construct($app) {
        parent::__construct($app);
        $this->member_model = $this->app->model('members');
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index() {
        $this->finder(
                'b2c_mdl_ticheng', array(
            'title' => app::get('b2c')->_('提成系列列表'),
            'actions' => array(
                array(
                    'label' => app::get('b2c')->_('添加提成规则'), 'icon' => 'add.gif',
                    'href' => 'index.php?app=b2c&ctl=admin_ticheng&act=add_xilie',
                    'target' => 'dialog::{title:\'添加提成规则\', width:800, height:420}'
                )
            )
        ));
    }

    public function add_xilie() {
        if(!empty($_GET['tc_id'])){
           $res = kernel::single('b2c_mdl_ticheng')->getList('*',array('tc_id'=>$_GET['tc_id']));
           $clos = 'one,two,thr';
           $filter = array(
               'tc_id'=>$_GET['tc_id'],
               'type'=>1,
           );
           $daili = kernel::single('b2c_mdl_ticheng_num')->getList($clos,$filter);
           $filter = array(
               'tc_id'=>$_GET['tc_id'],
               'type'=>2,
           );
           $fenxiao = kernel::single('b2c_mdl_ticheng_num')->getList($clos,$filter);
          //var_dump($fenxiao);
           $this->pagedata['ticheng'] = $res[0];
           $this->pagedata['daili'] = $daili[0];
           $this->pagedata['fenxiao'] = $fenxiao[0];
        }
        $this->display('admin/member/add_ticheng.html');
    }
    
    public function save(){
        //print_r($_POST['daili']);exit();
        $this->begin();
        if(!$this->check_post($_POST['ticheng'])){
            $this->end(false, app::get('b2c')->_('提成比例格式不对，请重新添加!'));
        }
        $saveData = $_POST['ticheng'];
        $saveData['create_time'] = time();
        if($_POST['tc_id'] > 0){
            $filter = array('tc_id'=>$_POST['tc_id']);
            if(kernel::single('b2c_mdl_ticheng')->update($saveData,$filter)){
                $daili = $_POST['daili'];
                $fenxiao = $_POST['fenxiao'];            
                $Tc_num = kernel::single('b2c_mdl_ticheng_num');
                $filter = array(
                    'tc_id'=>$_POST['tc_id'],
                    'type'=>2,
                );
                $Tc_num->update($fenxiao,$filter);
                $filter = array(
                    'tc_id'=>$_POST['tc_id'],
                    'type'=>1,
                );
                $Tc_num->update($daili,$filter);
                $this->end(true, app::get('b2c')->_('修改成功'));
            }else{
                $this->end(false, app::get('b2c')->_('修改失败'));
            }
            exit();
        }else{
            if($tc_id = kernel::single('b2c_mdl_ticheng')->insert($saveData)){
                $daili = $_POST['daili'];
                $fenxiao = $_POST['fenxiao'];
                $daili['tc_id'] = $tc_id;
                $daili['type'] = 1;
                $fenxiao['tc_id'] = $tc_id;
                $fenxiao['type'] = 2;
                $Tc_num = kernel::single('b2c_mdl_ticheng_num');
                $Tc_num->insert($fenxiao);
                $Tc_num->insert($daili);
                $this->end(true, app::get('b2c')->_('保存成功'));
            }else{
                $this->end(false, app::get('b2c')->_('添加失败，请重新添加!'));
            }
        }  
                
    }
    
    private function check_post($arr){
        if(is_array($arr)){
            $arr1 = $arr['daili'];
            foreach ($arr1 as $k=>$v){
                if($v > 1 || $v < 0){
                    return false;
                }
            }
            $arr = $arr['fenxiao'];
            foreach ($arr as $k=>$v){
                if($v > 1 || $v < 0){
                    return false;
                }
            }
            return true;
        }else{
            return false;
        }
        
    }

}
