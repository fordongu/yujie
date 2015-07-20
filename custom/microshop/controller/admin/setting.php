<?php
/**
 * ********************************************
 * Description   : 微店列表数据
 * Filename      : list.php
 * Create time   : 2014-06-11 17:07:45
 * Last modified : 2014-06-11 17:07:45
 * License       : MIT, GPL
 * ********************************************
 */

class microshop_ctl_admin_setting extends desktop_controller {

    var $workground = 'b2c.workground.microshop';
    
     public function index() 
    {
        $this->pagedata['hatao_ticheng'] = app::get('microshop')->getConf('desktop.microshop.hatao_ticheng');
      
       $this->page('admin/setting/index.html');
    }

    public function save() 
    {
        $this->begin();
        $params = $_POST;   
        app::get('microshop')->setConf('desktop.microshop.hatao_ticheng', $params['hatao_ticheng']);         
        $this->end(true, '设置成功');
    }

}
