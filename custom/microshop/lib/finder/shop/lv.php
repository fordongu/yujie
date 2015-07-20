<?php
/**
 * ********************************************
 * Description   : 商店finder
 * Filename      : shop.php
 * Create time   : 2014-06-16 14:34:38
 * Last modified : 2014-06-17 11:46:35
 * License       : MIT, GPL
 * ********************************************
 */
class microshop_finder_shop_lv {

    public $column_edit = '编辑';

    function column_edit($row) {
        return '<a href="index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act=addnew&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['shop_lv_id'].'" target="_blank">'.$this->column_edit.'</a>';
    }

    
}
