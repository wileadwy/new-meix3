<?php
class MoreoremptyWidget extends Widget{
    public function render($data){
        if(!empty($data['id'])&&!empty($data['table'])&&!empty($data['type'])){
            $rp = $this->moreorempty($data['id'],$data['table'],$data['type'],$data['map']);
            $content = $this->renderFile('list',$rp);
        }else{
            $content = false;
        }
        return $content;
    }
    private function moreorempty($d_id,$d_table,$d_type,$favmap){
        $moreorempty = M('Moreorempty');
        $map['lock'] = 1;
	    $map['d_id'] = $d_id;
	    $map['d_table'] = $d_table;
	    $map['d_type'] = $d_type;
	    $map['or'] = 'more';
	    //只看我收藏的
        if($favmap == 'fav'){
        	$favorite = M('Favorite');
        	$fmap['user_id'] = $_SESSION['MEIX']['iduser'];
        	$fmap['table'] = 'moreorempty';
        	$fmap['type'] = $d_type;
        	$favorite_list = $favorite->where($fmap)->select();
        	$favid_arr = array();
        	foreach($favorite_list as $key=>$val){
				$favid_arr[] = $val['id'];
        	}
        	$map['idmoreorempty'] = array('in',$favid_arr);
        }
        $rp['more'] = $moreorempty->where($map)->order('mktime desc')->select();

        $toporpoor = M('Toporpoor');///顶踩
        $toppoormap['user_id'] = $_SESSION['MEIX']['iduser'];
        $toppoormap['d_table'] = 'moreorempty';
        $favorite = M('Favorite');
        $favoritemap['user_id'] = $_SESSION['MEIX']['iduser'];
        $favoritemap['table'] = 'moreorempty';
        $favoritemap['lock'] = 1;
        foreach($rp['more'] as $morekey=>$morevo){
            if(($morevo['info_top']!=0)||($morevo['info_poor']!=0)){
                 $toppoormap['d_id'] = $morevo['idmoreorempty'];
                 $toporpoorhave = $toporpoor->where($toppoormap)->find();
                 if($toporpoorhave){$rp['more'][$morekey]['toporpoor'] = $toporpoorhave['toporpoor'];}
            }
            $favoritemap['id'] = $morevo['idmoreorempty'];
            $favoritefind = $favorite->where($favoritemap)->find();
            if($favoritefind){ $rp['more'][$morekey]['favorite'] = 1; }
        }
        $map['or'] = 'empty';
        $rp['empty'] = $moreorempty->where($map)->order('mktime desc')->select();
        foreach($rp['empty'] as $morekey=>$morevo){
            if(($morevo['info_top']!=0)||($morevo['info_poor']!=0)){
                 $toppoormap['d_id'] = $morevo['idmoreorempty'];
                 $toporpoorhaveempty = $toporpoor->where($toppoormap)->find();
                 if($toporpoorhaveempty){$rp['empty'][$morekey]['toporpoor'] = $toporpoorhaveempty['toporpoor'];}
            }
            $favoritemap['id'] = $morevo['idmoreorempty'];
            $favoritefind = $favorite->where($favoritemap)->find();
            if($favoritefind){ $rp['more'][$morekey]['favorite'] = 1; }
        }
        return $rp;
    }
}

?>
