<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends AclAction {
    public function _initialize(){
        if(!isset($_SESSION['MEIX'])||empty($_SESSION['MEIX']['iduser'])){
            $_SESSION['MEIX']['JS']['url_login'] = '';
            //R('Login/index');
            exit(0);
        }
    }
    public function index(){
        //$model = M('Stock');
        //$modellist = $model->select();
        /*
        foreach($modellist as $vo){
            $arr = R('Pinyin/index',array($vo['showname']));
            $map['idstock'] = $vo['idstock'];
            $data['name_pinyin'] = $arr['pinyin'];
            $data['name_jianpin'] = $arr['jianpin'];
            $model->where($map)->save($data);
        }
        */
        R('Home/index');
    }
    /*
    public function list_info1(){
        $ok = $this->qqapi();   
        if($ok=='ok'){
            $arraydata['format'] = "json";
            $arraydata['reqnum'] = 10;
            $arraydata['startindex'] = 0;
            $arraydata['mode'] = 0;
            $arraydata['sex'] = 0;
            //$arraydata['fopenids'] = '1EAC50975E0A09D58E4E3A9EC89D0B4F';
            $r = Tencent::api('friends/fanslist',$arraydata, 'GET');
            dump(json_decode($r)) ;
            dump($_SESSION);
            
        }
        
    }
    public function qqlist(){
        $this->qqapi();   
        if($ok=='ok'){ 
            //获取用户信息
            $r = Tencent::api('user/info');
            $userinfo = (json_decode($r, true));
            dump($userinfo);
        }
    }
    */
}