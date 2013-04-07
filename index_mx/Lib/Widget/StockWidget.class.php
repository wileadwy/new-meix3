<?php

class StockWidget extends Widget{

    public function render($data){
    	if(!empty($data)){
    		$sid = $data['id'];
    		$arr['list'] = $this->get_stock_user($sid);
    		$content = $this->renderFile('stock_right',$arr);
    	}else{
			$content = false;
    	}
    	return $content;
    }
    /*
	 * 2013-3-27上午10:08:37
	 * Jone
	 * 标签相关用户
	**/
	public function get_stock_user ($sid){
		$stock = M('Stock');
		$user = M('User');
		$point_view = M('Point_view');
		$point_view_tag = M('Point_view_tag');
		$rec_stocks = M('Rec_stocks');
		//荐股
		$rec_stocks_map['stocks_id'] = $sid;
		$rec_stocks_list = $rec_stocks->where($rec_stocks_map)->field('user_id')->select();
		foreach($rec_stocks_list as $key=>$val){
			$user_idarr[] =  $val['user_id'];
		}
		//观点
		$point_view_tag_map['stock_id'] = $sid;
		$point_view_tag_list = $point_view_tag->where($point_view_tag_map)->select();
		foreach($point_view_tag_list as $key=>$val){
			$point_view_idsrr[] = $val['point_view_id'];
		}
		$point_view_map['idpoint_view'] = array('in',$point_view_idsrr);
		$point_view_list = $point_view->where($point_view_map)->field('user_id')->select();
		foreach($point_view_idsrr as $key=>$val){
			$user_idarr[] = $val['user_id'];
		}
		$user_idarr = array_unique($user_idarr);
		$user_map['iduser'] = array('in',$user_idarr);
		$user_map['lock'] = 1;
		$user_list = $user->where($user_map)->field('iduser,name,avatar,accuracy')->select();
		return $user_list;
	}
}
?>