<?php
class upload{
    public $path;
    public $type;
    public $maxSize;
    function __construct($path="file",$type=array("image/png","image/jpeg","image/gif"),$maxSize=3145728){
        $this->path=$path;
        $this->type=$type;
        $this->maxSize=$maxSize;
    }
    function move($myFile="upimage",$isImage=true){
        if($_FILES){
            $errorArr=array();
            foreach($_FILES[$myFile]['error'] as $k => $v){
               if($v===0){
                    if($isImage){
                        $typeArr=getimagesize($_FILES[$myFile]['tmp_name'][$k]);
                        if(is_array($typeArr)){
                            $type=$typeArr['mime'];
                        }else{
                            $errorArr[$k]="不是一个真实的类型";
                        }
                    }else{
                        $type=$_FILES[$myFile]['type'][$k];
                    }
                    if(in_array($type,$this->type)){
                        if($_FILES[$myFile]['size'][$k]<=$this->maxSize){
                            if(is_uploaded_file($_FILES[$myFile]['tmp_name'][$k])){
                                //开始移动文件
                                //保证文件名的唯一性
                                $fileName=md5(uniqid(microtime(true),true));
                                //后缀名
                                $ext=strtolower(pathinfo($_FILES[$myFile]['name'][$k],PATHINFO_EXTENSION));
                                //文件夹是否存在
                                if(file_exists($this->path)){
                                        if(move_uploaded_file($_FILES[$myFile]['tmp_name'][$k],$this->path."/".$fileName.".".$ext)){
                                            $str = $fileName.".".$ext;
                                            return $str;
                                        }
                                }else{
                                    mkdir($this->path,0777,true);
                                    if(move_uploaded_file($_FILES[$myFile]['tmp_name'][$k],$this->path."/".$fileName.".".$ext)){
                                         $str = $fileName.".".$ext;   
                                         return $str;
                                    }
                                }
                            }else{
                                $errorArr[$k]="文件不是通过HTTP POST传输过来的";
                            }
                        }else{
                            $errorArr[$k]="文件超过最大值";
                        }
                    }else{
                        $errorArr[$k]="文件类型不合法";
                    }
                }else{
                    $errorArr[$k]="没有选择文件上传";
               }
            }
        }else{
            $errorArr="不是一个文件";
        }
        return $errorArr;
    }
}