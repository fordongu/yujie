<?php
/**
 * ShopMax licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopMax Technologies Inc. (http://www.shopmax.com)
 */

function theme_widget_basic_shopmax_search(&$setting,&$smarty){
    foreach($setting['keys'] as $ad){
      
        $data[] = $ad;
    }

  return $data;
}
?>
