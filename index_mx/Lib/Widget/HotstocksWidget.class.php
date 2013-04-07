<?php

class HotstocksWidget extends Widget {

	public function render($data){
		if(!empty($data)){
			if($data['table'] == 'user_right'){
				$uid = $data['id'];
				$user_tag_list_sort = $this->hot_user_tag_c($uid);
				$right['list'] = $this->hot_tag_toshow_c($user_tag_list_sort,9);

			}elseif($data['table'] == 'point_view_right'){
				$point_view_tag_list_sort = $this->hot_point_view_tag_c();
				$right['list'] = $this->hot_tag_toshow_c($point_view_tag_list_sort,9);
			}elseif($data['table'] == 'rec_stocks_right'){
				$rec_stocks_tag_list_sort = $this->hot_rec_stocks_tag_c();
				$right['list'] = $this->hot_tag_toshow_c($rec_stocks_tag_list_sort,9);
			}elseif($data['table'] == 'groups_right'){
				$groups_tag_list_sort = $this->hot_groups_tag_c();
				$right['list'] = $this->hot_tag_toshow_c($groups_tag_list_sort,9);
			}
			$content = $this->renderFile('hotstocks',$right);
		}else{
			$content = false;
		}
		return $content;
    }
    /*
	 * 2013-3-18上午08:33:24
	 * Jone
	 * 荐股右栏所有热门标签
	**/
	public function hot_point_view_tag_c ($idarr=''){
		$point_view_tag = M('Point_view_tag');
		$tag_map['lock'] = '1';
		if(!empty($idarr)){
			$tag_map['point_view_id'] = array('in',$idarr);
		}
		$point_view_tag_list = $point_view_tag->where($tag_map)->group('stock_id')->select();
		foreach($point_view_tag_list as $tag_key=>$tag_val){
			$tag_count_map['stock_id'] = $tag_val['stock_id'];
			$tag_count_map['lock'] = '1';
            $point_view_tag_list_sort[$tag_count_map['stock_id']] =  $point_view_tag->where($tag_count_map)->count();
		}
        return $point_view_tag_list_sort;
	}
	/*
	 * 2013-3-27上午03:03:58
	 * Jone
	 * 荐股右栏所有热门标签
	**/
	public function hot_rec_stocks_tag_c ($idarr=''){
		$rec_stocks = M('Rec_stocks');
		if(!empty($idarr)){
			$tag_map['idpoint_view'] = array('in',$idarr);
		}
		$rec_stocks_list = $rec_stocks->group('rec_stocks_cycle_id')->field('stocks_id')->select();
		foreach($rec_stocks_list as $key=>$val){
			$map['stocks_id'] = $val['stocks_id'];
			$rec_stockstag_list_sort[$val['stocks_id']] = $rec_stocks->where($map)->group('stocks_id')->count();
		}
		return $rec_stockstag_list_sort;
	}
	/*
	 * 2013-3-27上午03:19:10
	 * Jone
	 * 圈子右栏所有热门标签
	**/
	public function hot_groups_tag_c (){
		$groups_list = M('Groups_list');
		$groups_list_list = $groups_list->select();
		foreach($groups_list_list as $key=>$val){
			if($val['rec_stocks_id'] !='' ){
				$rec_stocks_idarr[] = $val['rec_stocks_id'];
			}elseif($val['point_view_id'] !='' ){
				$point_view_idarr[] = $val['point_view_id'];
			}
		}
		$point_view_tag_list_sort = $this->hot_point_view_tag_c($point_view_idarr);
		$rec_stocks_tag_list_sort = $this->hot_rec_stocks_tag_c($rec_stocks_idarr);
        if(!empty($point_view_tag_list_sort)){
            foreach($rec_stocks_tag_list_sort as $rkey=>$rvo){
                if(!empty($point_view_tag_list_sort[$rkey])){
                    $point_view_tag_list_sort[$rkey]+=$rvo;
                }else{
                    $point_view_tag_list_sort[$rkey] = $rvo;
                }
            }
        }else{
            $point_view_tag_list_sort = $rec_stocks_tag_list_sort;
        }
        return $point_view_tag_list_sort;
	}
	/*
	 * 2013-3-27上午03:52:59
	 * Jone
	 * 个人所有热门标签
	**/
	public function hot_user_tag_c ($uid){
		$rec_stocks = M('Rec_stocks');
		$point_view = M('Point_view');
		$rec_stocks_map['user_id'] = $uid;
		$rec_stocks_list = $rec_stocks->where($rec_stocks_map)->field('idrec_stocks')->select();
		foreach($rec_stocks_list as $key=>$val){
			$rec_stocks_idarr[] = $val['idrec_stocks'];
		}
		$point_view_map['user_id'] = $uid;
		$point_view_list = $point_view->where($point_view_map)->field('idpoint_view')->select();
		foreach($point_view_list as $key=>$val){
			$point_view_idarr[] = $val['idpoint_view'];
		}
		$point_view_tag_list_sort = $this->hot_point_view_tag_c($point_view_idarr);
		$rec_stocks_tag_list_sort = $this->hot_rec_stocks_tag_c($rec_stocks_idarr);
		foreach($rec_stocks_tag_list_sort as $rkey=>$rvo){
            if(!empty($point_view_tag_list_sort[$rkey])){
                $point_view_tag_list_sort[$rkey]+=$rvo;
            }else{
                $point_view_tag_list_sort[$rkey] = $rvo;
            }
        }
        return $point_view_tag_list_sort;
	}
    /*
	 * 2013-3-18上午08:33:24
	 * Jone
	 * 右栏热门标签显示
	**/
	public function hot_tag_toshow_c($point_view_tag_list_sort,$num=7){
		arsort($point_view_tag_list_sort);
        $stock = M('Stock');
        $mapstock['lock'] = 1;
        $ij = 0;
        foreach($point_view_tag_list_sort as $pvkey=>$pvvo){
            if($ij>$num){
            	break;
            }else{
                $ij++;
            }
            $mapstock['idstock'] = $pvkey;
            $stockfind = $stock->where($mapstock)->find();
            //dump($stockfind);
            if($stockfind){
                $stockfind['count'] = $pvvo;
                $stocklist[] = $stockfind;
            }
        }
        //dump($stocklist);
        return $stocklist;
    }
}
?>