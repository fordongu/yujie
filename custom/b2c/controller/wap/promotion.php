<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_wap_promotion extends wap_frontpage{


    public function index() {
   
   	   $this->set_tmpl_file('index-(1).html');
       $this->page('index.html');
    }
}

   