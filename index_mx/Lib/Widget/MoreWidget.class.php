<?php

class MoreWidget extends Widget{

    public function render($data){
        if(!empty($data)){
            if($data['table'] == 'groups_index'){
        		$data['more'] = $data;
				$content = $this->renderFile('groups_more',$data);
        	}elseif($data['table'] == 'list_more'){
        	    $data['more'] = $data;
                $content = $this->renderFile('list_more',$data);
        	}elseif($data['table'] == 'user'){
        	   $data['more'] = $data;
                $content = $this->renderFile('groups_more',$data);
        	}elseif($data['table'] == 'stock'){
        	   $data['more'] = $data;
               $content = $this->renderFile('groups_more',$data);
        	}
        }else{
            $content = false;
        }
        return $content;
    }
}
?>