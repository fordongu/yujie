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
class b2c_finder_member_auth
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
    public $column_edit='查看详情';
	
	/**
	* 显示在finder列表上的标题的宽度
	* @var string 
	*/
    public $column_edit_width='130';
	
	/**
	* 列表编辑的显示数据
	* @param array $row finder上的一条记录
	* @return string
	*/
    public function column_edit($row){
        return '<a href="index.php?app=b2c&ctl=admin_member_auth&act=detail_auth&auth_id=' . $row['auth_id'] . '" target="dialog::{ title:\''.app::get('b2c')->_('会员认证详情').'\', width:800, height:470}" >'.app::get('b2c')->_('查看').'</a>';
    }
	
}//End Class
