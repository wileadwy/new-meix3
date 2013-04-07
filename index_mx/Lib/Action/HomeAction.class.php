<?php
/**
 * 首页
 * */
class HomeAction extends AclAction {
    public function _initialize(){
        if(!isset($_SESSION['MEIX'])||empty($_SESSION['MEIX']['iduser'])){
            $_SESSION['MEIX']['JS']['url_login'] = '';
            R('Login/index');
            exit(0);
        }
    }

    /*
   * 2013-3-20上午03:24:35
   * Jone
   * 首页数据显示
  **/
	public function index (){
	    $type = $_GET['type'];
	    $stock_id = $_GET['sid'];
	    $user_listlist = $this->index_c(0,$type,$stock_id,10);
        $this->assign('stock_id',$stock_id);
        //dump($user_listlist);
        $this->assign('list',$user_listlist);
		$this->home_right();//右栏
    	$this->display('Home:index');
    }
    public function index_a(){
        if($this->isAjax()){
	      $data = $_POST;
            if(!empty($data)){
                $list = $this->index_c($data['start'],$data['type'],$data['stock_id'],$data['num']);
                $wdata['list'] = $list; $wdata['ifmode'] = 'mix';
                $this->ajaxReturn(W('Rpcontent',$wdata,true),'成功',1);
            }else{
   	            $this->ajaxReturn(0,'失败',0);
            }
	    }else{
	      exit();
	    }
    }
    private function index_c($start=0,$type='',$stock_id='',$num=8){
        //订阅
        if(!empty($_SESSION['MEIX']['iduser'])){
            $user_info = M('User_info');
            $userinfomap['user_id'] = $_SESSION['MEIX']['iduser'];
            $userinfomap['field'] = 'subscribe_user_id';
            $suserid = $user_info->where($userinfomap)->getField('value');
            if(!$suserid){
                $suserid = '4';
            }
        }
        
        //dump($suserid);
        //end订阅
        $user_list = M('User_list');
        $where = $this->index_c_type($stock_id,$type);
        if($where){ $user_listmap['_complex'] = $where; }
        $user_listmap['user_id'] = array('in',$suserid);
        if($type == 'point_view' || $type == 'point_more' || $type == 'point_empty'){
          $user_listmap['point_view_id'] = array('neq','');
        }elseif($type == 'rec_stock' || $type == 'rec_up' || $type == 'rec_down' || $type == 'rec_success' || $type == 'rec_lose' || $type == 'rec_ondo'){
      		$user_listmap['rec_stocks_id'] = array('neq','');
        }
        if(empty($num)){
            $num = 8;
        }
        $user_listlist = $user_list->where($user_listmap)->order('mktime desc')->limit($start,$num)->select();

        return $user_listlist;
    }
    private function index_c_type($stock_id='',$type=''){//筛选条件
        if(!empty($stock_id)){
			$point_view_tagmap['stock_id'] = $stock_id;
            $point_view_tag = M('Point_view_tag');
            $point_view_tagid = $point_view_tag->where($point_view_tagmap)->field('point_view_id')->select();
            foreach($point_view_tagid as $pvtvo){
                $point_view_id[] = $pvtvo['point_view_id'];
            }
            
            $rec_stocksmap['stocks_id'] = $stock_id;
            $rec_stocks = M('Rec_stocks');
            $rec_stocksid = $rec_stocks->where($rec_stocksmap)->field('idrec_stocks')->select();
            foreach($rec_stocksid as $rsvo){
                $rec_stocks_id[] = $rsvo['idrec_stocks'];
            }
		}
        if(!empty($type)&&(($type == 'point_more')||($type == 'point_empty'))){
            if($type == 'point_more'){
                $point_viewmap['moreorempty'] = 'more';
            }elseif($type == 'point_empty'){
                $point_viewmap['moreorempty'] = 'empty';
            }               
            if(!empty($point_view_id)){
                $point_viewmap['idpoint_view'] = array('in',$point_view_id);
            }
            $point_view = M('Point_view');
            $point_viewid = $point_view->where($point_viewmap)->field('idpoint_view')->select();
            foreach($point_viewid as $pvvo){
                $point_view_id[] = $pvvo['idpoint_view'];
            }
        }
        if(!empty($point_view_id)){
            $where['point_view_id'] = array('in',$point_view_id);
        }
        if(!empty($rec_stocks_id)){
            $where['rec_stocks_id'] = array('in',$rec_stocks_id);
        }
        if(!empty($where)){
            $where['_logic'] = 'or';
            return $where;
        }else{
            return false;
        }
        
    }
    /*
	 * 2013-3-24上午01:45:15
	 * Jone
	 *我的关注
	**/
    public function user(){
    	$user_id = $_SESSION['MEIX']['iduser'];
		$attention_user = M('Attention');
		$user2groups = M('User2groups');
		$user = M('User');
		$groups = D('Groups');
		//$map['table'] = 'user';
		$map['user_id'] = $user_id;
		$attention_user_list = $attention_user->where($map)->select();
		foreach($attention_user_list as $key=>$val){
			if($val['table'] == 'user'){
				$user_map['iduser'] = $val['id'];
				$user_map['lock'] = 1;
				$attention_user_list[$key]['User'] = $user->where($user_map)->field('iduser,name,avatar,accuracy')->find();
				$count_map['table'] = 'user';
				$count_map['id'] = $val['id'];
				$attention_user_list[$key]['User']['attention_count'] = $attention_user->where($count_map)->count();
			}elseif($val['table'] == 'groups'){
				$groups_map['idgroups'] = $val['id'];
				$attention_user_list[$key]['Groups'] = $groups->where($groups_map)->relation(true)->find();
				//关注数
				$attention_map['id'] = $attention_user_list[$key]['Groups']['idgroups'];
				$attention_map['table'] = 'groups';
				$attention_user_list[$key]['Groups']['attention_count'] = $attention_user->where($attention_map)->count();
				//参与数
				$join_map['groups_id'] = $attention_user_list[$key]['Groups']['idgroups'];
				$attention_user_list[$key]['Groups']['join_count'] = $user2groups->where($join_map)->count();

			}
		}
		$this->assign('attention_user_list',$attention_user_list);
		$this->home_right();//右栏
        $this->display('Home:user');
    }
    /*
	 * 2013-3-24上午01:58:16
	 * Jone
	 * 首页右栏
	**/
	public function home_right (){
		//可能感兴趣的人
        $user = M('User');
	    $map['lock'] = 1;
	    $user_count = $user->where($map)->count();
	    $num = rand(1,$user_count-3);
	    $user_list = $user->where($map)->limit($num,3)->field('name,iduser,avatar,accuracy')->select();
	    foreach($user_list as $key=>$val){
	    	$attention_count = R('User/attention_user_count',array($val['iduser']));
	    	$user_list[$key]['attention'] = $attention_count;
	    	$user_list[$key]['rate'] = $val['accuracy'].'%';
	    }
	    $this->assign('love_person',$user_list);
	    //可能感兴趣的圈子
	    $groups = D('Groups');
	    $map['rate'] = array('neq','0');
	    $groups_count = $groups->where($map)->count();
	    $num = rand(1,$groups_count-3);
	    $groups_list = $groups->relation(true)->limit($num,3)->where($map)->select();
	    foreach($groups_list as $key=>$val){
	    	$groups_msg = R('Groups/groups_msg',array($val['idgroups']));
	    	$groups_list[$key]['attention'] = $groups_msg['attention_count'];
	    	$groups_list[$key]['rate'] = '55%';
	    	$groups_list[$key]['join'] = $groups_msg['join_count'];
	    }
	    $this->assign('love_groups',$groups_list);
	    //我的股票
	    //$_SESSION['MEIX']['info']['home_my_stock'] = '10888,10889';
	    $stock_id_str = $_SESSION['MEIX']['info']['home_my_stock'];
		$stock_idarr = explode(',',$stock_id_str);
		$stock = M('Stock');
		$stock_map['idstock'] = array('in',$stock_idarr);
		$stock_list = $stock->where($stock_map)->select();
		foreach($stock_list as $key=>$val){
			$number =  $val['shownumberb'].$val['shownumber'];
			$stock_price = R('Tool/tool_stock_now',array($number));//ToolAction::tool_stock_now_c($val['stocks_id']);
			$stock_list[$key]['begin_price'] = $stock_price['high'];
			$stock_list[$key]['now_price'] = $stock_price['now'];
		}
		$this->assign('mystocks',$stock_list);
		//常去的圈子

		$user2groups = M('User2groups');
		$user2groups_map['user_id'] = $_SESSION['MEIX']['iduser'];
		$user2group_list = $user2groups->where($user2groups_map)->field('groups_id')->select();
		foreach($user2group_list as $key=>$val){
			$groups_idarr[] = $val['groups_id'];
		}

		$usually_groups_map['idgroups'] = array('in',$groups_idarr);
        $limit = 3;
		$usually_groups = $groups->where($usually_groups_map)->relation(true)->limit($limit)->order('mktime desc')->select();
        $usually_groups3 = $limit-count($usually_groups);
        if($usually_groups3){
            $countugm = $groups->where($usually_groups_map)->count();
            $usually_groups_b = $groups->relation(true)->limit(rand(0,$countugm-$usually_groups3),$usually_groups3)->select();
            if(!empty($usually_groups)){
                foreach($usually_groups_b as $ugvo){
                    $usually_groups[] = $ugvo;
                }
            }else{
                $usually_groups = $usually_groups_b;
            }
        }
		foreach($usually_groups as $key=>$val){
			$groups_msg = R('Groups/groups_msg',array($val['idgroups']));
	    	$usually_groups[$key]['attention'] = $groups_msg['attention_count'];
	    	$usually_groups[$key]['rate'] = '--%';
	    	$usually_groups[$key]['join'] = $groups_msg['join_count'];
	    }
		$this->assign('uid',$_SESSION['MEIX']['iduser']);
	    $this->assign('usually_groups',$usually_groups);
        //dump($usually_groups);
	}
    /*
   * 2013-3-22上午06:45:41
   * Jone
   * 可能感兴趣的人
  **/
	public function maybe_love_person (){
	    if($this->isAjax()){
	      $user = M('User');
	      $limit = $_POST['num'];
	      $map['lock'] = 1;
	      $user_count = $user->where($map)->count();
	      if($user_count>=8){
	        $num = rand(1,$user_count-$limit);
	        $user_list = $user->where($map)->limit($num,$limit)->field('name,iduser,avatar,accuracy')->select();
	      }else{
	        $user_list = $user->where($map)->field('name,iduser,avatar,accuracy')->select();
	      }
	      $attention = M('Attention');
	      foreach($user_list as $key=>$val){
	      	$attention_map['table'] = 'user';
	      	$attention_map['id'] = $val['iduser'];
	        $user_list[$key]['attention'] = $attention->where($attention_map)->count();
	        $user_list[$key]['rate'] = $val['accuracy'].'%';
	      }
	       if(!empty($user_list)){
	        $this->ajaxReturn($user_list,'成功',1);
	      }else{
	        $this->ajaxReturn(0,'失败',0);
	      }
	    }else{
	      exit();
	    }
	}
   /*
    * 2013-3-22上午06:45:41
    * Jone
    * 可能感兴趣的人
    **/
	public function maybe_love_groups (){
	    if($this->isAjax()){
	      $limit = $_POST['num'];
	      $groups = D('Groups');
	      $map['rate'] = array('neq','0');
	      $groups_count = $groups->where($map)->count();
	      $num = rand(1,$groups_count-$limit);
	      $groups_list = $groups->relation(true)->limit($num,$limit)->where($map)->select();
	      foreach($groups_list as $key=>$val){
	        $groups_list[$key]['attention'] = R('Groups/groups_attention_count',array($val['idgroups']));
	        $groups_list[$key]['rate'] = '55%';
	        $groups_list[$key]['join'] = R('Groups/groups_in_count',array($val['idgroups']));
	      }
	      if(!empty($groups_list)){
	        $this->ajaxReturn($groups_list,'成功',1);
	      }else{
	        $this->ajaxReturn(0,'失败',0);
	      }
	    }else{
	      exit();
	    }
	}
  /*
   * 2013-3-22上午10:10:58
   * Jone
   * 我的股票
  **/
	public function my_stock (){
    	if($this->isAjax()){
    	   $stocks = $_POST['stocks'];
      		$user_id = $_SESSION['MEIX']['iduser'];
      		$user_info = M('User_info');
      		$map['user_id'] = $user_id;
      		$map['field'] = 'home_my_stock';
      		$user_info_msg = $user_info->where($map)->find();
			if(!empty($user_info_msg)){
				$stock = M('Stock');
                $stockmap['idstock'] = array('in',$user_info_msg['value'].','.$stocks);
                $stocklist = $stock->where($stockmap)->select();
                foreach($stocklist as $stockvo){
                    $arrvalue[] = $stockvo['idstock'];
                }
                //$arrvalue1 = explode(',',$user_info_msg['value']);
                //$arrstocks = explode(',',$stocks);
                //$arrvalue = array_unique(array_merge($arrvalue1,$arrstocks));
                if(!empty($arrvalue)){
                    $okvalue = implode(',',$arrvalue);
                    $edit['value'] = $okvalue;
                    $edit['info'] = ','.$okvalue.',';
                }else{
                    $edit['value'] = '';
                    $edit['info'] = '';
                }
                $ok = $user_info->where($map)->save($edit);
			}else{
				$add['user_id'] = $user_id;
				$add['field'] = 'home_my_stock';
				$add['value'] = $stocks;
                $add['info'] = ','.$stocks.',';
				$ok = $user_info->add($add);
                $okvalue = $add['value'];
			}
			if($ok){
				$_SESSION['MEIX']['info']['home_my_stock'] = $okvalue;
				$this->ajaxReturn($ok,"成功",1);
			}else{
				$this->ajaxReturn('0',"失败",1);
			}
    	}else{
     		exit();
    	}
	}
    public function my_stock_del (){
    	if($this->isAjax()){
      		$stocks = $_POST['stocks'];
            $user_id = $_SESSION['MEIX']['iduser'];
      		$user_info = M('User_info');
      		$map['user_id'] = $user_id;
      		$map['field'] = 'home_my_stock';
      		$user_info_msg = $user_info->where($map)->find();
			if(!empty($user_info_msg)){
				$stock = M('Stock');
                $stockmap['idstock'] = array('in',$user_info_msg['value']);
                $stocklist = $stock->where($stockmap)->select();
                foreach($stocklist as $stockvo){
                    if($stocks!=$stockvo['idstock']){
                        $arrvalue[] = $stockvo['idstock'];
                    }
                }
                //$arrvalue = explode(',',$user_info_msg['value']);
                if(!empty($arrvalue)){
                    /*foreach($arrvalue as $key=>$vo){
                        if($stocks==$vo){
                            unset($arrvalue[$key]);
                        }
                    }*/
                    $okvalue = implode(',',$arrvalue);
                    $edit['value'] = $okvalue;
                    $edit['info'] = ','.$okvalue.',';
                }else{
                    $edit['value'] = '';
                    $edit['info'] = '';
                }
				$ok = $user_info->where($map)->save($edit);
			}else{
                $ok = 1;
			}
			if($ok){
				$_SESSION['MEIX']['info']['home_my_stock'] = $okvalue;
				$this->ajaxReturn($ok,"成功",1);
			}else{
				$this->ajaxReturn('0',"失败",0);
			}
    	}else{
     		exit();
    	}
	}
	/*
	 * 2013-3-23上午02:36:05
	 * Jone
	 * 常去的圈子
	**/
	public function usually_groups (){
    	if($this->isAjax()){
    	   $groups = $_POST['groups'];
      		$user_id = $_SESSION['MEIX']['iduser'];
      		$user_info = M('User_info');
      		if(!empty($_POST['stocks'])){
      			$map['user_id'] = $user_id;
      			$map['field'] = 'usually_groups';
      			$user_info_msg = $user_info->where($map)->find();
				if(!empty($user_info_msg)){
    				$arrvalue1 = explode(',',$user_info_msg['value']);
                    $arrstocks = explode(',',$groups);
                    $arrvalue = array_unique(array_merge($arrvalue1,$arrstocks));
                    if(!empty($arrvalue)){
                        $okvalue = implode(',',$arrvalue);
                        $edit['value'] = $okvalue;
                        $edit['info'] = ','.$okvalue.',';
                    }else{
                        $edit['value'] = '';
                        $edit['info'] = '';
                    }
                    $ok = $user_info->where($map)->save($edit);
    			}else{
    				$add['user_id'] = $user_id;
    				$add['field'] = 'usually_groups';
    				$add['value'] = $groups;
                    $add['info'] = ','.$stocks.',';
    				$ok = $user_info->add($add);
                    $okvalue = $add['value'];
    			}
				if($ok){
					$_SESSION['MEIX']['info']['usually_groups'] = $okvalue;
					$this->ajaxReturn($ok,"成功",1);
				}else{
					$this->ajaxReturn('0',"失败",0);
				}
      		}
    	}else{
     		exit();
    	}
	}
    public function usually_groups_del (){
    	if($this->isAjax()){
    	   $groups = $_POST['groups'];
      		$user_id = $_SESSION['MEIX']['iduser'];
      		$user_info = M('User_info');
      		if(!empty($_POST['stocks'])){
      			$map['user_id'] = $user_id;
      			$map['field'] = 'usually_groups';
      			$user_info_msg = $user_info->where($map)->find();
				if(!empty($user_info_msg)){
    				$arrvalue = explode(',',$user_info_msg['value']);
                    if(!empty($arrvalue)){
                        foreach($arrvalue as $key=>$vo){
                            if($groups==$vo){
                                unset($arrvalue[$key]);
                            }
                        }
                        $okvalue = implode(',',$arrvalue);
                        $edit['value'] = $okvalue;
                        $edit['info'] = ','.$okvalue.',';
                    }else{
                        $edit['value'] = '';
                        $edit['info'] = '';
                    }
    				$ok = $user_info->where($map)->save($edit);
    			}
				if($ok){
					$_SESSION['MEIX']['info']['usually_groups'] = $_POST['groups'];
					$this->ajaxReturn($ok,"成功",1);
				}else{
					$this->ajaxReturn('0',"失败",0);
				}
      		}
    	}else{
     		exit();
    	}
	}
    /**
     * //邀请码
     *
     * */
    public function user_invite(){
    	if($this->isAjax()){
      		if(!empty($_SESSION['MEIX']['iduser'])){
      			$groups_invite = M('Groups_invite');
                $add['user_id'] = $_SESSION['MEIX']['iduser'];
                $code = substr(md5(mktime()*$add['user_id']*13),8,16);
                $add['code'] = $code;
                $add['type'] = 'register_1';
                $add['mktime'] = mktime();
                $groups_inviteadd = $groups_invite->add($add);
				if($groups_inviteadd){
    				$this->ajaxReturn($code,$code,1);
                }else{
                    $this->ajaxReturn('0',"失败",0);
                }

            }else{
                $this->ajaxReturn('0',"Login/index",0);
            }
    	}else{
     		exit();
    	}
	}


}

?>