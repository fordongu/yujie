<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author afei, bryant
 */


class system_ctl_admin_fx_version extends desktop_controller {

    function index() {
		$this->finder(
		'system_mdl_fx_version', array(
		'title' => app::get('system')->_('APP版本列表'),
		'actions' => array(
			array(
				'label' => app::get('system')->_('添加新版本'), 'icon' => 'add.gif',
				'href' => 'index.php?app=system&ctl=admin_fx_version&act=add_version',
				'target' => 'dialog::{title:\'添加新版本\', width:800, height:420}'
			),
            )
        ));
    }
	
	public function edit(){
	
		
	}

    public function add_version() {	
	//var_dump($_SERVER['SERVER_NAME']);
		$this->datapage['time'] = time();
		$this->datapage['weburl'] = $_SERVER['SERVER_NAME'];
		$this->display('admin/add_version.html');
    }
	
    private function Upload($uploaddir = '/alidata/www/down')
    {
        $tmp_name =$_FILES['file']['tmp_name'];  // 文件上传后得临时文件名
        $name     =$_FILES['file']['name'];     // 被上传文件的名称
        $size     =$_FILES['file']['size'];    //  被上传文件的大小
        $type     =$_FILES['file']['type'];   // 被上传文件的类型
        $dir      = $uploaddir;
        @chmod($dir,0777);//赋予权限
        @is_dir($dir) or mkdir($dir,0777);
        //chmod($dir,0777);//赋予权限
        if(move_uploaded_file($_FILES['file']['tmp_name'],$dir."/".$name)){
            
        }else{
            //return 'llllllll';
        }
        $type = explode(".",$name);
        $type = @$type[1];
        $date   = date("YmdHis");
        $rename = @rename($dir."/".$name,$dir."/".$date.".".$type);
        if($rename)
        {
            return $date.".".$type;//$date.$type;//$dir."/".$date.".".$type;
        }else{
            return $name;
        }
    }
    
    public function save(){
        //var_dump($_POST);
        //var_dump($_FILES);
       // exit();
       
        $filename = $this->Upload();

        $model = app::get('system')->model('fx_version');
        $data['app_name'] = $_POST['auth']['app_name'];
        $data['update_time'] = time();
        $data['version_num'] = $_POST['auth']['version_num'];
        $data['down_url'] = DOWN_URL.$filename;
        $data['remark'] = $_POST['remark'];
        $data['app_type'] = $_POST['app_type'];
		//var_dump($data);exit();
		//$data['download_num'] = $_POST['app_name'];
        $url = $_SERVER['HTTP_HOST'].'/index.php/shopadmin/index.php#app=system&ctl=admin_fx_version&act=index';

        if( $model->insert($data)){
            $this->redirect($url);
        } else {
           $this->redirect($url);
        }
    }
	
	public function clean(){
		

		if($_POST['leixing'] == 'clean'){
			if(cachemgr::clean()){
				echo '缓存清除成功!';
			}else{
				echo '缓存清除失败!';
			}
		}else{
			$this->page('admin/memcached.html');
		}
	}



}
