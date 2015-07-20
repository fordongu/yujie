<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class b2c_ctl_admin_apply extends desktop_controller {
    private $Apply;


    public function __construct($app) {
        parent::__construct($app);
        $this->Apply = kernel::single('b2c_mdl_apply');
    }

    function index() {
        $this->finder(
                'b2c_mdl_apply', array(
            'title' => app::get('b2c')->_('代理申请表'),
            
        ));
    }
    
    /*
     * 查看详情
     */
    public function detail(){
        $apply_id = $_GET['apply_id'];
        $apply  = $this->Apply->dump(array('apply_id'=>$apply_id),'*');
        $Members = kernel::single('b2c_mdl_members');
        //var_dump($apply);
        
        $res = $Members->dump(array('member_id'=>$apply['member_id']),'nickname,mobile');
        $apply['nickname'] = $res['nickname'];
        $apply['mobile'] = $res['contact']['phone']['mobile'];
        $res1 = $Members->dump(array('member_id'=>$apply['faqi_member_id']),'nickname,mobile'); 
        $apply['dai_nickname'] = $res1['nickname'];
        $apply['dai_mobile'] = $res1['contact']['phone']['mobile'];
        //var_dump($res1['contact']['phone']);
        $this->pagedata['apply'] = $apply;
        $this->display('admin/apply_detail.html');
    }
    
    /*
     * 保存
     */
    public function save(){
        $this->begin();
        if($_POST['apply_id'] > 0){
            $filter  = array('apply_id'=>$_POST['apply_id']);
            $data  = array(
                'is_check' => $_POST['is_check'],
                'remark' => $_POST['remark'],
            );
            if($this->Apply->update($data,$filter)){
                 $this->end(true, app::get('b2c')->_('保存成功'));
            }            
        }
        $this->end(false, app::get('b2c')->_('修改失败'));
    }

}
