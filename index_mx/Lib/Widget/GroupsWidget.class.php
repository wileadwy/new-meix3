<?php

class GroupsWidget extends Widget{

    public function render($data){
        if(!empty($data)){
        	if($data['table'] != ''){
        		$map = array();
				if($data['page'] == ''){
					$start = '0';
					$num = '8';
				}else{
					$start = $data['page'];
					$num = '4';
				}
        		if($data['table'] == 'groups_index'){
	        		$groups_idarr = $this->get_groups_idarr($map);
	        		$groups_list['list'] = $this->get_groups_list($groups_idarr,$start,$num);
	        		$content = $this->renderFile('groups',$groups_list);
        		}elseif($data['table'] == 'user'){
        			$groups_idarr = $this->get_user_groups_idarr($data['id']);
	        		$groups_list['list'] = $this->get_groups_list($groups_idarr,$start,$num);
	        		$content = $this->renderFile('groups',$groups_list);
        		}elseif($data['table'] == 'stock'){
					$stock_id = $data['id'];
					$groups_idarr = $this->get_groups_idarr_by_sid($stock_id);
					$groups_list['list'] = $this->get_groups_list($groups_idarr,$start,$num);
					$content = $this->renderFile('groups',$groups_list);
        		}elseif($data['table'] == 'groups_right'){
        			$groups_msg = $this->get_groups_msg($data['id']);
	        		$arr['msg'] = $groups_msg;//圈子基本信息
	        		//圈子热门标签
	        		$arr['hot_tag'] = $this->get_groups_hot_tag($data['id']);
	        		//活跃用户
	        		$arr['ative_user'] = $this->get_groups_ative_user($data['id']);
	            	$content = $this->renderFile('groups_right',$arr);
	        	}
        	}else{
        		$arr['list'] = $data;
            	$content = $this->renderFile('group_view',$arr);
        	}
        }else{
            $content = false;
        }
        return $content;
    }
    /*
	 * 2013-3-27上午09:11:51
	 * Jone
	 * 获取圈子活跃用户
	**/
	public function get_groups_ative_user ($gid){
		$user2groups = M('User2groups');
		$attention = M('Attention');
		$user2groups_map['groups_id'] = $gid;
		$user2groups_list = $user2groups->where($user2groups_map)->field('user_id')->select();
		foreach($user2groups_list as $key=>$val){
			$user_idarr[] = $val['user_id'];
		}
		$user = M('User');
		$user_map['iduser'] = array('in',$user_idarr);
		$user_list = $user->where($user_map)->field('iduser,name,avatar,accuracy')->limit(10)->select();

		foreach($user_list as $key=>$val){
			$attention_map['table'] = 'user';
			$attention_map['id'] = $val['iduser'];
			$user_list[$key]['attention_count'] = $attention->where($attention_map)->count();
		}
		return $user_list;

	}
    /*
	 * 2013-3-27上午08:43:32
	 * Jone
	 * 圈子热门标签
	**/
	public function get_groups_hot_tag ($gid){
		$groups_point_view_tag = $this->get_group_pointview_tag_c($gid);//圈子中观点下的标签id
		$res_idarr = $this->get_group_recstock_tag_c($gid);//圈子中荐股下的标签id
        if(!empty($groups_point_view_tag)){
            $groups_tag_list = array_merge($groups_point_view_tag,$res_idarr);
        }else{
            $groups_tag_list = $res_idarr;
        }

		$tag_idarr = array();
		foreach($groups_tag_list as $idkey=>$idval){
			$tag_idarr[] = $idval['stock_id'];
		}
		$array1 = array_count_values($tag_idarr);
		foreach($array1 as $key=>$val){
			$arr[$key] = $val;
		}
		arsort($arr);
		$stock = M('Stock');
		foreach($arr as $key=>$val){
			$stock_map['idstock'] = $key;
			$stock_list = $stock->where($stock_map)->field('idstock,showname')->limit(10)->find();
			$stock_list['count'] = $val;
			$stock_tag[] = $stock_list;
		}
		return $stock_tag;
	}
    /*
	 * 2013-3-27上午08:34:40
	 * Jone
	 * 获取圈子信息
	**/
	public function get_groups_msg ($gid){
		$groups = M('Groups');
		$groups_map['idgroups'] = $gid;
		$groups_msg = $groups->where($groups_map)->find();
		//关注数
		$attetntion = M('Attention');
		$attetntion_map['table'] = 'groups';
		$attetntion_map['id'] = $gid;
		$groups_msg['attention'] = $attetntion->where($attetntion_map)->count();
		//参与人
		$user2groups = M('User2groups');
		$user2groups_map['groups_id'] = $gid;
		$groups_msg['in'] = $user2groups->where($user2groups_map)->count();
		//是否关注
		$attetntion_if_map['table'] = 'groups';
		$attetntion_if_map['id'] = $gid;
		$attetntion_if_map['user_id'] = $_SESSION['MEIX']['iduser'];
		$attetntion_if = $attetntion->where($attetntion_if_map)->count();
		if($attetntion_if == '0'){
			$groups_msg['if_attention'] = 1;
		}else{
			$groups_msg['if_attention'] = 0;
		}
		return $groups_msg;
	}
    /*
	 * 2013-3-27上午07:34:00
	 * Jone
	 * 根据sid找圈子id数组
	**/
	public function get_groups_idarr_by_sid ($stock_id){
		$point_view = M('Point_view');
		$point_view_tag = M('Point_view_tag');
		$rec_stocks = M('Rec_stocks');
		$groups_list = M('Groups_list');
		//荐股
		$rec_stocks_map['stocks_id'] = $stock_id;
		$rec_stocks_list = $rec_stocks->where($rec_stocks_map)->field('idrec_stocks')->select();
		foreach($rec_stocks_list as $key=>$val){
			$idrec_stocks[] = $val['idrec_stocks'];
		}
		//观点
		$point_view_tag_map['stock_id'] = $stock_id;
		$point_view_tag_list = $point_view_tag->where($point_view_tag_map)->field('point_view_id')->select();
		foreach($point_view_tag_list as $tag_key=>$tag_vo){
			$point_view_tag_idarr[] = $tag_vo['point_view_id'];
		}
		$point_view_map['lock'] = 1;
		$point_view_map['idpoint_view'] = array('in',$point_view_tag_idarr);
		$point_view_list = $point_view->where($point_view_map)->field('idpoint_view')->select();
		foreach($point_view_list as $key=>$val){
			$point_view_idarr[] = $val['idpoint_view'];
		}
		//荐股圈子
		$rec_stock_groups_list_map['rec_stocks_id'] = array('in',$idrec_stocks);
		$rec_stock_groups_list = $groups_list->where($rec_stock_groups_list_map)->field('groups_id')->select();
		foreach($rec_stock_groups_list as $rec_groups_key=>$rec_groups_val){
			$groups_idarr[] = $rec_groups_val['groups_id'];
		}
		//观点圈子
		$point_view_groups_list_map['point_view_id'] = array('in',$point_view_idarr);
		$point_view_groups_list = $groups_list->where($point_view_groups_list_map)->field('groups_id')->select();
		foreach($point_view_groups_list as $point_view_key=>$point_view_val){
			$groups_idarr[] = $point_view_val['groups_id'];
		}
		 $groups_idarr=array_unique($groups_idarr);
		 return $groups_idarr;
	}
    /*
	 * 2013-3-27上午04:07:08
	 * Jone
	 * 获取相关圈子的id数组
	**/
	public function get_groups_idarr ($map=''){
		$groups = M('Groups');
		$groups_list = $groups->where($map)->field('idgroups')->select();
		foreach($groups_list as $key=>$val){
			$groups_idarr[] = $val['idgroups'];
		}
		return $groups_idarr;

	}
	/*
	 * 2013-4-4上午08:31:51
	 * Jone
	 * 获取也人相关圈子的id数组
	**/
	public function get_user_groups_idarr ($gid){
		$user2groups = M('User2groups');
		$user2groups_map['user_id'] = $gid;
		$user2groups_list = $user2groups->where($user2groups_map)->field('groups_id')->select();
		foreach($user2groups_list as $key=>$val){
			$groups_idarr[] = $val['groups_id'];
		}
		return $groups_idarr;

	}
    /*
	 * 2013-3-27上午04:04:14
	 * Jone
	 * 圈子过滤搜索
	**/
	public function get_groups_list($groups_idarr,$start,$num){
		$groups = D('Groups');
		$stock = M('Stock');
		$groups_list = M('Groups_list');
		$map['idgroups'] = array('in',$groups_idarr);
		$map['lock'] = 1;
		$groups_arr = $groups->where($map)->relation(true)->limit($start,$num)->order('coco desc')->select($map);
		foreach($groups_arr as $key=>$val){
			$res_idarr = array();
			$groups_point_view_tag = array();
			//荐股数
			$rec_stocks_map['groups_id'] = $val['idgroups'];
			$rec_stocks_map['rec_stocks_id'] = array('neq','');
			$groups_arr[$key]['rec_stocks_count'] = $groups_list->where($rec_stocks_map)->count();
			$res_idarr = $this->get_group_recstock_tag_c($val['idgroups']);//圈子中荐股下的标签id
			//观点数
			$point_view_map['groups_id'] = $val['idgroups'];
			$point_view_map['point_view_id'] = array('neq','');
			$groups_arr[$key]['point_view_count'] = $groups_list->where($point_view_map)->count();
			$groups_point_view_tag = $this->get_group_pointview_tag_c($val['idgroups']);//圈子中观点下的标签id
			//关注数
			$groups_arr[$key]['attention_count'] = $this->groups_attention_count($val['idgroups']);
			//标签
			if(!empty($groups_point_view_tag) && !empty($res_idarr)){
				$groups_tag_list = array_merge($groups_point_view_tag,$res_idarr);
			}elseif(empty($groups_point_view_tag) && !empty($res_idarr)){
				$groups_tag_list = $res_idarr;
			}elseif(!empty($groups_point_view_tag) && empty($res_idarr)){
				$groups_tag_list = $groups_point_view_tag;
			}else{
				$groups_tag_list = array();
			}
			if(!empty($groups_tag_list)){
				$tag_idarr = array();
				foreach($groups_tag_list as $idkey=>$idval){
					$tag_idarr[] = $idval['stock_id'];
				}
				$stock_map['idstock'] = array('in',$tag_idarr);
				$stock_list = $stock->where($stock_map)->limit(5)->select();
				$groups_arr[$key]['Stock'] = $stock_list;
			}
		}
		return $groups_arr;
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
		$groups_list_point_view_list = $groups_list->where($groups_list_point_view_map)->field('point_view_id')->select();//圈子内的全部观点
		$groups_list_point_view_idarr = array();
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
	 * Jone
	 * 圈子的关注数
	**/
	public function groups_attention_count ($gid){
		$attention = M('Attention');
		$attention_map['id'] = $gid;
		$attention_map['table'] = 'groups';
		$attention_count = $attention->where($attention_map)->count();
		return $attention_count;
	}
}
?>