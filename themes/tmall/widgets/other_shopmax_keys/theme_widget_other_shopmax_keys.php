<?php
/**
 * ShopMax licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopMax Technologies Inc. (http://www.shopmaxmb.com)
 */
 
function theme_widget_other_shopmax_keys($setting,&$smarty){
  
    foreach($setting['keys'] as $ad){
      
        $data[] = $ad;
    }

  return $data;
}
?>
