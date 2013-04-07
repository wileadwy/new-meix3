<?php

class Cutimg  {
   /////////////////////////avatar-1/////////////////////
    public $ATTACHDIR = 'Public/upload/cutimg';//图片资源路径
    public $ATTACHSIZE = '8485760';
    public $ATTACHEXT = 'jpg,gif,png,jpeg';
    public $THUMBMAXWIDTH ='800';
    public $THUMBMAXHEIGHT = '800';
    public $AVATAR = 'Public/upload/cutimg/ok/1';//用户头像
    public $sessionid = '';//目录
    public function sessionid(){
        if(!empty($_SESSION['SITEADMIN']['idpersonnel'])){
            if($this->sessionid==''){
                return $_SESSION['SITEADMIN']['idpersonnel'];
            }else{
                return $this->sessionid;
            }
            
        }else{
            return 'tmep';
        }
    }
    /////////////////////////avatar-2/////////////////////
    /**
 * public function avatar(){
 *      $this->display();
 *     }
 *     public function cutimg(){
 *         $result = $this->upload('temp');
 *         if (!is_array($result)){
 *             $this->redirect('index');
 *         }else{
 *            $imgurl = '/'.$this->ATTACHDIR . '/temp/'.$this->sessionid().'/'. $result[0]['savename'];

 *            $this->assign('imgurl',__ROOT__.$imgurl);
 *            $this->assign('imgname',$result[0]['savename']);
 *            $this->assign('imgstyle',$this->avatar_getimagesize($imgurl));//style
 *            $retmp = getimagesize($imgurl);//wh
 *            $this->assign('imgwh',$retmp[0].','.$retmp[1]);//wh
 *            //dump($imgurl);
 *            $this->display();
 *         }
 *     }
 */
     // cutting images
    
    public function upload($module = '', $path = '', $thumb = '', $width = '', $height = ''){
        $module = $module = ""?'temp':$module;
        switch ($module){
           case 'temp':$path = $this->ATTACHDIR . '/temp/'.$this->sessionid().'/' . $path;
            break;
           case 'storehouse':$path = $this->ATTACHDIR . '/storehouse/' . $path;
            break;
           case 'shop':$path = $this->ATTACHDIR . '/shop/' . $path;
            break;
           case 'trader': $path = $this->ATTACHDIR . '/trader/' . $path;
            break;
           case 'group': $path = $this->ATTACHDIR . '/group/' . $path;
            break;
           case 'my': $path = $this->ATTACHDIR . '/avatar/' . $path;
            break;
           default:$path = $this->ATTACHDIR . '/file/' . $path;
        }
        if (!is_dir($path)) @mk_dir($path);
        import("ORG.Net.UploadFile");
        $upload = new UploadFile();
        $upload->maxSize = $this->ATTACHSIZE ;//C(ATTACHSIZE);
        $upload->allowExts = explode(',', strtolower($this->ATTACHEXT));//C(ATTACHEXT)
        $upload->savePath = $path;
        $upload->saveRule = uniqid;
        $upload->thumb = true;
        $upload->thumbMaxWidth = $this->THUMBMAXWIDTH;//C(THUMBMAXWIDTH);
        $upload->thumbMaxHeight = $this->THUMBMAXHEIGHT;//C(THUMBMAXHEIGHT);
        $upload->thumbPrefix = '';
          //$upload->thumbRemoveOrigin = true;
        if (!$upload->upload()){
            return $this->error($upload->getErrorMsg());
        }else{
            return $upload->getUploadFileInfo();
        }
    }
    public function avatar_getimagesize($url,$nu=true){///图片自适应//style="php echo AclAction::avatar_getimagesize("Public/".$v['Imgface']['url']."/t".$v['Imgface']['file'],165,200); "
        $retmp = getimagesize($url);
        if($nu){
            $re[] = 'width:'.$retmp[0].'px; ';
            $re[] = "height:".$retmp[1]."px; ";
        }else{
            $re[] = $retmp[0];
            $re[] = $retmp[1];
        }
        
        return $re;
    }
/////////////////////////avatar_2/////////////////////
}

?>