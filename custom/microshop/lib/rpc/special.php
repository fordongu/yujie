<?php
/**
 * ********************************************
 * Description   : 专辑接口
 * Filename      : special.php
 * Create time   : 2014-06-11 17:58:32
 * Last modified : 2014-06-11 17:58:32
 * License       : MIT, GPL
 * ********************************************
 */

class microshop_rpc_special {

    function __construct( $app ) {
        $this->app  = $app;
        $this->db   = kernel::database();
    }

    /**
     * 读取专辑列表
     */
    function index( $request, $rpcService ) {
        $page   = intval($request['page_no']);
        $page   = $page > 0 ? $page : 1;
        $mdl    = $this->app->model('special');
        $data   = array (
                'shop_id'       => 14,//$request['shop_id'],
                );
        // 验证会员店铺
        //$shop_info  = $this->checkMemberShop($data, $rpcService, $page);
        $count      = $mdl->count($data);
        $list       = array();
        if ( $count ) {
            $page_size  = intval($request['page_size']);
            $page_size  = $page_size > 0 ? $page_size : 20;
            $order      = trim($request['order']);
            $order      = $order ? $order : 'addtime';
            $order_type = trim($request['order_type']);
            $order_type = $order_type ? $order_type : 'DESC';
            $orderby    = $order.' '.$order_type;
            $platform   = trim($request['platform']);
            $platform   = $platform ? $platform : 'wap';
            $param      = array (
                'filter'    => $data,
                'limit'     => $page_size,
                'page'      => $page,
                'orderby'   => $orderby,
                'platform'  => $platform,
            );
            $list       = $mdl->getSpecialList($param);
        }
         
        $ret    = array(
            'code'          => 1,
            'total_results' => $count,
            'items'         => $list,
            'shop_info'     => $shop_info['data'],
        );
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 获得专辑中的商品
     */
    function get_products( $request, $rpcService ) {
        $ret    = array();
        if ( $request['special_id'] > 0 ) {
            $page   = intval($request['page_no']);
            $page   = $page > 0 ? $page : 1;
            $limit  = $request['page_size'] ? $request['page_size'] : 20;
            $data           = array (
                'special_id'=> $request['special_id'],
                'page'      => $page,
                'limit'     => $limit,
                'platform'  => $request['platform'] ? $request['platform'] : 'wap',
            );
            $mdl            = $this->app->model('special');
            $special_info   = $mdl->getSpecialInfo($data);
			//return $special_info;
            if ( $special_info['special_id'] ) {
                $ret        = array (
                    'code'  => 1,
                    'special_info'  => $special_info,
                );
            } else {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('microshop')->_('专辑信息不存在'),
                );
            }
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('microshop')->_('请确保专辑ID合法'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 验证会员店铺
     *
     * @param   array   $param      会员与店铺的相关数据
     * @param   array   $rpcService rpc对象
     */
    function checkMemberShop( $param = array(), $rpcService, $type = 2 ) {
        $member_id  = $param['member_id'] ? $param['member_id'] : 0;
        $shop_id    = $param['shop_id'] ? $param['shop_id'] : 0;
        $ret        = array ();
        if ( $shop_id > 0 || $member_id > 0 ) {
            $mdl    = $this->app->model('shop');
            $filter = array();
            if ( $shop_id > 0 ) {
                $filter['shop_id']      = $shop_id;
            }
            if ( $member_id > 0 ) {
                $filter['member_id']    = $member_id;
            }
            $info   = $mdl->getDetail($filter, $type);
            if ( $info ) {
                if ( $info['is_open'] == 'on' ) {
                    $ret    = array (
                        'code'  => 1,
                        'data'  => $info,
                        'msg'   => app::get('microshop')->_('会员店铺正常'),
                    );
                    // 会员ID > 0，则验证会员是否与用户一致
                    if ( $member_id > 0 && $member_id != $info['member_id'] ) {
                        $ret['code']    = -4;
                        $ret['msg']     = app::get('microshop')->_('微店所属用户验证失败');
                    }
                } else {
                    $ret    = array (
                        'code'  => -3,
                        'msg'   => app::get('microshop')->_('该会员店铺已关闭'),
                    );
                }
            } else {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('microshop')->_('该会员无任何微店信息'),
                );
            }
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('microshop')->_('很抱歉，本帐户不允许开设微店和创建专辑'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

}
