<?php
/**
 * 荐股
 * */
class StockAction extends AclAction {
    public function _initialize(){
        if(!isset($_SESSION['MEIX'])||empty($_SESSION['MEIX']['iduser'])){
            $_SESSION['MEIX']['JS']['url_login'] = '';
            R('Login/index');
            exit(0);
        }
    }
    /**
	 * echo
	 *
	**/
    public function index(){

		$this->display();
    }
    /*
	 * 2013-3-28上午04:01:34
	 * Jone
	 * 股票相关信息
	**/
	public function stock_msg ($stock_id){
		$stock = M('Stock');
		$map['idstock'] = $stock_id;
		$stock_msg = $stock->where($map)->find();
		$this->assign('stockfind',$stock_msg);
	}

    /**
	 *
	 * echo
	 * 标签内页_观点
	**/
    public function inside(){
    	$type = $_GET['type'];
        $stock_id = $_GET['id'];
		$point_view_list = $this->stock_point_view_c(0,$type,$stock_id,8);
		//$this->assign('point_view',$point_view_list);
        $this->assign('list',$point_view_list);
        $this->assign('stock_id',$stock_id);
        $this->assign('type',$type);
        $this->stock_msg($stock_id);
    	$this->display();
    }
    /*
	 * 2013-3-28上午03:40:43
	 * Jone
	 * 标签观点处理
	**/
	private function stock_point_view_c($start=0,$type='',$stock_id='',$num=8){
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
				$point_view_id_arr[] = $tag_val['point_view_id'];
			}
			$map['idpoint_view'] = array('in',$point_view_id_arr);
		}
        if(empty($num)){
            $num = 8;
        }
		$point_view_list = $point_view->where($map)->order('info_top desc')->limit($start,$num)->select();

        return $point_view_list;
    }
    /*
	 * 2013-3-28上午03:44:39
	 * Jone
	 * return_type
	**/
	public function stock_point_view_a(){
        if($this->isAjax()){
	    	$data = $_POST;
            if(!empty($data)){
                $list = $this->stock_point_view_c($data['start'],$data['type'],$data['stock_id'],$data['num']);
                $wdata['list'] = $list; $wdata['ifmode'] = 'point_view';
                $this->ajaxReturn(W('Rpcontent',$wdata,true),'成功',1);
            }else{
   	            $this->ajaxReturn(0,'失败',0);
            }
	    }else{
	    	exit();
	    }
    }
    /**
	 *
	 * echo
	 * 标签内页_荐股
	**/
    public function recstocks(){
    	$type = $_GET['type'];
        $stock_id = $_GET['id'];
        $rec_stocks_list = $this->stock_rec_stock_c(0,$type,$stock_id,8);
        $this->assign('list',$rec_stocks_list);
        $this->assign('stock_id',$stock_id);
        $this->stock_msg($stock_id);
        $this->assign('type',$type);
    	$this->display();
    }
    /*
	 * 2013-3-28上午04:09:07
	 * Jone
	 * 标签荐股处理
	**/
	public function stock_rec_stock_c ($start=0,$type='',$stock_id='',$num=8){
        $rec_stocks = M('Rec_stocks');
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
	 * 2013-3-28上午04:10:28
	 * Jone
	 * 标签荐股更多
	**/
	public function stock_rec_stock_a(){
        if($this->isAjax()){
	      $data = $_POST;
            if(!empty($data)){
                $list = $this->stock_rec_stock_c($data['start'],$data['type'],$data['stock_id'],$data['num']);
                $wdata['list'] = $list; $wdata['ifmode'] = 'rec_stocks';
                $this->ajaxReturn(W('Rpcontent',$wdata,true),'成功',1);
            }else{
   	            $this->ajaxReturn(0,'失败',0);
            }
	    }else{
	      exit();
	    }
    }
    /**
	 *
	 * echo
	 * 标签内页_圈子
	**/
    public function groups(){
    	$stock_id = $_GET['id'];
        $this->stock_msg($stock_id);
        $this->display();
    }
    /*
	 * 2013-3-25上午03:45:45
	 * Jone
	 * 右栏相关用户
	**/
	public function right_user (){
		$sid = $_GET['id'];
		$point_view_tag = M('Point_view_tag');
		$point_view_tag_map['stock_id'] = $sid;
		$point_view_taglist = $point_view_tag->where($point_view_tag_map)->field('point_view_id')->select();
		foreach($point_view_taglist as $key=>$val){
			$point_view_idarr[] = $val['point_view_id'];
		}
		$point_view = M('Point_view');
		$point_view_map['idpoint_view'] = array('in',$point_view_idarr);
		$point_view_list = $point_view->where($point_view_map)->field('user_id')->select();
		foreach($point_view_list as $key=>$val){
			$user_idarr[] = $val['user_id'];
		}
		$user = M('User');
		$user_map['iduser'] = array('in',$user_idarr);
		$user_list = $user->where($user_map)->field('iduser,name,avatar')->limit(8)->select();
		$this->assign('user_list',$user_list);
	}
    /*
	 * 2013-3-21上午06:20:09
	 * Jone
	 * 标签所关联的观点id,
	**/
	public function get_pointview_bytag_c ($stock_id){
        if(!empty($stock_id)){
        	$point_view_tag = M('Point_view_tag');
            $maptag['stock_id'] = $stock_id;
            $point_view_taglist = $point_view_tag->where($maptag)->group('point_view_id')->field('point_view_id')->select();
            foreach($point_view_taglist as $pvtvo){
               $pvid[] = $pvtvo['point_view_id'];
            }
            return $pvid;
        }else{
            return null;
        }
    }
    /*
	 * 2013-3-21上午06:20:09
	 * Jone
	 * 标签所关联的荐股id
	**/
	public function get_recstock_bytag_c ($stock_id){
		if(!empty($stock_id)){
			$rec_stock = M('Rec_stocks');
			$rec_stock_map['rate'] = array('neq','0');
			$rec_stock_map['stocks_id'] = $stock_id;
			$rec_stock_list = $rec_stock->where($rec_stock_map)->select();
			foreach($rec_stock_list as $key=>$val){
				$rec_stock_idarr[] = $val['idrec_stocks'];
			}
			return $rec_stock_idarr;
		}else{
            return null;
        }
	}


}

?>