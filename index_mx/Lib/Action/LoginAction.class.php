<?php
/**
 * 登录
 * */
class LoginAction extends AclAction {
    public function index(){
        $sinaurl = $this->sinadev();
        $this->assign('sinaurl',$sinaurl);
        //$_SESSION['MEIX']['JS']['url_login'] = __SELF__;
        if(!empty($_SESSION['MEIX']['iduser'])){
            $this->redirect('Index/index');
        }else{
            $this->display('Login:index');
        }

    }

    public function login_a(){
        if($this->isAjax()){
            $ok = $this->login_c($_POST['username'],$_POST['password']);
            if($ok){
                $this->ajaxReturn('','欢迎 '.$ok,1);
            }else{
                $this->ajaxReturn('','密码错误或用户名不存在',0);
            }
        }
    }
    public function login_c($username,$password){
        $user = M('User');
        $map['username'] = $username;
        $map['lock'] = 1;
        $userfind = $user->where($map)->find();
        if($userfind&&(($userfind['password']==md5($password))||($userfind['password']==$password))){
            $this->set_session($userfind);
            return $userfind['name'];
        }else{
            return false;
        }
    }
    private function set_session($userfind){
        $_SESSION['MEIX']['iduser'] = $userfind['iduser'];
        $_SESSION['MEIX']['name'] = $userfind['name'];
        $_SESSION['MEIX']['avatar'] = $userfind['avatar'];
        $_SESSION['MEIX']['t_openid'] = $userfind['t_openid'];
        $_SESSION['MEIX']['s_id'] = $userfind['s_id'];
        $user_info = M('User_info');
        $map['user_id'] = $userfind['iduser'];
        $user_infolist = $user_info->where($map)->select();
        foreach($user_infolist as $uservo){
            $_SESSION['MEIX']['info'][$uservo['field']] = $uservo['value'];
        }
    }
    //退出
    public function sign(){
        $_SESSION['MEIX'] = "";
        $this->redirect('Index/index');
    }
    /**
     * qq
     *
     * */
    public function qqlogin(){
        $ok=$this->qqapi();
        if($ok=='ok'){
            //获取用户信息
            $r = Tencent::api('user/info');
            $userinfo = (json_decode($r, true));
            $add = $this->qqlogin_c($userinfo);
            if($add){
                if($add=='username'){
                    $this->assign('userinfo',$userinfo);
                    $this->display('userinfo');
                }else{
                    if(!empty($_SESSION['MEIX']['JS']['url_login'])){
                        if(($_SESSION['MEIX']['JS']['url_login']!='/index.php/Login/index')||($_SESSION['MEIX']['JS']['url_login']!='/Login/index')){
                            $url_login = $_SESSION['MEIX']['JS']['url_login'];
                            $_SESSION['MEIX']['JS']['url_login'] = '';
                        }else{
                            $url_login = __APP__.'/Index/index';
                        }
                    }else{
                        $url_login = __APP__.'/Index/index';
                    }
                    $login_c_ok = $this->login_c($add['username'],$add['password']);//登录
                    if($login_c_ok){
                        //echo $url_login;
                        //$this->redirect($url_login);
                        $this->assign("jumpUrl",$url_login);
                        $this->success("");
                    }else{
                        $this->redirect('Login/index');
                    }
                }
            }else{
                $this->redirect('Login/index');
            }

        }
    }
    private function qqlogin_c($userinfo){
        if($userinfo['msg']=='ok'){
            $user = M('User');
            $add['lock'] = 1;
            $add['t_openid'] = $userinfo['data']['openid'];
            $userhave = $user->where($add)->find();
            if($userhave){
                if(empty($userhave['username'])){
                    return 'username';
                }else{
                    return $userhave;
                }
            }else{
                 return 'username';
            }
        }else{
            return false;
        }
    }
    /**
     * sina
     *
     * */
    public function sinalogin(){
        session_start();
        $this->sinaconfigandcallback();
        import("ORG.Weibo.Sinasaetv2");
        $o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
        $o->set_debug( DEBUG_MODE );
        if (isset($_REQUEST['code'])) {
        	$keys = array();
        	$state = $_REQUEST['state'];// 验证state，防止伪造请求跨站攻击
        	if ( empty($state) || $state !== $_SESSION['weibo_state'] ) {
        		$this->index();//echo '非法请求！';
        		exit;
        	}
        	unset($_SESSION['weibo_state']);
        	$keys['code'] = $_REQUEST['code'];
        	$keys['redirect_uri'] = WB_CALLBACK_URL;
        	try {
        		$token = $o->getAccessToken( 'code', $keys ) ;
        	} catch (OAuthException $e) {
        	}
        }
        if ($token) {
        	$_SESSION['token'] = $token;
        	setcookie( 'weibojs_'.$o->client_id, http_build_query($token) );
            //echo '授权完成,<a href="sinaweibolist">进入你的微博列表页面</a><br />';
            $userfind = $this->show_user_by_token();
            $add = $this->sinalogin_c($userfind);
            if($add){
                if($add=='username'){
                    $this->assign('userfind',$userfind);
                    $this->display('userinfo');
                }else{
                    if(!empty($_SESSION['MEIX']['JS']['url_login'])){
                        $url_login = $_SESSION['MEIX']['JS']['url_login'];
                        $_SESSION['MEIX']['JS']['url_login'] = '';
                    }else{
                        $url_login = 'Index/index';
                    }
                    $login_c_ok = $this->login_c($add['username'],$add['password']);//登录
                    if($login_c_ok){
                        //echo $url_login;
                        $this->assign("jumpUrl",$url_login);
                        $this->success("");
                    }else{
                        $this->redirect('Login/index');
                    }
                }
            }else{
                $this->redirect('Login/index');
            }
        } else {
            $this->index();// '授权失败。';
        }
    }
    public function show_user_by_token(){//微博列表页面
        if(!empty($_SESSION['token'])){
            session_start();
            $this->sinaconfigandcallback();
            import("ORG.Weibo.Sinasaetv2");
            $c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
            //$c->set_debug( DEBUG_MODE );
            //$ms  = $c->home_timeline(); // done
            //var_dump($ms);
            $uid_get = $c->get_uid();
            $uid = $uid_get['uid'];
            $user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息
            return $user_message;
        }else{
            return false;
        }

    }
    private function sinalogin_c($userinfo){
        if(!empty($userinfo['id'])){//$userinfo['msg']=='ok'
            $user = M('User');
            $add['lock'] = 1;
            $add['s_id'] = $userinfo['id'];
            $userhave = $user->where($add)->find();
            if($userhave){
                if(empty($userhave['username'])){
                    return 'username';
                }else{
                    return $userhave;
                }
            }else{
                 return 'username';
            }
        }else{
            return false;
        }
    }
    /**
     * 注册
     *
     * */
    public function register_1(){
        if($this->isAjax()){

            $user = M('User');
            $add['lock'] = 1;
            if(!empty($_POST['qq_openid'])){
                $addmap['t_openid'] = $_POST['qq_openid'];
                $add['t_openid'] = $_POST['qq_openid'];
                $add['t_name'] = $_POST['qq_name'];
                $add['t_nick'] = $_POST['qq_nick'];
                $add['t_seqid'] = $_POST['qq_seqid'];
                $add['t_head'] = $_POST['qq_head'].'/180';
                $add['name'] = $add['t_nick'];
                $add['avatar'] = $add['t_head'];
            }elseif(!empty($_POST['sina_id'])){
                $addmap['s_id'] = $_POST['sina_id'];
                $add['s_id'] = $_POST['sina_idstr'];
                $add['s_screen_name'] = $_POST['sina_screen_name'];
                $add['s_name'] = $_POST['sina_name'];
                $add['s_avatar_large'] = $_POST['sina_avatar_large'];
                $add['s_url'] = $_POST['sina_profile_url'];
                $add['name'] = $add['s_screen_name'];
                $add['avatar'] = $add['s_avatar_large'];
            }else{
                $this->ajaxReturn('Login','请重试',0);
                exit();
            }
            $userhave = $user->where($addmap)->find();

            if($_POST['password1']==$_POST['password2']){
                if(!$userhave){
                    //邀请码
                    $groups_invite = M('Groups_invite');
                    if(!empty($_POST['invite'])){
                        $map['code'] = $_POST['invite'];
                        $map['type'] = 'register_1';
                        $map['mktime'] = array('gt',(mktime()-604800));
                        $groups_invitefind = $groups_invite->where($map)->find();
                    }
                    //邀请码
//                    if($groups_invitefind){
                    if(1){
                        $add['mktime'] = mktime();
                        if(!empty($_POST['name'])){ $add['name'] = $_POST['name']; }
                        $pinyin = R('Pinyin/index',array($add['name']));
                        $add['name_pinyin'] = $pinyin['pinyin'];
                        $add['name_jianpin'] = $pinyin['jianpin'];
                        $add['username'] = $_POST['username'];
                        $add['information'] = $_POST['info'];
                        $add['style'] = $_POST['style'];
                        $add['password'] = md5($_POST['password2']);

                        $addhave['username'] = $_POST['username'];//此用户名已被使用
                        $userhave = $user->where($addhave)->find();
                        if(!$userhave){
                            $ok = $user->add($add);
                            if($ok){
                                $add['iduser'] = $user->getLastInsID();
                                $this->user_info_set($add['iduser']);//配置info
                                $inv_data['type'] = 'register_ed';
                                $inv_data['table'] = 'user';
                                $inv_data['id'] = $add['iduser'];
                                $groups_invite->where($map)->save($inv_data);//邀请码用过
                                $this->login_c($add['username'],$add['password']);//登录
                                //默认私信
                                $this->letter_add($add['iduser']);
                                $this->ajaxReturn('Index','欢迎 '.$add['name'],1);
                            }else{
                                $this->ajaxReturn('Login/qqlogin','请重试',0);
                            }
                        }else{
                            $this->ajaxReturn('Login/qqlogin','此用户名已被使用',0);
                        }
                    }else{
                        $this->ajaxReturn('Login/qqlogin','邀请码不正确、过期或已用过',0);
                    }

                 }elseif(empty($userhave['username'])){
                    $havedata['username'] = $_POST['username'];
                    $havedata['username'] = $_POST['password2'];
                    $userhavedata = $user->where($havedata)->find();
                    if($userhavedata){
                        $this->ajaxReturn('Login/qqlogin','该用户名被使用',0);
                    }else{
                        $havemap['iduser'] = $userhave['iduser'];
                        $ok = $user->where($havemap)->save($havedata);
                        if($ok){
                            $this->login_c($havedata['username'],$havedata['password']);//登录
                            $this->ajaxReturn('Index','欢迎 '.$ok['name'],1);
                        }else{
                            $this->ajaxReturn('Login/qqlogin','请重试',0);
                        }
                    }
                }else{
                    $this->ajaxReturn('Index','欢迎 '.$add['t_nick'],1);
                }

            }else{
                $this->ajaxReturn('Login/qqlogin','两次密码不一致',0);
            }

        }else{
            $this->ajaxReturn('Login','请重新登录',0);
        }
    }

    /**
     * //配置info
     * */
    private function user_info_set($uid){
        $arr[] = array('money_diamond_member',mktime()+8035200,mktime());//钻石会员3个月
        $arr[] = array('money_subscriber_count',20,20);//订阅器个数
        $arr[] = array('subscribe_user_id','1,2,3,4,5,6,16,21',',1,2,3,4,5,6,,16,21,');//默认订阅的人
        if(!empty($_POST['vip'])){ $arr[] = array('vip_sina_qq',$_POST['vip'],$_POST['vip']); }//QQ 还是Sina的VIP
        //home_my_stock     我的股票
        $user_info = M('User_info');
        $map['user_id'] = $uid;
        foreach($arr as $vo){
            $map['field'] = $vo[0];
            $map['value'] = $vo[1];
            $map['info'] = $vo[2];
            $user_info->add($map);
        }
        $_SESSION['MEIX']['REGISTER']['first_index'] = 1;//首次登录记录
    }
    /*
	 * 2013-4-7上午07:34:30
	 * Jone
	 * 注册关注圈子
	**/
	public function login_groups (){
		$this->display();
	}
    /*
	 * 2013-4-7上午07:34:30
	 * Jone
	 * 注册关注股票
	**/
	public function login_stocks (){
		$this->display();
	}
    /*
	 * 2013-4-1上午06:40:26
	 * Jone
	 * 默认私信
	**/
	 private function letter_add ($user_id){
		$letter = M('Message_letter');
		$add['user_id'] = $user_id;
		$user = M('User');
		$user_map['iduser'] = '4';
		$user_msg = $user->where($user_map)->field('iduser,name,avatar')->find();
		$add['fuser_id'] = $user_msg['iduser'];
		$add['fuser_name'] = $user_msg['name'];
		$add['fuser_avatar'] = $user_msg['avatar'];
		$add['ed'] = 1;
		$add['mktime'] = mktime();
		$add['lock'] = 1;
		$add['content'] = '欢迎大家多多荐股、多多发表观点。我们将不定期举办活动，邀请排名靠前的活跃高手和包括小美在内的多名首席客服一起晚餐或酒会。在美市，与高手一起投资论道，品美食、尝美酒、赏美女、聊美事！';
		$ok1 = $letter->add($add);
		if($ok1){
			$add['content'] = '#小美老师带你用美市#（2）：看荐股历史记录，了解朋友准确率和收益率。身边朋友能力如何？买点卖点是否得当？组合是否合理？我们不得而知。在MeiX，所有人的荐股都会保存历史记录，并跟踪准确率和收益率，能力水平一目了然。混高手小圈子，才能赚大钱！';
			$ok2 = $letter->add($add);
		}
		if($ok2){
			$add['content'] = '#小美老师带你用美市#（1）：不用翻聊天记录，只看精华观点、股票推荐。相比较QQ群、微信群，MeiX不仅能够让大家一起随时讨论，更重要的是能够将讨论过程中重要、精华的多空观点，以及股票的买点卖点推荐保存下来，不用辛苦翻聊天记录，只看观点、荐股！';
			$ok3 = $letter->add($add);
		}
	}


}

?>