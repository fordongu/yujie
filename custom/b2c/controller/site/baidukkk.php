<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class b2c_ctl_site_baidukkk extends b2c_frontpage{
    
    public function __construct() {
        parent::__construct();
        header("Content-type:text/html;charset=utf-8");
    }
    
    public function d($v){
        echo '<pre>';
        print_r($v);
        echo '</pre>';
    }

    public  function index(){
        echo <<<Eof
        <script type="text/javascript">
    function copy_code(copyText) 
    {
        if (window.clipboardData) 
        {
            alert(2222);
            window.clipboardData.setData("Text", copyText)
        } 
        else 
        {
            var flashcopier = 'flashcopier';
            if(!document.getElementById(flashcopier)){
              alert(88888);
              var divholder = document.createElement('div');
              divholder.id = flashcopier;
              document.body.appendChild(divholder);
            }
            document.getElementById(flashcopier).innerHTML = '';
            var divinfo = 'uuuuuuuuuu';
            document.getElementById(flashcopier).innerHTML = divinfo;
        }
      alert('copy成功！');
    }
    </script>
    
    <input id="inputTest" type="button" value="测试" onclick="copy_code('拷贝成功')"  />
Eof;
        
        $this->d($_SERVER);
        exit();
        $this->d(app::get('b2c')->getConf('system.bank.name'));exit();
        
        //$aa = app::get('site')->getConf('site.name');

        $aa = kernel::single('b2c_messenger_smshuyi');
        $contents = array('0'=>array('phones'=>'13580879979','content'=>'您的验证码是：【'.  rand(100000, 999999).'】。请不要把验证码泄露给其他人。如非本人操作，可不用理会！'));
        $aa->send($contents);
        $aa->index();
        //var_dump($aa);
        exit();
        $v = '8_jdjy0 
            ';
        //$v = str_replace(array(" ","　","\t","\n","\r"), '', $v);
        $arr1 = array(1,2,3,4);
        //$arr2 = array(5,6,7,8);
        //$base = kernel::single('base_storager');
        //$res = $base->image_path(app::get('b2c')->getConf('site.avatar'));
        //var_dump($res);exit();
        header('Content-type:text/html;charset=utf-8');
        $Members = kernel::single('b2c_mdl_members');
        
        
        $huanxin = kernel::single('b2c_huanxin_registhuanxin');
        $Group = kernel::single('b2c_huanxin_group');
        //var_dump($huanxin->options);exit();
        $arr1 = array(
            'username'=>'14_Q0jL8',//6_MT2sY',
            'password'=>'123456',
            'newpassword'=>'654321',
        );
        $arr2 = array('8_JdjY0','14_Q0jL8','13_Cbktq','10_NfibH','36_26WZp','46_qdo1o');
        $arr3  = array('data'=>$arr2);
        $arr = array(
            'username'=>'14_Q0jL8',
            'nickname'=>'baiducom',
        );
       //$res = $huanxin->edit_nickname($arr);
        //$res = $huanxin->editPassword($arr1);
        //$res = $huanxin->save_relative(8,52);
       //$res = $Group->add_group(array('groupname' => '我美好的大家庭', 'desc' => '最高级代理商', 'member_id' => '6',));
        //$res = $huanxin->send_message($arr2,'999hhhhhhh99999你个iiiiiiiiiiii锤子',"users");
        //$res  = $Group->add_group_username(14,7);
        //$res = $Members->getList('member_id,nickname,mobile',array('nickname|head'=>'23'));
        $res = kernel::single('b2c_mdl_huanxin_user')->getmember_id($v);
        //$res = explode('_', $v);
        var_dump($res);
        exit();
        $mems = kernel::single('b2c_mdl_members')->getList('member_id,nickname');
        
        foreach($mems as $v){
               $res = $Group->add_group(array(
                        'member_id' => $v['member_id'],
                        'desc' => $v['nickname'].'的小伙伴儿们',
                        'public' => true,
                        'groupname' => $v['nickname'].'伙伴儿群',
                        'approval' => true,
                        'maxusers' => 300,                        
                    )); //添加伙伴儿群 
                    var_dump($res);echo ' <hr>';
        }
         
        
    }

}//end
