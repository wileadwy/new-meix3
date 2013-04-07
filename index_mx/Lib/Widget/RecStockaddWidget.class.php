<?php
class RecStockaddWidget extends Widget{
    public function render($data){
        if(1){
            $rp['list'] = $this->recstock_c();
            $rp['group_id'] = $data['group_id'];
            $content = $this->renderFile('recadd',$rp);
        }else{
            $content = false;
        }
        return $content;
    }
    private function recstock_c(){
        if(!empty($_SESSION['MEIX']['iduser'])){
            $groups = M('Groups');
            $user2groups = M('User2groups');
            $user2groups_map['user_id'] = $_SESSION['MEIX']['iduser'];
            $user2groups_list = $user2groups->where($user2groups_map)->select();
            foreach($user2groups_list as $key=>$val){
				$groups_idarr[] = $val['groups_id'];
            }
            $map['idgroups'] = array('in',$groups_idarr);
            $rp = $groups->where($map)->select();
        }else{
            $rp = array();
        }
        return $rp;
    }
}

?>
