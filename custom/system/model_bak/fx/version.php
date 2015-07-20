<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class system_mdl_fx_version extends base_db_model {

    function __construct($app){
        parent::__construct($app);
        $this->db->exec('set SESSION autocommit=1;');
        $this->db->exec('set @msgID = -1;');
    }
    
    /**
     * 获取一个队列任务信息
     *
     * @param string $queue_name 队列名称
     *
     * @return array $row
     */
    public function get($queue_name){
	
	}

    
}


