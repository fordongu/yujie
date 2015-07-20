<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of video
 *
 * @author Administrator
 */
class b2c_ctl_admin_member_auth extends desktop_controller {


	//列表页面
    public function index() {
        $this->finder(
            'b2c_mdl_member_auth', array(
			'title' => app::get('b2c')->_('实名认证申请列表'),
			'actions' => array(
				array(
					'label' => app::get('b2c')->_('审核失败'), 'icon' => 'add.gif',
					'href' => 'index.php?app=b2c&ctl=admin_member_auth&act=liebiao&value=1',
					'target' => 'dialog::{title:\'审核失败列表\', width:800, height:420}'
				),
				array(
					'label' => app::get('b2c')->_('审核成功'), 'icon' => 'add.gif',
					'href' => 'index.php?app=b2c&ctl=admin_member_auth&act=liebiao&value=2',
					'target' => 'dialog::{title:\'审核成功列表\', width:800, height:420}'
				),
				array(
					'label' => app::get('b2c')->_('未审核'), 'icon' => 'add.gif',
					'href' => 'index.php?app=b2c&ctl=admin_member_auth&act=liebiao&value=0',
					'target' => 'dialog::{title:\'未审核列表\', width:800, height:420}'
				),
            )
        ));
    }
	
	public function liebiao(){
		$oML = kernel::single('b2c_mdl_member_auth');	
		$res = $oML->getList('*',array('is_checked'=>$_GET['value']),1,-1,'auth_id asc');
		$this->datapage['auths'] = $res[0];
		$this->datapage['weburl'] = $_SERVER['SERVER_NAME'];
		//var_dump($res[0]);
		$this->page('admin/member/liebiao.html');
	}
	
	public function detail_auth(){
		$this->begin('index.php?app=b2c&ctl=admin_member_auth&act=index');
		if($_GET['auth_id'] > 0){
			$auth = kernel::database()->select("select * from sdb_b2c_member_auth where auth_id = {$_GET['auth_id']}");
			//var_dump($auth[0]);
			$this->pagedata['auth'] = $auth[0];
			$this->pagedata['upimage'] = explode("|", $auth[0]['upimage']);
			$this->page('admin/member/edit_auth.html');
			exit();
		}
		$this->end(false, app::get('b2c')->_('页面错误'));
	}
	
	public function save(){
		//var_dump($_POST);exit();
		$this->begin('index.php?app=b2c&ctl=admin_member_auth&act=index');
		if($_POST['auth_id'] > 0){
			$oML = kernel::single('b2c_mdl_member_auth');
			if ($oML->update(array('is_checked'=>$_POST['is_checked']),array('auth_id'=>$_POST['auth_id']))) {
				$this->end(true, app::get('b2c')->_('保存成功'));
			} else {
				$this->end(false, app::get('b2c')->_('保存失败'));
			}
			exit();
		}
		$this->end(false, app::get('b2c')->_('页面错误'));
	} 
}
