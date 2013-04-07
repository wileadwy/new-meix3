<?php
/**
 * 观点精华
 * */
class PointviewAction extends AclAction {
    public function _initialize(){
        if(!isset($_SESSION['MEIX'])||empty($_SESSION['MEIX']['iduser'])){
            $_SESSION['MEIX']['JS']['url_login'] = '';
            R('Login/index');
            exit(0);
        }
    }
    /*
	 * 2013-3-18上午06:48:13
	 * Jone
	 * 精华页面列表数据显示
	**/
    public function index(){
		$type = $_GET['type'];
        $stock_id = $_GET['sid'];
		$point_view_list = $this->index_c(0,$type,$stock_id,10);
		//$this->assign('point_view',$point_view_list);
        $this->assign('list',$point_view_list);
        $this->assign('stock_id',$stock_id);
		//热门标签
		$point_view_tag_list_sort = $this->hot_point_view_tag_c();
        arsort($point_view_tag_list_sort);
        $hot_point_view_tag = $this->hot_tag_toshow_c($point_view_tag_list_sort);
        $this->assign('hot_point_view_tag',$hot_point_view_tag);
        //end热门标签
        $this->assign('type',$type);
		$this->display();
    }
    public function index_a(){
        if($this->isAjax()){
	      $data = $_POST;
            if(!empty($data)){
                $list = $this->index_c($data['start'],$data['type'],$data['stock_id'],$data['num']);
                $wdata['list'] = $list; $wdata['ifmode'] = 'point_view';
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
    	$groups_list_map['point_view_id'] = array('neq','');
    	$groups_list_list = $groups_list->where($groups_list_map)->field('point_view_id')->select();
    	foreach($groups_list_list as $key=>$val){
			$point_view_id_arr1[] = $val['point_view_id'];
    	}
        $point_view = M('Point_view');
		$map['lock'] = '1';
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
				$point_view_id_arr2[] = $tag_val['point_view_id'];
			}
		}
		if(!empty($point_view_id_arr2)){
			$point_view_id_arr = array_intersect($point_view_id_arr1,$point_view_id_arr2);
		}else{
			$point_view_id_arr = $point_view_id_arr1;
		}
		//dump($point_view_id_arr);
		$map['idpoint_view'] = array('in',$point_view_id_arr);
        if(empty($num)){
            $num = 8;
        }
        //$start = $start-1;
		$point_view_list = $point_view->where($map)->order('info_top desc')->limit($start,$num)->select();
        //echo '<br/><br/><br/><br/>';
        //echo $point_view->getLastSql();
        //dump($rec_stocks_list);
        return $point_view_list;
    }
    /*
	 * 2013-3-18上午08:33:24
	 * Jone
	 * 右栏热门标签
	**/
	public function hot_point_view_tag_c ($instockid=''){
		$point_view_tag = M('Point_view_tag');
		$tag_map['lock'] = '1';
        if(!empty($instockid)){ $tag_map['point_view_id'] = array('in',$instockid); }
		$point_view_tag_list = $point_view_tag->where($tag_map)->group('stock_id')->select();
		foreach($point_view_tag_list as $tag_key=>$tag_val){
			$tag_count_map['stock_id'] = $tag_val['stock_id'];
			$tag_count_map['lock'] = '1';
			//$point_view_tag_list[$tag_key]['tag_count'] = $point_view_tag->where($tag_count_map)->count();
            $point_view_tag_list_sort[$tag_count_map['stock_id']] =  $point_view_tag->where($tag_count_map)->count();
		}
        return $point_view_tag_list_sort;
	}
    public function hot_tag_toshow_c($point_view_tag_list_sort,$num=7){
        $stock = M('Stock');
        $mapstock['lock'] = 1;
        $ij = 0;
        foreach($point_view_tag_list_sort as $pvkey=>$pvvo){
            if($ij>$num){ break; }else{
                $ij++;
            }
            $mapstock['idstock'] = $pvkey;
            $stockfind = $stock->where($mapstock)->find();
            if($stockfind){
                $stockfind['count'] = $pvvo;
                $stocklist[] = $stockfind;
            }
        }
        return $stocklist;
    }
    /*
	 * 2013-3-18上午07:49:49
	 * 精华内页
	**/
    public function inside(){
    	$point_view_id = $_GET['pid'];
    	if(!empty($point_view_id)){
    		$point_view = M('Point_view');
    		$point_view_tag = M('Point_view_tag');
    		$point_view_map['idpoint_view'] = $point_view_id;
    		$point_view_map['lock'] = 1;
    		$point_view_msg = $point_view->where($point_view_map)->limit(1)->select();
    		//观点相关标签
            /*
			$point_view_tag_map['point_view_id'] = $point_view_msg[0]['idpoint_view'];
			$point_view_msg['Tag'] = $point_view_tag->where($point_view_tag_map)->select();
            */
    		$this->assign('list',$point_view_msg);
    		$this->maybe_love_person();
    		$this->maybe_love_groups();
			$this->display();
    	}else{
    		//错误页
    	}
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
	 * 2013-3-18上午07:49:49
	 * 精华添加方法
	**/
    public function pv_add_a(){
        if($this->isAjax()){
            $this->acl_input3();
            if(!empty($_POST['content'])&&!empty($_POST['moreorempty'])){
                $mainok = $this->pv_add_main_c($_POST['content'],$_POST['moreorempty']);
                if($mainok){
                    if(!empty($_POST['groups'])){
                        $groups = explode(',',$_POST['groups']);
                        if(is_array($groups)){
                            $this->pv_add_groups_c($mainok,$groups);
                        }
                    }
                    if(!empty($_POST['stocks'])){
                        $stocks = explode(',',$_POST['stocks']);
                        if(!$stocks){
                            $stocks = array($_POST['stocks']);
                        }
                        if(is_array($stocks)){
                            $this->pv_add_tag_c($mainok,$stocks);
                        }
                    }
                    if(!empty($_SESSION['MEIX']['iduser'])){
                        $user_list = M('User_list');
                        $ulmap['point_view_id'] = $mainok;
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
                $this->ajaxReturn('','',0);
            }
        }
    }
    /**
     * discuss 2 pointView
     * 标精
     * */
    public function discuess_pv_a(){
        if($this->isAjax()){
            if(!empty($_POST['listid'])&&!empty($_POST['moreorempty'])&&!empty($_SESSION['MEIX']['iduser'])){
                $groups_list = M('Groups_list');
                $glmap['idgroups_list'] = $_POST['listid'];
                $groups_listhave = $groups_list->where($glmap)->find();
                //
                $groups_discuss = M('Groups_discuss');
                $gdmap['idgroups_discuss'] = $groups_listhave['groups_discuss_id'];
                $gdmap['lock'] = 1;
                $groups_discussfind = $groups_discuss->where($gdmap)->find();//!!
                //
                $user2groups = M('User2groups');
                $u2g['user_id'] = $_SESSION['MEIX']['iduser'];
                $u2g['groups_id'] = $groups_discussfind['groups_id'];
                $user2groupsfind = $user2groups->where($u2g)->find();
                if($user2groupsfind&&$groups_discussfind){
                    $mainok = $this->pv_add_main_c($groups_discussfind['content'],$_POST['moreorempty']);
                    if($mainok){
                        $groups_listmap['idgroups_list'] = $groups_listhave['idgroups_list'];
                        $groups_listdata['groups_discuss_id'] = NULL;
                        $groups_listdata['point_view_id'] = $mainok;
                        $groups_list->where($groups_listmap)->save($groups_listdata);
                        if(!empty($_POST['stocks'])){///POST
                            $stocks = explode(',',$_POST['stocks']);
                            if(!$stocks){
                                $stocks = array($_POST['stocks']);
                            }
                            if(is_array($stocks)){
                                $this->pv_add_tag_c($mainok,$stocks);
                            }
                        }
                        if(!empty($u2g['user_id'])){
                            $user_list = M('User_list');
                            $ulmap['point_view_id'] = $mainok;
                            $ulmap['mktime'] = $user2groupsfind['mktime'];
                            $ulmap['top'] = 0;
                            $ulmap['user_id'] = $u2g['user_id'];
                            $user_list->add($ulmap);
                        }
                        $this->ajaxReturn('','',1);
                    }else{
                        $this->ajaxReturn('','1',0);
                    }

                }else{
                    $this->ajaxReturn('','2',0);
                }
            }else{
                $this->ajaxReturn('','3',0);
            }
        }
    }
    /*
	 * 2013-3-18上午07:49:49
	 * 精华主表信息添加方法
	**/
    private function pv_add_main_c($content,$moreorempty){
        $point_view = M('Point_view');
        $mappv['content'] = $content;
        $mappv['content_t'] = R('Tool/msubstr_txt',array($mappv['content']));
        $mappv['moreorempty'] = $moreorempty;
        $mappv['info_top'] = 0;
        $mappv['info_poor'] = 0;
        $mappv['info_message_count'] = 0;
        $mappv['info_digest'] = 0;
        $mappv['user_id'] = $_SESSION['MEIX']['iduser'];
        $mappv['user_name'] = $_SESSION['MEIX']['name'];
        $mappv['user_avatar'] = $_SESSION['MEIX']['avatar'];
        $mappv['mktime'] = mktime();
        $mappv['lock'] = 1;
        $ok = $point_view->add($mappv);
        if($ok){
            return $point_view->getLastInsID();
        }else{
            return false;
        }
    }
    /*
	 * 2013-3-18上午07:49:49
	 * 精华圈子信息添加方法
	**/
    private function pv_add_groups_c($id,$grouparr,$mktime=''){
        $groups_list = M('Groups_list');
        $map['point_view_id'] = $id;
        $map['mktime'] = mktime();
        foreach($grouparr as $vo){
            $map['groups_id'] = $vo;
            $groups_list->add($map);
        }
    }
    /*
	 * 2013-3-18上午07:49:49
	 * 精华标签信息添加方法
	**/
    private function pv_add_tag_c($id,$tag){
        $stock = M('Stock');
        $point_view_tag = M('Point_view_tag');
        $map['point_view_id'] = $id;
        $map['user_ic'] = $_SESSION['MEIX']['iduser'];
        foreach($tag as $vo){
            $maps['idstock'] = $vo;
            $stockfind = $stock->where($maps)->find();
            $map['stock_id'] = $stockfind['idstock'];
            $map['stock_name'] = $stockfind['showname'];
            $map['stock_number'] = $stockfind['shownumberb'].$stockfind['shownumber'];
            $point_view_tag->add($map);
        }
    }

}
?>