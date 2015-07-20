<?php
/**
 * ********************************************
 * Description   : 微店数据模型
 * Filename      : shop.php
 * Create time   : 2014-06-19 16:26:06
 * Last modified : 2014-06-19 16:27:07
 * License       : MIT, GPL
 * ********************************************
 */

class microshop_mdl_shop extends dbeav_model {
    

    function __construct($app) {
        parent::__construct($app);
      
    }

    /**
     * 获得商店详情
     * 
     * @param   array   $param          参数
     * @param   number  $update_visit   是否更新访问，1->更新(默认), 其它 -> 不更新
     * @return  array
     */
    function getDetail( $param = array(), $update_visit = 1 ) {
        $info   = array();
        $shop_id    = $param['shop_id'] ? $param['shop_id'] : 0;
        $member_id  = $param['member_id'] ? $param['member_id'] : 0;
        if ( $shop_id > 0 || $member_id > 0 ) {
            if ( $shop_id ) {
                $filter['shop_id']      = $param['shop_id'];
            }
            if ( $member_id ) {
                $filter['member_id']    = $param['member_id'];
            }
            $info   = parent::dump($filter);
            // 添加商品访问
            if ( $info['is_open'] == 1 ) {
                if ( $update_visit == 1 ) {
                    $_info      = array (
                        'see_num'   => $info['see_num'] + 1 
                    );  
                    parent::update( $_info, array('shop_id' => $info['shop_id']) );
                }
                $_param = array (
                        'app'   => 'microshop',
                        'ctl'   => 'site_index',
                        'full'  => 1,
                        'act'   => 'detail',
                        'arg0'  => $info['shop_id'],
                );  
                $info['shop_link']  = app::get('site')->router()->gen_url($_param);
                
                $m_mdl  = app::get('b2c')->model('members');
                $m_info = $m_mdl->dump($info['member_id']);
                $info['follow_num'] = $m_info['follow_num'];
                $info['fans_num']   = $m_info['follow_num'];
                $info['cover']      = $m_info['cover'] ? kernel::single('base_storager')->image_path($m_info['cover']) : $this->app->res_url.'/images/top-bg.png';
                $info['avatar']     = $m_info['avatar'] ? kernel::single('base_storager')->image_path($m_info['avatar']) : $this->app->res_url.'/images/top-bg.png';
                $info['info']       = $m_info['info'];
            }
        }
        return $info;
    }
    
    function addpoint($member_id,$point){
        $db=  kernel::database();
        $sql="UPDATE ".$db->prefix."microshop_shop SET shop_point=shop_point+".$point." WHERE member_id=".$member_id;
        if($db->exec($sql)){
            return true;
        }else{
            return false;
        }
    }
    
    function getallshopinfo(){
        $sql="SELECT s.*,m.invite_mem_nums FROM ".$this->db->prefix."microshop_shop as s LEFT JOIN ".$this->db->prefix."b2c_members as m ON s.member_id=m.member_id";
        $rows=$this->db->select($sql);
        return $rows;
    }
    
    function getshoplv($invite_mem_nums,$shop_point){
        $lv=  kernel::single('b2c_order_ticheng')->getallLv();
        $count=count($lv);

        
        for($i=1;$i<$count;$i++){
            if($i!=$count-1){
                if( ($invite_mem_nums >= $lv[$i]['mem_invite_nums'] && $shop_point>=$lv[$i]['point']) && ($invite_mem_nums < $lv[$i+1]['mem_invite_nums'] || $shop_point < $lv[$i+1]['point'])){
                    return $lv[$i]['shop_lv_id'];
                }elseif(($invite_mem_nums >= $lv[$i]['mem_invite_nums'] && $shop_point>=$lv[$i]['point']) && ($invite_mem_nums >= $lv[$i+1]['mem_invite_nums'] && $shop_point >= $lv[$i+1]['point']) ){
                    continue;
                } 
                else{
                    return 1;
                 }  
                
            }else{
                if($invite_mem_nums >= $lv[$i]['mem_invite_nums'] && $shop_point>=$lv[$i]['point']){
                    return $lv[$i]['shop_lv_id'];
                }
            }
                    
        }
        
    }
    
   function setshoplv($member_id,$shop_lvl){
       $data=array();
       $data['shop_lvl']=$shop_lvl;
       $filter['member_id']=$member_id;
       $this->update($data,$filter);
   }
   
   function addsale($table,$data){
       //插入前清空原有数据
       $sql="truncate ".$table;
       $this->db->exec($sql);
       $sql="INSERT INTO ".$this->db->prefix.$table."(member_id,sale_total,invite_mem_nums,month,create_time) "
               . "VALUES(".$data['member_id'].",".$data['sale_total'].",".$data['invite_mem_nums'].",".$data['month'].",".$data['create_time'].")";
       
       if($this->db->exec($sql)){
           return true;
       }
   }
   
   function setpointzero($member_id){
       
       $sql="UPDATE ".$this->db->prefix."microshop_shop SET shop_point=0 WHERE member_id=".$member_id;
       $this->db->exec($sql);
   }
    
}
