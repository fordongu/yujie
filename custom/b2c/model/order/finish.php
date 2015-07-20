<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class b2c_mdl_order_finish extends dbeav_model{
    public function __construct($app) {
        parent::__construct($app);
    }
   
    public function insert($data){
        $sql= 'INSERT INTO '.$this->db->prefix.'b2c_order_finish(order_id,finish_time) VALUES('.$data['order_id'].','.$data['finish_time'].')';
        $insert_id=$this->db->exec($sql);
        return $insert_id;
    }
    
    public function getListFinish(){
       // $sevenday=(time()-7*24*3600);
        $sql="SELECT order_id FROM ".$this->db->prefix.'b2c_order_finish ';//WHERE finish_time<='.$sevenday;
        
		$rows=$this->db->select($sql);
		
        return $rows;
    }
    
    public function delete($order_id) {
        $sql="DELETE FROM ".$this->db->prefix.'b2c_order_finish WHERE order_id='.$order_id;
        $this->db->exec($sql);
    }
    
}

