<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * finder 显示类
 */
class b2c_finder_ticheng {

    private $Tc_num;
    /**
     * 构造方法 实例化APP类
     * @param object $app
     */
    function __construct(&$app) {
        $this->app = $app;
        $this->Tc_num = kernel::single('b2c_mdl_ticheng_num');
    }

//End Function

    /**
     * 显示在finder列表上的标题
     * @var string 
     */
    public $column_edit = '编辑';
    public $column_thrdaili = '三星代理';
    public $column_twodaili = '二星代理';
    public $column_onedaili = '一星代理';
    public $column_thrfenxiao = '三星分销';
    public $column_twofenxiao = '二星分销';
    public $column_onefenxiao = '一星分销';

    /**
     * 显示在finder列表上的标题的宽度
     * @var string 
     */
    public $column_edit_width = '40';
    public $column_thrdaili_width = '70';
    public $column_twodaili_width = '70';
    public $column_onedaili_width = '70';
    public $column_thrfenxiao_width = '70';
    public $column_twofenxiao_width = '70';
    public $column_onefenxiao_width = '70';
    /**
     * 列表编辑的显示数据
     * @param array $row finder上的一条记录
     * @return string
     */
    public function column_edit($row) {
        return '<a href="index.php?app=b2c&ctl=admin_ticheng&act=add_xilie&tc_id=' . $row['tc_id'] . '" target="dialog::{ title:\'' . app::get('b2c')->_('会员认证详情') . '\', width:800, height:470}" >' . app::get('b2c')->_('编辑') . '</a>';
    }
    public function column_thrdaili($row) {
        return $this->get_ticheng_num($row['tc_id'],'thr',1);
    }
    public function column_twodaili($row) {
        return $this->get_ticheng_num($row['tc_id'],'two',1);
    }
    public function column_onedaili($row) {
        return $this->get_ticheng_num($row['tc_id'],'one',1);
    }
    public function column_thrfenxiao($row) {
        return $this->get_ticheng_num($row['tc_id'],'thr',2);
    }
    public function column_twofenxiao($row) {
        return $this->get_ticheng_num($row['tc_id'],'two',2);
    }
    public function column_onefenxiao($row) {
        return $this->get_ticheng_num($row['tc_id'],'one',2);
    }
    
    private function get_ticheng_num($tc_id,$num,$type){
        $filter = array(
            'tc_id'=>$tc_id,
            'type'=>$type,
        );
        $res = $this->Tc_num->getList($num,$filter);
        return $res[0][$num];
    }

}

//End Class
