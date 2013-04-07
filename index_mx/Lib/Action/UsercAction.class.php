<?php
/**
 * 用户 荐股环
 * */
class UsercAction extends AclAction {
    public function _initialize(){
        if(!isset($_SESSION['MEIX'])||empty($_SESSION['MEIX']['iduser'])){
            $_SESSION['MEIX']['JS']['url_login'] = '';
            R('Login/index');
            exit(0);
        }
    }


    public function index(){
    	if(!empty($_SESSION['MEIX']['iduser'])){
    	   $user_id = $_SESSION['MEIX']['iduser'];
           $rec_stocks = M('Rec_stocks');
           $maprec_stocks['user_id'] = $user_id;
           
           $rec_stocks_cycle = M('Rec_stocks_cycle');
           $mapcycle['user_id'] = $user_id;
           
           $list = $rec_stocks_cycle->where($mapcycle)->group('stock_id')->select();//股票
           foreach($list as $keysinfo=>$vosinfo){
               $list[$keysinfo]['Stocknow'] = R('Tool/tool_stock_now',array($vosinfo['stock_number']));
               $mapcycle['stock_id'] = $vosinfo['stock_id'];//环
               $list[$keysinfo]['huang'] = $rec_stocks_cycle->where($mapcycle)->select();//环
               unset($maprec_stocks);
               foreach($list[$keysinfo]['huang'] as $huangkey=>$huangvo){
                   $maprec_stocks['rec_stocks_cycle_id'] = $huangvo['idrec_stocks_cycle'];
                   $list[$keysinfo]['huang'][$huangkey]['Rec_stocks'] = $rec_stocks->where($maprec_stocks)->select();
               }
           }
           $this->assign('list',$list);
           $this->display();
    	
        }
    }
    
    /**
     * 3环
     * */
    public function rec_3_a(){
        if($this->isAjax()){
            $this->acl_input3();
            if(!empty($_POST['cycle'])&&!empty($_POST['price_a'])&&!empty($_POST['price_b'])){
                if($_POST['price_a']==$_POST['price_b']){ $status = 25; }else{ $status = 21; }
                $stocks_cycle = M('Rec_stocks_cycle');
                $stocks_cyclemap['user_id'] = $_SESSION['MEIX']['iduser'];
                $stocks_cyclemap['lock'] = 1;
                $stocks_cyclemap['idrec_stocks_cycle'] = $_POST['cycle'];
                $stocks_cyclefind = $stocks_cycle->where($stocks_cyclemap)->find();//
                if($stocks_cyclefind){
                    //3环
                    $stocks_cycledata['status'] = $status;
                    $stocks_cycle->where($stocks_cyclemap)->save($stocks_cycledata);
                    $rec_stocks_cycle_id = $stocks_cyclefind['idrec_stocks_cycle'];
                    //end3环
                    $mainok = $this->rec_add_main_c($_POST['content'],$stocks_cyclefind['stock_id'],$_POST['price_a'],$_POST['price_b'],$_POST['mktime_b'],$rec_stocks_cycle_id,$status,$_POST['first']);
                    if($mainok){
                        if(!empty($_POST['groups'])){
                            $groups = explode(',',$_POST['groups']);
                            if(!$groups){
                                $groups = array($_POST['groups']);
                            }
                            if(is_array($groups)){
                                $this->rec_add_groups_c($mainok,$groups);
                            }
                        }
                        if(!empty($_SESSION['MEIX']['iduser'])){
                            $user_list = M('User_list');
                            $ulmap['rec_stocks_id'] = $mainok;
                            $ulmap['mktime'] = mktime();
                            $ulmap['top'] = 0;
                            $ulmap['user_id'] = $_SESSION['MEIX']['iduser'];
                            $user_list->add($ulmap);
                        }
                        $this->ajaxReturn('','',1);
                    }else{
                        $this->ajaxReturn('','2',0);
                    }
                }else{
                    $this->ajaxReturn('','每人只能对20只股票开仓，每股票只能保留5个开仓',0);
                }
                    
            }else{
                $this->ajaxReturn('','1',0);
            }
        }
    }
    private function rec_add_main_c($content='',$stocks_id,$price_a,$price_b,$mktime_b,$rec_stocks_cycle_id,$status,$first=''){
        $rec_stocks = M('Rec_stocks');
        $map['content'] = $content;
        $map['content_t'] = R('Tool/msubstr_txt',array($map['content']));
            $stock = M('Stock');
            $mapstocks['idstock'] = $stocks_id;
            $mapstocks['lock'] = 1;
            $stocksfind = $stock->where($mapstocks)->find();
            if($stocksfind){
                $map['stocks_id'] = $stocks_id;
                $map['stocks_name'] = $stocksfind['showname'];
                $map['stocks_number'] = $stocksfind['shownumberb'].$stocksfind['shownumber'];
                
            }else{
                //return false;
                //exit();
            }
        if(!empty($first)){
            $map['moreorempty'] = $first;
        }else{
            $map['moreorempty'] = 'buy';
        }
        $map['price_a'] = $price_a;
        $map['price_b'] = $price_b;
        $map['mktime_a'] = mktime();
        $map['mktime_b'] = mktime()+($mktime_b*86400);
        $map['info_top'] = 0;
        $map['info_poor'] = 0;
        $map['info_message_count'] = 0;
        $map['info_digest'] = 0;
        $map['user_id'] = $_SESSION['MEIX']['iduser'];
        $map['user_name'] = $_SESSION['MEIX']['name'];
        $map['user_avatar'] = $_SESSION['MEIX']['avatar'];
        $map['mktime'] = mktime();
        if($status==25){ $map['rate'] = 2; }else{ $map['rate'] = 1; }
        $map['status'] = $status;
        $map['rec_stocks_cycle_id'] = $rec_stocks_cycle_id;
        $ok = $rec_stocks->add($map);
        if($ok){
            return $rec_stocks->getLastInsID();
        }else{
            return false;
        }
    }
    
    /**
     * 2环，4环
     * flush()
     * sleep()
     * */
     
    public function rec_stock_clear($lock){
        if($lock==13){
            set_time_limit(0); 
            $rec_stocks = M('Rec_stocks');
            $rec_stocksmap['rate'] = 1;
            $rec_stocksidlist = $rec_stocks->where($rec_stocksmap)->select();
            $i = array('all'=>0,'shibai'=>0,'chenggong'=>0);
            foreach($rec_stocksidlist as $recsidvo){
                $t2t = R('Tool/tool_stock_t2t',array($recsidvo['stocks_number'],$recsidvo['mktime_a'],$recsidvo['mktime_b']));
                if($recsidvo['status']==11){
                    $status = 15;    
                }else{
                    $status = 25;
                }
                if($recsidvo['moreorempty']=='sell'){
                    $moreorempty = 'buy';
                }else{
                    $moreorempty = 'sell';
                }
                if(!empty($t2t['high'])&&($t2t['high']>$recsidvo['price_b'])&&($t2t['low']<$recsidvo['price_b'])){
                    $i['chenggong']++;//成功
                    $mainok = $this->rec_add_main_c('成功',$recsidvo['stocks_id'],$recsidvo['price_a'],$recsidvo['price_b'],$recsidvo['mktime_b'],$recsidvo['rec_stocks_cycle_id'],$status,$moreorempty);
                        if($mainok){
                            if(1){
                                $groups_list = M('Groups_list');
                                $groups_listmap['rec_stocks_id'] = $recsidvo['idrec_stocks'];
                                $groups_listfind = $groups_list->where($groups_listmap)->find();
                                foreach($groups_listfind as $groups_listvo){
                                    $groups[] = $groups_listvo['groups_id'];
                                }
                                if(is_array($groups)){
                                    $this->rec_add_groups_c($recsidvo['idrec_stocks'],$groups);
                                }
                            }
                            if(!empty($recsidvo['user_id'])){
                                $user_list = M('User_list');
                                $ulmap['rec_stocks_id'] = $recsidvo['idrec_stocks'];
                                $ulmap['mktime'] = mktime();
                                $ulmap['top'] = 0;
                                $ulmap['user_id'] = $recsidvo['user_id'];
                                $user_list->add($ulmap);
                            }
                            $this->ajaxReturn('','',1);
                        }else{
                            $this->ajaxReturn('','2',0);
                        }
                }elseif($recsidvo['mktime_b']<mktime()){
                    $i['shibai']++;//失败
                    $mapsbrec['idrec_stocks'] = $recsidvo['idrec_stocks'];
                    $data['rate'] = 3;
                    $data['status'] = $status-5;
                    $rec_stocks_cyclemapdata['status'] = $status-5;
                    $rec_stocks->where($mapsbrec)->save($data);
                    $rec_stocks_cycle = M('Rec_stocks_cycle');
                    $rec_stocks_cyclemap['idrec_stocks_cycle'] = $recsidvo['rec_stocks_cycle_id'];
                    $rec_stocks_cycle->where($rec_stocks_cyclemap)->save($rec_stocks_cyclemapdata);
                }
            $i['all']++;
            }
            return $i;
        }
        
        
    }
    /*
	 * 2013-3-19上午02:31:29
	 * 荐股圈子表添加保存主方法
	**/
    private function rec_add_groups_c($id,$grouparr){
        $groups_list = M('Groups_list');
        $map['rec_stocks_id'] = $id;
        $map['mktime'] = mktime();
        foreach($grouparr as $vo){
            $map['groups_id'] = $vo;
            $groups_list->add($map);
        }
    }
    

}

?>