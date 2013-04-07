<?php
class AdminAction extends AclAction{

    public function _initialize(){
        if(!isset($_SESSION['MEIX'])||empty($_SESSION['MEIX']['iduser'])||(($_SESSION['MEIX']['iduser']!=10)&&($_SESSION['MEIX']['iduser']!=25)&&($_SESSION['MEIX']['iduser']!=30))){
            //$_SESSION['MEIX']['JS']['url_login'] = '';
            $this->redirect('Index/index');
            exit(0);
        }
    }

    public function cnzz(){
        echo '<frame src="http://new.cnzz.com/v1/login.php?siteid=5153495" />';
        //$this->display();
    }

    public function userc24(){
        set_time_limit(0);
        if(!empty($_GET['n'])){
            $getnum = $_GET['n'];
        }else{
            $getnum = 3;
        }
        if(!empty($_GET['s'])){
            $getsleep = $_GET['s'];
        }else{
            $getsleep = 3;
        }

        for($i=0;$i<$getnum;$i++){
            $iecho = R('Userc/rec_stock_clear',array(13));
            echo $i.']all='.$iecho['all'].'---shibai='.$iecho['shibai'].'---chenggong='.$iecho['chenggong'].'<br />';
            ob_flush(); //此句不能少
                flush();
            sleep($getsleep);
        }
        ob_end_flush();

    }
    public function rec(){
        /*
        @ob_start();
        //ob_end_clean();
        @ob_implicit_flush(true);
        //set_time_limit(0);
        for($i=0;$i<5;$i++)
        {
            echo $i.'...';
            @flush();
            @ob_flush();

            sleep(1);
            @ob_end_flush();
        }
        */
        for ($i=10; $i>2; $i--)
        {
            echo $i.'<br />';
            ob_flush(); //此句不能少
            flush();
            sleep(1);
            //
        }
        ob_end_flush();


     }
     /*
	 * 2013-4-3上午03:40:46
	 * Jone
	 * meix管理员消息提示
	**/
	public function all_notice (){
        $notice = M('Notice');
        $map['lock'] = 1;
        $map['type'] = array('in',array('application_groups','message','invite_groups'));
        $list = $notice->where($map)->order('mktime desc')->select();
        $groups = D('Groups');
        $user = D('User');
        foreach($list as $key=>$vo){
            if(!empty($vo['a_domain'])){
                $groupsmap['idgroups'] = $vo['a_domain'];
                $list[$key]['Groups'] = $groups->where($groupsmap)->relation(true)->find();
            }
            $user_map['iduser'] = $vo['user_id'];
            $list[$key]['User'] = $user->where($user_map)->find();
        }
        $this->assign('list',$list);
        $this->display();
	}
	/*
	 * 2013-4-3上午04:36:20
	 * Jone
	 * 荐股
	**/
	public function rp (){
		if($_GET['type'] == 'pv' ){
			$point_view_list = $this->point_view_c();
	        $this->assign('list',$point_view_list);
		}elseif($_GET['type'] == 'rs' ){
	        $rec_stocks_list = $this->rec_stocks_c();
	        $this->assign('list',$rec_stocks_list);
	    }
		$this->assign('type',$_GET['type']);
		$this->display();
	}
	 private function rec_stocks_c(){
	 	$rec_stocks = M('Rec_stocks');
	 	$rec_map['lock'] = 1;
		$rec_stocks_list = $rec_stocks->where($rec_map)->order('mktime desc')->limit(50)->select();
		return $rec_stocks_list;
    }

	private function point_view_c(){
		$point_view = M('Point_view');
		$map['lock'] = 1;
		$point_view_list = $point_view->where($map)->order('mktime desc')->limit(50)->select();
        return $point_view_list;
    }
    /*
	 * 2013-4-3上午04:54:36
	 * Jone
	 * 圈子建立
	**/
	public function groups (){
		$groups = M('Groups');
		$groups_map['lock'] = 1;
		$groups_list = $groups->where($groups_map)->order('mktime desc')->select();
		$user = M('User');
		foreach($groups_list as $key=>$val){
			$user_map['iduser'] = $val['user_id'];
			$groups_list[$key]['User'] = $user->where($user_map)->field('name,iduser')->find();
		}
		$this->assign('list',$groups_list);
		$this->display();
	}
    
    /**
     * 股票改候列表
     * */
    public function stock_demo(){
        $stock = M('Stock');
        if(!empty($_GET['p'])){ $page = $_GET['p']; }else{ $page = 1; }
        if(!empty($_GET['l'])){ $limit = $_GET['l']; }else{ $limit = 100; }
        if(!empty($_GET['s'])&&$_GET['s']==1){ $map['shownumbertype']=1; }
        $stocklist = $stock->where($map)->page($page)->limit($limit)->select();
        header("Content-type: text/html; charset=utf-8"); 
        $ps = '<table width="100%"><thead style="background-color: #aaa;"  align="center">
                <td width="50">ID</td>
                <td width="70">名称</td>
                <td>股票号</td>
                <td>全拼</td>
                <td>简拼</td>
                <td>层级</td>
                <td>类型</td>
                <td>父级ID</td>
                <td><a href="'.__APP__.'/Admin/stock_demo/p/'.($page-1).'/l/'.$limit.'/s/1" > 1为沪深</a></td>
                <td>辅助链接</td>
                <td>标记</td>
                <td>操作</td>
               </thead>';
        foreach($stocklist as $key=>$vo){
            if($key%2){$stype = 'style="background-color: #eeeeee;"'; }else{$stype = '';}
            $ps .= '<tr align="center" '.$stype.' >
                        <td>'.$vo['idstock'].'</td>
                        <td>'.$vo['showname'].'</td>
                        <td>'.$vo['shownumberb'].$vo['shownumber'].'</td>
                        <td>'.$vo['name_pinyin'].'</td>
                        <td>'.$vo['name_jianpin'].'</td>
                        <td>'.$vo['level'].'</td>
                        <td>'.$vo['type'].'</td>
                        <td>'.$vo['parent_id'].'</td>
                        <td>'.$vo['shownumbertype'].'</td>
                        <td><a target="_blank" href="http://hq.sinajs.cn/list='.$vo['shownumberb'].$vo['shownumber'].'">'.$vo['shownumberb'].$vo['shownumber'].'</a></td>
                        <td>'.$vo['lock'].'</td>
                        <td><a target="_blank" href="'.__APP__.'/Admin/stock_demo_g/id/'.$vo['idstock'].'">加入到删除列表</a></td>
                        </tr>';
        }
        $ps .= '</table>';
        if($page>1){ $ps .= '<a href="'.__APP__.'/Admin/stock_demo/p/'.($page-1).'/l/'.$limit.'/s/'.$map['shownumbertype'].'">上一页</a>  '; }
        if($page<(10890/$limit)){ $ps .= '  <a href="'.__APP__.'/Admin/stock_demo/p/'.($page+1).'/l/'.$limit.'/s/'.$map['shownumbertype'].'">下一页</a>'; }
        
        echo $ps;
        //dump($stocklist);
    }
    public function stock_demo_g(){
        $data = F('Stock/delete');
        $stock = M('Stock');
        if(!empty($_GET['id'])){ 
            if(empty($data[$_GET['id']])){
                $data[$_GET['id']] = $_GET['id'];
            }
        }
        $map['idstock'] = array('in',$data);
        $stocklist = $stock->where($map)->select();
        header("Content-type: text/html; charset=utf-8"); 
        $ps = '<table width="100%"><thead style="background-color: #aaa;"  align="center">
                <td width="50">ID</td>
                <td width="70">名称</td>
                <td>股票号</td>
                <td>全拼</td>
                <td>简拼</td>
                <td>层级</td>
                <td>类型</td>
                <td>父级ID</td>
                <td>1为沪深</td>
                
                <td>标记</td>
               </thead>';
        foreach($stocklist as $key=>$vo){
            if($key%2){$stype = 'style="background-color: #eeeeee;"'; }else{$stype = '';}
            $ps .= '<tr align="center" '.$stype.' >
                        <td>'.$vo['idstock'].'</td>
                        <td>'.$vo['showname'].'</td>
                        <td>'.$vo['shownumberb'].$vo['shownumber'].'</td>
                        <td>'.$vo['name_pinyin'].'</td>
                        <td>'.$vo['name_jianpin'].'</td>
                        <td>'.$vo['level'].'</td>
                        <td>'.$vo['type'].'</td>
                        <td>'.$vo['parent_id'].'</td>
                        <td>'.$vo['shownumbertype'].'</td>
                        
                        <td>'.$vo['lock'].'</td>
                        </tr>';
        }
        $ps .= '</table>';
        F('Stock/delete',$data);
        echo $ps;
    }
    
}
?>