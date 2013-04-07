<?php

class MessageWidget extends Widget{

    public function render($data){
        if(!empty($data)){
            if($data['once']==1){
                $arr['list'] = $data;
                $content = $this->renderFile('message_return',$arr);
            }elseif(!empty($data['table'])&&!empty($data['id'])){
                $message = M('Message');
                $mesmap['table'] = $data['table'];
                $mesmap['id'] = $data['id'];
                $mesmap['lock'] = 1;
                $arr['list'] = $message->where($mesmap)->order('mktime asc')->select();
                $arr['data'] = $data;
                $content = $this->renderFile('message',$arr);
            }

        }else{
            $content = false;
        }
        return $content;
    }
}
?>