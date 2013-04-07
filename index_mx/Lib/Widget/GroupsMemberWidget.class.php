<?php

class GroupsMemberWidget extends Widget{

    public function render($data){
        if(!empty($data)){
        	$list['list'] = $data;
        	$content = $this->renderFile('member',$list);

        }else{
            $content = false;
        }
        return $content;
    }

}
?>