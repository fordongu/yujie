<?php

/**
 * 
 */
class mobileapi_rpc_member extends mobileapi_frontpage {

    private $data_post;
    private $page_nums;
    private $offset; //
    private $n_page;
    private $where = 'WHERE 1';
    private $Members;

    function __construct($app) {
        parent::__construct($app);
        $this->app = $app;
        $this->verify_member();
        $this->member = $this->get_current_member();
        //$this->app->rpcService = kernel::single('base_rpc_service');
        $this->Members = kernel::single('b2c_mdl_members');
        $this->pagesize = 10;

        if (!empty($_POST)) {
            unset($_POST['sign']);
            unset($_POST['direct']);
            unset($_POST['method']);
            unset($_POST['date']);
            $this->data_post = $this->check_input($_POST);
            //初始化分页处理
            if ($this->data_post['page_no'] > 0) {
                $this->data_post['n_page'] = $this->data_post['page_no'];
            }
            $this->page_nums = $this->data_post['page_nums'] ? $this->data_post['page_nums'] : 10;
            $this->n_page = $this->data_post['n_page'] ? $this->data_post['n_page'] : 1;
            $this->offset = ($this->n_page - 1) * $this->page_nums;
            if ($this->data_post['order'] == 'null') {
                $this->data_post['orderby'] = '';
            }
        }
    }

    /**
     * 取到个人信息字段
     * @param null
     * @return string json
     */
    public function setting() {
        $userObject = kernel::single('b2c_user_object');
        $membersData = $userObject->get_pam_data('*', $this->app->member_id);
        foreach ($membersData as $value) {
            
        }

        $attr = kernel::single('b2c_user_passport')->get_signup_attr($this->app->member_id);
        foreach ($attr as $key => $value) {
            unset($attr[$key]['attr_order'], $attr[$key]['attr_search'], $attr[$key]['attr_group'], $attr[$key]['attr_sdfpath']);
            if ($value['attr_column'] == 'profile[gender]') {
                $attr[$key]['attr_option'][] = 'male';
                $attr[$key]['attr_option'][] = 'female';
            }
            if ($value['attr_type'] == 'checkbox')
                $attr[$key]['attr_column'] = 'box:' . $value['attr_column'];
        }

        return array('mem' => 'null', 'attr' => $attr);
    }
    
    /**
     * 用户取消订单
     * @params string order id
     * @return null
     */
    public function cancel($param = array())
    {
        $order_id = $param['order_id'];
        $this->member = $this->get_current_member();
        $objM = app::get('b2c')->model('orders');
        $order = $objM->getRow('status,pay_status,ship_status',array('order_id'=>$order_id,'member_id'=>$this->member['member_id']));
        if(!$order){
            $this->app->rpcService->send_user_error('4003', '您无权进行此操作');
        }
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_cancel($order_id,'',$message))
        {
            $this->app->rpcService->send_user_error('4003', $message);
        }
        
        $sdf['order_id'] = $order_id;
        $sdf['op_id'] = $this->member['member_id'];
        $sdf['opname'] = $this->member['uname'];
        $sdf['account_type'] = $this->member['account_type'];
         
        $b2c_order_cancel = kernel::single("b2c_order_cancel");
        if ($b2c_order_cancel->generate($sdf))
        {
             return '操作成功';
        }
        else
        {
            $this->app->rpcService->send_user_error('4003', '参数错误');
        }
    }    

    /**
     * 修改昵称
     * @param  mixed
     * @return string json
     * @提交例子 '&contact[name]=测试&contact[area]=&profile[gender]=female&box:testing[]=选项1&box:testing[]=选项2&sfa=2222'
     */
    public function edit_nickname() {
        $nickname = $this->data_post['nickname'];
        $data = array('nickname' => $nickname);
        $filter = array('member_id' => $this->app->member_id);
        if ($this->Members->update($data, $filter)) {
            kernel::single('b2c_huanxin_registhuanxin')->edit_nickname($this->app->member_id, $nickname);
            return '昵称修改该成功';
        }
        $this->Rpc->send_user_error('4003', '昵称修改失败');
    }

    //收货地址
    public function receiver() {
        $objMem = app::get('b2c')->model('members');
        return $objMem->getMemberAddr($this->app->member_id);
    }

    /*
     * 设置和取消默认地址，$disabled 2为设置默认1为取消默认
     */

    public function set_default() {
        $addrId = $_POST['addr_id'];
        $disabled = $_POST['disabled'];
        if (!$addrId)
            $this->Rpc->send_user_error('4003', '参数错误');
        $obj_member = app::get('b2c')->model('members');
        $member_id = $this->app->member_id;
        if ($obj_member->check_addr($addrId, $member_id)) {
            if ($obj_member->set_to_def($addrId, $member_id, $message, $disabled)) {
                return '操作成功';
            } else {
                $this->Rpc->send_user_error('4003', $message);
            }
        } else {
            $this->Rpc->send_user_error('4003', '参数错误');
        }
    }

    /*
     * 添加、修改收货地址
     * */

    public function modify_receiver() {
        $addrId = $_POST['addr_id'];
        if (!$addrId) {
            $this->Rpc->send_user_error('4003', '参数错误');
        }
        $obj_member = app::get('b2c')->model('members');
        if ($obj_member->check_addr($addrId, $this->app->member_id)) {
            if ($aRet = $obj_member->getAddrById($addrId)) {
                $aRet['defOpt'] = array('0' => app::get('b2c')->_('否'), '1' => app::get('b2c')->_('是'));
                return $aRet;
            } else {
                $this->Rpc->send_user_error('4003', '修改的收货地址不存在！');
            }
        } else {
            $this->Rpc->send_user_error('4003', '参数错误');
        }
    }

    /*
     * 获取地址数据
     * */

    public function get_regions() {
        $this->getAllRegions('', '', $region_count, $regions);
        $arr = array();
        foreach ($regions as $v) {
            $arr[] = $v;
        }
        unset($this->regions);
        return $arr;
    }

    private function getAllRegions($p_regionid = '', $pkey = '', &$region_count = array(), &$regions) {
        $show_depth = app::get('ectools')->getConf('system.area_depth');
        if ($p_regionid)
            $sql = "select region_id,region_grade,local_name,ordernum,(select count(*) from sdb_ectools_regions where p_region_id=r.region_id) as child_count from sdb_ectools_regions as r where r.p_region_id=" . intval($p_regionid) . " order by ordernum asc,region_id";
        else
            $sql = "select region_id,region_grade,local_name,ordernum,(select count(*) from sdb_ectools_regions where p_region_id=r.region_id) as child_count from sdb_ectools_regions as r where r.p_region_id is null order by ordernum asc,region_id";

        $row = kernel::database()->select($sql);

        if (isset($row) && $row) {
            $cur_row = current($row);
            if (!$region_count[$cur_row['region_grade']]) {
                $start_index = 0;
            } else {
                $start_index = $region_count[$cur_row['region_grade']];
            }
            foreach ($row as $key => $val) {
                $tmp = array(
                    $val['local_name'],
                    $val['region_id'],
                );
                $index = $pkey !== '' ? $pkey : $key;
                if ($val['child_count']) {
                    if ($val['region_grade'] < $show_depth) {
                        $tmp[] = $start_index;
                    }
                    $start_index++;
                    $region_count[$cur_row['region_grade']] = $start_index;
                }
                if ($val['region_grade'] != 1) {
                    $regions[$val['region_grade']][$index][] = implode(":", $tmp);
                } else {
                    $regions[$val['region_grade']][$index] = implode(":", $tmp);
                }
                if ($val['child_count'] && $val['region_grade'] < $show_depth) {
                    $this->getAllRegions($val['region_id'], $start_index - 1, $region_count, $regions);
                }
            }
        }
    }

    /*
     * 保存收货地址
     * */

    public function save_rec() {
        if (!$_POST['def_addr']) {
            $_POST['def_addr'] = 0;
        }
        $save_data = kernel::single('b2c_member_addrs')->purchase_save_addr($_POST, $this->app->member_id, $msg);
        if (!$save_data) {
            $this->Rpc->send_user_error('4003', $msg);
        }
        return '保存成功';
    }

    //添加收货地址
    public function add_receiver() {
        $obj_member = app::get('b2c')->model('members');
        if ($obj_member->isAllowAddr($this->app->member_id)) {
            return true;
        } else {
            $this->Rpc->send_user_error('4003', '最多添加10个收货地址');
        }
    }

    //删除收货地址
    public function del_rec() {
        $addrId = $_POST['addr_id'];
        if (!$addrId)
            $this->Rpc->send_user_error('4003', '参数错误');
        $obj_member = app::get('b2c')->model('members');
        if ($obj_member->check_addr($addrId, $this->app->member_id)) {
            if ($obj_member->del_rec($addrId, $message, $this->app->member_id)) {
                return true;
            } else {
                $this->Rpc->send_user_error('4003', $message);
            }
        } else {
            $this->Rpc->send_user_error('4003', '操作失败');
        }
    }

    //我的订单
    public function orders() {
        //return $_POST;
        $nPage = $_POST['n_page'] ? $_POST['n_page'] : 1;
        if (isset($_POST['pay_status'])) {
            $pay_status = $_POST['pay_status'];  // 支付状态
        }
        if (isset($_POST['ship_status'])) {
            $ship_status = $_POST['ship_status']; //发货状态
        }
        if (isset($_POST['status'])) {
            $status = $_POST['status'];  //订单状态
        }
        if (isset($_POST['createtime_status'])) {
            $createtime_status = $_POST['createtime_status']; //订单时间
        }

        $order = app::get('b2c')->model('orders');
        if (!isset($_POST['pay_status']) && !isset($_POST['ship_status']) && !isset($_POST['status']) && !isset($_POST['createtime_status'])) {
            $aData = $order->fetchByMember($this->app->member_id, $nPage);
        } else {
            $order_status = array();
            //return $pay_status;
            //支付状态
            if (isset($pay_status) && $pay_status <= 5) {
                $order_status['pay_status'] = $pay_status;
                $order_status['status'] = 'active';
            }

            // 发货状态
            if (isset($ship_status) && $ship_status <= 4) {
                $order_status['pay_status'] = 1;
                $order_status['ship_status'] = $ship_status;
                $order_status['status'] = 'active';
            }

            //订单状态
            if (isset($status) && $status == 'finish') {
                $order_status['status'] = 'finish';
            }


            //一个月前的订单
            if ($createtime_status == 'prior_to') {
                $ago = time() - 86400 * 31;
                $order_status['createtime_to'] = $ago;
            } elseif ($createtime_status == 'recent') {
                $ago = time() - 86400 * 31;
                $order_status['createtime_from'] = $ago;
            }
            //return $order_status;
            $aData = $order->fetchByMember($this->app->member_id, $nPage, $order_status);
        }

        $this->get_order_details($aData, 'member_orders');
        $oImage = app::get('image')->model('image');
        $oGoods = app::get('b2c')->model('goods');
        $imageDefault = app::get('image')->getConf('image.set');
        $obj_payments_cfgs = app::get('ectools')->model('payment_cfgs');
        foreach ($aData['data'] as $k => &$v) {
            foreach ($v['goods_items'] as $k2 => &$v2) {
                $spec_desc_goods = $oGoods->getList('spec_desc', array('goods_id' => $v2['product']['goods_id']));
                $select_spec_private_value_id = @reset($v2['product']['products']['spec_desc']['spec_private_value_id']);
                $spec_desc_goods = @reset($spec_desc_goods[0]['spec_desc']);
                if ($spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']) {
                    list($default_product_image) = explode(',', $spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']);
                    $v2['product']['thumbnail_pic'] = $default_product_image;
                } else {
                    if (!$v2['product']['thumbnail_pic'] && !$oImage->getList("image_id", array('image_id' => $v['image_default_id']))) {
                        $v2['product']['thumbnail_pic'] = $imageDefault['S']['default_image'];
                    }
                    $v2['product']['thumbnail_pic_src'] = base_storager::image_path($v2['product']['thumbnail_pic'], 's');
                }
            }

            if ($v['payinfo']['pay_app_id']) {
                $aData['data'][$k]['payinfo']['display_name'] = $obj_payments_cfgs->get_app_display_name($v['payinfo']['pay_app_id']);
            }
        }

        foreach ($aData['data'] as $key => $value) {
            unset($aData['data'][$key]['goods_items']);
            $aData['data'][$key]['goods_items'] = array_values($value['goods_items']);
            if ($value['consignee']['area']) {
                $str = explode(':', $value['consignee']['area']);
                $aData['data'][$key]['consignee']['txt_area'] = str_replace('/', '', $str[1]);
            }
        }
        $deliveryMdl = app::get('b2c')->model('delivery');

        foreach ($aData['data'] as $key => $value) {
            $delivery = $deliveryMdl->getList('logi_name,logi_no', array('order_id' => $value['order_id']));
            $dly = kernel::single('b2c_mdl_dlycorp')->getList('corp_code', array('name' => $delivery[0]['logi_name']));
            $aData['data'][$key]['logi_name'] = $delivery[0]['logi_name'];
            $aData['data'][$key]['logi_no'] = $delivery[0]['logi_no'];
            $aData['data'][$key]['corp_code'] = $dly[0]['corp_code'];
        }


        return $aData['data'];
    }

    /**
     * 得到订单列表详细
     * @param array 订单详细信息
     * @param string tpl
     * @return null
     */
    protected function get_order_details(&$aData, $tml = 'member_orders') {
        if (isset($aData['data']) && $aData['data']) {
            $objMath = kernel::single('ectools_math');
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type) {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
            }

            foreach ($aData['data'] as &$arr_data_item) {
                $this->get_order_detail_item($arr_data_item, $tml);
            }
        }
    }

    /**
     * 得到订单列表详细
     * @param array 订单详细信息
     * @param string 模版名称
     * @return null
     */
    protected function get_order_detail_item(&$arr_data_item, $tpl = 'member_order_detail') {
        if (isset($arr_data_item) && $arr_data_item) {
            $objMath = kernel::single('ectools_math');
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type) {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
            }


            $arr_data_item['goods_items'] = array();
            $obj_specification = app::get('b2c')->model('specification');
            $obj_spec_values = app::get('b2c')->model('spec_values');
            $obj_goods = app::get('b2c')->model('goods');
            $oImage = app::get('image')->model('image');
            if (isset($arr_data_item['order_objects']) && $arr_data_item['order_objects']) {
                foreach ($arr_data_item['order_objects'] as $k => $arr_objects) {
                    $index = 0;
                    $index_adj = 0;
                    $index_gift = 0;
                    $image_set = app::get('image')->getConf('image.set');
                    if ($arr_objects['obj_type'] == 'goods') {
                        foreach ($arr_objects['order_items'] as $arr_items) {
                            if (!$arr_items['products']) {
                                $o = app::get('b2c')->model('order_items');
                                $tmp = $o->getList('*', array('item_id' => $arr_items['item_id']));
                                $arr_items['products']['product_id'] = $tmp[0]['product_id'];
                            }

                            if ($arr_items['item_type'] == 'product') {
                                if ($arr_data_item['goods_items'][$k]['product'])
                                    $arr_data_item['goods_items'][$k]['product']['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k]['product']['quantity']));
                                else
                                    $arr_data_item['goods_items'][$k]['product']['quantity'] = $arr_items['quantity'];

                                $arr_data_item['goods_items'][$k]['product'] = $arr_items;
                                $arr_data_item['goods_items'][$k]['product']['name'] = $arr_items['name'];
                                $arr_data_item['goods_items'][$k]['product']['goods_id'] = $arr_items['goods_id'];
                                $arr_data_item['goods_items'][$k]['product']['price'] = $arr_items['price'];
                                $arr_data_item['goods_items'][$k]['product']['score'] = intval($arr_items['score'] * $arr_data_item['goods_items'][$k]['product']['quantity']);
                                $arr_data_item['goods_items'][$k]['product']['amount'] = $arr_items['amount'];
                                $arr_goods_list = $obj_goods->getList('image_default_id,spec_desc', array('goods_id' => $arr_items['goods_id']));
                                $arr_goods = $arr_goods_list[0];
                                // 获取货品关联第一张图片
                                $select_spec_private_value_id = @reset($arr_items['products']['spec_desc']['spec_private_value_id']);
                                $spec_desc_goods = @reset($arr_goods['spec_desc']);
                                if ($spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']) {
                                    list($default_product_image) = explode(',', $spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']);
                                    $arr_goods['image_default_id'] = $default_product_image;
                                } else {
                                    if (!$arr_goods['image_default_id'] && !$oImage->getList("image_id", array('image_id' => $arr_goods['image_default_id']))) {
                                        $arr_goods['image_default_id'] = $image_set['S']['default_image'];
                                    }
                                }

                                $arr_data_item['goods_items'][$k]['product']['thumbnail_pic'] = $arr_goods['image_default_id'];
                                if ($arr_items['addon']) {
                                    $arrAddon = $arr_addon = unserialize($arr_items['addon']);
                                    if ($arr_addon['product_attr'])
                                        unset($arr_addon['product_attr']);
                                    $arr_data_item['goods_items'][$k]['product']['minfo'] = $arr_addon;
                                }else {
                                    unset($arrAddon, $arr_addon);
                                }
                                if ($arrAddon['product_attr']) {
                                    foreach ($arrAddon['product_attr'] as $arr_product_attr) {
                                        $arr_data_item['goods_items'][$k]['product']['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                    }
                                }

                                if (isset($arr_data_item['goods_items'][$k]['product']['attr']) && $arr_data_item['goods_items'][$k]['product']['attr']) {
                                    if (strpos($arr_data_item['goods_items'][$k]['product']['attr'], " ") !== false) {
                                        $arr_data_item['goods_items'][$k]['product']['attr'] = substr($arr_data_item['goods_items'][$k]['product']['attr'], 0, strrpos($arr_data_item['goods_items'][$k]['product']['attr'], $this->app->_(" ")));
                                    }
                                }
                            } elseif ($arr_items['item_type'] == 'adjunct') {
                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_items['item_type']];
                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id' => $arr_items['products']['product_id']), $arrGoods, $tpl);


                                if ($arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj])
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['quantity']));
                                else
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['quantity'] = $arr_items['quantity'];

                                if (!$arrGoods['image_default_id']) {
                                    $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                }
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj] = $arr_items;
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['name'] = $arr_items['name'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['score'] = intval($arr_items['score'] * $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['quantity']);
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['goods_id'] = $arr_items['goods_id'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['price'] = $arr_items['price'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['link_url'] = $arrGoods['link_url'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['amount'] = $arr_items['amount'];

                                if ($arr_items['addon']) {
                                    $arr_addon = unserialize($arr_items['addon']);

                                    if ($arr_addon['product_attr']) {
                                        foreach ($arr_addon['product_attr'] as $arr_product_attr) {
                                            $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                        }
                                    }
                                }

                                if (isset($arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['attr']) && $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['attr']) {
                                    if (strpos($arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['attr'], $this->app->_(" ")) !== false) {
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['attr'] = substr($arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['attr'], 0, strrpos($arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_adj]['attr'], $this->app->_(" ")));
                                    }
                                }

                                $index_adj++;
                            } else {
                                // product gift.
                                if ($arr_service_goods_type_obj[$arr_objects['obj_type']]) {
                                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_items['item_type']];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id' => $arr_items['products']['product_id']), $arrGoods, $tpl);

                                    if ($arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift])
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['quantity']));
                                    else
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['quantity'] = $arr_items['quantity'];

                                    if (!$arrGoods['image_default_id']) {
                                        $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                    }
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift] = $arr_items;
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['name'] = $arr_items['name'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['goods_id'] = $arr_items['goods_id'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['price'] = $arr_items['price'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['score'] = intval($arr_items['score'] * $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['quantity']);
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['link_url'] = $arrGoods['link_url'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['amount'] = $arr_items['amount'];

                                    if ($arr_items['addon']) {
                                        $arr_addon = unserialize($arr_items['addon']);

                                        if ($arr_addon['product_attr']) {
                                            foreach ($arr_addon['product_attr'] as $arr_product_attr) {
                                                $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                            }
                                        }
                                    }

                                    if (isset($arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['attr']) && $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['attr']) {
                                        if (strpos($arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['attr'], $this->app->_(" ")) !== false) {
                                            $arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['attr'] = substr($arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['attr'], 0, strrpos($arr_data_item['goods_items'][$k][$arr_items['item_type'] . '_items'][$index_gift]['attr'], $this->app->_(" ")));
                                        }
                                    }
                                }
                                $index_gift++;
                            }
                        }
                    } else {
                        if ($arr_objects['obj_type'] == 'gift') {
                            if ($arr_service_goods_type_obj[$arr_objects['obj_type']]) {
                                foreach ($arr_objects['order_items'] as $arr_items) {
                                    if (!$arr_items['products']) {
                                        $o = $this->app->model('order_items');
                                        $tmp = $o->getList('*', array('item_id' => $arr_items['item_id']));
                                        $arr_items['products']['product_id'] = $tmp[0]['product_id'];
                                    }

                                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_objects['obj_type']];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id' => $arr_items['products']['product_id']), $arrGoods, $tpl);

                                    if (!isset($arr_items['products']['product_id']) || !$arr_items['products']['product_id'])
                                        $arr_items['products']['product_id'] = $arr_items['goods_id'];

                                    if ($arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']])
                                        $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['quantity']));
                                    else
                                        $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['quantity'] = $arr_items['quantity'];

                                    if (!$arrGoods['image_default_id']) {
                                        $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                    }

                                    $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['name'] = $arr_items['name'];
                                    $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['goods_id'] = $arr_items['goods_id'];
                                    $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['price'] = $arr_items['price'];
                                    $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                    $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['point'] = intval($arr_items['score'] * $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['quantity']);
                                    $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['nums'] = $arr_items['quantity'];
                                    $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['link_url'] = $arrGoods['link_url'];
                                    $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['amount'] = $arr_items['amount'];

                                    if ($arr_items['addon']) {
                                        $arr_addon = unserialize($arr_items['addon']);

                                        if ($arr_addon['product_attr']) {
                                            foreach ($arr_addon['product_attr'] as $arr_product_attr) {
                                                $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                            }
                                        }
                                    }

                                    if (isset($arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['attr']) && $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['attr']) {
                                        if (strpos($arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['attr'], $this->app->_(" ")) !== false) {
                                            $arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['attr'] = substr($arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['attr'], 0, strrpos($arr_data_item[$arr_items['item_type'] . '_items'][$arr_items['products']['product_id']]['attr'], $this->app->_(" ")));
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($arr_service_goods_type_obj[$arr_objects['obj_type']]) {

                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_objects['obj_type']];
                                $arr_data_item['extends_items'][] = $str_service_goods_type_obj->get_order_object($arr_objects, $arr_Goods, $tpl);
                            }
                        }
                    }
                }
            }
        }
    }

    public function coupon($nPage = 1) {
        $nPage = $_POST['n_page'] ? $_POST['n_page'] : 1;
        $oCoupon = kernel::single('b2c_coupon_mem');
        $aData = $oCoupon->get_list_m($this->app->member_id, $nPage);
        if ($aData) {
            $this->member = kernel::single('b2c_user_object')->get_members_data(array('members' => 'member_lv_id'));
            foreach ($aData as $k => $item) {
                if ($item['coupons_info']['cpns_status'] != 1) {
                    $aData[$k]['coupons_info']['cpns_status'] = false;
                    $aData[$k]['memc_status'] = app::get('b2c')->_('此种优惠券已取消');
                    continue;
                }

                $member_lvs = explode(',', $item['time']['member_lv_ids']);
                if (!in_array($this->member['members']['member_lv_id'], (array) $member_lvs)) {
                    $aData[$k]['coupons_info']['cpns_status'] = false;
                    $aData[$k]['memc_status'] = app::get('b2c')->_('本级别不准使用');
                    continue;
                }

                $curTime = time();
                if ($curTime >= $item['time']['from_time'] && $curTime < $item['time']['to_time']) {
                    if ($item['memc_used_times'] < app::get('b2c')->getConf('coupon.mc.use_times')) {
                        if ($item['coupons_info']['cpns_status']) {
                            $aData[$k]['memc_status'] = app::get('b2c')->_('可使用');
                        } else {
                            $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券已作废');
                        }
                    } else {
                        $aData[$k]['coupons_info']['cpns_status'] = false;
                        $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券次数已用完');
                    }
                } else {
                    $aData[$k]['coupons_info']['cpns_status'] = false;
                    $aData[$k]['memc_status'] = app::get('b2c')->_('还未开始或已过期');
                }
            }
        }

        $total = $oCoupon->get_list_m($this->app->member_id);
        return $aData;
    }

    /*
     * 发送站内信
     * */

    public function send_msg() {
        if (!isset($_POST['msg_to']) || $_POST['msg_to'] == '管理员') {
            $_POST['to_type'] = 'admin';
            $_POST['msg_to'] = 0;
        } else {
            $userObject = kernel::single('b2c_user_object');
            $to_id = $userObject->get_id_by_uname($_POST['msg_to']);
            if (!$to_id) {
                $this->splash('failed', null, app::get('b2c')->_('收件人不存在'), $_POST['response_json']);
            }
            $_POST['to_id'] = $to_id;
        }
        if ($_POST['subject'] && $_POST['comment']) {
            $objMessage = kernel::single('b2c_message_msg');
            $_POST['has_sent'] = $_POST['has_sent'] == 'false' ? 'false' : 'true';
            $_POST['member_id'] = $this->app->member_id;
            $_POST['uname'] = $this->member['uname'];
            $_POST['contact'] = $this->member['email'];
            $_POST['ip'] = $_SERVER["REMOTE_ADDR"];
            $_POST['subject'] = strip_tags($_POST['subject']);
            $_POST['comment'] = strip_tags($_POST['comment']);
            if ($_POST['comment_id']) {
                //$data['comment_id'] = $_POST['comment_id'];
                $_POST['comment_id'] = ''; //防止用户修改comment_id
            }
            if ($objMessage->send($_POST)) {
                if ($_POST['has_sent'] == 'false') {
                    return true;
                } else {
                    return true;
                }
            } else {
                $this->Rpc->send_user_error('4003', '发送失败');
            }
        } else {
            $this->Rpc->send_user_error('4003', '必填项不能为空');
        }
    }

    /*
      过滤POST来的数据,基于安全考虑,会把POST数组中带HTML标签的字符过滤掉
     */

    private function check_input($data) {
        $aData = $this->arrContentReplace($data);
        return $aData;
    }

    private function arrContentReplace($array) {
        if (is_array($array)) {
            foreach ($array as $key => $v) {
                $array[$key] = $this->arrContentReplace($array[$key]);
            }
        } else {
            $array = strip_tags($array);
        }
        return $array;
    }

    private function fileext($filename) {
        return substr(strrchr($filename, '.'), 1);
    }

    /**
     * 我要提现
     */
    public function withdrawal($param = array(), $rpcService) {
        $param['page_no'] = $param['page_no'] ? max(1, (int) $param['page_no']) : '1';
        $param['page_size'] = $param['page_size'] ? (int) $param['page_size'] : '10';

        $page_size = $param['page_size'];
        $page_no = ($param['page_no'] - 1) * $page_size;

        $db = kernel::database();

        $member = app::get('b2c')->model('members');
        $mem_wit = kernel::single('b2c_mdl_member_withdrawal');

//         $items_wit = $mem_wit->get_list_bymemId($this->app->member_id);
//         $witlogs = $mem_wit->getList('*',array('member_id' => $this->app->member_id),$page_no,$page_size,'create_time desc',false);
//         $total_results = $mem_wit->count();

        $sql = "SELECT * FROM `sdb_b2c_member_withdrawal` WHERE member_id='" . $this->app->member_id . "'";
        $witlogs = $db->select(" $sql order by  id desc LIMIT $page_no, $page_size");
        $rs = $db->select("SELECT count(*) FROM `sdb_b2c_member_withdrawal` WHERE member_id='" . $this->app->member_id . "'");
        if (!$rs[0]['count']) {
            $total_results = $rs[0]['count'];
        }

        $data['total_results'] = $total_results ? $total_results : 0;

        $total = $member->dump($this->app->member_id, 'advance');

        $where = " where member_id = '" . $this->app->member_id . "' and has_op='true' ";
        $sum_row = $db->selectrow("SELECT SUM(amount) as total_withdrawal FROM `sdb_b2c_member_withdrawal` $where ");


        $data['total'] = $total['advance']['total'];
        $data['total_withdrawal'] = $sum_row['total_withdrawal'] ? $sum_row['total_withdrawal'] : 0;

        $data['witlogs'] = $witlogs;
        return $data;
    }

    /**
     * 申请提现
     */
    public function submit_withdrawal($param = array(), $rpcService) {
        $money = $param['money'];
        // 验证
        if (!preg_match('/^\d*$/', $money) || $money <= 0 || ($money % 100) != 0) {
            $rpcService->send_user_error('4003', '提交的金额不是数字或者金额小于0了！以佰元为单位！');
        }
        $member = app::get('b2c')->model('members');
        $mem_wit = app::get('b2c')->model('member_withdrawal');
        $total = $member->dump($this->app->member_id, 'advance');
        if (($total['advance']['total'] - $money) < 0) {
            $rpcService->send_user_error('4003', '您当前的预存款余额不足');
        }

        $arr['member_id'] = $this->app->member_id;
        $arr['amount'] = $money;
        $arr['create_time'] = time();
        $arr['has_op'] = 'false';
        $arr['alipay_user'] = trim($param['alipay_user']);

        if ($mem_wit->insert($arr)) {
            return '申请成功，请等待管理员操作!';
        } else {
            $rpcService->send_user_error('4003', '申请错误稍后再试');
        }
    }

    private function get_start($nPage, $count) {
        $maxPage = ceil($count / $this->pagesize);
        if ($nPage > $maxPage)
            $nPage = $maxPage;
        $start = ($nPage - 1) * $this->pagesize;
        $start = $start < 0 ? 0 : $start;
        $aPage['start'] = $start;
        $aPage['maxPage'] = $maxPage;
        return $aPage;
    }




    /*     * *--------------------2014.11.二次开发.549224868@qq.com--------------------start---------* * */

    //获取会员等级
    public function get_member_dengji() {
        $lv_id = $this->Members->getList('member_lv_id', array('member_id' => $this->app->member_id));
        $res = kernel::single('b2c_mdl_member_lv')->getList('name', array('member_lv_id' => $lv_id[0]['member_lv_id']));
        if ($res) {
            return $res[0]['name'];
        }
        $this->Rpc->send_user_error('4003', '查询失败');
    }

    /**
     * 	获取银行卡信息
     */
    public function get_banklists() {
        $Bank = kernel::single('b2c_mdl_member_bank');
        $banklists = $Bank->getList('*', array('member_id' => $this->app->member_id), $this->offset, $this->page_nums);
        return $banklists;
    }

    //获取银行卡数量
    public function get_banknums() {
        $res = kernel::single('b2c_mdl_member_bank')->count(array('member_id' => $this->app->member_id));
        return empty($res) ? 0 : $res;
    }

    //添加新的银行卡
    public function addbankcard() {
        if (!empty($this->data_post['bank_num'])) {
            $this->data_post['member_id'] = $this->app->member_id;
            $this->data_post['create_time'] = time();
            $res = kernel::single('b2c_mdl_member_bank')->save($this->data_post);
            if ($res) {
                return true;
            }
        }
        $this->Rpc->send_user_error('4003', '添加失败');
    }

    //删除已绑定的银行卡
    public function delete_bankcard() {
        //$_POST = $this->check_input($_POST);
        if (!empty($_POST['member_bank_id'])) {
            if (kernel::single('b2c_mdl_member_bank')->delete(array('member_bank_id' => $this->data_post['member_bank_id']))) {
                return true;
            }
        }
        $this->Rpc->send_user_error('4003', '操作失败');
    }

    /**
     * 获取账户余额
     */
    public function get_member_wallet() {
        $PayDetail = kernel::single('b2c_mdl_member_advance');
        $res = $PayDetail->getList('member_advance', array('member_id' => $this->app->member_id), 0, 1, 'mtime desc');
        // return $res[0]['member_advance'];
        foreach ($res as $v) {
            
        }
        return $v['member_advance'];
    }

    /*
     * 	收支明细
     */

    public function get_payment_detail() {
        //return $this->data_post;
        $this->where .= ' AND `member_id` = ' . $this->app->member_id;
        if (!empty($this->data_post['pay_status'])) {
            if ($this->data_post['pay_status'] == '+') {
                $this->where .= ' AND `import_money` > 0';
            } else {
                $this->where .= ' AND `explode_money` > 0';
            }
        }
        $this->where .= ' ORDER by mtime DESC';
        //return $this->where;
        return kernel::database()->select('SELECT * FROM sdb_b2c_member_advance ' . $this->where . ' LIMIT ' . $this->offset . ',' . $this->page_nums);
    }

    /**
     * 获取用户相关信息
     */
    public function get_member_aloneinfo() {
        $res = $this->Members->getList('mobile,name,firstname,lastname', array('member_id' => $this->app->member_id));
        if ($res) {
            $res[0]['show_name'] = $this->get_member_showname($this->app->member_id);
            return $res;
        }
        return false;
    }

    //好友列表显示	9，已经被邀请过了	8，未被邀请		1-7,在被邀请的1-7天当中		10,暂停邀请
    public function get_friends_list() {
        if (is_array($this->data_post)) {
            $this->data_post['sign'] = null;
            $this->data_post['date'] = null;
            unset($this->data_post['sign']);
            unset($this->data_post['date']);
            $res = array();
            $DB = kernel::database();
            $Members = $this->Members;
            $a = 0;
            //return $this->data_post;
            foreach ($this->data_post as $k_f => $v_friend) {
                $arr[$a] = $this->get_friend_num($v_friend, $k_f, $DB, $Members);
                $a++;
            }
            return $arr;
        }
        return false;
    }

    protected function get_friend_num($v_friend, $k_f, $DB, $Members) {
        $mobile = str_replace('+86', '', $v_friend);
        $mobile = str_replace(' ', '', $mobile);

        $arr['id'] = $k_f;
        $arr['mobile'] = $v_friend;
        $parent = $Members->getList('parent_id,member_id,name', array('mobile' => $mobile));
        $res = $DB->select("select member_id from sdb_pam_members where login_account = '{$mobile}'");
        //return $res;exit();
        if ($parent[0]['parent_id'] > 0 || $parent[0]['member_id'] > 0 || $res[0]['member_id'] > 0) {
            $arr['status'] = 9;
        } else {
            $time = time() - 7 * 24 * 3600;
            $where = " invite_phone = '{$mobile}' and invite_time > '{$time}'";
            $res = $DB->select("SELECT member_id,invite_time FROM sdb_b2c_member_invite WHERE {$where} LIMIT 1");
            //return $where;
            if ($res) {
                $arr['status'] = ceil((time() - $res[0]['invite_time']) / 24 / 3600);
            } else {
                $time = time() - 14 * 24 * 3600;
                if ($DB->select("SELECT member_id FROM sdb_b2c_member_invite WHERE invite_phone = '{$mobile}' AND member_id != '{$this->app->member_id}' invite_time > '{$time}' ")) {
                    $arr['status'] = 10;
                } else {
                    $arr['status'] = 8;
                }
            }
        }
        return $arr;
    }

    //实名认证
    public function check_real_membername() {
        if (app::get('b2c')->model('member_auth')->getList('is_checked ', array('member_id' => $this->app->member_id))) {
            return '正在审核，您请稍等！';
            exit();
        }
        if (strlen($this->data_post['identity_num']) == 18) {
            if (!empty($this->data_post['real_name'])) {

                $image = $this->imageUpload();

                $this->data_post['uptime'] = time();
                $this->data_post['member_id'] = $this->app->member_id;
                $this->data_post['upimage'] = $image['image_url'];
                $this->data_post['image_id'] = $image['image_id'];
                if ($auth_id = app::get('b2c')->model('member_auth')->insert($this->data_post)) {
                    $is_checked = kernel::single('b2c_mdl_member_auth')->getList('is_checked', array('member_id' => $this->app->member_id));
                    if ($is_checked) {
                        return $is_checked[0];
                    }
                }
            } else {
                $msg = '真实名字不能为空';
            }
        } else {
            $msg =  '身份证号码不正确';
        }
        
    }

    //获取的认真信息
    public function get_check_rel_membername() {
        return kernel::single('b2c_mdl_member_auth')->getList('*', array('member_id' => $this->app->member_id));
    }

    //获取微店的基本信息
    public function get_shop_info() {
        $res = kernel::single('microshop_mdl_shop')->getList('*', array('member_id' => $this->app->member_id));
        if ($res) {
            if (empty($res[0]['wx_name'])) {
                $res[0]['wx_name'] = '您还没有设置微信号呢';
            }
            if (empty($res[0]['desc'])) {
                $res[0]['desc'] = '您还没有给您的微店写点简介呢';
            }
            return $res;
        }
        return false;
    }

    //图片上传公共类
    protected function imageUpload($max_size = 3, $size = '') {
        if (empty($size)) {
            $size == array('width' => '400', 'height' => '300');
        }
        if ($_FILES['file']['size'] > ($max_size * 1024 * 1024)) {
            $this->Rpc->send_user_error('4003', '上传文件不能超过3M！');
        }

        if ($_FILES['file']['name'] != "") {
            $type = array("png", "jpg", "gif", "jpeg"); //允许上传的文件

            if (!in_array(strtolower($this->fileext($_FILES['file']['name'])), $type)) {
                $text = implode(",", $type);
                $this->Rpc->send_user_error('4003', '您只能上传以下类型文件' . $text);
            }
        }

        $mdl_img = app::get('image')->model('image');

        $image_name = $_FILES['file']['name'];
        //return $_FILES;
        $image_id = $mdl_img->store($_FILES['file']['tmp_name'], null, null, $image_name);
        //return $image_id;
        $mdl_img->rebuild($image_id, array('l', 'm', 's'));
        $image_src = base_storager::image_path($image_id, 'l');
        //return 'baid.com';
        if (empty($image_id)) {
            $this->Rpc->send_user_error('4013', '文件上传失败，请重新上传');
            exit();
        }
        return array(
            'res' => 'success',
            'image_id' => $image_id,
            'image_url' => $image_src,
            'image_name' => $image_name
        );
    }

    public function upload_image()
    {
        //return $_FILES['file']['name'].'llll';exit();
        # avatar 160 160
        # cover  640 316

        $uptype = $_POST['type'];


        if ($uptype != 'avatar' && $uptype != 'cover') $this->Rpc->send_user_error('4003', $uptype);

        $old_images = kernel::single('b2c_user_object')->get_members_data(array('members'=> $uptype));

        $size = array(
            'width'   => $uptype == 'avatar' ? '160' : '640',
            'height'  => $uptype == 'avatar' ? '160' : '316',
        );  // 尺寸

        $image = $this->imageUpload(3,$size);
        
        // 更新图片
        app::get('b2c')->model('members')->update(array($uptype => $image['image_id']), array('member_id' => $this->app->member_id));
        
        // 删除旧图
        if ($old_images) {
            app::get('image')->model('image')->delete_image($old_images,'network');
        }

        return $image['image_url'];
    }

    //推荐好友验证信息
    public function tuijian_friend_log() {
        if (!empty($this->data_post['invite_phone']) && $this->data_post['member_id'] == $this->app->member_id) {
            $arr = $this->get_friend_num($this->data_post['invite_phone'], 1, kernel::database(), $this->Members);

            if ($arr['status'] == 8) {
                $mobile = str_replace('+86', '', $this->data_post['invite_phone']);
                $mobile = str_replace(' ', '', $mobile);
                $data['invite_phone'] = $mobile;
                $data['member_id'] = $this->app->member_id;
                $data['invite_time'] = time();

                if (kernel::single('b2c_mdl_member_invite')->insert($data)) {
                    return true;
                }
            }
        }
        return false;
    }

    //积分记录
    public function integral_record() {
        $point = kernel::single('b2c_mdl_member_point');
        $data = $point->getList('*', array('member_id' => $this->app->member_id));
        return $data;
    }
    //修改微信信息
    public function edit_shop_info(){
        if($this->data_post['shop_id'] > 0){
            $id=array();
            $id['shop_id']=$this->data_post['shop_id'];
            unset($this->data_post['shop_id']);
            if(kernel::single('microshop_mdl_shop')->update($this->data_post,$id)){
                return '修改成功';
            }
        }
        return false;
    } 
    /**
     * 未来评价的商品
     */
    public function nodiscuss($param = array(), $rpcService)
    {
        $nPage = $param['n_page'] ? $param['n_page'] : 1;

        //获取会员已发货的商品日志
        $sell_logs = app::get('b2c')->model('sell_logs')->getList('order_id,product_id,goods_id',array('member_id'=>$this->app->member_id));
        //获取会员已评论的商品
        $comments = app::get('b2c')->model('member_comments')->getList('order_id,product_id',array('author_id'=>$this->app->member_id,'object_type'=>'discuss','for_comment_id'=>'0'));
        $data = array();
        if($comments){
            foreach((array)$comments as $row){
                if($row['order_id'] && $row['product_id']){
                    $data[$row['order_id']][$row['product_id']] = $row['product_id'];
                }
            }
        }

        foreach((array)$sell_logs as $key=>$log_row){
            if($data && $data[$log_row['order_id']][$log_row['product_id']] == $log_row['product_id']){
                unset($sell_logs[$key]);
            }else{
                $filter['order_id'][] = $log_row['order_id'];
                $filter['product_id'][] = $log_row['product_id'];
                $filter['item_type|noequal'] = 'gift';
            }
        }

        $orderItemModel = app::get('b2c')->model('order_items');
        $limit = $this->pagesize;
        $start = ($nPage-1)*$limit;
        $i = 0;
        $nogift = $orderItemModel->getList('order_id,product_id',$filter);
        if($nogift){
            foreach($nogift as $row){
                $tmp_nogift_order_id[] = $row['order_id'];
                $tmp_nogift_product_id[] = $row['product_id'];
            }
        }
        foreach((array)$sell_logs as $key=>$log_row){
            if(in_array($log_row['order_id'],$tmp_nogift_order_id) && in_array($log_row['product_id'],$tmp_nogift_product_id) ){//剔除赠品,赠品不需要评论
                if($i >= $start && $i < ($nPage*$limit) ){
                    $sell_logs_data[] = $log_row;
                    $gids[] = $log_row['goods_id'];
                }
                if($nogift){
                    $i += 1;
                }
            }
        }
        $totalPage = ceil($i/$limit);
        if($nPage > $totalPage) $nPage = $totalPage;

        $result['pagination']['nPage'] = $nPage;
        $result['pagination']['totalPage'] = $totalPage;

        if($gids){
            //获取商品信息
            $goodsData = app::get('b2c')->model('goods')->getList('goods_id,name,image_default_id',array('goods_id'=>$gids));
            $goodsList = array();
            foreach((array)$goodsData as $goods_row){
                foreach ($sell_logs_data as $k => $v) {
                    if ($v['goods_id'] == $goods_row['goods_id']) {
                        $goodsList[$k]['goods_id'] = $goods_row['goods_id'];
                        $goodsList[$k]['product_id'] = $v['product_id'];
                        $goodsList[$k]['order_id'] = $v['order_id'];
                        $goodsList[$k]['goods_name'] = $goods_row['name'];
                        $goodsList[$k]['default_img_url'] = base_storager::image_path($goods_row['image_default_id'], 's' );
                    }
                }
            }
            shuffle($goodsList);
            $result['list'] = $goodsList;

            $result['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
            $result['verifyCode'] = app::get('b2c')->getConf('comment.verifyCode');
            if($result['point_status'] == 'on'){
                //评分类型
                $comment_goods_type = app::get('b2c')->model('comment_goods_type');
                $result['comment_goods_type'] = $comment_goods_type->getList('*');
                if(!$result['comment_goods_type']){
                    $sdf['type_id'] = 1;
                    $sdf['name'] = app::get('b2c')->_('商品评分');
                    $addon['is_total_point'] = 'on';
                    $sdf['addon'] = serialize($addon);
                    $comment_goods_type->insert($sdf);
                    $result['comment_goods_type'] = $comment_goods_type->getList('*');
                }
            }

        return $result;
        //$result['submit_comment_notice'] = $this->app->getConf('comment.submit_comment_notice.discuss');
        }
    }

}
