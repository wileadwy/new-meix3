<?php
/*
	屁屁哥哥共享飞信号免费发短信代码

	群发最好使用循环， 而且循环的速度需要控制，最好是1秒一次，

	发短信之前，必须要先加为飞信好友

	这个短信功能有限，适合企业内部的短信提醒功能等

	剩下的根据自己的需要，改良吧
*/


/*
	使用前  一定要先把 xxxxx  改成需要的手机号和密码以及内容哦，亲！
*/

$phone = '13798957336';   //自己或准备发送短信的手机号或飞信号
$pwd = 'qilong@677336';			//飞信的密码  这里是明文
$to = '13798957524';	//被发送的手机号
$msg = '测试飞信短信接口 feixin 2012-12-15日！';		//发送的内容  不要超过 255个字节
$url="http://2.ibtf.sinaapp.com/?phone=".$phone."&pwd=".$pwd."&to=".$to."&msg=".$msg;  
$str = file_get_contents($url);
$test = str_replace('div>发送成功<iframe','111111',$str,$i);
if($i == 0){
	$test = str_replace('div>发送失败<iframe','111111',$str,$j);
	if($j == 0){
		echo "操作过快或接口发生异常，请重新发送一遍";
	}else{
		echo "发送失败";
	}
}else{
	echo "发送成功";
}
?>


