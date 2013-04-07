<?php
/**
 * 荐股
 * */
class RecstocksAction extends AclAction {
    public function _initialize(){
        if(!isset($_SESSION['MEIX'])||empty($_SESSION['MEIX']['iduser'])){
            $_SESSION['MEIX']['JS']['url_login'] = '';
            R('Login/index');
            exit(0);
        }
    }
    /*
	 * 2013-3-18上午10:52:09
	 * Jone
	 * 荐股列表页面显示方法
	**/
    public function index(){
    	$type = $_GET['type'];
        $stock_id = $_GET['sid'];
        $rec_stocks_list = $this->index_c(0,$type,$stock_id,10);
		//右栏热门标签
		//$this->assign('hot_rec_stock_rate',$this->hot_rec_stcok_rate_c());
        //热门标签
		$hot_rec_stcok_rate = $this->hot_rec_stcok_rate_c();
        arsort($hot_rec_stcok_rate);
        $hot_rec_stock_ass = R('Pointview/hot_tag_toshow_c',array($hot_rec_stcok_rate));
        $this->assign('hot_rec_stock_rate',$hot_rec_stock_ass);
        //end热门标签
		$this->assign('rec_stocks_list',$rec_stocks_list);
        $this->assign('list',$rec_stocks_list);
        $this->assign('type',$type);
        $this->assign('stock_id',$stock_id);
		$this->display();
    }
    public function index_a(){
        if($this->isAjax()){
	      $data = $_POST;
            if(!empty($data)){
                $list = $this->index_c($data['start'],$data['type'],$data['stock_id'],$data['num']);
                $wdata['list'] = $list; $wdata['ifmode'] = 'rec_stocks';
                $this->ajaxReturn(W('Rpcontent',$wdata,true),'成功',1);
            }else{
   	            $this->ajaxReturn(0,'失败',0);
            }
	    }else{
	      exit();
	    }
    }
    private function index_c($start=0,$type='',$stock_id='',$num=8){
    	$groups_list = M('Groups_list');
    	$groups_list_map['rec_stocks_id'] = array('neq','');
    	$groups_list_list = $groups_list->where($groups_list_map)->field('rec_stocks_id')->select();
    	foreach($groups_list_list as $key=>$val){
			$rec_stocks_id_arr[] = $val['rec_stocks_id'];
    	}
        $rec_stocks = M('Rec_stocks');
		if($type == 'success'){
			$rec_map['rate'] = '2';
		}elseif($type == 'lose'){
			$rec_map['rate'] = '3';
		}elseif($type == 'ondo'){
			$rec_map['rate'] = '1';
		}
		if($stock_id != ''){
			$rec_map['stocks_id'] = $stock_id;
		}
		$rec_map['idrec_stocks'] = array('in',$rec_stocks_id_arr);
        if(empty($num)){
            $num = 8;
        }
		$rec_stocks_list = $rec_stocks->where($rec_map)->limit($start,$num)->order('mktime desc')->select();
        //echo '<br/><br/><br/><br/>';
        //echo $rec_stocks->getLastSql();
        //dump($rec_stocks_list);
		return $rec_stocks_list;
    }

    /*
	 * 2013-3-19上午02:31:29
	 * Jone
	 * 荐股内页
	**/
    public function inside(){
    	$rid = $_GET['rid'];
    	$map['idrec_stocks'] = $rid;
    	$rec_stocks = M('Rec_stocks');
    	$rec_stocks_list = $rec_stocks->where($map)->select();
        /*
    	$rec_stock_rate_cycle = M('Rec_stocks_cycle');
        $rec_stocks_list['Cycle'] = $rec_stock_rate_cycle->where('idrec_stocks_cycle='.$rec_stocks_list['rec_stocks_cycle_id'])->find();
        $reccycleidmap['rec_stocks_cycle_id'] = $rec_stocks_list['rec_stocks_cycle_id'];
        $reccycleidmap['idrec_stocks'] = array('neq',$rec_stocks_list['idrec_stocks']);
        $rec_stocks_list['Rec_stocks'] = $rec_stocks->where($reccycleidmap)->select();
        unset($reccycleidmap);
        $rec_stocks_list['Stocknow'] = R('Tool/tool_stock_now',array($rec_stocks_list['stocks_number']));
        */
		$this->assign('list',$rec_stocks_list);
		$this->maybe_love_person();
    	$this->maybe_love_groups();
        $this->display();
    }
        /*
	 * 2013-3-22上午08:36:48
	 * Jone
	 * 肯定感兴趣的人
	**/
	public function maybe_love_person (){
		$user = M('User');
		$map['lock'] = 1;
		$user_count = $user->where($map)->count();
		$num = rand(1,$user_count-6);
		$user_list = $user->where($map)->limit($num,6)->field('name,iduser,avatar')->select();
		foreach($user_list as $key=>$val){
			$attention_count = R('User/attention_user_count',array($val['iduser']));
	    	$user_list[$key]['attention'] = $attention_count;
	    	$user_list[$key]['rate'] = '44%';
		}
		$this->assign('user_right',$user_list);
	}
    /*
	 * 2013-3-22上午08:36:48
	 * Jone
	 * 肯定感兴趣的人
	**/
	public function maybe_love_groups (){
		$groups = D('Groups');
		$map['rate'] = array('neq','0');
		$groups_count = $groups->where($map)->count();
		$num = rand(1,$groups_count-2);
		$groups_list = $groups->relation(true)->limit($num,2)->where($map)->select();
		foreach($groups_list as $key=>$val){
			$groups_msg = R('Groups/groups_msg',array($val['idgroups']));
	    	$groups_list[$key]['attention'] = $groups_msg['attention_count'];
	    	$groups_list[$key]['rate'] = '55%';
	    	$groups_list[$key]['join'] = $groups_msg['join_count'];
		}
		$this->assign('groups_right',$groups_list);
	}
    /*
	 * 2013-3-19上午02:31:29
	 * Jone
	 * 右栏热门标签
	**/
	public function hot_rec_stcok_rate_c($instockid=''){
		$rec_stocks = M('Rec_stocks');
		$rec_stocks_map['rate'] = array('gt',0);
        if(!empty($instockid)){ $rec_stocks_map['idrec_stocks'] = array('in',$instockid); }
		$rec_stock_rate_list = $rec_stocks->where($rec_stocks_map)->group('stocks_id')->select();
		foreach($rec_stock_rate_list as $rate_key=>$rate_val){
			$rec_stocks_map['stocks_id'] = $rate_val['stocks_id'];
			$rec_stock_rate_return[$rate_val['stocks_id']] = $rec_stocks->where($rec_stocks_map)->count();
		}
		return $rec_stock_rate_return;
	}
	/*
	 * 2013-3-19上午02:31:29
	 * 荐股添加保存主方法
	**/
    public function rec_add_a(){
        if($this->isAjax()){
            $this->acl_input3();
            if(!empty($_POST['stocks_id'])&&!empty($_POST['price_a'])&&!empty($_POST['price_b'])){
                if($_POST['price_a']==$_POST['price_b']){ $status = 15; }else{ $status = 11; }
                $stocks_cycle = M('Rec_stocks_cycle');
                $stocks_cyclemap['user_id'] = $_SESSION['MEIX']['iduser'];
                $stocks_cyclemap['lock'] = 1;
                $stocks_cyclefind_stocks = $stocks_cycle->where($stocks_cyclemap)->group('stocks_id')->count();//20
                $stocks_cyclemap['stock_id'] = $_POST['stocks_id'];
                $stocks_cyclemap['status'] = array('in','11,15,10,21');
                $stocks_cyclefind = $stocks_cycle->where($stocks_cyclemap)->count();//5
                if(($stocks_cyclefind<5)&&($stocks_cyclefind_stocks<20)){
                    //开环
                    $stock = M('Stock');
                    $stocks_cyclemapstocks['idstock'] = $stocks_cyclemap['stock_id'];
                    $stocks_cyclemapstocks['lock'] = 1;
                    $stocksfind = $stock->where($stocks_cyclemapstocks)->find();
                    if($stocksfind){
                        $stocks_cyclemap['stock_name'] = $stocksfind['showname'];
                        $stocks_cyclemap['stock_number'] = $stocksfind['shownumberb'].$stocksfind['shownumber'];
                    }
                    if(!empty($_POST['first'])){ //先买先卖
                        $stocks_cyclemap['first'] = $_POST['first'];
                    }else{
                        $stocks_cyclemap['first'] = 'buy'; //sell
                    }
                    $stocks_cyclemap['status'] = $status;
                    $stocks_cyclemap['currency_unit'] = $stocksfind['shownumbertype'];
                    $stocks_cyclemap['mktime'] = mktime();
                    $stocks_cycle->add($stocks_cyclemap);
                    $rec_stocks_cycle_id = $stocks_cycle->getLastInsID();
                    //end开环
                    $mainok = $this->rec_add_main_c($_POST['content'],$_POST['stocks_id'],$_POST['price_a'],$_POST['price_b'],$_POST['mktime_b'],$rec_stocks_cycle_id,$status,$_POST['first']);
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
                        $this->ajaxReturn('','',0);
                    }
                }else{
                    $this->ajaxReturn('','每人只能对20只股票开仓，每股票只能保留5个开仓',0);
                }

            }else{
                $this->ajaxReturn('','不能为空',0);
            }
        }
    }

	/*
	 * 2013-3-19上午02:31:29
	 * 荐股主表添加保存主方法
	**/
    private function rec_add_main_c($content='',$stocks_id,$price_a,$price_b,$mktime_b,$rec_stocks_cycle_id,$status,$first=''){
        $rec_stocks = M('Rec_stocks');
        $map['content'] = $content;
        if(!empty($content)){
            $map['content_t'] = R('Tool/msubstr_txt',array($map['content']));
        }
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
        if($status==15){ $map['rate'] = 2; }else{ $map['rate'] = 1; }
        $map['status'] = $status;
        $map['rec_stocks_cycle_id'] = $rec_stocks_cycle_id;
        $ok = $rec_stocks->add($map);
        if($ok){
            return $rec_stocks->getLastInsID();
        }else{
            return false;
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