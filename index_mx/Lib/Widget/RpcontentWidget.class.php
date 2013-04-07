
<?php

class RpcontentWidget extends Widget{
/**
 * $data['ifmode']=='mix'== 'point_view'== 'rec_stocks'
 * $data['rehtml']=='red'
 * */
    public function render($data){
        if(!empty($data)){
	        if($data['ifmode'] == 'mix'){
                $render['list'] = $this->ifdata($data['list']);
        	}elseif($data['ifmode'] == 'point_view'){
                $render['list'] = $this->point_view($data['list']);
        	}elseif($data['ifmode'] == 'rec_stocks'){
                $render['list'] = $this->rec_stocks($data['list']);
        	}elseif($data['ifmode'] == 'user_point_view'){
				$render['list'] = $this->point_view($data['list']);
        	}elseif($data['ifmode'] == 'user_rec_stocks'){
				$render['list'] = $this->rec_stocks($data['list']);
        	}elseif($data['ifmode'] == 'fmix'){
                $render['list'] = $this->fifdata($data['list']);
        	}elseif($data['ifmode'] == 'gmix'){
                $render['list'] = $this->gfdata($data['list']);
        	}else{
        	    //$render['list'] = $this->ifdata($data['list']);
        	}
        	if($data['rehtml']=='red'){
        	   $content = $this->renderFile('red',$render);
        	}elseif($data['rehtml']=='groups_view'){
				$content = $this->renderFile('groups_rp',$render);
        	}else{
        	   $content = $this->renderFile('rpcontent',$render);
        	}
        }else{
            $content = false;
        }
        return $content;
    }

    private function point_view($list){
        foreach($list as $key=>$vo){
            $list[$key]['USER'] = $this->user_id($vo['user_id']);
            $list[$key]['TAG'] = $this->point_view_tag($vo['idpoint_view']);
            $list[$key]['PV'] = 1;
        }

        return $list;
    }
    private function rec_stocks($list){
        foreach($list as $key=>$vo){
            $relist[$key] = $this->cycle($vo['idrec_stocks']);
            $relist[$key]['USER'] = $this->user_id($relist[$key]['user_id']);
            $relist[$key]['Stocknow'] = $this->tool_stock_now($relist[$key]['stocks_number']);
            $relist[$key]['RS'] = 1;
        }
        return $relist;
    }
    /*
	 * 2013-4-3上午03:19:34
	 * Jone
	 * 圈子列表弹出框观点荐股显示
	**/
	private function gfdata ($list){
        $point_view = M('Point_view');
        $map['lock'] = 1;
        foreach($list as $key=>$vo){
            if($vo['point_view_id'] != ''){
                $map['idpoint_view'] = $vo['point_view_id'];
                $relist[$key] = $point_view->where($map)->find();
                $relist[$key]['TAG'] = $this->point_view_tag($vo['point_view_id']);
                $relist[$key]['USER'] = $this->user_id($relist[$key]['TAG']['user_id']);
                $relist[$key]['PV'] = $vo;
                unset($map['idpoint_view']);
            }elseif($vo['rec_stocks_id'] != ''){
                $relist[$key] = $this->cycle($vo['rec_stocks_id']);
                $relist[$key]['USER'] = $this->user_id($relist[$key]['user_id']);
                $relist[$key]['Stocknow'] = $this->tool_stock_now($relist[$key]['stocks_number']);
                $relist[$key]['RS'] = $vo;
            }
        }
        return $relist;
    }
    private function ifdata($list){
        $point_view = M('Point_view');
        $groups_discuss = M('Groups_discuss');
        $map['lock'] = 1;
        foreach($list as $key=>$vo){
            if(!empty($vo['point_view_id'])){
                $map['idpoint_view'] = $vo['point_view_id'];
                $relist[$key] = $point_view->where($map)->find();
                $relist[$key]['TAG'] = $this->point_view_tag($vo['point_view_id']);
                $relist[$key]['USER'] = $this->user_id($relist[$key]['user_id']);
                $relist[$key]['PV'] = $vo;
                unset($map['idpoint_view']);
            }elseif(!empty($vo['rec_stocks_id'])){
                $relist[$key] = $this->cycle($vo['rec_stocks_id']);
                $relist[$key]['USER'] = $this->user_id($relist[$key]['user_id']);
                $relist[$key]['Stocknow'] = $this->tool_stock_now($relist[$key]['stocks_number']);
                $relist[$key]['RS'] = $vo;
            }elseif(!empty($vo['groups_discuss_id'])){
                $map['idgroups_discuss'] = $vo['groups_discuss_id'];
                $relist[$key] = $groups_discuss->where($map)->find();
                $relist[$key]['USER'] = $this->user_id($relist[$key]['user_id']);
                $relist[$key]['GD'] = $vo;
                unset($map['groups_discuss_id']);
            }
        }
        return $relist;
    }
    /*
	 * 2013-3-28上午03:16:56
	 * Jone
	 * 我的收藏
	**/
	public function fifdata ($list){
        $point_view = M('Point_view');
        $groups_discuss = M('Groups_discuss');
        $map['lock'] = 1;
        foreach($list as $key=>$vo){
            if($vo['table'] == 'point_view'){
                $map['idpoint_view'] = $vo['id'];
                $relist[$key] = $point_view->where($map)->find();
                $relist[$key]['TAG'] = $this->point_view_tag($vo['id']);
                $relist[$key]['USER'] = $this->user_id($relist[$key]['user_id']);
                $relist[$key]['PV'] = $vo;
                unset($map['idpoint_view']);
            }elseif($vo['table'] == 'rec_stocks'){
                $relist[$key] = $this->cycle($vo['id']);
                $relist[$key]['USER'] = $this->user_id($relist[$key]['user_id']);
                $relist[$key]['Stocknow'] = $this->tool_stock_now($relist[$key]['stocks_number']);
                $relist[$key]['RS'] = $vo;
            }
        }
        return $relist;
    }
    private function point_view_tag($idpoint_view){//观点相关标签
        $point_view_tag = M('Point_view_tag');
		$point_view_tag_map['point_view_id'] = $idpoint_view;
		return $point_view_tag->where($point_view_tag_map)->select();
    }
    private function cycle($rec_stocks_id){
        $rec_stocks = M('Rec_stocks');
        //$rsmap['lock'] = 1;
        $rsmap['idrec_stocks'] = $rec_stocks_id;
        $rec_stocksfind = $rec_stocks->where($rsmap)->find();//主条
        $content = $rec_stocksfind;
        $rec_stocks_cycle = M('Rec_stocks_cycle');
        $rscmap['idrec_stocks_cycle'] = $rec_stocksfind['rec_stocks_cycle_id'];
        $rec_stocks_cyclefind = $rec_stocks_cycle->where($rscmap)->find();//环
        $content['cycle'] = $rec_stocks_cyclefind;
        if($rec_stocksfind['status']==25){
            $lsmap['status'] = array('in',array(11,10,15,21,20));
        }elseif(($rec_stocksfind['status']==21)|($rec_stocksfind['status']==20)){
            $lsmap['status'] = array('in',array(11,10,15));
        }elseif(($rec_stocksfind['status']==15)|($rec_stocksfind['status']==10)){
            $lsmap['status'] = array('in',array(11));
        }else{
            $lsmap['status'] = array('in',array(1));
        }
        $lsmap['rec_stocks_cycle_id'] = $rec_stocksfind['rec_stocks_cycle_id'];
        $rec_stockslist = $rec_stocks->where($lsmap)->order('mktime desc')->select();//历史
        $content['lslist'] = $rec_stockslist;
        return $content;
    }
    private function user_id($id){
        $user = M('User');
        $map['iduser'] = $id;
        return $user->where($map)->field('iduser,name,avatar,information,style')->find();
    }
    private function tool_stock_now($numb=''){
        if($numb){
            $data = S('data/stock/'.$numb);
            if(empty($data)){
                $url="http://hq.sinajs.cn/list=".$numb;
                $string=file_get_contents($url);
                $string1 = explode('="',$string);
                $string_array = explode(',',$string1[1]);
                $arr_re['open'] = iconv("GB2312", "UTF-8", $string_array[1]);	//今日开盘价
                $arr_re['now'] = iconv("GB2312", "UTF-8", $string_array[3]);	//当前价格
                $arr_re['high'] = iconv("GB2312", "UTF-8", $string_array[4]);	//今日最高价
                $arr_re['low'] = iconv("GB2312", "UTF-8", $string_array[5]);	//今日最低价
                $arr_re['mktime'] = mktime();
                S('data/stock/'.$numb,$arr_re,300);
            }else{
                $arr_re = $data;
            }
            return $arr_re;
        }else{
            return false;
        }
    }




}
?>