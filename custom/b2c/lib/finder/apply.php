<?php

/**
 * ShopEx licence
 *  代理申请
 * @atuth 549224868@qq.com
 */

/**
 * finder 显示类
 */
class b2c_finder_apply {

    private $Members;
    private $Apply;
    /**
     * 构造方法 实例化APP类
     * @param object $app
     */
    function __construct(&$app) {
        $this->app = $app;
        $this->Members = kernel::single('b2c_mdl_members');
        $this->Apply = kernel::single('b2c_mdl_apply');
    }

//End Function

    /**
     * 显示在finder列表上的标题
     * @var string 
     */
    public $column_look = '查看';
    public $column_member = '申请人昵称';
    public $column_daimember = '代申请人昵称';
    

    /**
     * 显示在finder列表上的标题的宽度
     * @var string 
     */
    public $column_look_width = '40';
    public $column_member_width = '100';
    public $column_daimember_width = '100';
    /**
     * 列表编辑的显示数据
     * @param array $row finder上的一条记录
     * @return string
     */
    public function column_look($row) {
        return '<a href="index.php?app=b2c&ctl=admin_apply&act=detail&apply_id=' . $row['apply_id'] . '" target="dialog::{ title:\'' . app::get('b2c')->_('查看详细') . '\', width:800, height:470}" >' . app::get('b2c')->_('查看') . '</a>';
    }

    public function column_member($row){
        $row = $this->Apply->dump(array('apply_id'=>$row['apply_id']),'member_id');
        return $this->showname($row['member_id']);
    }
    public function column_daimember($row){
        $row = $this->Apply->dump(array('apply_id'=>$row['apply_id']),'faqi_member_id');
        //var_dump($row);
        return $this->showname($row['faqi_member_id']);
    }
    
    private function showname($member_id){
        $res = $this->Members->dump(array('member_id'=>$member_id),'nickname');
        return $res['nickname'];
    }
    


}

//End Class
