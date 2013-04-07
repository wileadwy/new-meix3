<?php
class DiscusshotWidget extends Widget{
    public function render($data){
        if(!empty($data['id'])&&!empty($data['table'])&&!empty($data['type'])){
            $rp['list'] = $this->discuss($data['id'],$data['table'],$data['type']);
            $content = $this->renderFile('listhot',$rp);
        }else{
            $content = false;
        }
        return $content;
    }
    private function discuss($d_id,$d_table,$d_type){
        $discuss = M('Discuss');
        $map['lock'] = 1;
        $map['d_id'] = $d_id;
        $map['d_table'] = $d_table;
        $map['d_type'] = $d_type;
        $rp = $discuss->where($map)->order('info_message_count desc,mktime desc')->limit(60)->select();
        return $rp;
    }
}

?>
