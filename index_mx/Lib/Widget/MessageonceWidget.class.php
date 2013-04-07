<?php
class MessageonceWidget extends Widget{
    public function render($data){
    	//dump($data);
        if(1){
            if(!empty($data['stype'])&&$data['stype']=='onep'&&!empty($data['idmessage'])){
                $this->messagesql_onep($data['idmessage']);
                $content = $this->renderFile('onep',$rp);//多空单条
            }elseif(!empty($data['stype'])&&$data['stype']=='donep'&&!empty($data['idmessage'])){
                $this->messagesql_onep($data['idmessage']);
                $content = $this->renderFile('donep',$rp);//讨论单条
            }elseif(!empty($data['id'])&&!empty($data['table'])&&!empty($data['stype'])&&$data['stype']=='dlist'){
                $drp = $this->messagesql($data['id'],$data['table'],$data['order']);
                $rp['list'] = $this->topandfiv($drp);
                $content = $this->renderFile('dlist',$rp);//讨论
            }elseif(!empty($data['stype'])&&$data['stype']=='replylist'){
                $content = $this->renderFile('replylist',$data);//Ajax回复列表
            }elseif(!empty($data['id'])&&!empty($data['table'])){
                $rp['list'] = $this->messagesql($data['id'],$data['table']);
                $content = $this->renderFile('list',$rp);//多空
            }
        }else{
            $content = false;
        }
        return $content;
    }
    private function messagesql($id,$table,$order,$idmessage=''){
        $message = M('Message');
        $map['table'] = $table;
        $map['id'] = $id;
        $map['lock'] = 1;
        if($order == 'num'){
			$messagefind = $message->where($map)->order('info_top desc')->select();
        }else{
        	$messagefind = $message->where($map)->order('mktime desc')->select();
        }

        return $messagefind;
    }
    private function messagesql_onep($idmessage){
        $message = M('Message');
        $map['idmessage'] = $idmessage;
        $map['lock'] = 1;
        $messagefind = $message->where($map)->find();
        return $messagefind;
    }
    private function topandfiv($drp){
        if(!empty($drp)&&is_array($drp)){
            $toporpoor = M('Toporpoor');///顶踩
            $toppoormap['user_id'] = $_SESSION['MEIX']['iduser'];
            $toppoormap['d_table'] = 'message';
            $favorite = M('Favorite');//收藏
            $favoritemap['user_id'] = $_SESSION['MEIX']['iduser'];
            $favoritemap['table'] = 'message';
            $favoritemap['lock'] = 1;
            foreach($drp as $key=>$vo){
                if(($vo['info_top']!=0)||($vo['info_poor']!=0)){
                     $toppoormap['d_id'] = $vo['idmessage'];
                     $toporpoorhave = $toporpoor->where($toppoormap)->find();
                     if($toporpoorhave){$drp[$key]['toporpoor'] = $toporpoorhave['toporpoor'];}
                }
                $favoritemap['id'] = $vo['idmessage'];
                $favoritefind = $favorite->where($favoritemap)->find();
                if($favoritefind){ $drp[$key]['favorite'] = 1; }
            }
        }
        return $drp;
    }

}

?>
