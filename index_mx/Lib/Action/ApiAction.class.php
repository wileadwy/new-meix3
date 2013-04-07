<?php
class ApiAction extends AclAction{
	
    public function login(){
        $sinaurl = $this->sinadev();
        $this->assign('sinaurl',$sinaurl);
        $this->display('Api:login');
    }
    public function me() {
        if(!empty($_GET['txt'])){
            import("ORG.Util.Input");
            $txt = str_replace('\\\\','\\',str_replace('\&quot;','&quot;',Input::forShow($_GET['txt'])));//self::forShow($vo);//forShow($vo);
        }elseif(!empty($_SESSION['MEIX']['JS']['txt'])){
            $txt =$_SESSION['MEIX']['JS']['txt'];
        }
        if(!isset($_SESSION['MEIX'])||empty($_SESSION['MEIX']['iduser'])){
            $_SESSION['MEIX']['JS']['url_login'] = __APP__.__SELF__;//__APP__.'/Api/me/txt/'.$txt;
            //$_SESSION['MEIX']['JS']['txt'] = $txt;
            R('Api/login');
            exit();
        }
        
        $this->assign('txt',$txt);
		$this->display();
    }
    public function search_a(){
        if($this->isAjax()){//$this->isAjax()
            $this->acl_input3();
            $name = trim($_POST['name']);
            if(!empty($name)){
                $limitnum = 9;
                $stock = M('Stock');
                $map['lock'] = 1;
                if(!empty($_POST['level'])){ $map['level'] = $_POST['level']; }
                $map['showname'] = $name;
                $stocklist[0] = $stock->where($map)->find();///////////1
                $map['showname'] = array('like',$name.'%');
                if($stocklist[0]){
                    $map['idstock'] = array('not in',$stockidarray[0]['idstock']);
                }
                $stock2 = $stock->where($map)->limit($limitnum)->select();////////////////2
                if($stock2){
                    if(!empty($stocklist[0])){
                        $stocklist = array_merge($stocklist,$stock2);
                    }else{
                        $stocklist = $stock2;
                    }
                }
                    
                if((count($stocklist)<$limitnum)){
                    $map['showname'] = array('like','%'.$name.'%');
                    if($stock2){
                        foreach($stocklist as $vo){$stockidarray[] = $vo['idstock']; }
                        $map['idstock'] = array('not in',$stockidarray);
                    }
                    $stock3 = $stock->where($map)->limit($limitnum-count($stocklist))->select();/////////////3
                    if($stock3){
                        if(!empty($stocklist)){
                            $stocklist = array_merge($stocklist,$stock3);
                        }else{
                            $stocklist = $stock3;
                        }
                    }
                    if((count($stocklist)<$limitnum)){
                        $map['shownumber'] = array('like','%'.$name.'%');
                        if($stock3){
                            foreach($stocklist as $vo){$stockidarray[] = $vo['idstock']; }
                            $map['idstock'] = array('not in',$stockidarray);
                        }
                        $stock4 = $stock->where($map)->limit($limitnum-count($stocklist))->select();//////////4
                        if($stock4){
                            if(!empty($stocklist)){
                                $stocklist = array_merge($stocklist,$stock4);
                            }else{
                                $stocklist = $stock4;
                            }
                        }
                            
                    }
                }
                $theme['theme'] = 'search_api';
                $theme['stocklist'] = $stocklist;
                $themew = W('Theme',$theme,true);
            }
            if(!empty($themew)){
                $this->ajaxReturn($themew,'',1);
            }else{
                $this->ajaxReturn('','无',0);
            }
        }
    }
    
    
    
}
?>