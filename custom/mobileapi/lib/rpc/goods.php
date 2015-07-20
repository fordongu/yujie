<?php

/**
 *
 * @author iegss
 *        
 */
class mobileapi_rpc_goods {
    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {
        $this->app = app::get("b2c");
    }
    
    /**
     * 相关商品
     * @return array goodslink
     */
    function goodslink() {
    
    	$gid = $_REQUEST['iid'];
    
    	$objGoods = kernel::single("b2c_mdl_goods");
    	$objProduct =  kernel::single("b2c_mdl_products");
    
    	$aLinkId['goods_id'] = array();
    	foreach($objGoods->getLinkList($gid) as $rows){
    		if($rows['goods_1']==$gid){
    			$aLinkId['goods_id'][] = $rows['goods_2'];
    		}else {
    			$aLinkId['goods_id'][] = $rows['goods_1'];
    		}
    	}
    	if(count($aLinkId['goods_id'])>0){
    		$aLinkId['marketable'] = 'true';
    		$goodslink = $objGoods->getList('name,price,goods_id,image_default_id,marketable, view_count',$aLinkId,0,500);
    		foreach ($goodslink as $k => $v){
    			$goodslink[$k]['image_default'] = kernel::single('base_storager')->image_path($v['image_default_id'], 'm');
    		}
    	}
    
    	return $goodslink;
    }
    
    /**
     * 商品评论
     * @return array goods Comment
     */
    function comment() {
    
    	$page = isset($_REQUEST['page_no']) && $_REQUEST['page_no']>0?$_REQUEST['page_no']:1;
    	$limit = isset($_REQUEST['page_size']) && $_REQUEST['page_size']>0?$_REQUEST['page_size']:10;
    	$gid = $_REQUEST['iid'];
    
    	$objdisask = kernel::single('b2c_message_disask');
    	$aComment = $objdisask->good_all_disask($gid,'discuss',$page,null,$limit);
    
    	$objPoint = kernel::single('b2c_mdl_comment_goods_point');
    	$aComment['goods_point'] = $objPoint->get_single_point($gid);
    	$aComment['total_point_nums'] = $objPoint->get_point_nums($gid);
    	$aComment['_all_point'] = $objPoint->get_goods_point($gid);
    
    	return $aComment;
    }
    

    /**
     * 验证新增商品的数据合理有效性
     * @param array sdf数据
     * @param string message
     * @return boolean 成功还是失败
     */
    private function _checkInsertData(&$sdf, &$msg=''){
        if (!$sdf['name']){
            $msg = app::get('b2c')->_('商品名称不能为空，必要参数！');
            return false;
        }

        if(!isset($sdf['is_simple'])){
            $msg = app::get('b2c')->_('是否简单商品不能为空，必要参数！');
            return false;
        }

        if(isset($sdf['brief']) && $sdf['brief'] && strlen($sdf['brief'])>210){
            $msg = app::get('b2c')->_('简短的商品介绍,请不要超过70个字！');
            return false;
        }

        if(isset($sdf['brief']) && $sdf['num']>=0){
            $msg = app::get('b2c')->_('商品库存数量必须是大于等于零！');
            return false;
        }
        return true;
    }

    /**
     * 验证更新商品的数据合理有效性
     * @param array sdf数据
     * @param string message
     * @return boolean 成功还是失败
     */
    private function _checkUpdateData(&$sdf, &$msg=''){
        if (!$sdf['iid']){
            $msg = app::get('b2c')->_('商品ID不能为空，必要参数！');
            return false;
        }

        if (!$sdf['name']){
            $msg = app::get('b2c')->_('商品名称不能为空，必要参数！');
            return false;
        }

        if(!isset($sdf['is_simple'])){
            $msg = app::get('b2c')->_('参数是否简单商品不能为空，必要参数！');
            return false;
        }

        if(isset($sdf['brief']) && $sdf['brief'] && strlen($sdf['brief'])>210){
            $msg = app::get('b2c')->_('简短的商品介绍,请不要超过70个字！');
            return false;
        }

        if(isset($sdf['brief']) && $sdf['num']>=0){
            $msg = app::get('b2c')->_('商品库存数量必须是大于等于零！');
            return false;
        }
        return true;
    }


    /**
     * 商品关联商品信息处理
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    private function goods_related_items(&$sdf, $goods_id){
        // 生成推荐商品
        if ($sdf['related_items'])
        {
            $arr_related_items = json_decode($sdf['related_items'], 1);
            if ($arr_related_items)
            {
                $obj_goods_rate = $this->app->model('goods_rate');
                foreach ((array)$arr_related_items as $related_item)
                {
                    $filter = array(
                        'filter_sql'=>'(`goods_1`="'.$goods_id.'" AND `goods_2`="'.$related_item.'") OR (`goods_1`="'.$related_item.'" AND `goods_2`="'.$goods_id.'")',
                    );
                    $tmp = $obj_goods_rate->getList('*',$filter);
                    if (count($tmp) == 2)
                    {
                        // 当前商品与关联商品已经存在相互绑定，不做任何处理.
                    }
                    elseif (count($tmp) ==1)
                    {
                        //当查询结果只有一条时，判断是否是当前商品发起的商品关联，有就跳出不做处理，没有就设置双向绑定
                        if ($tmp[0]['goods_1'] == $goods_id)
                            continue;

                        $filter = array(
                            'goods_1'=>$tmp[0]['goods_1'],
                            'goods_2'=>$tmp[0]['goods_2'],
                        );
                        $is_save = $obj_goods_rate->update(array('manual'=>'both'),$filter);
                    }
                    else
                    {
                        //如果之前没有关联数据，则创建单向关联关系
                        $data = array(
                            'goods_1'=>$goods_id,
                            'goods_2'=>$related_item,
                            'manual'=>'left',
                        );
                        $is_save = $obj_goods_rate->insert($data);
                    }
                }
            }
            return $is_save;
        }
        return true;
    }



    /**
     * 获取商品列表
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get_all_list(&$sdf, &$thisObj){
		$key = md5(json_encode($sdf));
		$res2 = cachemgr::get($key,$data);
		if(!$res2 || empty($data)){
		
        $sdf['page_no'] = $sdf['page_no'] ? max(1,(int)$sdf['page_no']) : '1';
        $sdf['page_size'] = $sdf['page_size'] ? (int)$sdf['page_size'] : '20';

        /** 生成过滤条件 **/
        $db = kernel::database();
        $condition = "";
        if ($sdf['cat_id'])
            $condition .= " AND cat_id=".intval($sdf['cat_id']);
        if ($sdf['cid'])
            $condition .= " AND type_id=".intval($sdf['cid']);
        if ($sdf['brand_id'])
            $condition .= " AND brand_id=".$sdf['brand_id'];

        $page_size = $sdf['page_size'];
        $page_no = ($sdf['page_no'] - 1) * $page_size;

        $start_time = $sdf['start_time'];
        $end_time = $sdf['end_time'];
        if($start_time)
        {
            if(($start_time = strtotime($start_time)) === false || $start_time == -1)
            {
                $thisObj->send_user_error('5001', '开始时间格式不能正确解析!');
            }

            $condition .= " AND last_modify>=".$start_time;
        }

        if($end_time)
        {
            if(($end_time = strtotime($end_time)) === false || $end_time == -1)
            {
                $thisObj->send_user_error('5002', '结束时间格式不能正确解析!');
            }
            $condition .= " AND last_modify<".$end_time;
        }
        
        if ($sdf['search_keyword']){
        	$condition .= " AND ( name LIKE '%".$sdf['search_keyword']."%' OR brief LIKE '%".$sdf['search_keyword']."%' OR bn LIKE '%".$sdf['search_keyword']."%' OR barcode LIKE '%".$sdf['search_keyword']."%')"; 
        }
        
        $orderby = isset($_REQUEST['orderby'])?trim($_REQUEST['orderby']):'';
        $orderby = $orderby?" order by ".$orderby:'';
        
        //虚拟分类
        if(isset($sdf['virtual_cat_id']) && $sdf['virtual_cat_id']){

        	$goodsModel = kernel::single('b2c_mdl_goods');
        	$goodsVcatModel = kernel::single('b2c_mdl_goods_virtual_cat');
        	
        	$vcat = $goodsVcatModel->dump($sdf['virtual_cat_id']);
        	
        	if(!$vcat)return array('total_results'=>0, 'items'=>'[]');
        		
        	parse_str($vcat['filter'],$filter);
        	$filter['marketable'] = 'true';
        	$filter['is_buildexcerpts'] = 'true';
        	
        	$rs[0]['count'] = $goodsModel->count($filter);
        	if(!$rs[0]['count']){
        		return array('total_results'=>0, 'items'=>'[]');
        	}
        	
        	$rows = $goodsModel->getList('*',$filter,$page_no,$page_size,$_REQUEST['orderby'],$total=false);
        }else{
	
        	$filter = Array(
        			'marketable' => 'true',
        	);
        	
        	if($sdf['cid']) $filter['type_id'] = $sdf['cid'];
        	if($sdf['cat_id']) $filter['cat_id'] = $sdf['cat_id'];
        	if($sdf['brand_id']) $filter['brand_id'] = $sdf['brand_id'];
        	if($sdf['search_keyword']) $filter['search_keywords'][] = $sdf['search_keyword'];
        	
        	$goodsModel = kernel::single('b2c_mdl_goods');
        	
        	$rs[0]['count'] = $goodsModel->count($filter);
        	if(!$rs[0]['count']){
        		return array('total_results'=>0, 'items'=>'[]');
        	}
        	$rows = $goodsModel->getList('*',$filter,$page_no,$page_size,$_REQUEST['orderby'],$total=false);
        	
        }
        

        

        /**
         * 得到返回的商品信息
         */
        $sdf_goods = array();
        //$obj_ctl_goods = kernel::single('b2c_ctl_site_product');
        foreach($rows as $arr_row)
        {
            $sdf_goods['item'][] = $this->get_item_detail($arr_row, $obj_ctl_goods);
        }

        $screen = $this->screen($sdf['cat_id'], null);
        
        $ret_items = isset($_REQUEST['son_object']) && $_REQUEST['son_object'] == 'json'?$sdf_goods:json_encode($sdf_goods);
		$data = array('total_results'=>$rs[0]['count'], 'items'=> $ret_items, 'screen' => $screen['screen']);
		cachemgr::set($key, $data,array());
		}
		return $data;
		
	}


    /*
     * 将列表页中的搜索条件和虚拟分类条件合并
     *
     * @params int $virtual_cat_id 虚拟分类ID
     * @params array $filter  列表页搜索条件
     * */
    private function _merge_vcat_filter($virtual_cat_id,$filter){
        $virCatObj = $this->app->model('goods_virtual_cat');
        /** 颗粒缓存商品虚拟分类 **/
        if(!cachemgr::get('goods_virtual_cat_'.intval($virtual_cat_id), $vcat)){
            cachemgr::co_start();
            $vcat = $virCatObj->getList('cat_id,cat_path,virtual_cat_id,filter,virtual_cat_name as cat_name',array('virtual_cat_id'=>intval($virtual_cat_id)));
            cachemgr::set('goods_virtual_cat_'.intval($virtual_cat_id), $vcat, cachemgr::co_end());
        }
        $vcat = current( $vcat );
        parse_str($vcat['filter'],$vcatFilters);

        if($filter['cat_id'] && $vcatFilters['cat_id']){
            unset($vcatFilters['cat_id']);
        }
        $filter = array_merge_recursive($filter,$vcatFilters);
        return $filter;
    }

    /* 根据条件返回搜索到的商品
     * @params array $filter 搜索条件
     * @params int   $page   页码
     * @params string $orderby 排序
     * @return array
     * */
    public function get_goods($filter,$page=1,$orderby){
        $goodsObject = kernel::single('b2c_goods_object');
        $goodsModel = app::get('b2c')->model('goods');
        $userObject = kernel::single('b2c_user_object');
        $member_id = $userObject->get_member_id();
        if( empty($member_id) ){
            //$this->pagedata['login'] = 'nologin';
        }

        $page = $page ? $page : 1;
        $pageLimit = $this->app->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 20);
        $data['pager']['pageLimit'] = $pageLimit;
        $goodsData = $goodsModel->getList('*',$filter,$pageLimit*($page-1),$pageLimit,$orderby,$total=false);
        if($goodsData && $total ===false){
           $total = $goodsModel->count($filter);
        }
        $data['pager']['total'] =  $total;
        $pagetotal= $total ? ceil($total/$pageLimit) : 1;
        $max_pagetotal = $this->app->getConf('gallery.display.pagenum');
        $max_pagetotal = $max_pagetotal ? $max_pagetotal : 100;
        $data['pager']['pagetotal'] = $pagetotal > $max_pagetotal ? '1231' : $pagetotal;
        $data['pager']['page'] = $page;
        $gfav = explode(',',$_COOKIE['S']['GFAV'][0]);
        foreach($goodsData as $key=>$goods_row){
            if(in_array($goods_row['goods_id'],$gfav)){
                $goodsData[$key]['is_fav'] = 'true';
            }
            if($goods_row['udfimg'] == 'true' && $goods_row['thumbnail_pic']){
                $goodsData[$key]['image_default_id'] = $goods_row['thumbnail_pic'];
            }
            $gids[$key] = $goods_row['goods_id'];
        }

        if($filter['search_keywords'] || $filter['virtual_cat_id']){
            if(kernel::service('server.search_type.b2c_goods') && $searchrule = searchrule_search::instance('b2c_goods') ){
                if($searchrule){
                    $catCount = $searchrule->get_cat($filter);
                }
            }else{
                $sfilter = 'select cat_id,count(cat_id) as count from sdb_b2c_goods WHERE ';
                $sfilter .= $goodsModel->_filter($filter);
                $sfilter .= ' group by cat_id';
                $cats = $goodsModel->db->select($sfilter);
                if($cats){
                    foreach($cats as $cat_row){
                        $catCount[$cat_row['cat_id']] = $cat_row['count'];
                    }
                }
            }
        }
        //搜索时的分类
        if(!empty($catCount) && count($catCount) != 1){
            arsort($catCount);
            $this->pagedata['show_cat_id'] = key($catCount);
            $this->pagedata['catArr'] = array_keys($catCount);
            $this->catCount = $catCount;
        }else{
            $this->pagedata['show_cat_id'] = @key($catCount);
        }

        //货品
        $goodsData = $this->get_product($gids,$goodsData);

        //促销标签
        $goodsData = $this->get_goods_promotion($gids,$goodsData);

        //商品标签信息
        foreach( kernel::servicelist('tags_special.add') as $services ) {
            if ( is_object($services)) {
                if ( method_exists( $services, 'add') ) {
                    $services->add( $gids, $goodsData);
                }
            }
        }
        $data['goodsData'] = $this->get_goods_point($gids,$goodsData);
        return $data;
    }

    /*
     * 获取搜索到的商品的默认货品数据，并且格式化货品数据(货品市场价，库存等)
     *
     * @params array $gids 搜索到到的商品ID集合
     * @params array $goodsData 搜索到的商品数据
     * @return array
     * */
    private function get_product($gids,$goodsData){
        $this->pagedata['imageDefault'] = app::get('image')->getConf('image.set');
        $productModel = $this->app->model('products');
        $products =  $productModel->getList('*',array('goods_id'=>$gids,'is_default'=>'true','marketable'=>'true'));
        $show_mark_price = $this->app->getConf('site.show_mark_price');

        #检测货品是否参与special活动
        if($object_price = kernel::service('sepcial_goods_check')){
            $object_price->check_special_goods_list($products);
        }

        $sdf_product = array();
        foreach($products as $key=>$row){
            $sdf_product[$row['goods_id']] = $row;
        }
        foreach ($goodsData as $gk=>$goods_row){
            $product_row = $sdf_product[$goods_row['goods_id']];
            $goodsData[$gk]['products'] = $product_row;
            //市场价
            if($show_mark_price =='true'){
                if($product_row['mktprice'] == '' || $product_row['mktprice'] == null)
                    $goodsData[$gk]['products']['mktprice'] = $productModel->getRealMkt($product_row['price']);
            }

            //库存
            if($goods_row['nostore_sell'] || $product_row['store'] === null){
                $goodsData[$gk]['products']['store'] = 999999;
            }else{
                $store = $product_row['store'] - $product_row['freez'];
                $goodsData[$gk]['products']['store'] = $store > 0 ? $store : 0;
            }
        }
        return $goodsData;
    }

    /*
     * 获取搜索到的商品的促销信息
     *
     * @params array $gids 搜索到到的商品ID集合
     * @params array $goodsData 搜索到的商品数据
     * @return array
     * */
    private function get_goods_promotion($gids,$goodsData){
        //商品促销
        $time = time();
        $order = kernel::single('b2c_cart_prefilter_promotion_goods')->order();
        $goodsPromotion = app::get('b2c')->model('goods_promotion_ref')->getList('*', array('goods_id'=>$gids, 'from_time|sthan'=>$time, 'to_time|bthan'=>$time,'status'=>'true'),0,-1,$order);
        if($goodsPromotion){
            $black_gid = array();
            foreach($goodsPromotion as $row) {
                if(in_array($row['goods_id'],$black_gid)) continue;
                $tags[] = $row['tag_id'];
                $promotionData[$row['goods_id']][] = $row['tag_id'];
                if( $row['stop_rules_processing']=='true' ){
                    $black_gid[] = $row['goods_id'];
                }
            }
        }
        $tagModel = app::get('desktop')->model('tag');
        $sdf_tags = $tagModel->getList('tag_id,tag_name',array('tag_id'=>$tags));
        foreach($sdf_tags  as $tag_row){
            $tagsData[$tag_row['tag_id']] = $tag_row;
        }
        foreach((array)$promotionData as $gid=>$p_row){
            foreach($p_row as $k=>$tag_id){
                $promotion_tags[$gid][$k] = $tagsData[$tag_id];
            }
        }
        foreach($goodsData as $key=>$goods_row){
            if($goods_row['products']['promotion_type']){
                continue;   
            }
            $goodsData[$key]['promotion_tags'] = $promotion_tags[$goods_row['goods_id']];
        }
        return $goodsData;
    }

    /*
     * 获取搜索到的商品的积分信息
     *
     * @params array $gids 搜索到到的商品ID集合
     * @params array $goodsData 搜索到的商品数据
     * @return array
     * */
    private function get_goods_point($gids,$goodsData){
        $pointModel = $this->app->model('comment_goods_point');
        $goods_point_status = app::get('b2c')->getConf('goods.point.status');
        $this->pagedata['point_status'] = $goods_point_status ? $goods_point_status: 'on';
        if($this->pagedata['point_status'] == 'on'){
            $sdf_point = $pointModel->get_single_point_arr($gids);
            foreach($goodsData as $key=>$row){
                $goodsData[$key]['goods_point'] = $sdf_point[$row['goods_id']];
                #$goodsData[$key]['comments_count'] = $sdf_point[$row['goods_id']]['comments_count'];
            }
        }
        return $goodsData;
    }

    /*
     * 根据分类ID提供筛选条件，并且返回已选择的条件数据
     *
     * @params int $cat_id 分类ID
     * @params array $filter 已选择的条件
     * */
    private function screen($cat_id,$filter){
        if ( empty($cat_id) ) {
            $screen = array();
        }
        $screen['cat_id'] = $cat_id;
        $cat_id = $cat_id ?  $cat_id : $this->pagedata['show_cat_id'];
        //搜索时的分类
        if(!$screen['cat_id'] && count($this->pagedata['catArr']) > 1){
            $searchCat = app::get('b2c')->model('goods_cat')->getList('cat_id,cat_name',array('cat_id'=>$this->pagedata['catArr']));
            $i=0;
            foreach($this->catCount as $catid=>$num){
                $sort[$catid] = $i;
                if($i == 9) break;
                $i++;
            }
            foreach($searchCat as $row){
                $screen['search_cat'][$sort[$row['cat_id']]] = $row;
            }
            ksort($screen['search_cat']);
        }

        //商品子分类
        $show_cat = $this->app->getConf('site.cat.select');
        if($show_cat == 'true'){
            $sCatData = app::get('b2c')->model('goods_cat')->getList('cat_id,cat_name',array('parent_id'=>$cat_id));
            $screen['cat'] = $sCatData;
        }

        cachemgr::co_start();
        if(!cachemgr::get("goodsObjectCat".$cat_id, $catInfo)){
            $goodsInfoCat = app::get("b2c")->model("goods_cat")->getList('*',array('cat_id'=>$cat_id) );
            $catInfo = $goodsInfoCat[0];
            cachemgr::set("goodsObjectCat".$cat_id, $catInfo, cachemgr::co_end());
        }
        $this->goods_cat = $catInfo['cat_name'];//seo

        cachemgr::co_start();
        if(!cachemgr::get("goodsObjectType".$catInfo['type_id'], $typeInfo)){
            $typeInfo = app::get("b2c")->model("goods_type")->dump2(array('type_id'=>$catInfo['type_id']) );
            cachemgr::set("goodsObjectType".$catInfo['type_id'], $typeInfo, cachemgr::co_end());
        }
        $this->goods_type = $typeInfo['name'];//seo

        if($typeInfo['price'] && $filter['price'][0]){
            $active_filter['price']['title'] = $this->app->_('价格');
            $active_filter['price']['label'] = 'price';
            $active_filter['price']['options'][0]['data'] =  $filter['price'][0];
            foreach($typeInfo['price'] as $key=>$price){
                $price_filter = implode('~',$price);
                if($filter['price'][0] == $price_filter){
                    $typeInfo['price'][$key]['active'] = 'active';
                    $active_arr['price'] = 'active';
                }
                $active_filter['price']['options'][0]['name'] = $filter['price'][0];
            }
        }
        $screen['price'] = $typeInfo['price'];

        if ( $typeInfo['setting']['use_brand'] ){
            $type_brand = app::get('b2c')->model('type_brand')->getList('brand_id',array('type_id'=>$catInfo['type_id']));
            if ( $type_brand ) {
                foreach ( $type_brand as $brand_k=>$brand_row ) {
                    $brand_ids[$brand_k] = $brand_row['brand_id'];
                }
            }
            $brands = app::get('b2c')->model('brand')->getList('brand_id,brand_name',array('brand_id'=>$brand_ids,'disabled'=>'false'));
            //是否已选择
            foreach($brands as $b_k=>$row){
                if(in_array($row['brand_id'],$filter['brand_id'])){
                    $brands[$b_k]['active'] = 'active';
                    $active_arr['brand'] = 'active';
                    $active_filter['brand']['title'] = $this->app->_('品牌');;
                    $active_filter['brand']['label'] = 'brand_id';
                    $active_filter['brand']['options'][$row['brand_id']]['data'] =  $row['brand_id'];
                    $active_filter['brand']['options'][$row['brand_id']]['name'] = $row['brand_name'];
                }
            }
            $screen['brand'] = $brands;
        }

        //扩展属性
        if ( $typeInfo['setting']['use_props'] && $typeInfo['props'] ){
            foreach ( $typeInfo['props'] as $p_k => $p_v){
                if ( $p_v['search'] != 'disabled' ) {
                    $props[$p_k]['name'] = $p_v['name'];
                    $props[$p_k]['goods_p'] = $p_v['goods_p'];
                    $props[$p_k]['type'] = $p_v['type'];
                    $props[$p_k]['search'] = $p_v['search'];
                    $props[$p_k]['show'] = $p_v['show'];
                    $propsActive = array();
                    if($p_v['options']){
                        foreach($p_v['options'] as $propItemKey=>$propItemValue){
                            $activeKey = 'p_'.$p_v['goods_p'];
                            if($filter[$activeKey] && in_array($propItemKey,$filter[$activeKey])){
                                $active_filter[$activeKey]['title'] = $p_v['name'];
                                $active_filter[$activeKey]['label'] = $activeKey;
                                $active_filter[$activeKey]['options'][$propItemKey]['data'] =  $propItemKey;
                                $active_filter[$activeKey]['options'][$propItemKey]['name'] = $propItemValue;
                                $propsActive[$propItemKey] = 'active';
                            }
                        }
                    }
                    $props[$p_k]['options'] = $p_v['options'];
                    $props[$p_k]['active'] = $propsActive;
                }
            }

            $screen['props'] = $props;
        }

        //规格
        $gType = &$this->app->model('goods_type');
        $SpecList = $gType->getSpec($catInfo['type_id'],1);//获取关联的规格
        if($SpecList){
            foreach($SpecList as $speck=>$spec_value){
                if($spec_value['spec_value']){
                    foreach($spec_value['spec_value'] as $specKey=>$SpecValue){
                        $activeKey = 's_'.$speck;
                        if($filter[$activeKey] && in_array($specKey,$filter[$activeKey])){
                            $active_filter[$activeKey]['title'] = $spec_value['name'];
                            $active_filter[$activeKey]['label'] = $activeKey;
                            $active_filter[$activeKey]['options'][$specKey]['data'] =  $specKey;
                            $active_filter[$activeKey]['options'][$specKey]['name'] = $SpecValue['spec_value'];
                            $specActive[$specKey] = 'active';
                        }
                    }
                }
                $SpecList[$speck]['active'] = $specActive;
            }
        }
        $screen['spec'] = $SpecList;

        //排序
        $orderBy = $this->app->model('goods')->orderBy();
        $screen['orderBy'] = $orderBy;

        //标签
        $tagFilter['app_id'][] = 'b2c';
        $giftAppActive = app::get('gift')->is_actived();
        if($giftAppActive){
            $tagFilter['app_id'][] = 'gift';
        }
        $progetcouponAppActive = app::get('progetcoupon')->is_actived();
        if($progetcouponAppActive){
            //$progetcouponAppActive['app_id'][] = 'progetcoupon';
        }
        $tags = app::get('desktop')->model('tag')->getList('*',$tagFilter);
        if($filter['pTag']){
            $active_arr['pTags'] = 'active';
        }
        foreach($tags as $tag_key=>$tag_row){
            if($tag_row['tag_type'] == 'goods'){//商品标签
                if(@in_array($tag_row['tag_id'], $filter['gTag'])){
                    $screen['tags']['goods'][$tag_key]['active'] = 'checked';
                }
                $screen['tags']['goods'][$tag_key]['tag_id'] = $tag_row['tag_id'];
                $screen['tags']['goods'][$tag_key]['tag_name'] = $tag_row['tag_name'];
            }elseif($tag_row['tag_type'] == 'promotion'){//促销标签
                if(@in_array($tag_row['tag_id'],$filter['pTag'])){
                    $screen['tags']['promotion'][$tag_key]['active'] = 'active';
                    $active_filter['pTag']['title'] = $this->app->_('促销商品');;
                    $active_filter['pTag']['label'] = 'pTag';
                    $active_filter['pTag']['options'][$tag_row['tag_id']]['data'] =  $tag_row['tag_id'];
                    $active_filter['pTag']['options'][$tag_row['tag_id']]['name'] = $tag_row['tag_name'];
                }
                $screen['tags']['promotion'][$tag_key]['tag_id'] = $tag_row['tag_id'];
                $screen['tags']['promotion'][$tag_key]['tag_name'] = $tag_row['tag_name'];
            }
        }
        $this->pagedata['active_arr'] = $active_arr;
        $return['screen'] = $screen;
        $return['active_filter'] = $active_filter;
        $return['seo_info'] = $catInfo['seo_info'];
        return $return;
    }

    /**
     * 获取商品信息
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get_item(&$sdf, &$thisObj)
    {
		$key = md5(json_encode($sdf));
		$res2 = cachemgr::get($key,$data);
		if(!$res2 || empty($data)){
			
		
        if (!$sdf['iid'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('必填参数不存在！'), array('item'=>''));
        }

        $db = kernel::database();
        if (!$rows = $db->select("SELECT * FROM `sdb_b2c_goods` WHERE `goods_id`=".$sdf['iid']))
        {
            return array('item'=>'');
        }
        /**
         * 得到返回的商品信息
         */
        $sdf_goods = array();
        //$obj_ctl_goods = kernel::single('b2c_ctl_site_product');
        $sdf_goods['item'] = isset($_REQUEST['son_object']) && $_REQUEST['son_object'] == 'json'?$this->get_item_detail($rows[0], $obj_ctl_goods):json_encode($this->get_item_detail($rows[0], $obj_ctl_goods));

        $data = array('item'=>$sdf_goods['item']);
		cachemgr::set($key,$data,array());
		}
		return $data;
    }

    /**
     * 生成商品明细的数组
     * @param mixed 每行商品的数组-数据
     * @param object goods controller
     * @return mixed
     */
    protected function get_item_detail($arr_row, $obj_clt_goods)
    {
        if (!$arr_row)
            return array();

        $cnt_props = 20;
        $cn_input_props = 50;

        $detal_url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg0'=>$arr_row['goods_id']));
        
        
        
        /** props 目前是1-20 **/
        $props = "";
        $props_value_ids = array();
        for ($i=1;$i<=$cnt_props;$i++)
        {
            if ($arr_row['p_'.$i]){
            	$props .= $i.":".$arr_row['p_'.$i].";";
            	$props_value_ids[] = $arr_row['p_'.$i];
            }
                
        }
        if ($props)
            $props = substr($props, 0, strlen($props)-1);
        /** end **/
        
        $db = kernel::database();
        $props_values = '';
        if($props_value_ids){
        	$props_values = $db->select("SELECT p.name as props_name, pv.name as props_value FROM `sdb_b2c_goods_type_props_value` as pv 
        			left join `sdb_b2c_goods_type_props` as p on p.props_id = pv.props_id 
        			where pv.props_value_id in (".implode(',', $props_value_ids).") order by pv.order_by asc;");
        }
        

        /** input props 21-50 **/
        $input_pids = "";
        $input_str = "";
        for ($i=$cnt_props+1;$i<=$cn_input_props;$i++)
        {
            if ($arr_row['p_'.$i])
            {
                $input_pids .= $i.",";
                $input_str .= $arr_row['p_'.$i].";";
            }
        }
        if ($input_pids)
            $input_pids = substr($input_pids, 0, strlen($input_pids)-1);
        if ($input_str)
            $input_str = substr($input_str, 0, strlen($input_str)-1);
        /** end **/
        
        
        $db = kernel::database();
        $arr_skus = $db->select("SELECT * FROM `sdb_b2c_products` WHERE `goods_id`=".$arr_row['goods_id']);
        $arr_sdf_skus = array();
        $str_skus = "";
        if ($arr_skus)
        {
            foreach ($arr_skus as $arr)
            {
                $str_skus_properties = '';
                $arr_spec_desc = unserialize($arr['spec_desc']);
                if($arr_spec_desc['spec_value_id'])
                {
                	ksort($arr_spec_desc['spec_value_id']);
                	$spec_values = array();
                    foreach ($arr_spec_desc['spec_value_id'] as $spec_id_key =>$arr_value){
                    	$str_skus_properties .= $spec_id_key.":".$arr_value . '_' . $arr_spec_desc['spec_value'] [$spec_id_key]. ";";
                    	
                    	$spec_v['spec_value_id'] = $arr_value;
                    	$spec_v['spec_value'] = $arr_spec_desc['spec_value'] [$spec_id_key];
                    	$spec_values[] = $spec_v;
                    }
                }
                if ($str_skus_properties)
                    $str_skus_properties = substr($str_skus_properties, 0, strlen($str_skus_properties)-1);

                $arr_sdf_skus[] = array(
                    'sku_id'=>$arr['product_id'],
                    'iid'=>$arr['goods_id'],
                    'bn'=>$arr['bn'],
                    'properties'=>$str_skus_properties,
                    'quantity'=>$arr['store'],
                    'weight'=>$arr['weight'],
                    'price'=>$arr['price'],
                    'market_price'=>$arr['mktprice'],
                    'modified'=>$arr['last_modify'],
                    'cost'=>$arr['cost'],
					//'spec_values' => $spec_values,
                );
            }
            $str_skus = isset($_REQUEST['son_object']) && $_REQUEST['son_object'] == 'json'?$arr_sdf_skus:json_encode($arr_sdf_skus);
        }
        
        if($arr_row['udfimg'] == 'true' && $arr_row['thumbnail_pic']){
        	$arr_row['image_default_id'] = $arr_row['thumbnail_pic'];
        }
        $default_img_url = kernel::single('base_storager')->image_path($arr_row['image_default_id']);

        $goods_images  = array();
        //$arr_goods_images = $db->select("SELECT b.`image_id`, b.`l_url`, b.`m_url`, b.`s_url`  FROM `sdb_image_image_attach` a LEFT JOIN `sdb_image_image` b ON a.image_id=b.image_id WHERE `target_type`='goods' and `target_id`=".$arr_row['goods_id']);
        $arr_goods_images = $db->select("SELECT `image_id` FROM `sdb_image_image_attach` WHERE `target_type`='goods' and `target_id`=".$arr_row['goods_id']);
        if($arr_goods_images)
        {
            foreach($arr_goods_images as $single_row)
            {
                $goods_images[] = array(
                    'image_id'=>$single_row['image_id'],
                    'big_url'=>kernel::single('base_storager')->image_path($single_row['image_id'], 'l'),
                    'thisuasm_url'=>kernel::single('base_storager')->image_path($single_row['image_id'], 'm'),
                    'small_url'=>kernel::single('base_storager')->image_path($single_row['image_id'], 's'),
                    'is_default'=>'false',
                    );
            }
        }
                
        $specification = $db->select("SELECT spec_name, spec_id FROM `sdb_b2c_specification` order by spec_id asc;");
        $specif = array();
        foreach ($specification as $key => $value) {
        	$specif[$value['spec_id']]['spec_id'] = $value['spec_id'];
        	$specif[$value['spec_id']]['spec_name'] = $value['spec_name'];
        }
        
        $spec_desc = @unserialize($arr_row['spec_desc']);
        
        $spec_info = array();
        if($spec_desc){
	        foreach ($spec_desc as $key => $value) {	
	        	$spec_values = array();
	        	foreach ($value as $k => $v) {
	        		$v['spec_goods_images'] = kernel::single('base_storager')->image_path($v['spec_goods_images'], 'm');
	        		$v['spec_image'] = kernel::single('base_storager')->image_path($v['spec_image'], 'm');
	        		$v['properties'] = $key.':'.$v['spec_value_id'].'_'.$v['spec_value'];
	        		
	        		$spec_values[] = $v;
	        	}
	        	$specif[$key]['spec_values'] = $spec_values;
	        	$spec_info[] = $specif[$key];
	        }
        }
        
        
        $objGoods = app::get('b2c')->model('goods');
        $aGoods_list = $objGoods->getList("wapintro",array('goods_id'=>$arr_row['goods_id']));
        
        
        $obj_member = app::get('b2c')->model('member_goods');
        $goods_favorite_count = $obj_member->goods_favorite_count($arr_row['goods_id']);

        return array(
            'iid'=>$arr_row['goods_id'],
            'title'=>$arr_row['name'],
            'bn'=>$arr_row['bn'],
            'shop_cids'=>$arr_row['cat_id'],
            'cid'=>$arr_row['type_id'],
            'brand_id'=>$arr_row['brand_id'],
            'props'=>$props,
        	'props_values' => $props_values,
            'input_pids'=>$input_pids,
            'input_str'=>$input_str,
            'description'=> $arr_row['intro'],
        	'wapintro'=> !empty($aGoods_list[0]['wapintro']) && strlen($aGoods_list[0]['wapintro']) > 8 ? $aGoods_list[0]['wapintro'] : '', //手机端介绍
            'brief'=>$arr_row['brief'],
            'default_img_url'=>$default_img_url,
            'num'=>$arr_row['store'],
            'weight'=>$arr_row['weight'] ? $arr_row['weight'] : '',
            'score'=>$arr_row['score'] ? $arr_row['score'] : '',
            'price'=>$arr_row['price'],
            'market_price'=>$arr_row['mktprice'],
            'unit'=>$arr_row['unit'],
            'cost_price'=>$arr_row['cost'],
            'marketable'=>$arr_row['marketable'],
            'item_imgs'=>$goods_images,
            'buy_count'=>$arr_row['buy_count'],
            'comments_count'=>$arr_row['comments_count'],
            'modified'=>date('Y-m-d H:i:s',$arr_row['last_modify']),
            'list_time'=>date('Y-m-d H:i:s',$arr_row['uptime']),
            'delist_time'=>date('Y-m-d H:i:s',$arr_row['downtime']),
            'created'=>date('Y-m-d H:i:s',$arr_row['last_modify']),
            'skus'=>$str_skus,
            'spec_info' => $spec_info,
        	'goods_favorite_count' => $goods_favorite_count,
        );
    }
    
    
    
}

?>
