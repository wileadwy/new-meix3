<?php
class RecStockscycleWidget extends Widget{
    public function render($data){
        if($data['rec_stocks_id']){
            $rp['find'] = $this->recsc_c($data);
            $rp['groupslist'] = $this->recstock_c();//圈子
            $content = $this->renderFile('recsc3',$rp);
        }else{
            $content = false;
        }
        return $content;
    }
    private function recsc_c($data){
        if(!empty($_SESSION['MEIX']['iduser'])){
            $rec_stocks = M('Rec_stocks');
            $rec_stocksmap['idrec_stocks'] = $data['rec_stocks_id'];
            $rec_stocksfind = $rec_stocks->where($rec_stocksmap)->find();
            $rp['idrec_stocks'] = $rec_stocksfind['idrec_stocks'];
            $rp['price_b'] = $rec_stocksfind['price_b'];
            $rp['mktime_b'] = $rec_stocksfind['mktime_b'];
            $rp['cycle'] = $rec_stocksfind['rec_stocks_cycle_id'];
            $rp['stocks_name'] = $rec_stocksfind['stocks_name'];
            $arr_re = $this->tool_stock_now($rec_stocksfind['stocks_number']);
            $rp['now'] = $arr_re['now'];
            //moreorempty
            if($rec_stocksfind['moreorempty']=='buy'){
                $rp['first'] = 'sell';
                $rp['zmsyl'] = number_format((($rp['price_b']-$arr_re['now'])/$arr_re['now']),3);
            }else{
                $rp['first'] = 'buy';
                $rp['zmsyl'] = number_format((($arr_re['now']-$rp['price_b'])/$arr_re['now']),3);
            }
        }else{
            $rp = array();
        }
        return $rp;
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
