<?php
/**
 * 用户
 * */
class UserAction extends AclAction {
    public function _initialize(){
        if(!isset($_SESSION['MEIX'])||empty($_SESSION['MEIX']['iduser'])){
            $_SESSION['MEIX']['JS']['url_login'] = '';
            R('Login/index');
            exit(0);
        }
    }
    /*
	 * 2013-4-2上午08:31:05
	 * Jone
	 * 用户列表
	**/
	public function all_user_list_c (){
		$user = M('User');
		$map['lock'] = 1;
		$user_list = $user->where($map)->field('iduser,name,avatar,mktime,information,username')->order('mktime desc')->select();
		$this->assign('list',$user_list);
		$this->display('alluser');
	}
    /*
	 * 2013-3-21上午10:54:34
	 * Jone
	 * 个人页面
	**/
    public function index(){
		$type = $_GET['type'];
        $stock_id = $_GET['sid'];
        if(!empty($_GET['id'])){
            $uid = $_GET['id'];
        }else{
            $this->redirect('Index/index');
        }
		$point_view_list = $this->my_point_view_c(0,$uid,$type,$stock_id,8);
		//$this->assign('point_view',$point_view_list);
        $this->assign('list',$point_view_list);
        //准确率计算
        if(!empty($uid)){
            $this->calculation_c($uid);
        }
        $this->assign('iduser',$_GET['id']);
        $this->assign('stock_id',$stock_id);
        $this->assign('type',$type);
		$this->display();
    }
    /*
	 * 2013-3-29上午03:35:40
	 * Jone
	 * 准确率计算
	**/
	private function calculation_c ($user_id){
		$rearray = array('accuracy'=>0,'accuracy_v'=>0,'accuracy_z'=>0);
        $rec_stocks_cycle = D('Rec_stocks_cycle');
        $rec_stocks = M('Rec_stocks');
		$rec_stocks_cycle_map['user_id'] = $user_id;
		$rec_stocks_cycle_map['lock'] = 1;
		//$rec_stocks_cycle_map['status'] = 25;
		$rec_cycle = $rec_stocks_cycle->where($rec_stocks_cycle_map)->relation('Rec_stocks')->select();
		foreach($rec_cycle as $key=>$val){
			if($val['status']==25){
			    foreach($val['Rec_stocks'] as $vo25){
    			     if($vo25['moreorempty']=='buy'){
    			         $buy25[$key] = $vo25['price_b'];
    			     }elseif($vo25['moreorempty']=='sell'){
    			         $sell25[$key] = $vo25['price_b'];
    			     }
    			}
                if(!empty($buy25[$key])&&!empty($sell25[$key])){
                    $accuracy_v[$key] = 100*($sell25[$key]-$buy25[$key])/$buy25[$key];
                }
			}elseif($val['status']==15){
			    foreach($val['Rec_stocks'] as $vo15){
    			     $rtool_stock_now_c = R('Tool/tool_stock_now_c',array($vo15['stocks_id']));
                     if($vo15['moreorempty']=='buy'){
    			         $buy15[$key] = $vo15['price_b'];
                         $sell15[$key] = $rtool_stock_now_c[0];
    			     }elseif($vo15['moreorempty']=='sell'){
    			         $buy15[$key] = $rtool_stock_now_c[0];
                         $sell15[$key] = $vo15['price_b'];
    			     }
    			}
                if(!empty($buy15[$key])&&!empty($sell15[$key])){
                    $accuracy_z[$key] = 100*($sell15[$key]-$buy15[$key])/$buy15[$key];
                }
			}else{
                $shibai++;
			}
		}
        $rec_cyclecount = count($rec_cycle);
		$edit['accuracy'] = number_format((($rec_cyclecount-$shibai)/$rec_cyclecount)*100,2);
        $edit['accuracy_v'] = number_format(array_sum($accuracy_v)/count($accuracy_v),2);
        $edit['accuracy_z'] = number_format(array_sum($accuracy_z)/count($accuracy_z),2);
        $user = M('User');
		$user_map['iduser'] = $user_id;
        if(!empty($user_map['iduser'])){
            $user->where($user_map)->save($edit);
        }
	}
    /*
    private function calculation ($user_id){
		$rec_stocks_cycle = M('Rec_stocks_cycle');
		$rec_stocks_cycle_map['user_id'] = $user_id;
		$rec_stocks_cycle_map['lock'] = 1;
		$rec_stocks_cycle_map['status'] = 25;
		$rec_stocks_cycle_list = $rec_stocks_cycle->where($rec_stocks_cycle_map)->select();
		foreach($rec_stocks_cycle_list as $key=>$val){
			$rec_stocks_idarr[] = $val['idrec_stocks_cycle'];
		}
		$rec_stocks = M('Rec_stocks');
		$rec_stocks_map['rec_stocks_cycle_id'] = array('in',$rec_stocks_idarr);
		$rec_stocks_map['moreorempty'] = 'sell';
		$rec_stocks_list = $rec_stocks->where($rec_stocks_map)->select();
		foreach($rec_stocks_list as $skey=>$sval){
			$map['rec_stocks_cycle_id'] = $sval['rec_stocks_cycle_id'];
			$map['moreorempty'] = 'buy';
			$rec_stocks_bs[$sval['rec_stocks_cycle_id']]['buy'] = $rec_stocks->where($map)->find();
			$rec_stocks_bs[$sval['rec_stocks_cycle_id']]['sell'] = $rec_stocks_list[$key];
		}
		foreach($rec_stocks_bs as $bkey=>$bval){
			if($bval['sell']['price_b']-$bval['buy']['price_b']>=0){
				$calculation[] = 100;
			}else{
				$calculation[] = 0;
			}
			$price = $price + (($bval['sell']['price_b']-$bval['buy']['price_b'])/$bval['buy']['price_b']);
		}
		foreach($calculation as $ckey=>$cval){
			$num = $num+$cval;
		}
		$cal = $num/count($calculation);
		$user = M('User');
		$edit['accuracy'] =  floor(($cal)*10000)/10000*100;
		$edit['accuracy_v'] =  floor($price/count($rec_stocks_bs))*100;
		$user_map['iduser'] = $user_id;
		$user->where($user_map)->save($edit);

	}
    */
    /*
	 * 2013-3-28上午02:12:34
	 * Jone
	 * 我的观点处理
	**/
    private function my_point_view_c($start=0,$user_id='',$type='',$stock_id='',$num=8){
        $point_view = M('Point_view');
		$map['lock'] = '1';
		$map['user_id'] = $user_id;
		if($type == 'more'){
			$map['moreorempty'] = 'more';
		}elseif($_GET['type'] == 'empty'){
			$map['moreorempty'] = 'empty';
		}
		if(!empty($stock_id)){
			$point_view_tag = M('Point_view_tag');
			$point_view_tag_map['stock_id'] = $stock_id;
			$point_view_tag_list = $point_view_tag->where($point_view_tag_map)->select();
			foreach($point_view_tag_list as $tag_key=>$tag_val){
				$point_view_id_arr[] = $tag_val['point_view_id'];
			}
			$map['idpoint_view'] = array('in',$point_view_id_arr);
		}
        if(empty($num)){
            $num = 8;
        }
		$point_view_list = $point_view->where($map)->order('mktime desc')->limit($start,$num)->select();
        return $point_view_list;
    }
    /*
	 * 2013-3-28上午02:34:34
	 * Jone
	 * 我的观点更多
	**/
	public function my_point_view_a (){
        if($this->isAjax()){
	      $data = $_POST;
            if(!empty($data)){
                $list = $this->my_point_view_c($data['start'],$data['user_id'],$data['type'],$data['stock_id'],$data['num']);
                $wdata['list'] = $list; $wdata['ifmode'] = 'point_view';
                $this->ajaxReturn(W('Rpcontent',$wdata,true),'成功',1);
            }else{
   	            $this->ajaxReturn(0,'失败',0);
            }
	    }else{
	      exit();
	    }
    }
    /*
	 * 2013-3-21上午10:54:34
	 * Jone
	 * 个人页面-荐股
	**/
    public function recstock(){
    	$type = $_GET['type'];
        $stock_id = $_GET['sid'];
        $rec_stocks_list = $this->my_rec_stock_c(0,$_GET['id'],$type,$stock_id,8);
        $this->assign('list',$rec_stocks_list);

		$this->assign('iduser',$_GET['id']);
		$this->assign('stock_id',$stock_id);
		$this->assign('type',$type);
		$this->display();
    }
    /*
	 * 2013-3-28上午02:24:18
	 * Jone
	 * 我的荐股处理
	**/
	private function my_rec_stock_c($start=0,$user_id='',$type='',$stock_id='',$num=8){
        $rec_stocks = M('Rec_stocks');
        $rec_map['user_id'] = $user_id;
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
        if(empty($num)){
            $num = 8;
        }
		$rec_stocks_list = $rec_stocks->where($rec_map)->limit($start,$num)->order('mktime desc')->select();
        //dump($rec_stocks_list);
		return $rec_stocks_list;
    }
    /*
	 * 2013-3-28上午02:46:01
	 * Jone
	 * 我的荐股更多
	**/
	public function my_rec_stocks_a(){
        if($this->isAjax()){
	      $data = $_POST;
            if(!empty($data)){
                $list = $this->my_rec_stock_c($data['start'],$data['user_id'],$data['type'],$data['stock_id'],$data['num']);
                $wdata['list'] = $list; $wdata['ifmode'] = 'rec_stocks';
                $this->ajaxReturn(W('Rpcontent',$wdata,true),'成功',1);
            }else{
   	            $this->ajaxReturn(0,'失败',0);
            }
	    }else{
	      exit();
	    }
    }
    /*
	 * 2013-3-21上午10:54:34
	 * Jone
	 * 个人页面
	**/
    public function groups(){
		$this->assign('iduser',$_GET['id']);
		$this->assign('stock_id',$_GET['sid']);
		$this->display();
    }
    /*
	 * 2013-3-23上午06:19:42
	 * Jone
	 * 我的收藏
	**/
	public function favorite (){
		$type = $_GET['type'];
	    $stock_id = $_GET['sid'];
	    $favorite_list = $this->my_favorite_c(0,$_GET['id'],$type,$stock_id,8);
        $this->assign('stock_id',$stock_id);
        $this->assign('list',$favorite_list);

		$this->assign('iduser',$_GET['id']);
		$this->display();
    }
    /*
	 * 2013-3-28上午02:59:54
	 * Jone
	 * 我的收藏处理
	**/
	private function my_favorite_c($start=0,$user_id = '',$type='',$stock_id='',$num=8){
        $favorite = M('Favorite');
		$map['user_id'] = $user_id;
		$map['lock'] = 1;
        if($type == 'point_view' || $type == 'point_more' || $type == 'point_empty'){
          $map['table'] = 'point_view';
        }elseif($type == 'rec_stock' || $type == 'rec_up' || $type == 'rec_down' || $type == 'rec_success' || $type == 'rec_lose' || $type == 'rec_ondo'){
      		$map['table'] = 'rec_stocks';
        }
        if(empty($num)){
            $num = 8;
        }
        $favorite_list = $favorite->where($map)->order('mktime desc')->limit($start,$num)->select();
        return $favorite_list;
    }
    /*
	 * 2013-3-28上午03:02:27
	 * Jone
	 * 我的收藏更多
	**/
	public function my_favorite_a(){
        if($this->isAjax()){
	      $data = $_POST;
            if(!empty($data)){
                $list = $this->my_favorite_c($data['start'],$data['user_id'],$data['type'],$data['stock_id'],$data['num']);
                $wdata['list'] = $list; $wdata['ifmode'] = 'fmix';
                $this->ajaxReturn(W('Rpcontent',$wdata,true),'成功',1);
            }else{
   	            $this->ajaxReturn(0,'失败',0);
            }
	    }else{
	      exit();
	    }
    }
    /*
	 * 2013-3-22上午02:04:29
	 * Jone
	 * 根据id数组找圈子
	**/
	public function get_groups_byidarr ($groups_idarr){
		if(!empty($groups_idarr)){
	    	$groups = D('Groups');//圈子
	    	$groups_list = M('Groups_list');//圈子下的内容
	    	$groups_user = D('user2groups');//圈子内部人员
			$stock = M('Stock');//标签
			$groups_map['idgroups'] = array('in',$groups_idarr);
			$groups_arr = $groups->where($groups_map)->select();
			foreach($groups_arr as $key=>$val){
	    		//观点数及标签
				$groups_list_point_view_map['groups_id'] = $val['idgroups'];
				$groups_list_point_view_map['point_view_id'] = array('NEQ','');
				$groups_arr[$key]['point_view_count'] = $groups_list->where($groups_list_point_view_map)->field('point_view_id')->count();//观点数
				$groups_point_view_tag = R('Groups/get_group_pointview_tag_c',array($val['idgroups']));//圈子中观点下的标签id

				//荐股数
				$groups_list_rec_stocks_map['groups_id'] = $val['idgroups'];
				$groups_list_rec_stocks_map['rec_stocks_id'] = array('NEQ','');
				$groups_list_rec_stocks_list = $groups_list->where($groups_list_rec_stocks_map)->field('rec_stocks_id')->select();
				$groups_arr[$key]['rec_stocks_count'] = $groups_list->where($groups_list_rec_stocks_map)->field('rec_stocks_id')->count();//荐股数

				$res_idarr = R('Groups/get_group_recstock_tag_c',array($val['idgroups']));//圈子中荐股下的标签id
				//标签
				$groups_tag_list = array_merge($groups_point_view_tag,$res_idarr);
				$tag_idarr = array();
				foreach($groups_tag_list as $idkey=>$idval){
					$tag_idarr[] = $idval['stock_id'];
				}
				$stock_map['idstock'] = array('in',$tag_idarr);
				$stock_list = $stock->where($stock_map)->limit(5)->select();
				$groups_arr[$key]['Stock'] = $stock_list;
				//成员
				$group_user_map['idgroups'] = $val['idgroups'];
				$group_user_list = $groups->where($group_user_map)->relation(true)->select();
				$groups_arr[$key]['User2groups'] = $group_user_list;
	    	}
	    	return $groups_arr;
		}
	}
    /**
     *提示列表页面
     *
     * */
     public function notice(){
        if(!empty($_SESSION['MEIX']['iduser'])){
            $notice = M('Notice');
            $map['user_id'] = $_SESSION['MEIX']['iduser'];
            $map['lock'] = 1;
            $list = $notice->where($map)->order('mktime desc')->select();
            $groups = D('Groups');
            foreach($list as $key=>$vo){
                if(!empty($vo['a_domain'])){
                    $groupsmap['idgroups'] = $vo['a_domain'];
                    $list[$key]['Groups'] = $groups->where($groupsmap)->relation(true)->find();
                }

            }
            $this->assign('list',$list);
            //dump($list);
            $this->display();
        }
     }
	/*
	 * 2013-3-24上午03:49:26
	 * Jone
	 * 人的被关注数
	**/
	public function attention_user_count ($uid){
		$attention = M('Attention');
		$attention_map['table'] = 'user';
		$attention_map['id'] =$uid;
		$count = $attention->where($attention_map)->count();
		return $count;
	}
     /**
     * 提示已读
     *
     * */
     public function notice_ed_a(){
        if($this->isAjax()){
            if(!empty($_POST['notice_id'])){
                $notice = M('Notice');
                $map['idnotice'] = $_POST['notice_id'];
                $map['user_id'] = $_SESSION['MEIX']['iduser'];
                $data['ed'] = 0;
                $ok = $notice->where($map)->save($data);
                if($ok){
                    $this->ajaxReturn('','已读',1);
                }else{
                    $this->ajaxReturn('','',0);
                }
            }else{
                $this->ajaxReturn('','',0);
            }
        }
     }

    /**
     *私信列表页面
     * */
     public function letter(){
        if(!empty($_SESSION['MEIX']['iduser'])){
            $messageletter = M('Message_letter');

            if(empty($_GET['uid'])||($_GET['uid']==$_SESSION['MEIX']['iduser'])){
                $map_f['user_id'] = $_SESSION['MEIX']['iduser'];
                $map_f['lock'] = 1;
                $list_fuser = $messageletter->where($map_f)->group('fuser_id')->select();
                foreach($list_fuser as $vo){
                    $arrid[] = $vo['fuser_id'];
                }
                $map_u['fuser_id'] = $_SESSION['MEIX']['iduser'];
                $map_u['lock'] = 1;
                $list_user = $messageletter->where($map_u)->group('user_id')->select();
                foreach($list_user as $vo){
                    $arrid[] = $vo['user_id'];
                }
                $user = M('User');
                $usermap['iduser'] = array('in',$arrid);
                $userlist = $user->where($usermap)->field('iduser,name,avatar')->select();
                foreach($userlist as $key=>$vo){
                    $map1['fuser_id'] = $vo['iduser'];
                    $map1['user_id'] = $_SESSION['MEIX']['iduser'];
                    $list1 = $messageletter->where($map1)->order('mktime desc')->find();
                    $list[$key]['count'] += $messageletter->where($map1)->count();
                    $map2['user_id'] = $vo['iduser'];
                    $map2['fuser_id'] = $_SESSION['MEIX']['iduser'];
                    $list2 = $messageletter->where($map2)->order('mktime desc')->find();
                    $list[$key]['count'] += $messageletter->where($map2)->count();
                    if(!empty($list1) && !empty($list2)){
    					if($list1['mktime']>$list2['mktime']){
    					   $letter = $list1;
    					}else{
    					   $letter = $list2;
    					}
                    }elseif(empty($list1)){
                    	$letter = $list2;
                    }elseif(empty($list2)){
                    	$letter = $list1;
                    }

                    $list[$key]['letter'] = $letter;

                    $list[$key]['user'] = $vo;
                }
                $this->assign('list',$list);
                $this->display('User:letter');
            }else{
                $map1['fuser_id'] = $_GET['uid'];
                $map1['user_id'] = $_SESSION['MEIX']['iduser'];
                $list1 = $messageletter->where($map1)->order('mktime desc')->select();
                $map2['user_id'] = $_GET['uid'];
                $map2['fuser_id'] = $_SESSION['MEIX']['iduser'];
                $list2 = $messageletter->where($map2)->order('mktime desc')->select();
                if(!empty($list1) && !empty($list2)){
					$list = array_merge($list1,$list2);
                }elseif(empty($list1)){
                	$list = $list2;
                }elseif(empty($list2)){
                	$list = $list1;
                }

                foreach($list as $key=>$val){
					  $accuracy[$key] = $val['mktime'];
					  $litter_idarr[] = $val['idmessage_letter'];
                }
                //和谁对话
                $user = M('User');
                $user_map['iduser'] = $_GET['uid'];
                $user_list = $user->where($user_map)->field('iduser,name')->find();
                $this->assign('user_list',$user_list);
                //标记已读
                $edit['ed'] = 0;
                $map['fuser_id'] = array('neq',$_SESSION['MEIX']['iduser']);
                $map['idmessage_letter'] = array('in',$litter_idarr);
                $messageletter->where($map)->save($edit);
                array_multisort($accuracy, SORT_DESC,$list);
                $this->assign('uid',$_GET['uid']);
                $this->assign('list',$list);
                $this->display('User:letterin');
            }
        }
     }
     /**
     * 私信已读
     * */
     public function letter_ed_a(){
        if($this->isAjax()){
            if(!empty($_POST['letter_id'])){
                $notice = M('Message_letter');
                $map['idmessage_letter'] = $_POST['letter_id'];
                $map['user_id'] = $_SESSION['MEIX']['iduser'];
                $data['ed'] = 0;
                $ok = $notice->where($map)->save($data);
                if($ok){
                    $this->ajaxReturn('','已读',1);
                }else{
                    $this->ajaxReturn('','',0);
                }
            }else{
                $this->ajaxReturn('','',0);
            }
        }
     }
     /**
      * 发私信Ajax
      * */

     public function message_letter_a(){
        if($this->isAjax()){
            $map['user_id'] = $_POST['user_id'];
            $map['content'] = $_POST['content'];
            $map['fuser_id'] = $_SESSION['MEIX']['iduser'];
            $map['fuser_name'] = $_SESSION['MEIX']['name'];
            $map['fuser_avatar'] = $_SESSION['MEIX']['avatar'];
            $map['ed'] = 1;
            $map['mktime'] = mktime();
            $map['lock'] = 1;
            if(!empty($map['user_id'])){
                if($map['user_id']!=$map['fuser_id']){
                    $messageletter = M('Message_letter');
                    $ok = $messageletter->add($map);
                }

                if($ok){
                    $this->ajaxReturn('','',1);
                }else{
                    $this->ajaxReturn('','',0);
                }
            }else{
                $this->ajaxReturn('','',0);
            }
        }
     }
     /**
      * 订阅人
      * */
     public function subscribe_user_a(){
        if($this->isAjax()&&!empty($_POST['user_id'])&&!empty($_SESSION['MEIX']['iduser'])){
            $num = 1;//每次几分
            $nummeix = 2;//每次几分
            $user_info = M('User_info');
            $smap['user_id'] = $_POST['user_id'];//被订人
            $smap['field'] = 'money_subscriber_count';
            if(!empty($_SESSION['MEIX']['iduser'])){
                $ok = true;
            }else{
                $messageretrun = '请登录';//
                $ok = false;
            }
            $mapuserid['user_id'] = $_SESSION['MEIX']['iduser'];
            $mapuserid['field'] = 'subscribe_user_id';
            $user_infouserid = $user_info->where($mapuserid)->find();
            $user_infouseridarray = explode(',',$user_infouserid['value']);
            foreach($user_infouseridarray as $useridvo){
                if($useridvo==$smap['user_id']){
                    $messageretrun = '已订阅过';//已订阅过
                    $ok = false;
                }
            }
            if($ok){
                $mapuserinfo['user_id'] = $_SESSION['MEIX']['iduser'];
                $mapuserinfo['field'] = 'money_subscriber_count';
                $user_infofind = $user_info->where($mapuserinfo)->find();
                if($user_infofind['value']>=$num){
                    if(!empty($user_infouserid['value'])){
                        $data['value'] = $user_infouserid['value'].','.$smap['user_id'];
                        $data['info'] = $user_infouserid['info'].$smap['user_id'].',';
                    }else{
                        $data['value'] = $smap['user_id'];
                    }
                    $saveok = $user_info->where($mapuserid)->save($data);
                    if($saveok){
                        $user_info->where($smap)->setInc('value',$num); // 用户癿积分加n
                        $user_info->where($mapuserinfo)->setDec('value',$num+$nummeix); // 用户癿积分减n
                        $this->ajaxReturn('','已订阅',1);
                    }else{
                        $this->ajaxReturn('','请重试',0);
                    }
                }else{
                    $this->ajaxReturn('','你的订阅器不够了',0);
                }
            }else{
                $this->ajaxReturn('',$messageretrun,0);
            }
        }
     }



}

?>