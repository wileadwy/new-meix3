<?php

class UserWidget extends Widget{

    public function render($data){
        if(!empty($data)){
        	if($data['part'] == 'head'){
				$user = M('User');
	        	$user_map['iduser'] = $data['id'];
	        	$user_map['lock'] = 1;
	        	$user_msg = $user->where($user_map)->field('iduser,name,information,avatar,style,accuracy,accuracy_v,accuracy_z')->find();
	        	$user_msg['style'] = explode(',',$user_msg['style']);
	        	$point_view = M('Point_view');
	        	$point_view_map['user_id'] = $user_msg['iduser'];
	        	$point_view_map['lock'] = 1;
	        	$user_msg['point_view_count'] = $point_view->where($point_view_map)->count();//观点数
	        	$rec_stocks = M('Rec_stocks');
	        	$rec_stocks_map['user_id'] = $user_msg['iduser'];
	        	$user_msg['rec_stocks_count'] = $rec_stocks->where($rec_stocks_map)->count();//荐股数
	        	//是否关注
	        	$attention_user = M('Attention');
				$map['id'] = $data['id'];
				$map['table'] = 'user';
				$map['user_id'] = $_SESSION['MEIX']['iduser'];
				$attention = $attention_user->where($map)->find();
				if(!empty($attention)){
					$user_msg['attention'] = 1;
				}else{
					$user_msg['attention'] = 0;
				}
                //订阅宝
                $user_info = M('User_info');
				$user_info_map['user_id'] = $data['id'];
                $user_info_map['field'] = 'money_subscriber_count';
                $user_info_dingyuebao = $user_info->where($user_info_map)->getField('value');
				//是否订阅

				$user_info_map['field'] = 'subscribe_user_id';
				$user_info_map['info'] = array('like','%,'.$data['id'].',%');
				$user_info_list = $user_info->where($user_info_map)->find();
				if(!empty($user_info_list)){
					$user_msg['subscribe'] = 1;
				}else{
					$user_msg['subscribe'] = 0;
				}
                $arr['user_info_dingyuebao'] = $user_info_dingyuebao;
	        	$arr['user'] = $user_msg;
	        	$content = $this->renderFile('head',$arr);
        	}elseif($data['part'] == 'right'){
				$right['me_attention'] = $this->me_attention($data['id']);
				$right['attention_me'] = $this->attention_me($data['id']);
				$right['user_id'] = $data['id'];
				$arr['right'] = $right;
				//dump($right);
        		$content = $this->renderFile('right',$arr);
        	}

        }else{
            $content = false;
        }
        return $content;
    }
    /*
	 * 2013-3-26下午01:55:51
	 * Jone
	 * 我关注的人
	**/
	public function me_attention ($user_id){
		$user = M('User');
		$attention_user = M('Attention');
		$map['table'] = 'user';
		$map['user_id'] = $user_id;
		$me_attention_list['count'] = $attention_user->where($map)->count();
		$me_attention_list['list'] = $attention_user->where($map)->limit(8)->select();
		foreach($me_attention_list['list'] as $key=>$val){
			$user_map['iduser'] =  $val['id'];
			$me_attention_list['list'][$key]['User'] = $user->where($user_map)->field('iduser,name,avatar,accuracy')->find();
		}
		$me_attention_list['list'] = $this->attention_me_count($me_attention_list['list']);
		return $me_attention_list;
	}
	/*
	 * 2013-3-26下午01:56:13
	 * Jone
	 * 关注我的人
	**/
	public function attention_me ($user_id){
		$user = M('User');
		$attention_user = M('Attention');
		$mapm['table'] = 'user';
		$mapm['id'] = $user_id;
		//$mapm['user_id'] = $_SESSION['MEIX']['iduser'];
		$attention_me_list['count'] = $attention_user->where($mapm)->count();
		$attention_me_list['list'] = $attention_user->where($mapm)->limit(8)->select();
		foreach($attention_me_list['list'] as $key=>$val){
			$user_map['iduser'] =  $val['user_id'];
			$attention_me_list['list'][$key]['User'] = $user->where($user_map)->field('iduser,name,avatar,accuracy')->find();
		}
		$attention_me_list['list'] = $this->attention_me_count($attention_me_list['list']);
		return $attention_me_list;
	}
	/*
	 * 2013-4-2上午06:55:38
	 * Jone
	 * 别关注数
	**/
	private function attention_me_count ($user_list){
		$attention = M('Attention');
		foreach($user_list as $key=>$val){
			$attention_map['table'] = 'user';
			$attention_map['id'] = $val['User']['iduser'];
			$user_list[$key]['attention_count'] = $attention->where($attention_map)->count();
		}
		return $user_list;
	}
}
?>