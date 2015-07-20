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
class system_finder_fx_version 
{
	/**
	* 构造方法 实例化APP类
	* @param object $app
	*/
    function __construct(&$app) 
    {
        $this->app = $app;
    }//End Function
    
	/**
	* 显示在finder列表上的标题
	* @var string 
	*/
    public $column_edit='编辑';
	
	/**
	* 显示在finder列表上的标题的宽度
	* @var string 
	*/
    public $column_edit_width='40';
	
	/**
	* 列表编辑的显示数据
	* @param array $row finder上的一条记录
	* @return string
	*/
    public function column_edit($row){
        return '<a href="index.php?app=systom&ctl=admin_fx_version&act=edit&fx_id=' . $row['fx_id'] . '" target="dialog::{ title:\''.app::get('system')->_('编辑品牌视频').'\', width:800, height:470}" >'.app::get('system')->_('编辑').'</a>';
    }
	
}//End Class
