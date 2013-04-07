<?php
/**
 * Ȧ��
 * */
class GroupsAction extends AclAction {
    public function _initialize(){
        if(!isset($_SESSION['MEIX'])||empty($_SESSION['MEIX']['iduser'])){
            $_SESSION['MEIX']['JS']['url_login'] = '';
            R('Login/index');
            exit(0);
        }
    }
    /*
	 * 2013-3-19上午03:59:56
	 * Jone
	 * 圈子列表显示方法
	**/
    public function index(){
		$this->display();
    }
    /*
	 * 2013-3-19上午03:59:56
	 * Jone
	 * 更多圈子W方法
	**/
    public function more_groups(){
    	if($this->isAjax()){
			$data['table'] = $_POST['table'];
			$data['stock_id'] = $_POST['stock_id'];
			$data['id'] = $_POST['id'];
			$data['page'] = $_POST['page'];
			$countent = W('Groups',$data,true);
			if($countent){
				$this->ajaxReturn($countent,'成功',1);
			}else{
				$this->ajaxReturn('','失败',0);
			}
    	}else{
    		exit();
    	}
    }
    /*
	 * 2013-3-24上午06:25:45
	 * Jone
	 * 获取圈子的关注数
	**/
	public function groups_attention_count ($gid){
		$attention = M('Attention');
		$attention_map['id'] = $gid;
		$attention_map['table'] = 'groups';
		$attention_count = $attention->where($attention_map)->count();
		return $attention_count;
	}
		/*
	 * 2013-3-28上午06:18:31
	 * Jone
	 * 圈子参与人
	**/
	public function groups_in_count ($gid){
		$user2groups = M('User2groups');
		$user2groups_map['groups_id'] = $gid;
		$user2groups_count = $user2groups->where($user2groups_map)->count();
		return $user2groups_count;
	}
    /*
	 * 2013-3-19上午07:40:08
	 * Jone
	 * 圈子下观点所涉及的标签
	**/
	public function get_group_pointview_tag_c ($groups_id){
    	$groups_list = M('Groups_list');//圈子下的内容
		$point_view_tag = M('Point_view_tag');//观点标签
		$groups_list_point_view_map['groups_id'] = $groups_id;
		$groups_list_point_view_map['point_view_id'] = array('NEQ','');
		$groups_list_point_view_list = $groups_list->where($groups_list_point_view_map)->field('point_view_id')->select();//圈子内的全部观点$groups_list_point_view_idarr = array();
		foreach($groups_list_point_view_list as $pkey=>$pval){
			$groups_list_point_view_idarr[] = $pval['point_view_id'];//圈子内的全部观点的id数组
		}
		$point_view_tag_map['point_view_id'] = array('in',$groups_list_point_view_idarr);
		$groups_point_view_tag = $point_view_tag->where($point_view_tag_map)->field("stock_id")->select();//圈子中观点下的标签id
		return $groups_point_view_tag;
	}
	/*
	 * 2013-3-19上午07:48:26
	 * Jone
	 * 圈子下荐股所涉及的标签
	**/
	public function get_group_recstock_tag_c ($groups_id){
		$rec_stock = M('Rec_stocks');//荐股
    	$groups_list = M('Groups_list');//圈子下的内容
		$groups_list_rec_stocks_map['groups_id'] = $groups_id;
		$groups_list_rec_stocks_map['rec_stocks_id'] = array('NEQ','');
		$groups_list_rec_stocks_list = $groups_list->where($groups_list_rec_stocks_map)->field('rec_stocks_id')->select();
		$groups_list_rec_stock_idarr = array();
		foreach($groups_list_rec_stocks_list as $reskey=>$resval){
			$groups_list_rec_stock_idarr[] = $resval['rec_stocks_id'];
		}
		$rec_stock_map['idrec_stocks'] = array('in',$groups_list_rec_stock_idarr);
		$rec_stock_list = $rec_stock->where($rec_stock_map)->field('stocks_id')->select();
		$res_idarr = array();
		foreach($rec_stock_list as $residkey=>$residval){
			$res_idarr[]['stock_id'] = $residval['stocks_id'];
		}
		return $res_idarr;

	}
    /*
    public function groups_accuracy($gid){///圈子成功率
        $groups_list = D('Groups_list');
        $map['groups_id'] = $gid;
        $map['rec_stocks_id'] = array('neq','');
        $groups_listlist = $groups_list->where($map)->relation('Rec_stocks')->select();
        foreach($groups_listlist as $glvo){
            if($glvo['Rec_stocks']['status']==25){
                $glistarr[] = $glvo['Rec_stocks']['rec_stocks_cycle_id'];
            }
        }
        $rec_stocks = M('Rec_stocks');
		$rec_stocks_map['rec_stocks_cycle_id'] = array('in',$glistarr);
		$rec_stocks_map['moreorempty'] = 'sell';
		$rec_stocks_list = $rec_stocks->where($rec_stocks_map)->select();
		foreach($rec_stocks_list as $skey=>$sval){
			$mapbuy['rec_stocks_cycle_id'] = $sval['rec_stocks_cycle_id'];
			$mapbuy['moreorempty'] = 'buy';
			$rec_stocks_bs[$sval['rec_stocks_cycle_id']]['buy'] = $rec_stocks->where($mapbuy)->find();
			$rec_stocks_bs[$sval['rec_stocks_cycle_id']]['sell'] = $rec_stocks_list[$key];
            $ca[$skey] = $rec_stocks_bs[$sval['rec_stocks_cycle_id']]['sell']['price_b']-$rec_stocks_bs[$sval['rec_stocks_cycle_id']]['buy']['price_b'];
            $num = 0;
            if($ca[$skey]>0){
                $num+=100;
            }
            $price = $price + $ca[$skey];
		}
        //coco综合排序
        $rec_pvmap['rec_stocks_id|point_view_id'] = array('neq','');
        $rec_pvmap['groups_id'] = $gid;
        $edit['coco'] = $groups_list->where($rec_pvmap)->count();
        //
        $cal = $num/count($rec_stocks_list);
		$groups = M('Groups');
		$edit['accuracy'] =  floor(($cal)*10000)/10000*100;
		$edit['accuracy_v'] =  $price;
		$groups_map['idgroups'] = $gid;
		$groups->where($groups_map)->save($edit);

    }
    */
    public function groups_accuracy($gid){///圈子成功率
        $groups_list = D('Groups_list');
        $map['groups_id'] = $gid;
        $map['rec_stocks_id'] = array('neq','');
        $groups_listlist = $groups_list->where($map)->relation('Rec_stocks')->select();
        foreach($groups_listlist as $glvo){
            if($glvo['Rec_stocks']['status']==25){
                $glistarr[] = $glvo['Rec_stocks']['rec_stocks_cycle_id'];
            }elseif(($glvo['Rec_stocks']['status']==20)||($glvo['Rec_stocks']['status']==10)){
                $shibai++;
			}
        }
        $rec_stocks_cycle = D('Rec_stocks_cycle');
        $rec_stocks = M('Rec_stocks');
        $rec_stocks_cycle_map['idrec_stocks_cycle'] = array('in',$glistarr);
		$rec_stocks_cycle_map['lock'] = 1;
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
			}
		}
        $rec_cyclecount = count($groups_listlist);
		$edit['accuracy'] = number_format((($rec_cyclecount-$shibai)/$rec_cyclecount)*100,2);
        $edit['accuracy_v'] = number_format(array_sum($accuracy_v)/count($accuracy_v),2);
		$groups = M('Groups');
		$groups_map['idgroups'] = $gid;
        if(!empty($groups_map['idgroups'])){
		  $groups->where($groups_map)->save($edit);
        }

    }
    public function discuess(){//圈子内页第一页显示
        $gid = $_GET['gid'];
        $type = $_GET['type'];
        if(!empty($gid)){
            $this->groups_accuracy($gid);///圈子成功率
        	//圈子基本信息
	        $groupsfind = $this->groups_msg($gid);
	        $this->assign('groupsfind',$groupsfind);
            $this->assign('groups_id',$gid);
            $user2groups = M('User2groups');
            $u2g['user_id'] = $_SESSION['MEIX']['iduser'];
            $u2g['groups_id'] = $gid;
            $user2groupshave = $user2groups->where($u2g)->find();
            if(empty($_SESSION['MEIX']['iduser'])||(!$user2groupshave)){
//                $this->assign("jumpUrl",__APP__.'/Groups/pointview/gid/'.$gid);
//                $this->assign("waitSecond",0);
//                $this->success("您不在圈子内不能查看讨论");
				$this->display('Groups:error');
                exit();
            }
            //活跃用户
            $this->active_person($gid);
            $list = $this->discuess_c($gid,0,$type,'',10);
            $this->assign('list',$list);
            //圈子内页热门标签
            $hot = $this->hot_discuess_tag(array($gid));
            $this->assign('hot',$hot);
            //dump($list);

            $this->display('Groups:discuess');
        }else{
            $this->redirect('Groups/index');
        }
    }
    public function discuess_a(){
        if($this->isAjax()){
	      $data = $_POST;
            if(!empty($data)){
                $list = $this->discuess_c($data['id'],$data['start'],$data['type'],$data['stock_id'],$data['num']);
                $wdata['list'] = $list; $wdata['ifmode'] = 'mix'; $wdata['rehtml'] = 'red';
                $this->ajaxReturn(W('Rpcontent',$wdata,true),'成功',1);
            }else{
   	            $this->ajaxReturn(0,'失败',0);
            }
	    }else{
	      exit();
	    }
    }
    private function discuess_c($id='',$start=0,$type='',$stock_id='',$num=8){
        $groups_list = M('Groups_list');
        $map['groups_id'] = $id;
        if(empty($num)){
            $num = 8;
        }
        if(!empty($type)){
            if($type=='discuss'){
                $map['groups_discuss_id'] = array('neq','');
            }elseif($type=='rec_stocks'){
                $map['rec_stocks_id'] = array('neq','');
            }elseif($type=='point_view'){
                $map['point_view_id'] = array('neq','');
            }
        }
        $groups_listlist = $groups_list->where($map)->order('mktime desc')->limit($start,$num)->select();
        if($groups_listlist){
            return array_reverse($groups_listlist);
        }else{
            return false;
        }
    }
	/*
	 * 2013-3-21上午03:22:13
	 * Jone
	 * 圈子内页观点页面显示方法
	**/
    public function pointview(){
    	$groups_id = $_GET['gid'];
    	$type = $_GET['type'];
    	$stock_id = $_GET['sid'];
    	if(!empty($groups_id)){
    		//圈子基本信息
	        $groupsfind = $this->groups_msg($groups_id);
	        $this->assign('groupsfind',$groupsfind);

            $point_view_list = $this->pointview_c($groups_id,0,$type,$stock_id,10);

			$this->assign('point_view',$point_view_list);
            $this->assign('list',$point_view_list);
			$this->assign('groups_id',$groups_id);
			$this->assign('stock_id',$stock_id);
			//活跃用户
            $this->active_person($groups_id);
            //圈子内页热门标签
            $hot = $this->hot_discuess_tag(array($groups_id));
            $this->assign('hot',$hot);
            $this->assign('type',$type);
			$this->display();
    	}else{
    		//404
    	}
    }
    public function pointview_a(){
        if($this->isAjax()){
	      $data = $_POST;
            if(!empty($data)){
                $list = $this->discuess_c($data['id'],$data['start'],$data['type'],$data['stock_id'],$data['num']);
                $wdata['list'] = $list; $wdata['ifmode'] = 'point_view';
                $this->ajaxReturn(W('Rpcontent',$wdata,true),'成功',1);
            }else{
   	            $this->ajaxReturn(0,'失败',0);
            }
	    }else{
	      exit();
	    }
    }
    private function pointview_c($groups_id='',$start=1,$type='',$stock_id='',$num=8){
        $groups_list = M('Groups_list');
    		$groups_list_map['point_view_id'] = array('neq','');
    		$groups_list_map['groups_id'] = $groups_id;
    		$groups_list_list = $groups_list->where($groups_list_map)->select();
    		foreach($groups_list_list as $val){
				$point_view_idarr1[] = $val['point_view_id'];
    		}
    		$point_view = M('Point_view');
    		$point_view_map['lock'] = 1;
    		if($type == 'more'){
				$point_view_map['moreorempty'] = 'more';
    		}elseif($type == 'empty'){
				$point_view_map['moreorempty'] = 'empty';
    		}
    		$point_view_tag = M('Point_view_tag');
    		if(!empty($stock_id)){
				$point_view_tag_map['stock_id'] = $stock_id;
				$point_view_tag_list = $point_view_tag->where($point_view_tag_map)->select();
				foreach($point_view_tag_list as $tag_key=>$tag_val){
					$point_view_id_arr2[] = $tag_val['point_view_id'];
				}
			}
			if(!empty($point_view_id_arr2)){
				$point_view_id_arr = array_intersect($point_view_idarr1,$point_view_id_arr2);
			}else{
				$point_view_id_arr = $point_view_idarr1;
			}
			$point_view_map['idpoint_view'] = array('in',$point_view_id_arr);
    		$point_view_list = $point_view->where($point_view_map)->limit($start,$num)->order('mktime desc')->select();
        if($point_view_list){
            return $point_view_list;
        }else{
            return false;
        }
    }
    /*
	 * 2013-3-21上午03:22:13
	 * Jone
	 * 圈子内页观点页面显示方法
	**/
    public function recstocks(){
    	$groups_id = $_GET['gid'];
    	$type = $_GET['type'];
    	$stock_id = $_GET['sid'];
    	if(!empty($groups_id)){
    		//圈子基本信息
	        $groupsfind = $this->groups_msg($groups_id);
	        $this->assign('groupsfind',$groupsfind);
    		$rec_stocks_list = $this->recstocks_c($groups_id,0,$type,$stock_id,10);
            //dump($rec_stocks_list);
			//R('Recstocks/rec_stock_t2t',$rec_stocks_list);//检查成功结算
			$this->assign('list',$rec_stocks_list);
			$this->assign('groups_id',$groups_id);
			$this->assign('stock_id',$stock_id);
			//活跃用户
            $this->active_person($groups_id);
            //圈子内页热门标签
            $hot = $this->hot_discuess_tag(array($groups_id));
            $this->assign('hot',$hot);
            $this->assign('type',$type);
			$this->display();
    	}else{
    		//404
    	}
    }
    public function recstocks_a(){
        if($this->isAjax()){
	      $data = $_POST;
            if(!empty($data)){
                $list = $this->recstocks_c($data['start'],$data['type'],$data['stock_id'],$data['num']);
                $wdata['list'] = $list; $wdata['ifmode'] = 'rec_stocks';
                $this->ajaxReturn(W('Rpcontent',$wdata,true),'成功',1);
            }else{
   	            $this->ajaxReturn(0,'失败',0);
            }
	    }else{
	      exit();
	    }
    }
    private function recstocks_c($id,$start=1,$type='',$stock_id='',$num=8){
        $groups_list = M('Groups_list');
    		$groups_list_map['rec_stocks_id'] = array('neq','');
    		$groups_list_map['groups_id'] = $id;
    		$groups_list_list = $groups_list->where($groups_list_map)->select();
    		foreach($groups_list_list as $val){
				if(!empty($val['rec_stocks_id'])){
				    $rec_stock_idarr[] = $val['rec_stocks_id'];
				}
    		}
    		$rec_stock = M('Rec_stocks');
    		$rec_stock_map['idrec_stocks'] = array('in',$rec_stock_idarr);
    		if($_GET['type'] == 'success'){
				$rec_stock_map['rate'] = '2';
			}elseif($_GET['type'] == 'lose'){
				$rec_stock_map['rate'] = '3';
			}elseif($_GET['type'] == 'ondo'){
				$rec_stock_map['rate'] = '1';
			}elseif($_GET['type'] == 'up'){
				$rec_stock_map['moreorempty'] = 'more';
			}elseif($_GET['type'] == 'down'){
				$rec_stock_map['moreorempty'] = 'empty';
			}
			if(!empty($stock_id)){
				$rec_stock_map['stocks_id'] = $stock_id;
			}
            if(!empty($num)){
                $num = 8;
            }
			$rec_stocks_list = $rec_stock->where($rec_stock_map)->limit($start,$num)->order('mktime desc')->select();

        //dump($rec_stocks_list);
		return $rec_stocks_list;
    }
    /*
	 * 2013-3-23上午07:38:03
	 * Jone
	 * 圈子成员
	**/
	public function ingroups (){

		$groups_id = $_GET['gid'];
		//圈子基本信息
        $groupsfind = $this->groups_msg($groups_id);
        $this->assign('groupsfind',$groupsfind);
		$user2groups = D('User2groups');
		$attention = M('Attention');
		$user2groups_map['groups_id'] = $groups_id;
		$user2groups_list = $user2groups->where($user2groups_map)->relation(true)->select();
		foreach($user2groups_list as $key=>$val){
			$attention_map['id'] = $val['user_id'];
			$attention_map['table'] = 'user';
			$user2groups_list[$key]['User']['attention_count'] = $attention->where($attention_map)->count();
		}
		$this->assign('user_list',$user2groups_list);
		$this->assign('groups_id',$groups_id);
        $this->assign('groupsfind',$groupsfind);
		//活跃用户
        $this->active_person($groups_id);
        //圈子内页热门标签
        $hot = $this->hot_discuess_tag(array($groups_id));
        $this->assign('hot',$hot);
		$this->display();
	}
	/*
	 * 2013-3-24上午03:24:16
	 * Jone
	 * 圈子基本信息
	**/
	public function groups_msg ($gid){
		//圈子基本信息
    	$groups = M('Groups');
        $groupsmap['idgroups'] = $gid;
        $groupsfind = $groups->where($groupsmap)->find();
        //关注人
        $attention = M('Attention');
        $attention_map['table'] = 'groups';
        $attention_map['id'] = $gid;
        $groupsfind['attention_count'] = $attention->where($attention_map)->count();
        //参与人
        $user2groups = M('User2groups');
        $user2groups_map['groups_id'] = $gid;
        $groupsfind['join_count'] = $user2groups->where($user2groups_map)->count();

        return $groupsfind;
	}

    /*
	 * 2013-3-22上午08:09:03
	 * Jone
	 * 圈子活跃用户
	**/
	public function active_person ($gid){
		$groups_list = 	M('User2groups');
		$groups_list_map['groups_id'] = $gid;
		$groups_list_list = $groups_list->where($groups_list_map)->field('user_id')->group('user_id')->select();
		foreach($groups_list_list as $key=>$val){
			$user_idarr[] =  $val['user_id'];
		}
		$user = M('User');
		$map['lock'] = 1;
		$map['iduser'] = array('in',$user_idarr);
		$user_count = $user->where($map)->count();
		if($user_count>=2){
			$num = rand(1,$user_count-2);
			$user_list = $user->where($map)->limit($num,2)->field('name,iduser,avatar')->select();
		}else{

			$user_list = $user->where($map)->field('name,iduser,avatar')->select();
		}

		foreach($user_list as $key=>$val){
			$attention_count = R('User/attention_user_count',array($val['iduser']));
			$user_list[$key]['attention'] = $attention_count;
			$user_list[$key]['rate'] = '44%';
		}
		$this->assign('active',$user_list);
	}
    public function groups_list_more_a(){//Ajax_more
        if($this->isAjax()){
            $list = $this->groups_list($_POST['gid'],$_POST['page'],$_POST['limit'],$_POST['type']);
            if($list){
                $this->ajaxReturn($list,$_POST['page']+1,1);
            }else{
                $this->ajaxReturn('','无',0);
            }
        }
    }
    private function groups_list($id,$page=1,$limit=8,$type=''){//三元列表搜索函数
        $groups_list = D('Groups_list');
        $map['groups_id'] = $id;
        if(!empty($type)){
            if($type=='discuss'){
                $map['groups_discuss_id'] = array('neq','');
            }elseif($type=='rec_stocks'){
                $map['rec_stocks_id'] = array('neq','');
            }elseif($type=='point_view'){
                $map['point_view_id'] = array('neq','');
            }
        }
        $groups_listlist = $groups_list->where($map)->relation(true)->order('mktime desc')->limit($limit)->page($page)->select();
        if($groups_listlist){
            return array_reverse($groups_listlist);
        }else{
            return false;
        }
    }
    /**
     * 发讨论
     * */
    public function discuss_add_a(){
        if($this->isAjax()){
            $this->acl_input3();
            if(!empty($_POST['content'])&&!empty($_POST['groups'])){
                $groups = $_POST['groups'];
                $mainok = $this->discuss_add_main_c($_POST['content'],$groups);
                if($mainok){
                    $this->discuss_add_groups_c($mainok,$groups);
                    //
                    $list = $this->discuess_c($groups,0,'','',10);
                    $wdata['list'] = $list; $wdata['ifmode'] = 'mix'; $wdata['rehtml'] = 'red';
                    $this->ajaxReturn(W('Rpcontent',$wdata,true),'成功',1);
                    //$this->ajaxReturn('','',1);
                }else{
                    $this->ajaxReturn('','',0);
                }
            }else{
                $this->ajaxReturn('','',0);
            }
        }
    }
    private function discuss_add_main_c($content,$groups){
        $groups_discuss = M('Groups_discuss');
        $mappv['content'] = $content;
        $mappv['content_t'] = R('Tool/msubstr_txt',array($mappv['content']));
        $mappv['groups_id'] = $groups;
        $mappv['user_id'] = $_SESSION['MEIX']['iduser'];
        $mappv['user_name'] = $_SESSION['MEIX']['name'];
        $mappv['user_avatar'] = $_SESSION['MEIX']['avatar'];
        $mappv['mktime'] = mktime();
        $mappv['lock'] = 1;
        $ok = $groups_discuss->add($mappv);
        if($ok){
            return $groups_discuss->getLastInsID();
        }else{
            return false;
        }
    }
    private function discuss_add_groups_c($id,$group){
        $groups_list = M('Groups_list');
        $map['groups_discuss_id'] = $id;
        $map['mktime'] = mktime();
        $map['groups_id'] = $group;
        $groups_list->add($map);
    }
    /**
     * 圈子内页热门标签
     * */
    private function hot_discuess_tag($gid){//圈子内页热门标签
        $groups_list = M('Groups_list');
        $map['groups_id'] = array('in',$gid);
        $groups_listlist = $groups_list->where($map)->select();
        foreach($groups_listlist as $gvo){
            if(!empty($gvo['rec_stocks_id'])){ $retuid['recstocks'][] = $gvo['rec_stocks_id']; }
            if(!empty($gvo['point_view_id'])){ $retuid['pointview'][] = $gvo['point_view_id']; }
        }
        $recstocks = R('Recstocks/hot_rec_stcok_rate_c',array($retuid['recstocks']));
        $pointview = R('Pointview/hot_point_view_tag_c',array($retuid['pointview']));
        foreach($recstocks as $rkey=>$rvo){
            if(!empty($pointview[$rkey])){
                $pointview[$rkey]+=$rvo;
            }else{
                $pointview[$rkey] = $rvo;
            }
        }
        arsort($pointview);
        $returnok = R('Pointview/hot_tag_toshow_c',array($pointview));
        return $returnok;
    }
    /**
     * 修改圈子简介
     * */
    public function groups_info_edit_a(){//Ȧ��½�½�Ȧ
        if($this->isAjax()){
            $this->acl_input3();
            if(!empty($_POST['gid'])){
                $groups = M('Groups');
                $map['idgroups'] = $_POST['gid'];
                $groupsfind = $groups->where($map)->find();
                if($groupsfind&&$groupsfind['user_id']){
                    if(!empty($_SESSION['MEIX']['iduser'])&&($groupsfind['user_id']==$_SESSION['MEIX']['iduser'])){
                        if(!empty($_POST['name_info'])&&($groupsfind['name_info']!=$_POST['name_info'])){
                            $data['name_info'] = $_POST['name_info'];
                            $ok = $groups->where($map)->save($data);
                            if($ok){
                                $this->ajaxReturn('','OK',1);
                            }else{
                                $this->ajaxReturn('','请重试',0);
                            }
                        }else{
                            $this->ajaxReturn('','OK',1);
                        }
                    }else{
                        $this->ajaxReturn('','你没有修改权力',0);
                    }
                }else{
                    $this->ajaxReturn('','圈子不存在',0);
                }
            }else{
                $this->ajaxReturn('','',0);
            }
        }
    }
    /**
     * 创建圈子
     * */
    public function groups_add_a(){//�½�Ȧ��
        if($this->isAjax()){
            $this->acl_input3();
            if(!empty($_POST['name'])&&!empty($_POST['name_info'])){
                if(!empty($_SESSION['MEIX']['info']['groups_add'])){
                    $user_num = $_SESSION['MEIX']['info']['groups_add'];
                    $info_message = '会员用户只能创建'.$user_num.'个圈子';
                }else{
                    $user_num = 5;
                    $info_message = '非会员用户只能创建'.$user_num.'个圈子';
                }
                $groups = M('Groups');
                $map['user_id'] = $_SESSION['MEIX']['iduser'];
                $groupshave = $groups->where($map)->select();
                if(count($groupshave)<$user_num){
                    $map['name'] = $_POST['name'];
                    $map['name_info'] = $_POST['name_info'];
                    $map['accuracy'] = 0;
                    $map['accuracy_v'] = 0;
                    $map['mktime'] = mktime();
                    $map['lock'] = 1;
                    $groupsadd = $groups->add($map);
                    if($groupsadd){
                        $data2['groups_id'] = $groups->getLastInsID();
                        $data2['user_id'] = $map['user_id'];
                        $data2['nickname'] = '圈主';
                        $data2['as'] = 10;
                        $data2['endtime'] = mktime()+99999999;
                        $user2groups = M('User2groups');
                        $user2groups->add($data2);
                        $attention = M('Attention');
                        $data3['table'] = 'groups';
                        $data3['id'] = $data2['groups_id'];
                        $data3['user_id'] = $_SESSION['MEIX']['iduser'];
                        $data3['mktime'] = mktime();
                        $data3['lock'] = 1;
                        $attention->add($data3);
                        $this->ajaxReturn('','',1);
                    }else{
                        $this->ajaxReturn('','',0);
                    }
                }else{
                    $this->ajaxReturn($user_num,$info_message,0);
                }
            }else{
                $this->ajaxReturn('','',0);
            }
        }
    }


    /**
     * 申请进圈
     * */
    public function application_groups_a(){
        if($this->isAjax()){//$this->isAjax()
            if(!empty($_POST['gid'])&&!empty($_SESSION['MEIX']['iduser'])){
                $user2groups = M('User2groups');
                $map['user_id'] = $_SESSION['MEIX']['iduser'];////必需不在圈
                $map['groups_id'] = $_POST['gid'];
                $user2groupsfind = $user2groups->where($map)->find();
                if(!$user2groupsfind){
                    $code = $this->invite_code($_SESSION['MEIX']['iduser'],$_POST['gid'],'user','application_groups');
                    if($code){
                        $ok = $this->notice_groups_c($code,'application_groups','申请加入圈子');
                        if($ok){
                            $this->ajaxReturn('','',1);
                        }else{
                            $this->ajaxReturn('','等待用户确认中...',0);
                        }
                    }else{
                        $this->ajaxReturn('','等待用户确认中..',0);
                    }
                }else{
                    $this->ajaxReturn('','你已在圈子内',0);
                }
            }else{
                $this->ajaxReturn('','1',0);
            }
        }

    }

    /**
     *提示存
     **/
    private function notice_groups_c($code,$type='application_groups',$mes='申请加入圈子'){
        if(!empty($code)){
            $groups_inviet = M('Groups_invite');
            $gimap['code'] = $code;
            $groups_invietfind = $groups_inviet->where($gimap)->find();
            if($groups_invietfind){
                $groups = M('Groups');
                $groupsmap['idgroups'] = $groups_invietfind['groups_id'];
                $groupsfind = $groups->where($groupsmap)->find();
                $notice = M('Notice');
	            $notice_map['type'] = array('in','application_groups,invite_groups');//$type
	            $notice_map['a_domain'] = $groupsfind['idgroups'];
	            $notice_map['ed'] = 1;
	            $notice_list = $notice->where($notice_map)->find();
				if(1){//empty($notice_list)
					if($type=='application_groups'){
	                    $map['user_id'] = $groupsfind['user_id'];
	                }elseif($type=='invite_groups'){
	                    $map['user_id'] = $groups_invietfind['id'];
	                }
	                $map['type'] = $type;
	                $map['ed'] = 1;
	                $map['name'] = $_SESSION['MEIX']['name'];
	                $map['showtype'] = $mes;
	                $map['content_t'] = $mes.'加入圈子:'.$groupsfind['name'];
	                $map['content_info'] = '<a class="link0 js_gruop_agree" data-groups-code="'.$code.'" href="javascript:;">同意</a><span class="pl5 pr5">|</span><a class="link0 js_group_reject" data-groups-code="'.$code.'" href="javascript:;">忽略</a>';
	                //<a href="__APP__/Groups/agree_groups_a/code/'.$code.'">同意</a>，<a href="__APP__/Groups/refuse_groups_a/code/'.$code.'">拒绝</a>
	                $map['a_domain'] = $groupsfind['idgroups'];
	                    //$map['a_action'] = '';
	                    //$map['a_function'] = '';
	                $map['a_get'] = $groups_invietfind['user_id'];
	                $map['a_tictactoe'] = $groups_invietfind['id'];
	                $map['mktime'] = mktime();
	                $map['lock'] = 1;
	                return $notice->add($map);
				}
            }else{
                //圈子有问题
                //dump($groups_invietfind);
                return false;
            }
        }else{
            return false;
        }

    }

    /**
     * 申请进圈，同意，拒绝
     * agree_refuse
     * */
    /*
    public function app_agree_groups_a(){
        if($this->isAjax()){//$this->isAjax()
            $gid = $_POST['gid'];
            $uid = $_POST['uid'];
            if(!empty($uid)&&!empty($gid)&&!empty($_SESSION['MEIX']['iduser'])){
                $groups = M('Groups');
                $groupsmap['idgroups'] = $gid;
                $groupsmap['user_id'] = $_SESSION['MEIX']['iduser'];
                $groupshave = $groups->where($groupsmap)->find();
                if($groupshave){
                    $user2groups = M('User2groups');
                    $u2gmap['nickname'] = '圈子成员';
                    $u2gmap['as'] = 3;//申请来的3，邀请来的5，主10
                    $u2gmap['endtime'] = mktime()+99999999;
                    $u2gmap['user_id'] = $uid;
                    $u2gmap['groups_id'] = $gid;
                    $ok = $user2groups->add($u2gmap);
                    if($ok){
                        $this->ajaxReturn('','',1);
                    }else{
                        $this->ajaxReturn('','Please try again',0);
                    }
                }else{
                    $this->ajaxReturn('','Do not have permission',0);
                }
            }else{
                $this->ajaxReturn('','Can not be empty',0);
            }
        }
    }
    */
    /**
     *邀请码
     * */
    private function invite_code($id,$gid='',$table='user',$type='invite_group'){
        $groups_invite = M('Groups_invite');
        $map['groups_id'] = $gid;
        if($type=='invite_group'){
            $map['user_id'] = $_SESSION['MEIX']['iduser'];
        }else{
            $groups = M('Groups');
            $groupsmap['idgroups'] = $gid;
            $groupsuser_id = $groups->where($groupsmap)->getField('user_id');
            $map['user_id'] = $groupsuser_id;
        }


        $map['id'] = $id;
        $map['table'] = $table;
        $map['type'] = $type;
        $groups_invitehave = $groups_invite->where($map)->find();
        if(!empty($_SESSION['MEIX']['iduser'])){//!$groups_invitehave&&!empty($_SESSION['MEIX']['iduser'])
            $map['mktime'] = mktime();
            $map['code'] = md5($map['type'].($map['mktime']*13));

            $ok = $groups_invite->add($map);
            if($ok){
                return $map['code'];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    /**
     * 邀请进圈
     **/

    public function invite_groups_a(){
        if($this->isAjax()){//$this->isAjax()
            if(!empty($_POST['uid'])&&!empty($_POST['gid'])&&!empty($_SESSION['MEIX']['iduser'])){
                $groups = M('Groups');
                $map['user_id'] = $_SESSION['MEIX']['iduser'];////必需圈主人
                $map['idgroups'] = $_POST['gid'];
                $groupsfind = $groups->where($map)->find();
                if($groupsfind){
                    $code = $this->invite_code($_POST['uid'],$_POST['gid'],'user','invite_groups');
                    if($code){
                        $ok = $this->notice_groups_c($code,'invite_groups','邀请您加入圈子');
                        if($ok){
                            $this->ajaxReturn('','',1);
                        }else{
                            $this->ajaxReturn('','等待用户确认中...',0);
                        }
                    }else{
                        $this->ajaxReturn('','不准许邀请..',0);
                    }
                }else{
                    $this->ajaxReturn('','必需圈主人',0);
                }
            }else{
                $this->ajaxReturn('','1',0);
            }
        }
    }

     /**
     * 申请 邀请进圈，同意
     * */
    public function agree_groups_a(){
        if($this->isAjax()){//$this->isAjax()
            $map['code'] = $_POST['code'];
            if(!empty($map['code'])){
                $groups_invite = M('Groups_invite');
                $groups_invitefind = $groups_invite->where($map)->find();
                if($groups_invitefind['type']!='invite_groups'){
                    $groups = M('Groups');
                    $groupsmap['user_id'] = $_SESSION['MEIX']['iduser'];
                    $groupsmap['idgroups'] = $groups_invitefind['groups_id'];
                    $groupsuser_id = $groups->where($groupsmap)->find();
                    if(!$groupsuser_id){
                        $this->ajaxReturn('','Not the main',0);
                        exit();
                    }
                }
                if($groups_invitefind){
                    $user2groups = M('User2groups');
                    $u2gmap['nickname'] = '圈子成员';

                    if($groups_invitefind['table']=='user'){
                        $u2gmap['user_id'] = $groups_invitefind['id'];
                    }
                    $u2gmap['groups_id'] = $groups_invitefind['groups_id'];
                    $user2groupshave = $user2groups->where($u2gmap)->find();
                    if(!$user2groupshave){
                        $u2gmap['as'] = 3;//申请来的3，邀请来的5，主10
                        $u2gmap['endtime'] = mktime()+99999999;
                        $ok = $user2groups->add($u2gmap);
                    }else{
                        $ok = 1;
                    }
                    if($ok){
                        $groups_invite->where($map)->delete();//
                        $this->notice_content_info_ar($groups_invitefind,'已同意');
                        $this->ajaxReturn('','',1);
                    }else{
                        $this->ajaxReturn('','Please try again',0);
                    }
                }else{
                    $this->ajaxReturn('','Please try again',0);
                }
            }else{
                $this->ajaxReturn('','Can not be empty',0);
            }
        }
    }
    /**
     * 申请 邀请进圈，拒绝
     * */
    public function refuse_groups_a(){
        if($this->isAjax()){//$this->isAjax()
            $map['code'] = $_POST['code'];
            if(!empty($map['code'])){
                $groups_invite = M('Groups_invite');
                $groups_invitefind = $groups_invite->where($map)->find();
                $groups = M('Groups');
                $mes = $groups->where('idgroups='.$groups_invitefind['groups_id'])->getField('name');
                if($groups_invitefind){
                    $notice = M('Notice');
                    if($groups_invitefind['type']=='invite_group'){
                        $add['user_id'] = $groups_invitefind['user_id'];
                    }else{
                        $add['user_id'] = $groups_invitefind['id'];
                    }
                    $add['type'] = 'invite_refuse_groups';
                    $add['ed'] = 1;
                    $add['name'] = $_SESSION['MEIX']['name'];
                    $add['showtype'] = '拒绝';
                    $add['content_t'] = '加入圈子:'.$mes;
                    $add['a_action'] = '/Groups';
                    $add['a_function'] = '/discuess';
                    $add['a_get'] = '/gid/'.$groups_invitefind['groups_id'];
                    $add['mktime'] = mktime();
                    $add['lock'] = 1;
                    $ok = $notice->add($add);
                    if($ok){
                        $groups_invite->where($map)->delete();
                        $this->notice_content_info_ar($groups_invitefind,'已拒绝');
                        $this->ajaxReturn('','',1);
                    }else{
                        $this->ajaxReturn('','Please try again',0);
                    }
                }else{
                    $this->ajaxReturn('','Please try again',0);
                }
            }else{
                $this->ajaxReturn('','Can not be empty',0);
            }
        }
    }
    private function notice_content_info_ar($groups_invitefind,$content_info=''){
        if(!empty($groups_invitefind)){
            $notice = M('Notice');
            $map['a_domain'] = $groups_invitefind['groups_id'];
            $data['content_info'] = $content_info;
            $data['ed'] = 0;
            if($groups_invitefind['type']=='invite_groups'){
                $map['type'] = 'invite_groups';//$groups_invitefind['type'];
                $map['user_id'] = $groups_invitefind['id'];
                $notice->where($map)->save($data);
            }elseif($groups_invitefind['type']=='application_groups'){
                $map['type'] = 'application_groups';//$groups_invitefind['type'];
                $map['user_id'] = $groups_invitefind['user_id'];
                $map['a_tictactoe'] = $groups_invitefind['id'];
                $notice->where($map)->save($data);
            }
        }
    }

    /**
     * 移除圈子内成员
     * */
    public function remove_user_groups_a(){
        if($this->isAjax()){//$this->isAjax()
            $gid = $_POST['gid'];
            $uid = $_POST['uid'];
            $user_id = $_SESSION['MEIX']['iduser'];
            if(!empty($gid)&&!empty($uid)&&!empty($user_id)){
                $groups = M('Groups');
                $mapg['idgroups'] = $gid;
                $mapg['user_id'] = $user_id;
                $groupsfind = $groups->where($mapg)->find();
                if($groupsfind){
                    $user2groups = M('User2groups');
                    $map['user_id'] = $uid;
                    $map['groups_id'] = $gid;
                    $ok = $user2groups->where($map)->delete();
                    if($ok){
                        $this->ajaxReturn('','',1);
                    }else{
                        $this->ajaxReturn('','Please try again',0);
                    }
                }else{
                    $this->ajaxReturn('','You did the permissions',0);
                }
            }else{
                $this->ajaxReturn('','Can not be empty',0);
            }
        }
    }

}
?>