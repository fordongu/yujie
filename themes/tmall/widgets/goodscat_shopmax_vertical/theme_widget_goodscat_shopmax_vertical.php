<?php
/**
 * ShopMax licence
 *
 * @copyright  Copyright (c) 2005-2020 ShopEx Technologies Inc. (http://www.shopmaxmb.com)
 * @license  http://www.shopmaxmb.com ShopEx License
 */

function theme_widget_goodscat_shopmax_vertical(&$setting,&$render){
     	 if( false&& base_kvstore::instance('b2c_goods')->fetch('goods_cat_ex_vertical_widget.data',$cat_list) ){
     	    	return $cat_list;
     	    }


	     $cat_mdl = app::get('b2c')->model('goods_cat');
	     $brand_mdl  = app::get('b2c')->model('brand');
		 
	     $salesList = _ex_vertical_getSales();
	arsort($salesList,$salesList['sort_order']);
	     foreach ($brand_mdl->getAll() as $key => $value) {
	     	$brand_list[$value['brand_id']] = $value;
	     }
           // $cat_mdl->cat2json(true);
	     $cat_list =$cat_mdl->get_cat_list();
	     $_returnData['brand_list'] = $brand_list;
	     //print_r( $cat_list);
	     foreach ($cat_list as $k=>$cat) {

	     	switch ($cat['step']) {
	     		case 1:
		     		$all_cids 	= _ex_vertical_getAllChildAttr($cat_list,$cat['cat_id']);
		      		$all_cids[] 	= $cat['cat_id'];
		      		$all_typeids 	= _ex_vertical_getAllChildAttr($cat_list,$cat['cat_id'],'type');
		      		$all_typeids[] 	= $cat['type'];
		      		$all_brandids 	= _ex_vertical_getLinkBrandIds($all_typeids);

		      		$cat['brand'] = $all_brandids;

		  				//关联促销
		      		foreach ($salesList as $sale) {
		      			$allowLink = false;
		      			foreach ($sale['conditions']['conditions'] as $condition) {
							//$orderby=$salesList->orderBy($sale['sort_order']);
		      				switch ($condition['attribute']) {
		      					case 'goods_cat_id':
		      						$instersect = array_intersect($condition['value'],$all_cids);
		      						if(count($instersect)>0){
		      							$allowLink = true;
		      						}

		      						break;
		      					case 'goods_brand_id':
		      						$instersect = array_intersect($condition['value'],$all_brandids);
		      						if(count($instersect)>0){
		      							$allowLink = true;
		      						}
		      						break;
		      				}
		      			}

		      			if($allowLink){
		      				$cat['sales'][] = $sale;
							
		      			}

		      		}

		      		$_returnData['lv1'][] = $cat;
	     			break;
	     		case 2:
	     			$_returnData['lv2'][] = $cat;
	     			break;
	     		case 3:
	     			$_returnData['lv3'][] = $cat;
	     			break;
	     		
	     	}//end switch
			
	      }


	        // echo "<pre class='notice'>";
	        // var_dump($_returnData['lv1']);return;
	     // base_kvstore::instance('b2c_goods')->store('goods_cat_ex_vertical_widget.data',$cat_list);
//print_r($cat['sales']);
	     return $_returnData;
                                        
    }

function _ex_vertical_getAllChildAttr($arr,$pid,$attribute = 'cat_id'){

	
	foreach ($arr as $item) {
		if(in_array($pid, explode(',', $item['cat_path']))){
			$_return[] = $item[$attribute];
		}
	}

	return $_return;


}


function _ex_vertical_getLinkBrandIds($typeids){

	$sql = 'SELECT b.ordernum,b.brand_id FROM '.kernel::database()->prefix.'b2c_brand b right join '.kernel::database()->prefix.'b2c_type_brand t  on b.brand_id=t.brand_id  WHERE t.type_id  in('.implode(',',array_unique($typeids)).') order by b.ordernum desc';
	$res =  app::get('b2c')->model('brand')->db->select($sql);
	foreach ($res as $key => $value) {
		$_return[] = $value['brand_id'];
	}

	return array_unique($_return);

}

function _ex_vertical_getSales(){

	$goods_sales_mdl = app::get('b2c')->model('sales_rule_goods');
	$goods_sales_list = $goods_sales_mdl->getList('*');
	return $goods_sales_list;
}

?>






