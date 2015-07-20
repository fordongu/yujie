<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_article_shopmax(&$setting,&$smarty){
    $setting['order'] or $setting['order'] = 'desc';
    $setting['order_type'] or $setting['order_type'] = 'pubtime';
    $func = array('asc'=>'ksort','desc'=>'krsort');

    $oAN = kernel::single("content_article_node");
    $oMAI = app::get('content')->model('article_indexs');
	$oIMI = app::get('content')->model('article_bodys');
    $iNodeId = $setting['node_id'];
    $lv = $setting['lv'];
    $limit = $setting['limit'];
    $tmp = $oAN->get_node($iNodeId, true);
    article_new_foo($lv, $iNodeId, $limit, $setting['showallart'], $oAN, $oMAI, $oIMI, $tmp['child'], $setting);
    $html = array();

    article_new_show($smarty, $tmp['child'], $setting, $html, 0, $limit);
    if( !$setting['shownode'] ) {
        $func[$setting['order']]($html);
    }
    $html = implode(' ',$html);
    $filter = array();
    $filter['ifpub'] = 'true';
    $filter['pubtime|than'] = time();
    $arr = $oMAI->getList( 'pubtime',$filter,0,1,' pubtime ASC' );
    if( $arr ) { //设置缓存过期时间
        reset( $arr );
        $arr = current($arr);
        cachemgr::set_expiration($arr['pubtime']);
    }

    $tmp['__html'] = $html;
    $tmp['__shownode'] = $setting['shownode'];
    $tmp['__stripparenturl'] = $setting['stripparenturl'];
	$tmp['__showmore'] = $setting['showmore'];
	$tmp['__showpic'] = $setting['showpic'];

    if( $tmp['homepage']=='true' ) 
        $tmp['node_url'] = app::get('site')->router()->gen_url( array('app'=>'content', 'ctl'=>'site_article', 'act'=>'i', 'arg0'=>$setting['node_id']) );
    else 
        $tmp['node_url'] = app::get('site')->router()->gen_url( array('app'=>'content', 'ctl'=>'site_article', 'act'=>'l', 'arg0'=>$setting['node_id']) );
    return $tmp;
}

function article_new_foo($lv=1, $iNodeId=1, $limit, $showallart, $oAN, $oMAI, $oIMI, &$tmp, $setting) {
    if($lv<0)return;
    $aNodes = $oAN->get_nodes($iNodeId);
	

    if(is_array($aNodes)) {
        foreach ($aNodes as $val) {
            if($val['ifpub']=='false')continue;
            article_new_foo(($lv-1), $val['node_id'], $limit, $showallart, $oAN, $oMAI, $oIMI, $tmp['child'][$val['node_id']], $setting);
            if(empty($tmp['child'][$val['node_id']])) unset($tmp[$val['node_id']]);
            $tmp['child'][$val['node_id']]['info'] = $val;
        }
    }
    if( $showallart ) {

        if(!$limit) return ;
        #if( $lv==$setting['lv'] ) return false;
        $tmp['article'] = $oMAI->getList_1('*', array('node_id'=>$iNodeId, 'ifpub'=>'true', 'pubtime|lthan'=>time(),'nochildren'=>true),0, $limit,"{$setting['order_type']} {$setting['order']} ");
    } 
}

			

function article_new_show(&$smarty, $tmp, $setting, &$html, $lv=0, &$limit) {
	$article_ids = array();
      
	foreach ($tmp['article'] as $idx=>$row) {
			$article_ids[] = $row['article_id'];
			 $tmp_arr_articles[$row['article_id']] = $row;
		}
		if($article_ids){
		 $sql = "SELECT a.hot_link, a.article_id, a.content, a.image_id, b.storage, b.s_url FROM `sdb_content_article_bodys` a LEFT JOIN `sdb_image_image` b ON a.image_id = b.image_id WHERE a.article_id IN (" .join(',' , $article_ids).")";
            $arr_article_bodys = kernel::database()->select($sql);
			}
			foreach((array)$arr_article_bodys as $idx=>$row){
			
            $tmp['article'][] = array_merge($row,$tmp_arr_articles[$row['article_id']]);}
       
		
    if($setting['shownode'] && $lv!=0) {
        $typeClass = 'node-list';
        if(is_object($smarty) && method_exists($smarty, 'gen_url')) {
            if( $tmp['info']['homepage']=='true' ){
                $url = $smarty->gen_url(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'i', 'arg0'=>$tmp['info']['node_id']));
				
				 
                $typeClass = 'node-index';
            } else{
                $url = $smarty->gen_url(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'l', 'arg0'=>$tmp['info']['node_id']));
				
            } 

        }
        $html[] = article_new_html($setting, $lv, $url, $iImageId, $icontent, $tmp['info']['node_name'],$typeClass);
    }

    if( !$setting['shownode'] ) {
        if( $limit<=0 ) return;
        #$tmp['article'] = array_slice( $tmp['article'], 0, $setting['limit'] );
    }

    if($tmp['article']) {
        if($setting['styleart']) {
            $tmp_lv = $setting['shownode'] ? ($setting['lv'] + 1) : 2;
        } else {
            $tmp_lv = $lv + 1;
        }
		
        $len = $limit;
		
		$article_ids = array();
		
 		
        foreach ($tmp['article'] as $idx=>$row) {
			if(!$row['hot_link']) continue;
            if(is_object($smarty) && method_exists($smarty, 'gen_url'))
                $url = $smarty->gen_url(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'index', 'arg0'=>$row['article_id']));
				
            $key = $row[$setting['order_type']];
            while(true) {
                if( !isset($html[$key]) )break;
                $key++;
            } 
			$icontent = strip_tags($row['content'],"<b>"); 
			$icontent = mb_strcut($icontent,0,100,'utf-8');
            $article_title = mb_strcut($row['title'],0,60,'utf-8');
			if ($row['s_url']){
				$iImageId = "<img src='/".$row['s_url']."' alt='".$row['title']."'/>";
			}
			else{
				$iImageId = "<img src='/public/images/35/8a/a9/23be0fa6395ff443b43b813196a2305a.jpg?1358003959#h' alt='".$row['title']."'/>";
			}
            if($setting['showuptime']){
                $article_title.="<i>".date('y-m-d',$row['uptime'])."</i>";
            }

            if($limit>0 ){
                    $html[$key] = article_new_html($setting, $tmp_lv, $url, $iImageId, $icontent, $article_title,'article-index');
            }
		

            $limit--;
        }
		
    }
	
    if($tmp['child']) {
        foreach ($tmp['child'] as $row) {
            article_new_show($smarty, $row, $setting, $html, $lv+1, $limit);
        }
		
    }
	
}

function article_new_html($setting, $lv, $url, $iImageId, $icontent, $name,$type) {
if ($setting['showpic']){
    return <<<EOF
<li class="$type lv-$lv item clearfix">
<a href="$url" class="p"> $iImageId </a><div class="i">
    <h6><a href="$url" title="$name"> $name </a></h6>
	<div class="gel-intro"> $icontent <a href="$url" >查看详情>></a></div>
	</div>
</li>
EOF;
}
else{
	 return <<<EOF
<li class="{$type} lv-{$lv} item"><a href="{$url}" title="{$name}">{$name}</a></li>
EOF;
}
}
?>
