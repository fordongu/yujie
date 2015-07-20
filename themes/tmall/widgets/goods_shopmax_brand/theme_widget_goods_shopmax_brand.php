<?php
/**
 * ShopMax licence
 *
 * @copyright  Copyright (c)  ShopMax studio.  (http://mb.shopex.cn/designer-show-975.html)
 * @license ShopMax License
 */
 
function theme_widget_goods_shopmax_brand($setting,&$smarty){
  $data = b2c_widgets::load('Brand')->getBrandList($brandId); //新数据接口
      return $data;
}
?>
