<?php
class ToolAction extends AclAction {
    public function _initialize(){
        if(!isset($_SESSION['MEIX'])||empty($_SESSION['MEIX']['iduser'])){
            $_SESSION['MEIX']['JS']['url_login'] = '';
            R('Login/index');
            exit(0);
        }
    }
    /*** st:股票相关* */
	/*
	 * 2013-3-14上午07:38:25
	 * Echo
	 * 获取当时股票信息
	**/
    public function tool_stock_now_a(){
        if($this->isAjax()){
            if(!empty($_POST['stock'])){
                $stock = M('Stock');
                $map['idstock'] = $_POST['stock'];
                $stockfind = $stock->where($map)->find();
                if($stockfind['shownumbertype']==3){
                    $arr_re['now'] = $this->tool_stock_now_m($stockfind['shownumber']);
                    $infoajax = 'dollar';
                }else{
                    $arr_re = $this->tool_stock_now($stockfind['shownumberb'].$stockfind['shownumber']);
                    $infoajax = 'yuan';
                }
                if(!empty($arr_re['now'])){
                    $this->ajaxReturn($arr_re['now'],$infoajax,1);
                    //$this->ajaxReturn(randNumber(0.01,100.00),'',1);
                }else{
                    $this->ajaxReturn('','暂无价格',0);
                }
            }else{
                $this->ajaxReturn('','股票不存在',0);
            }
        }
    }
    public function tool_stock_now_c($id=''){
        if($id){
            $stock = M('Stock');
                $map['idstock'] = $id;
                $stockfind = $stock->where($map)->find();
                if($stockfind['shownumbertype']==3){
                    $arr_re['now'] = ToolAction::tool_stock_now_m($stockfind['shownumber']);
                    $infoajax = 'dollar';
                }else{
                    $arr_re = ToolAction::tool_stock_now($stockfind['shownumberb'].$stockfind['shownumber']);
                    $infoajax = 'yuan';
                }
            return array($arr_re['now'],$infoajax);
        }
    }
    private function tool_stock_now_m($numb=''){//美股ID
        if($numb){
            $url = "http://www.imeigu.com/".$numb;
                $string=$this->cut_p($url);
                preg_match_all("'class=\"cGreen\">([\s\S]*?)</span>'isx",$string,$mat[1]);
                preg_match_all("'class=\"cRed\">([\s\S]*?)</span>'isx",$string,$mat[2]);
                //preg_match_all("/<h2[^>]*>[\s\S]*?<span[^>]*class=\"cRed\">([\s\S]*?)<\/span>[\s\S]*?<\/h2>/isx",$string,$mat[3]);
                $matarr = array_merge($mat[1][1],$mat[2][1]);
                foreach($matarr as $vo){
                    $voif = explode('$',$vo);
                    if(!empty($voif[1])){
                        $reok = $voif[1];
                        break;
                    }
                }
                if(!empty($reok)){
                    return $reok;
                }else{
                    return false;
                }
        }else{
            return false;
        }
    }
    public function tool_stock_now($numb=''){
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
    /*
	 * 2013-3-14上午07:38:25
	 * Echo
	 * 获取区间股票信息
	 * $num_stock 股票号
	 * $startdate 开始时间
	 * $enddate 结束时间
	**/
    public function tool_stock_t2t($num_stock,$startdatey='',$enddatey=''){
        if(!empty($startdate)){ $startdate = Date('Y-m-d-H-i-s',$startdatey); }else{ return false; }
        if(!empty($enddate)){ $enddate = Date('Y-m-d-H-i-s',$enddatey); }else{ return false; }
        $url = 'http://market.finance.sina.com.cn/pricehis.php?symbol='.$num_stock.'&startdate='.$startdate.'&enddate='.$enddate.'';
        $string=$this->cut_p($url);
        //http://market.finance.sina.com.cn/pricehis.php?symbol=sz000048&startdate=2013-01-25-09-00-00&enddate=2013-02-01-10-00-00
        //preg_match_all("/<tr[^>]*>[\s\S]*?<\/tr>/isx",$string,$matches1);
        //foreach($matches1[0] as $vo){
            //preg_match_all("/<td[^>]*>([\s\S]*?)<\/td>/isx",$vo,$vp);
            //$tout[] = $vp[1][0];
            //$re['all'][] = $vp;
        //}
        preg_match_all("/<tr[^>]*>[\s\S]*?<td[^>]*>([\s\S]*?)<\/td>[\s\S]*?<\/tr>/isx",$string,$matchesall);
        //$re['all'] = $matchesall[1];
        $re['high'] = $matchesall[1][1];	//最高值
        $re['low'] = end($matchesall[1]);	//最低值
        $re['enddate'] = $enddate;			//开始时间
        $re['startdate'] = $startdate;		//结束时间
        return $re;

    }
    /*
	 * 2013-3-14上午07:38:25
	 * Echo
	 * 处理curl函数
	**/
    private function cut_p($url){
        //$url = "http://quote.eastmoney.com/stocklist.html";
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        //在需要用户检测的网页里需要增加下面两行
        //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        //curl_setopt($ch, CURLOPT_USERPWD, US_NAME.":".US_PWD);
        $contents = curl_exec($ch);
        curl_close($ch);
        return $contents;
    }

 	/*** ed:股票相关* */
    /*** st:股票搜索* */
    /**
     * Ajax搜索股票和后台定义标签
     * @$_POST['code']，搜索内容，必填
     * @$_POST['pagenum']，翻页，默认：1
     * @$_POST['limitnum']，一页个数，默认：7
     * @$_POST['levelin']，搜索级别，默认：31,41
     * return : AjaxReturn ; array()
     * */
    public function tool_search_stock_a(){
        if($this->isAjax()){
            $this->acl_input3();
            //header("Content-type: text/html; charset=utf-8");
            //$_POST['pagenum'] = $_GET['pa'];
            //$codes = trim($_GET['st']);
            $codes = trim($_POST['code']);
            if(!empty($_POST['type'])&&$_POST['type']=='hs'){ ///屏蔽美股
                $map['shownumbertype'] = 1;
                $map2['shownumbertype'] = 1;
            }
            if(!empty($_POST['levelin'])){ $levelin = $_POST['levelin']; }else{ $levelin = '31,41'; }///31是股票，41是后台定义标签
            if(!empty($_POST['limitnum'])){ $limitnum = $_POST['limitnum']; }else{ $limitnum = 10; }//第次个数
            if(!empty($_POST['pagenum'])){ $pagenum = $_POST['pagenum']; }else{ $pagenum = 1; }//页数
            if(!empty($_POST['notin'])){ $notin = explode(',',$_POST['notin']); }//逼出
            if(!empty($codes)||$codes==0){
                $stock = M('Stock');
                $map['name_jianpin|name_pinyin|showname|shownumber'] = array('like','%'.$codes.'%');
                //$map['name_pinyin'] = array('like','%'.$codes.'%');
                //$map['showname'] = array('like','%'.$codes.'%');
                //$map['_logic'] = 'OR';
                $map['level'] = array('in',$levelin);///31是股票，41是后台定义标签
                if(!empty($notin)){ $map['idstock'] = array('not in',$notin); }
                $map['lock'] = 1;
                $stocklist = $stock->where($map)->limit($limitnum)->page($pagenum)->order('idstock asc')->select();
                if(!$stocklist||(count($stocklist)<$limitnum)){
                    $map2['shownumber'] = array('like','%'.$codes.'%');
                    $map2['level'] = array('in',$levelin);///31是股票，41是后台定义标签
                    foreach($stocklist as $svo){
                        $notin[] = $svo['idstock'];
                    }
                    if(!empty($notin)){ $map2['idstock'] = array('not in',$notin); }
                    $map2['lock'] = 1;
                    $stocklist2 = $stock->where($map2)->limit($limitnum-count($stocklist))->select();
                    if($stocklist2){
                        $stocklist = array_merge($stocklist2,$stocklist);
                    }
                }
                if($stocklist){
                    $this->ajaxReturn($stocklist,'',1);
                }else{
                    $this->ajaxReturn('','查无结果',0);
                }
                //dump($stocklist);
            }else{
                $this->ajaxReturn('','搜索错误',0);
            }
        }
    }
    /*** ed:股票搜索* */
    /**
     * 搜索人
     * */
    public function tool_search_user_a(){
        if($this->isAjax()){
            $this->acl_input3();
            $name = trim($_POST['name']);
            if(!empty($_POST['gid'])){
                $user2groups = M('User2groups');
                $gmap['groups_id'] = $_POST['gid'];
                $user2groupslist = $user2groups->where($gmap)->select();
                foreach($user2groupslist as $uglvo){
                    $notin[] = $uglvo['user_id'];
                }
                $groups_invite = M('Groups_invite');
                $gimap['type'] = 'invite_group';
                $gimap['table'] = 'user';
                $gimap['groups_id'] = $_POST['gid'];
                $groups_invitelist = $groups_invite->where($gimap)->select();
                foreach($groups_invitelist as $givo){
                    //$notin[] = $givo['id'];
                }
            }
            if(!empty($name)){
                $user = M('User');
                $map['name|name_pinyin|name_jianpin'] = array('like','%'.$name.'%');
                if(!empty($_POST['limitnum'])){ $limitnum = $_POST['limitnum']; }else{ $limitnum = 10; }//第次个数
                if(!empty($_POST['pagenum'])){ $pagenum = $_POST['pagenum']; }else{ $pagenum = 1; }//页数
                if(!empty($notin)){ $map['iduser'] = array('not in',$notin); }
                $userlist = $user->where($map)->field('iduser,name,avatar')->limit($limitnum)->page($pagenum)->select();
                if($userlist){
                    $this->ajaxReturn($userlist,'',1);
                }else{
                    $this->ajaxReturn('','查无结果',0);
                }
                //dump($stocklist);
            }else{
                $this->ajaxReturn('','搜索错误',0);
            }
        }
    }
    /**
     * QQ发图片链接微博
     * */
    public function qqadd_pic_url($coutent='http://www.icome.cc'){
        if($this->isAjax()){
            $this->qqapi();
            $params = array(
                    'content' => $coutent,//微博内容（若在此处@好友，需正确填写好友的微博账号，而非昵称），不超过140字
                    'pic_url' => 'http://mat1.gtimg.com/www/iskin960/qqcomlogo.png'
                );
            $r = Tencent::api('t/add_pic_url', $params, 'POST');
            $info = (json_decode($r, true));
            //dump($info);
            if($info['msg']=='ok'){//ok
                return true;
            }else{
                return false;
            }
        }
    }


    /**
     * 讨论
     * */
    public function discuss_a(){
        if($this->isAjax()){
            $this->acl_input3();
            $add['d_table'] = $_POST['d_table'];
            $add['d_id'] = $_POST['d_id'];
            $add['d_type'] = $_POST['d_type'];
            $add['name'] = $_SESSION['MEIX']['name'];
            $add['avatar'] = $_SESSION['MEIX']['avatar'];
            $add['user_id'] = $_SESSION['MEIX']['iduser'];
            $add['subject'] = $_POST['subject'];
            $add['content'] = $_POST['content'];
            $add['content_t'] = $this->inputkbr($this->msubstr_txt($add['content']));
            $add['right_read'] = $_POST['right_read'];//all   callfriend
            $add['right_reply'] = $_POST['right_reply'];//all    callfriend
            $add['info_message_count'] = 0;
            $add['lock'] = 1;
            if(!empty($add['d_table'])&&!empty($add['d_id'])&&!empty($add['d_type'])&&!empty($add['user_id'])){
                $discuss = M('Discuss');
                $discussone = $discuss->where($add)->find();
            }
            if(!$discussone){
                $add['mktime'] = mktime();
                $addok = $discuss->add($add);
                $add['iddiscuss'] = $discuss->getLastInsID();
            }
            if($addok){
                if($_POST['ynchronous']=='y'){
                    if(!empty($_SESSION['MEIX']['t_openid'])){

                    }elseif(!empty($_SESSION['MEIX']['s_id'])){

                    }
                }
                $this->stock_info_c($add,'discuss');//补主表数
                $this->ajaxReturn('','成功',1);
            }else{
                $this->ajaxReturn('','',0);
            }
        }

    }
    /**
     * 顶踩
     * */
/*
    public function toporpoor_a(){
        if($this->isAjax()){
            $map['d_table'] = $_POST['table'];
            $map['d_id'] = $_POST['id'];
            $map['toporpoor'] = $_POST['toporpoor'];//top   poor
            $map['user_id'] = $_SESSION['MEIX']['iduser'];
            if(!empty($map['d_table'])&&!empty($map['d_id'])&&!empty($map['user_id'])&&!empty($map['toporpoor'])){
                $toporpoor = M('Toporpoor');
                $toporpoorhave = $toporpoor->where($map)->find();
                if(!$toporpoorhave){
                    $mapa['d_table'] = $_POST['table'];
                    $mapa['d_id'] = $_POST['id'];
                    $mapa['user_id'] = $_SESSION['MEIX']['iduser'];
                    $toporpoorhave = $toporpoor->where($mapa)->find();
                    if($toporpoorhave){
                        //有反意见
                        $toporpoor->where($mapa)->delete();
                        if($map['toporpoor']=='poor'){
                            $mapa['toporpoor'] = 'top';
                        }else{
                            $mapa['toporpoor'] = 'poor';
                        }
                        $this->toporpoor_set($mapa,'setDec');
                        $addok = $toporpoor->add($map);
                        $mesdata = $this->toporpoor_set($map,'setInc');
                        $mes = '反面意见';
                    }else{
                        $addok = $toporpoor->add($map);
                        $mesdata = $this->toporpoor_set($map,'setInc');
                        $mes = '打分';
                    }
                }else{
                    //取消
                    $mes = '取消';
                    $addok = $toporpoor->where($map)->delete();
                    $mesdata = $this->toporpoor_set($map,'setDec');
                }
            }
            if($addok){
                $this->ajaxReturn(array('top'=>$mesdata['info_top'],'poor'=>$mesdata['info_poor']),$mes.'成功',1);
            }else{
                $this->ajaxReturn('',$mes.'失败',1);
            }
        }
    }
*/
    public function toporpoor_a(){
        if($this->isAjax()){
            $map['d_table'] = $_POST['table'];
            $map['d_id'] = $_POST['id'];
            $map['toporpoor'] = $_POST['toporpoor'];//top   poor
            $map['user_id'] = $_SESSION['MEIX']['iduser'];
            if(!empty($map['d_table'])&&!empty($map['d_id'])&&!empty($map['user_id'])&&!empty($map['toporpoor'])){
                $toporpoor = M('Toporpoor');
                $toporpoorhave = $toporpoor->where($map)->find();
                if(!$toporpoorhave){
                    $mapa['d_table'] = $_POST['table'];
                    $mapa['d_id'] = $_POST['id'];
                    $mapa['user_id'] = $_SESSION['MEIX']['iduser'];
                    $toporpoorhaveq = $toporpoor->where($mapa)->find();
                    if(!$toporpoorhaveq){
                        $addok = $toporpoor->add($map);
                        $mesdata = $this->toporpoor_set($map,'setInc');
                    }
                }
            }

            if($addok){
                $this->ajaxReturn($mesdata['info_'.$map['toporpoor']],'成功',1);
            }else{
                $this->ajaxReturn('',$mes.'失败',0);
            }
        }
    }
    private function toporpoor_set($map,$setc='setInc'){
        if($map){
            $modelsql = M(ucfirst($map['d_table']));
            $sqlmap['id'.$map['d_table']] = $map['d_id'];
            if($setc=='setInc'){
                $modelsql->where($sqlmap)->setInc('info_'.$map['toporpoor']); // 用户癿积分加1
            }else{
                $modelsql->where($sqlmap)->setDec('info_'.$map['toporpoor']); // 用户癿积分减1

            }
            $list = $modelsql->where($sqlmap)->find();
            return $list;
        }else{
            return false;
        }
    }
    ////////////////////顶踩End
    /**
     * 留言
     * */
    public function message_a(){
        if($this->isAjax()){
            $this->acl_input3();
            $add['table'] = $_POST['table'];
            $add['id'] = $_POST['id'];
            $add['name'] = $_SESSION['MEIX']['name'];
            $add['avatar'] = $_SESSION['MEIX']['avatar'];
            $add['user_id'] = $_SESSION['MEIX']['iduser'];
            if(!empty($_POST['re_user'])){
                $user = M('User');
                $reuserfind = $user->where('iduser='.$_POST['re_user'])->find();
                $add['is_reply'] = 1;
                $add['re_name'] = $reuserfind['name'];
                $add['re_avatar'] = $reuserfind['avatar'];
                $add['re_user_id'] = $reuserfind['iduser'];
            }
            $add['content'] = $_POST['content'];
            $add['info_top'] = 0;
            $add['info_poor'] = 0;
            $add['info_message_count'] = 0;
            $add['lock'] = 1;
            if(!empty($add['table'])&&!empty($add['id'])&&!empty($add['user_id'])){
                $add['mktime'] = mktime();
                $message = M('Message');
                $addok = $message->add($add);
                $add['idmessage'] = $message->getLastInsID();
            }
            if($addok){
                $noticemap = $add;
                $noticemap = array('id'=>$add['id'],'table'=>$add['table'],'name'=>$add['name'],'content'=>$this->inputkbr($this->msubstr_txt($add['content'],0,16)));
                $this->notice_message_c($noticemap);///提示
                $messagecount = $this->message_count($add['id'],$add['table']);
                if($_POST['ynchronous']=='y'){
                    if(!empty($_SESSION['MEIX']['t_openid'])){

                    }elseif(!empty($_SESSION['MEIX']['s_id'])){

                    }
                }
                $message_map['idmessage'] = $add['idmessage'];
                $message_msg = $message->where($message_map)->find();
                $message_msg['once'] = 1;
                $content = W('Message',$message_msg,true);
                $this->ajaxReturn($content,$messagecount,1);
            }else{
                $this->ajaxReturn('','',0);
            }
        }

    }

    /**
     * 留言后补info_message_count
     * */

    private function message_count($id,$table){
        $message = M('Message');
        $map['table'] = $table;
        $map['id'] = $id;
        $map['lock'] = 1;
        $messagecount = $message->where($map)->count();
        if($messagecount){
            $modelsql = M(ucfirst($map['table']));
            $sqlmap['id'.$map['table']] = $map['id'];
            $sqldata['info_message_count'] = $messagecount;
            $modelsql->where($sqlmap)->save($sqldata);
            return $messagecount;
        }
    }
    /**
     * 回复后补info_message_count
     * *//*
    private function messagereply_count($id,$table){
        $messagereply = M('Messagereply');
        $map['message_id'] = $id;
        $map['lock'] = 1;
        $messagereplycount = $messagereply->where($map)->count();
        if($messagereplycount){
            $modelsql = M('Message');
            $sqlmap['idmessage'] = $map['message_id'];
            $sqldata['info_message_count'] = $messagereplycount;
            $modelsql->where($sqlmap)->save($sqldata);
        }
    }*/
    /**
     * 留言回复
     * */
     /*
    public function messagereply_a(){
        if($this->isAjax()){
            $this->acl_input3();
            $user = M('User');
            $mapuser['iduser'] = $_POST['to_id'];
            $userfind = $user->where($mapuser)->find();
            if($userfind){
                $add['re_name'] = $userfind['name'];
                $add['re_avatar'] = $userfind['avatar'];
                $add['re_user_id'] = $userfind['iduser'];
            }
            $add['message_id'] = $_POST['message_id'];
            $add['name'] = $_SESSION['MEIX']['name'];
            $add['avatar'] = $_SESSION['MEIX']['avatar'];
            $add['user_id'] = $_SESSION['MEIX']['iduser'];
            $add['content'] = $_POST['content'];
            $add['lock'] = 1;
            if(!empty($add['message_id'])&&!empty($add['re_user_id'])&&!empty($add['user_id'])){
                $add['mktime'] = mktime();
                $messagereply = M('Messagereply');
                $addok = $messagereply->add($add);
                $add['idmessagereply'] = $messagereply->getLastInsID();
            }
            if($addok){
            	$message = M('Message');
            	$messager_map['idmessage'] = $_POST['message_id'];
            	$messager_msg = $message->where($messager_map)->find();

				$messagereply_map['idmessagereply'] = $add['idmessagereply'];
				$messagereply_msg =  $messagereply->where($messagereply_map)->find();
				$messagereply_msg['reply'] = 1;
				$messagereply_msg['info_top'] = $messager_msg['info_top'];
				$messagereply_msg['info_poor'] = $messager_msg['info_poor'];
				$messagereply_msg['idmessage'] = $messager_msg['idmessagereply'];
				$messagereply_return = W('Message',$messagereply_msg,true);
				$this->ajaxReturn($messagereply_return,'成功',1);
            }else{
                $this->ajaxReturn('','',0);
            }
        }

    }
    */
    /**
     * 留言回复列表显示
     * */
     /*
    public function messagereply_w_replylist(){
        if($this->isAjax()){
            $map['message_id'] = $_POST['message_id'];
            $map['lock'] = 1;
            if(!empty($map['message_id'])){
                $messagereply = M('Messagereply');
                $messagereplylist['list'] = $messagereply->where($map)->select();
                $messagereplylist['message_id']=$map['message_id'];
                $messagereplylist['re_id']=$_POST['user_id'];
                $messagereplylist['stype']='replylist';
                $wreturn = W('Messageonce',$messagereplylist,true);
                $this->ajaxReturn($wreturn,'',1);
            }else{
                $this->ajaxReturn('','',0);
            }
        }
    }
    */
    /**
     * 提示
     * 'id''table''name''action''function''type''showtype''content''get''tictactoe''domain'
     * */
     private function notice_message_c($map){
            $modelsql = M(ucfirst($map['table']));
            $sqlmap['id'.$map['table']] = $map['id'];
            $modelsqlfind = $modelsql->where($sqlmap)->find();
            $map['iduser'] = $modelsqlfind['user_id'];
            $map['type'] = 'message';
            if($map['is_reply']==1){
                $map['showtype'] = '回复了你';
            }else{
                $map['showtype'] = '给你留言';
            }
        if($map['table']=='point_view'){
            $map['action'] = '/Pointview';
            $map['function'] = '/inside';
            $map['get'] = '/pid/'.$map['id'];
            $map['tictactoe'] = '#ml'.$map['idmessage'];
        }elseif($map['table']=='rec_stocks'){
            $map['a_action'] = '/Recstocks';
            $map['a_function'] = '/inside';
            $map['a_get'] = '/rid/'.$map['id'];
            $map['a_tictactoe'] = '#ml'.$map['idmessage'];
        }
        if(!empty($map['iduser'])&&!empty($map['name'])&&!empty($map['action'])&&!empty($map['function'])){
            $this->notice_c($map['iduser'],$map['name'],$map['action'],$map['function'],$map['type'],$map['showtype'],$map['content'],$map['get'],$map['tictactoe'],$map['domain']);
        }
     }
     private function notice_c($iduser,$name,$action,$function,$type='ly',$showtype='',$content='',$get='',$tictactoe='',$domain=''){
        if(!empty($iduser)){
            $notice = M('Notice');
            $add['user_id'] = $iduser;
            $add['type'] = $type;
            $add['ed'] = 1;
            $add['name'] = $name;
            $add['showtype'] = $showtype;
            $add['content_t'] = $content;
            $add['a_domain'] = $domain;
            $add['a_action'] = $action;
            $add['a_function'] = $function;
            $add['a_get'] = $get;
            $add['a_tictactoe'] = $tictactoe;
            $add['mktime'] = mktime();
            $add['lock'] = 1;
            $ok = $notice->add($add);
            if($ok){
                return true;
            }else{
                return false;
            }
        }
    }
    /**
     *全站提醒 
     * */
    public function letternotice_a(){
        if($this->isAjax()){
            if(!empty($_SESSION['MEIX']['iduser'])){
                $map['user_id'] = $_SESSION['MEIX']['iduser'];
                $map['ed'] = 1;
                $map['lock'] = 1;
                $notice = M('Notice');
                $returnarr['notice'] = $notice->where($map)->count();
                $message_letter = M('Message_letter');
                $returnarr['letter'] = $message_letter->where($map)->count();
                if($returnarr){
                    $this->ajaxReturn($returnarr,'',1);
                }else{
                    $this->ajaxReturn('','',0);
                }
            }else{
                $this->ajaxReturn('','',0);
            }
        }
    }
     
    /**
     * 收藏
     * */
    public function favorite_a(){
        if($this->isAjax()){
            $add['table'] = $_POST['table'];
            $add['id'] = $_POST['id'];
            //$add['type'] = $_POST['type'];
            $add['user_id'] = $_SESSION['MEIX']['iduser'];
            $add['lock'] = 1;
            if(!empty($add['table'])&&!empty($add['id'])&&!empty($add['user_id'])){
                $favorite = M('Favorite');
                $favoritefind = $favorite->where($add)->find();
                $add['mktime'] = mktime();
                if(empty($favoritefind)){
                    $addok = $favorite->add($add);
                    $messageinfo = '收藏';
                    $add['idfavorite'] = $favorite->getLastInsID();
                }else{//setDec
                    unset($add['mktime']);
                    $set = 'setDec';
                    $messageinfo = '收藏';//取消
                    $addok = $favorite->where($add)->delete();
                }
            }
            if($addok){
                $favoritecount = $this->favorite_main_c($add,$set);//补主表数
                $this->ajaxReturn($favoritecount,$messageinfo,1);
            }else{
                $this->ajaxReturn('','',0);
            }
        }
    }
    private function favorite_main_c($map,$set=''){
        if($map){
            $modelsql = M(ucfirst($map['table']));
            $sqlmap['id'.$map['table']] = $map['id'];
            if(!empty($set)&&$set=='setDec'){
                $modelsql->where($sqlmap)->setDec('info_digest'); // 用户癿积分加1
            }else{
                $modelsql->where($sqlmap)->setInc('info_digest'); // 用户癿积分加1
            }
            $modelsql = $modelsql->where($sqlmap)->find();
            return $modelsql['info_digest'];
        }else{
            return false;
        }
    }
    /**
     * 关注
     * */
    public function attention_a(){
        if($this->isAjax()){
            $add['user_id'] = $_SESSION['MEIX']['iduser'];
            $add['table'] = $_POST['table'];
            $add['id'] = $_POST['id'];
            $add['lock'] = 1;
            if(!empty($_POST['table'])&&!empty($_POST['id'])){
                $attention = M('Attention');
                $attentionhave = $attention->where($add)->find();
                if($attentionhave){
                    $ok = $attention->where($add)->delete();
                    $mes = '关注';//取消关注
                }else{
                    $ok = $attention->add($add);
                    $mes = '取消关注';//关注成功
                }
                if($ok){
                    $this->ajaxReturn($add,$mes,1);
                }else{
                    $this->ajaxReturn('','失败',0);
                }

            }else{
                $this->ajaxReturn('','失败',0);
            }
        }
    }
     /*
    public function attention_a(){
        if($this->isAjax()){
            $add['user_id'] = $_SESSION['MEIX']['iduser'];
            $add['stock_id'] = $_POST['id'];
            if(!empty($_POST['type'])&&!empty($_POST['id'])&&(($_POST['type']=='stock')||($_POST['type']=='lndustry'))){
                $attention = M('Attention_stock');
                if($_POST['type']=='lndustry'){
                    $add['level'] = 21;
                    $info_add['d_table'] = 'stock';
                }elseif($_POST['type']=='stock'){
                    $add['level'] = 31;
                    $info_add['d_table'] = 'stock';
                }
                $info_add['d_id'] = $add['stock_id'];
                $addok = true;
            }elseif(!empty($_POST['type'])&&!empty($_POST['id'])&&$_POST['type']=='stocktab'){
                $attention = M('Attention_stocktab');
                $info_add['d_table'] = 'stocktab';
                $info_add['d_id'] = $add['stock_id'];
                $addok = true;
            }else{
                $addok = false;
            }
            if($addok&&!empty($add['user_id'])&&!empty($add['stock_id'])){
                //$this->notice_message_c($add);
                $attentionhave = $attention->where($add)->find();
                if($attentionhave){
                    $ok = $attention->where($add)->delete();
                    $mes = '取消关注';
                    $this->stock_info_c($info_add,'attention',true);//补主表数
                }else{
                    $ok = $attention->add($add);
                    $mes = '关注成功';
                    $this->stock_info_c($info_add,'attention');//补主表数

                }
                if($ok){

                    $this->ajaxReturn($add,$mes,1);
                }else{
                    $this->ajaxReturn('','失败',0);
                }

            }else{
                $this->ajaxReturn('','失败',0);
            }
        }
    }
    */
    /**
     * 概念
     * */
     /*
    public function stocktab_a(){
        if($this->isAjax()){
            $add['name'] = trim($_POST['name']);
            $add['lock'] = 1;
            $add['stock_id'] = $_POST['stock_id'];
            if(!empty($_SESSION['MEIX']['iduser'])&&!empty($add['name'])&&!empty($add['stock_id'])){
                $stocktab = M('Stocktab');
                $stocktabhave = $stocktab->where($add)->find();
                if($stocktabhave){
                    $add['idstocktab'] = $stocktabhave['idstocktab'];
                    $this->stocktabstock_c($add['idstocktab'],$_POST['array_id']);
                    $addok = true;
                }else{
                    $add['user_id'] = $_SESSION['MEIX']['iduser'];
                    $add['mktime'] = mktime();
                    $add['num'] = 1;
                    $addok = $stocktab->add($add);
                    $add['idstocktab'] = $stocktab->getLastInsID();
                    $this->stocktabstock_c($add['idstocktab'],$_POST['array_id']);
                    $addok = true;
                }
            }else{
                $addok = false;
            }
            if($addok){
                $this->ajaxReturn($add,'',1);
            }else{
                $this->ajaxReturn('','失败',0);
            }
        }
    }
    private function stocktabstock_c($id,$array_id){
        if(!empty($id)&&!empty($array_id)){
            $array_id = explode(',',$array_id);
            if(is_array($array_id)){
                $stocktab_stock = M('Stocktab_stock');
                $map['stocktab_id'] = $id;
                $stocktab_stockhave = $stocktab_stock->where($map)->select();
                foreach($stocktab_stockhave as $havevo){
                    $have[$havevo['stock_id']] = 1;
                }
                foreach($array_id as $vo){
                    if(empty($have[$vo])&&!empty($vo)&&$vo!=0){
                        $map['stock_id'] = $vo;
                        $data[] = $map;
                    }
                }
                $stocktab_stock->addAll($data);
            }

        }
    }
    */
    //中英文字符串截取函数
    public function msubstr_txt($str, $start=0, $length=100, $charset="utf-8", $suffix=true)  { //中英文字符串截取函数38
        if(!empty($str)){
            if(function_exists("mb_substr")){
                if($suffix){
                     $slice = mb_substr($str, $start, $length, $charset);//."..."
                }else{
                     $slice = mb_substr($str, $start, $length, $charset);
                }
            }elseif(function_exists('iconv_substr')) {
                if($suffix){
                     $slice = iconv_substr($str,$start,$length,$charset);//."..."
                }else{
                     $slice = iconv_substr($str,$start,$length,$charset);
                }
            }else{
                $re['utf-8']   = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
                $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
                $re['gbk']    = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
                $re['big5']   = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
                preg_match_all($re[$charset], $str, $match);
                $slice = join("",array_slice($match[0], $start, $length));
            }
        }else{
            $slice = '';
        }
        return $slice;
    }
    private function inputkbr($content){
        import("ORG.Util.Input");
        return preg_replace("(<br />)", "   ", $content);

    }
    public function mktime_a(){
        if($this->isAjax()){
        $this->ajaxReturn(mktime(),'',1);
        }
    }

}
?>