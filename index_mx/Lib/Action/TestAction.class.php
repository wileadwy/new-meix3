<?php

class TestAction extends AclAction{

//3D饼图
function index(){
    $mktime1 = mktime();
    for($i=0;$i<1;$i++){
        $oww[] = R('Tool/tool_stock_t2t',array('sz000049',mktime()-(86400*30),mktime()+999));
    }
    $mktime = mktime()-$mktime1;
    echo $mktime;
    dump($oww);
    //$now = R('Tool/tool_stock_now',array('sz000049'));
    //dump($now);
}
   
function text(){
    import("ORG.Util.Chart");
    $chart = new Chart();
    $title = "3D饼图"; //标题
    $data = array(20,27,45,75,90,10,20,40); //数据
    $size = 140; //尺寸
    $width = 750; //宽度
    $height = 350; //高度
    $legend = array("aaaa ","bbbb","cccc","dddd ","eeee ","ffff ","gggg ","hhhh ");//说明
    $chart->create3dpie($title,$data,$size,$height,$width,$legend);
}

//柱状图 
function test1(){
    import("ORG.Util.Chart");
$chart = new Chart();
$title = "柱状图"; //标题
$data = array(20,27,45,75,90,10,80,100); //数据
$size = 140; //尺寸
$width = 750; //宽度
$height = 350; //高度
$legend = array("aaaa ","bbbb","cccc","dddd ","eeee ","ffff ","gggg ","hhhh ");//说明
$chart->createcolumnar($title,$data,$size,$height,$width,$legend);
}
//线图 
function test2(){
    import("ORG.Util.Chart");
$chart = new Chart();
$title = "柱状图"; //标题
$data = array(20,27,45,75,90,10,80,11); //数据
$size = 140; //尺寸
$width = 750; //宽度
$height = 350; //高度
$legend = array("aaaa ","bbbb","cccc","dddd ","eeee ","ffff ","gggg ","hhhh ");//说明
$chart->createmonthline($title,$data,$size,$height,$width,$legend);
}

//环状图
function test3(){
    import("ORG.Util.Chart");
$chart = new Chart();
$title = "柱状图"; //标题
$data = array(20,27,45,75,90,10,80,100); //数据
$size = 140; //尺寸
$width = 750; //宽度
$height = 350; //高度
$legend = array("aaaa ","bbbb","cccc","dddd ","eeee ","ffff ","gggg ","hhhh ");//说明
$chart->createring($title,$data,$size,$height,$width,$legend);
}

//横柱图
function test4(){
    import("ORG.Util.Chart");
$chart = new Chart();
$title = "柱商务图"; //标题
$subtitle = "2012 年6月";
$data = array(20,27,45,75,90,100,80,100,300,500,1000,200,300,100,400,600); //数据
$size = 140; //尺寸
$width = 750; //宽度
$height = 350; //高度
$legend = array("张三1","张三2","张三3","张三4","张三5","张三6","张三7","张三8");//说明
$chart = new Chart();
$chart->createhorizoncolumnar($title,$subtitle,$data,$size,$height,$width,$legend);
}
}
?>