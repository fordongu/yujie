<?php
/**
 * ShopMax licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopMax Technologies Inc. (http://www.shopmaxmb.com)
 */

function theme_widget_goods_shopmax_multiple(&$setting,&$render){
      $_return = false;
      foreach ($setting['goods_list'] as $key => $glist) {
                     $_return[$key] = $glist;
                     $goodsFilter['goods_id'] = explode(",", $setting["goods_select_".$key]);
                     $goodsFilter['goodsNum'] = $glist['limit'];
                     $goodsFilter['goodsOrderBy'] = $glist['order_by'];
                      $_return[$key] = b2c_widgets::load('Goods')->getGoodsList($goodsFilter);
                      foreach ($glist as $k => $v) {
                             if($k=='id')continue;
                            $_return[$key][$k] = $v;
                      }
                              /*foreach (json_decode($setting['goods_select_linkobj_'.$key],1) as $obj) {
                                      if(trim($obj['pic'])!=""){
                                          $_return[$key]['goodsRows'][$obj['id']]['goodsPicL'] = 
                                          $_return[$key]['goodsRows'][$obj['id']]['goodsPicM'] = 
                                          $_return[$key]['goodsRows'][$obj['id']]['goodsPicS'] = $obj['pic'];
                                      }
                                      if(trim($obj['nice'])!=""){
                                          $_return[$key]['goodsRows'][$obj['id']]['goodsName'] = $obj['nice'];
                                      }
                              }*/
                  //$gids = array_keys($_return[$key]['goodsRows']);
                   //if(!$gids||count($gids)<1)return;
                 $_dependApp = app::get('b2c');
                 $mdl_product = $_dependApp->model('products');
                 $mdl_point = $_dependApp->model('comment_goods_point');
                $products = $mdl_product ->getList('product_id, spec_info, price, freez, store, marketable, goods_id',array('goods_id'=>$gids,'marketable'=>'true'));
                foreach ($products as $product) {
                      $_return[$key]['goodsRows'][$product['goods_id']]['products'][] = $product;
                      //商品评分
                     // $_return[$key]['goodsRows'][$product['goods_id']]['point'] = $mdl_point->get_single_point($product['goods_id']);

               } 
			   foreach ($_return[$key]['goodsRows'] as $k => $grow) {                  
                  /*$sql = "select t.tag_name from sdb_desktop_tag t inner join sdb_desktop_tag_rel tr on t.tag_id=tr.tag_id ".
                            " where t.tag_type='goods' and tr.rel_id=".$db->quote($grow['goodsId']);                
                  $_returnmult[$key]['goodsRows'][$k]['tags'] = $db->select($sql);                                   
                  */
                  $grow['goods_id'] = $grow['goodsId'];
                  $prds = array($grow);

                  foreach( kernel::servicelist('tags_special.add') as $services ) {
                    if ( is_object($services)) {
                        if ( method_exists( $services, 'add') ) {
                            $services->add( $grow['goodsId'], $prds );
                        }
                    }
                  }
                  //echo '<PRE>';var_dump($prds);
                  $_return[$key]['goodsRows'][$k]['tags'] = $prds[0]['tags'];
               }
      }
    return $_return; 
}
?>
