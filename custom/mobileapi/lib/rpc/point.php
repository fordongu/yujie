<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class mobileapi_rpc_point extends mobileapi_rpc_member
{
    /**
     * 构造方法
     * @param object application
     */
    public function __construct(&$app)
    {
        $this->app_current = app::get('pointprofessional');
        $this->app_b2c = app::get('b2c');
        parent::__construct($this->app_b2c);
        $this->pagesize = 2;
    }

    public function point_detail($param = array())
    {
        $nPage = $param['n_page'] > 0 ? $param['n_page'] : 1;
        $member = $this->app_current->model('members');
        $member_point = $this->app_current->model('member_point');

        // 扩展的积分信息
        $obj_extend_point = kernel::servicelist('b2c.member_extend_point_info');
        if ($obj_extend_point)
        {
            foreach ($obj_extend_point as $obj)
            {
                $this->pagedata['extend_point_html'] = $obj->gen_extend_detail_point($this->app->member_id);
            }
        }
        $data = $member->dump($this->app->member_id,'*',array('score/event'=>array('*')));
        $count = count($data['score']['event']);
        $aPage = $this->get_starts($nPage,$count);
        $params['data'] = $member_point->get_all_list('*',array('member_id' => $this->app->member_id),$aPage['start'], $this->pagesize);
        $result['page'] = $aPage['maxPage'];
        $result['n_page'] = $nPage;
        $result['total'] = $data['score']['total'];
        $result['historys'] = $params['data'];

        return $result;
    }

    function get_starts($nPage,$count)
    {
        $maxPage = ceil($count / $this->pagesize);
        if($nPage > $maxPage) $nPage = $maxPage;
        $start = ($nPage-1) * $this->pagesize;
        $start = $start<0 ? 0 : $start;
        $aPage['start'] = $start;
        $aPage['maxPage'] = $maxPage;
        return $aPage;
    }

}
