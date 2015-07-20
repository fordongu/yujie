<?php
class wap_ctl_default extends wap_controller{

    function index(){
        $GLOBALS['runtime']['path'][] = array('title'=>app::get('wap')->_('首页'),'link'=>kernel::base_url(1));
        $this->set_tmpl('index');
        $this->title=app::get('wap')->getConf('wap.shopname');
        if(in_array('index', $this->weixin_share_page)){
            $this->pagedata['from_weixin'] = $this->from_weixin;
            $this->pagedata['weixin']['appid'] = $this->weixin_a_appid;
            $this->pagedata['weixin']['imgUrl'] = base_storager::image_path(app::get('weixin')->getConf('weixin_basic_setting.weixin_logo'));
            $this->pagedata['weixin']['linelink'] = app::get('wap')->router()->gen_url(array('app'=>'wap','ctl'=>'default', 'full'=>1));
            $this->pagedata['weixin']['shareTitle'] = app::get('weixin')->getConf('weixin_basic_setting.weixin_name');
            $this->pagedata['weixin']['descContent'] = app::get('weixin')->getConf('weixin_basic_setting.weixin_brief');
        }
        
        $db = kernel::database();
        $sql = "select *  FROM `sdb_mobileapi_indexad` where disabled = 'false' order by group_code asc, ordernum asc;";
        $indexads = $db->select($sql);
        $groupad = array();
        foreach ($indexads as $key => $v){

                $v['ad_img'] = base_storager::image_path($v['ad_img']);
                $groupad[$v['group_code']]['group_code'] = $v['group_code'];
                $groupad[$v['group_code']]['group_name'] = $v['group_name'];
                $groupad[$v['group_code']]['items'][] = $v;
        }
        $res = array();
        foreach ($groupad as $key => $value) {
                $res[] = $value;
        }
	$this->pagedata['adv_list'] = $res;	
        
        $this->page('index.html');
    }

    //验证码组件调用
    function gen_vcode($key='vcode',$len=4){
        $vcode = kernel::single('base_vcode');
        $vcode->length($len);
        $vcode->verify_key($key);
        $vcode->display();
    }

}