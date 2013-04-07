<?php
class ThemeAction extends AclAction {
    public function _initialize(){
        if(!isset($_SESSION['MEIX'])||empty($_SESSION['MEIX']['iduser'])){
            $_SESSION['MEIX']['JS']['url_login'] = '';
            R('Login/index');
            exit(0);
        }
    }
    public function index(){
        if($this->isAjax()&&!empty($_POST['theme'])){
            $data['theme'] = $_POST['theme'];//register
            $data['datapost'] = $_POST;
            $content = W('Theme',$data,true);
            if($content){
                $this->ajaxReturn($content,'',1);
            }else{
                $this->ajaxReturn('','',0);
            }

        }
    }
    /*
	 * 2013-3-20上午10:14:32
	 * Jone
	 * 圈子信息查看弹出框
	**/
	public function groups_view(){
        if($this->isAjax() && !empty($_POST['groups_id'])){
            $groups_id = $_POST['groups_id'];
            $groups = D('Groups');
            $groups_map['idgroups'] = $groups_id;
            $groups_list = $groups->where($groups_map)->relation(true)->find();
            //$groups_list['point_recstock'] = $this->groups_point_recstock($groups_id);
            $groups_list_action = M('Groups_list');
            //观点数
            $groups_list_point_map['point_view_id'] = array('neq','');
            $groups_list_point_map['groups_id'] = $groups_id;
            $groups_list['point_view_count'] = $groups_list_action->where($groups_list_point_map)->count();
            //荐股数
            $groups_list_stock_map['rec_stocks_id'] = array('neq','');
            $groups_list_stock_map['groups_id'] = $groups_id;
            $groups_list['rec_stock_count'] = $groups_list_action->where($groups_list_stock_map)->count();
            $rec_stocks_list = $groups_list['rec_stock_count'];
            $rec_stock_rate_cycle = M('Rec_stocks_cycle');
			foreach($rec_stocks_list as $key=>$val){
	            $rec_stocks_list[$key]['Cycle'] = $rec_stock_rate_cycle->where('idrec_stocks_cycle='.$val['rec_stocks_cycle_id'])->find();
	            $reccycleidmap['rec_stocks_cycle_id'] = $val['rec_stocks_cycle_id'];
	            $reccycleidmap['idrec_stocks'] = array('neq',$val['idrec_stocks']);
	            $rec_stocks_list[$key]['Rec_stocks'] = $rec_stocks->where($reccycleidmap)->select();
	            unset($reccycleidmap);
	            $rec_stocks_list[$key]['Stocknow'] = R('Tool/tool_stock_now',array($val['stocks_number']));
			}
			$groups_list['rec_stock_count'] = $rec_stocks_list;
			//关注数
			$groups_list['attention_count'] = R('Groups/groups_attention_count',array($groups_id));

            //是否已加入
            $user2groups = M('User2groups');
            $user_id = $_SESSION['MEIX']['iduser'];
            $user2groups_map['groups_id'] = $groups_id;
            $user2groups_map['user_id'] = $user_id;
            $user2groups_msg = $user2groups->where($user2groups_map)->find();
            if(!empty($user2groups_msg)){
            	$groups_list['in'] = 1;
            }else{
            	$groups_list['in'] = 0;
            }
            //是否关注
            $attention = M('Attention');
            $attention_map['table'] = 'groups';
            $attention_map['id'] = $groups_id;
            $attention_map['user_id'] = $_SESSION['MEIX']['iduser'];
            $attention_msg = $attention->where($attention_map)->find();
            if(!empty($attention_msg)){
            	$groups_list['attention'] = 1;
            }else{
            	$groups_list['attention'] = 0;
            }
            //圈子推荐荐股观点
            $groupslist = M('Groups_list');
            $groupslist_map['groups_id'] = $groups_id;
            $groups_list['rplist'] = $groupslist->where($groupslist_map)->select();
//            dump($groups_list);
            $content = W('Groups',$groups_list,true);
            if($content){
                $this->ajaxReturn($content,'',1);
            }else{
                $this->ajaxReturn('','',0);
            }
        }
    }
    /*
	 * 2013-3-28上午08:24:19
	 * Jone
	 * 圈子成员
	**/
	public function ig_groups_member (){
		if($this->isAjax()){
			$user2groups = M('User2groups');
			$user2groups_map['groups_id']  = $_POST['gid'];
			$user2groups_list = $user2groups->where($user2groups_map)->field('user_id')->select();
			foreach($user2groups_map as $key=>$val){
				$user_idarr[] =  $val['user_id'];
			}
			$user = M('User');
			$user_map['iduser'] = array('in',$user_idarr);
			$user_list = $user->where($user_map)->field('avatar')->select();
			$this->ajaxReturn($user_list,'成功',1);
		}else{
			exit();
		}
	}
    /*
	 * 2013-3-20上午10:20:36
	 * Jone
	 * 获取圈子下的内容（观点，荐股）
	**/
	public function groups_point_recstock ($groups_id){
		if(!empty($groups_id)){
			$groups_list = D('Groups_list');
			$point_view_tag = M('Point_view_tag');
			$groups_list_map['groups_id'] = $groups_id;
			$groups_list_list = $groups_list->where($groups_list_map)->relation(true)->order('mktime desc')->limit(2)->select();

			foreach($groups_list_list as $key=>$val){

				if(!empty($val['Rec_stocks'])){
					if($val['Rec_stocks']['rate'] == 1){
						$stock_price = R('Tool/tool_stock_now_c',array($val['Rec_stocks']['stocks_id']));//ToolAction::tool_stock_now_c($val['stocks_id']);
						$groups_list_list[$key]['Rec_stocks']['rate_info'] = $stock_price[0];
						$rec_stocks_list[] = $val['Rec_stocks'];
					}
				}elseif($val['Point_view']){
					$groups_list_list[$key]['Point_view']['point_view_tag'] = $point_view_tag->where('point_view_id='.$val['Point_view']['idpoint_view'])->select();
				}
			}
			return $groups_list_list;
		}else{
			return null;
		}

	}
    public function recstockadd(){//荐股
        if($this->isAjax()){
            $data['group_id'] = $_POST['group_id'];//register
            $content = W('RecStockadd',$data,true);
            if($content){
                $this->ajaxReturn($content,'',1);
            }else{
                $this->ajaxReturn('','',0);
            }

        }
    }
    public function viewpointadd(){//观点
        if($this->isAjax()){
            $data['group_id'] = $_POST['group_id'];//register
            $content = W('Viewpointadd',$data,true);
            if($content){
                $this->ajaxReturn($content,'',1);
            }else{
                $this->ajaxReturn('','',0);
            }

        }
    }
	/*
	 * 2013-3-23上午04:39:40
	 * Jone
	 * 列表上留言列表调取
	**/
	public function list_message_w (){
        if($this->isAjax()){
        	$messagedata['table']=$_POST['table'];
        	$messagedata['id']=$_POST['id'];
            $content = W('Message',$messagedata,true);
            if($content){
                $this->ajaxReturn($content,'',1);
            }else{
                $this->ajaxReturn('','',0);
            }

        }
    }
    public function rec_stocks_cycle3(){
        if($this->isAjax()){
        	$data['rec_stocks_id']=$_POST['rec_stocks_id'];
        	$data['first']=$_POST['first'];
            //$messagedata['content']=$_POST['content'];
            //$messagedata['groups']=$_POST['groups'];
            $content = W('RecStockscycle',$data,true);
            if($content){
                $this->ajaxReturn($content,'',1);
            }else{
                $this->ajaxReturn('','',0);
            }

        }
    }
    /**
     * 圈子成员
     * */
    public function groupsmember(){
        if($this->isAjax()){
        	if(!empty($_POST['gid'])&&!empty($_SESSION['MEIX']['iduser'])){
	            $content = W('GroupsMember',array('gid'=>$_POST['gid']),true);
            }
            if($content){
                $this->ajaxReturn($content,'',1);
            }else{
                $this->ajaxReturn('','',0);
            }

        }
    }

}

?>