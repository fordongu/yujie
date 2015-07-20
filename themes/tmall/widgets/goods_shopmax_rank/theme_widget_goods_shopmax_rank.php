<?php
function theme_widget_goods_shopmax_rank(&$setting,&$render){
     $_dependApp = app::get('b2c');
     $_return = false;

     switch ($setting['selector']) {
     	case 'filter':
     		     parse_str($setting['goods_filter'],$goodsFilter);
                                $goodsFilter['goodsOrderBy'] = $setting['goods_order_by'];
                                $goodsFilter['goodsNum'] = $setting['limit'];
     		      $_return = b2c_widgets::load('Goods')->getGoodsList($goodsFilter);
                                 //$_return = array_values($_return['goodsRows']);
     		break;
     	
     	case 'select':

                            $goodsFilter['goods_id'] = explode(",", $setting["goods_select"]);
                            $goodsFilter['goodsNum'] = $setting['limit'];
	               //$goodsFilter['goodsOrderBy'] = $setting['goods_order_by'];
                            $_return = b2c_widgets::load('Goods')->getGoodsList($goodsFilter);
                            
                            foreach (json_decode($setting['goods_select_linkobj'],1) as $obj) {
                                    if(trim($obj['pic'])!=""){
                                        $_return['goodsRows'][$obj['id']]['goodsPicL'] = 
                                        $_return['goodsRows'][$obj['id']]['goodsPicM'] = 
                                        $_return['goodsRows'][$obj['id']]['goodsPicS'] = $obj['pic'];
                                    }
                                    if(trim($obj['nice'])!=""){
                                        $_return['goodsRows'][$obj['id']]['goodsName'] = $obj['nice'];
                                    }
                            }

	break;
     	
     }

     $gids = array_keys($_return['goodsRows']);
     if(!$gids||count($gids)<1)return $_return;
    $mdl_product = $_dependApp->model('products');
    $mdl_point = $_dependApp->model('comment_goods_point');

    $products = $mdl_product ->getList('product_id, spec_info, price, freez, store, marketable, goods_id',array('goods_id'=>$gids,'marketable'=>'true'));
    
    foreach ($products as $product) {
            
            $_return['goodsRows'][$product['goods_id']]['products'][] = $product;


            if(!$_return['goodsRows'][$product['goods_id']]['point']&&$setting['show_goods_point'])
            $_return['goodsRows'][$product['goods_id']]['point'] = $mdl_point->get_single_point($product['goods_id']);

    }


    return $_return; 
}

?>
