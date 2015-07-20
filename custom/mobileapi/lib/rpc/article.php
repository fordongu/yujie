<?php

/**
 *
 * @author iegss
 *        
 */
class mobileapi_rpc_article extends mobileapi_frontpage {

    private $Article_indexs;
    private $Article_bodys;
    private $Article_nodes;

    public function __construct(&$app) {
        parent::__construct($app);

        $this->Article_indexs = kernel::single('content_mdl_article_indexs');
        $this->Article_bodys = kernel::single('content_mdl_article_bodys');
        $this->Article_nodes = kernel::single('content_mdl_article_nodes');
    }

    /**
     * 获取文章内容
     * @return array goodslink 
     */
    function get_detail() {
        $article_id = $_POST['article_id'];
        if($article_id > 0){
            $detail = $this->Article_bodys->dump(array('article_id'=>$article_id),'content,tmpl_path,image_id');
            $detail['tmpl_path'] = WWW_URL.$detail['tmpl_path'];
            if(!empty($detail['image_id'])){
                $detail['image_id'] = kernel::single('base_storager')->image_path($detail['image_id']);
            }            
            $title = $this->Article_indexs->dump(array('article_id'=>$article_id),'title,uptime');
            return  array_merge($detail,$title);
        }else{
            $msg = '参数错误';
        }        
        $this->Rpc->send_user_error('4003', $msg);
        
    }

    /* 获取某类的  文章列表
     *  node_name  
     */

    public function get_node_list() {
        if (!empty($_POST['node_name'])) {
            $node = $this->Article_nodes->dump(array('node_name' => $_POST['node_name']), 'node_id,node_path,parent_id');
            if (empty($node)) {
                $this->Rpc->send_user_error('4003', '没有' . $_POST['node_name'] . '文章分类');
                exit();
            }
            if ($node['parent_id'] == 0) {
                $res = $this->Article_nodes->getList('node_id', array('parent_id' => $node['node_id']));
            }
            //`platform` ="wap" 
            $where = ' WHERE  `ifpub` = "true" and platform="wap"';
            if (is_array($res)) {
                foreach ($res as $v) {
                    $str .= $v['node_id'] . ',';
                }
                $str = $str . $node['node_id'];
                $where .= ' AND node_id IN(' . $str . ') ';
            } else {
                $where .= ' AND node_id = ' . $node['node_id'];
            }
            $sql = 'SELECT * FROM sdb_content_article_indexs ' . $where . ' ORDER BY uptime DESC';
            return kernel::database()->select($sql);
        } else {
            $msg = '非法参数';
        }
        $this->Rpc->send_user_error('4003', $msg);
    }

}

?>
