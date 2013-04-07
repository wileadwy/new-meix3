<?php
class AclAction extends Action{

    
     public function _initialize(){
        if(isset($_SESSION['MEIX'])&&!empty($_SESSION['MEIX']['iduser'])){
            if($_SESSION['MEIX']['info']['money_diamond_member']>mktime()){
                $_SESSION['MEIX']['member']['mktime'] = 1;
                $_SESSION['MEIX']['member']['mes'] = '钻石会员剩余'.(int)($_SESSION['MEIX']['info']['money_diamond_member']/86400).'天';
            }else{
                $_SESSION['MEIX']['member']['mktime'] = 86400;
                $_SESSION['MEIX']['member']['mes'] = '非钻石会员可以查看一天之前的数据';
            }
        }else{
            $_SESSION['MEIX']['member']['mktime'] = 86400*3;
            $_SESSION['MEIX']['member']['mes'] = '未登录可以查看三天之前的数据';
        }
    }
    
     public function acl_input3(){
        import("ORG.Util.Input");
        foreach($_POST as $key=>$vo){
            if(is_string($vo)){
                $_POST[$key] =   str_replace('\\\\','\\',str_replace('\&quot;','&quot;',Input::forShow($vo)));//self::forShow($vo);//forShow($vo);
            }elseif(is_array($vo)){
                foreach($vo as $key1=>$vo1){
                    if(is_string($vo1)){
                        $_POST[$key][$key1] =   str_replace('\\\\','\\',str_replace('\&quot;','&quot;',Input::forShow($vo1)));//self::forShow($vo);//forShow($vo);
                    }elseif(is_array($vo1)){
                        foreach($vo1 as $key2=>$vo2){
                            if(is_string($vo2)){
                                $_POST[$key][$key1][$key2] =   str_replace('\\\\','\\',str_replace('\&quot;','&quot;',Input::forShow($vo2)));//self::forShow($vo);//forShow($vo);
                            }
                        }
                    }
                }
            }

        }
	}
    //public function _empty(){
    //    $this->error('该页不存在请重试');
	//}
    
    
    /**
     * QQ_API
     * qqapi
     * */
     
    public function qqapi(){
        if ($_SESSION['t_access_token'] || ($_SESSION['t_openid'] && $_SESSION['t_openkey'])) {//用户已授权
            $this->qqconfigqq();
            return 'ok';
        } else {
            $this->qqdev();//未授权
        }
    }
    private function qqdev(){//未授权
        $this->qqconfigqq();
        $callback = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];//回调url
            if ($_GET['code']) {//已获得code
                $code = $_GET['code'];
                $openid = $_GET['openid'];
                $openkey = $_GET['openkey'];
                //获取授权token
                $url = OAuth::getAccessToken($code, $callback);
                $r = Http::request($url);
                parse_str($r, $out);
                //存储授权数据
                if ($out['access_token']) {
                    $_SESSION['t_access_token'] = $out['access_token'];
                    $_SESSION['t_refresh_token'] = $out['refresh_token'];
                    $_SESSION['t_expire_in'] = $out['expires_in'];
                    $_SESSION['t_code'] = $code;
                    $_SESSION['t_openid'] = $openid;
                    $_SESSION['t_openkey'] = $openkey;
                    
                    //验证授权
                    $r = OAuth::checkOAuthValid();
                    if ($r) {
                        header('Location: ' . $callback);//刷新页面
                    } else {
                        exit('<h3>授权失败,请重试</h3>');
                    }
                } else {
                    exit($r);
                }
            } else {//获取授权code
                if ($_GET['openid'] && $_GET['openkey']){//应用频道
                    $_SESSION['t_openid'] = $_GET['openid'];
                    $_SESSION['t_openkey'] = $_GET['openkey'];
                    //验证授权
                    $r = OAuth::checkOAuthValid();
                    if ($r) {
                        header('Location: ' . $callback);//刷新页面
                    } else {
                        exit('<h3>授权失败,请重试</h3>');
                    }
                } else{
                    $url = OAuth::getAuthorizeURL($callback);
                    header('Location: ' . $url);
                }
            }
    }
    private function qqconfigqq(){
        error_reporting(0);
        $client_id = '801065573';//填写自己的appid
        $client_secret = '94a5c459cb55826ceeb6c34bb50a4c80';//填写自己的appkey
        import("ORG.Weibo.Tencent");
        OAuth::init($client_id, $client_secret);
        Tencent::$debug = false;//false//true//调试模式
        header('Content-Type: text/html; charset=utf-8');//session_start();//打开session
    }
    
    /**
     * sina_API
     * 
     * */
     
    public function sinadev($callbackurl){//授权
        session_start();
        $this->sinaconfigandcallback($callbackurl);
        import("ORG.Weibo.Sinasaetv2");
        $o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
        $o->set_debug( DEBUG_MODE );
        $state = uniqid( 'weibo_', true);
        $_SESSION['weibo_state'] = $state;// 生成state并存入SESSION，以供CALLBACK时验证使用
        $code_url = $o->getAuthorizeURL( WB_CALLBACK_URL , 'code', $state );
        return $code_url;
    }
    
    public function sinaconfigandcallback(){
        
        header('Content-Type: text/html; charset=UTF-8');
        define( 'DEBUG_MODE', false );// 调试模式开关
        if ( !function_exists('curl_init') ) {
            echo '您的服务器不支持 PHP 的 Curl 模块，请安装或与服务器管理员联系。';
            exit;
        }
        define( "WB_AKEY" , '2829273577' );
        define( "WB_SKEY" , '3b0bbd2f82d1c2d0243df140e958782c' );
        define( "WB_CALLBACK_URL" , C('SITE_SINACALLBACKURL') );//http://weibosdk.sinaapp.com/callback.php
        if ( DEBUG_MODE ) {
            error_reporting(E_ALL);
            ini_set('display_errors', true);
        }
    }
    

}


?>