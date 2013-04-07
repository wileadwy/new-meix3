<?php
if (!defined('THINK_PATH')) exit();
ini_set('session.cookie_domain', "meix.com");//跨域访问Session
return array(
	'SITENAME'=>'w.meix.com',//'www.meix.com',
    'SITENAMEAD'=>'http://ad.meix.com',
	'SITEURL'=>'http://w.meix.com/index.php',//'http://www.meix.com/index.php',
    'SITE_SINACALLBACKURL'=>'http://w.meix.com/index.php/Login/sinalogin',//http://www.meix.com/index.php/Login/sinalogin
    //'HTML_CACHE_ON' => true, // 开启静态缓存
    //'URL_ROUTER_ON' => true, // 开启路由转换
    'LOG_RECORD'=>true,
    'LOG_EXCEPTION_RECORD'  => true,
    'DB_FIELDS_CACHE'=> false, // 字段缓存信息
    
    'DB_TYPE'=>'mysql',
  	'DB_HOST'=>'localhost',
  	'DB_NAME'=>'meix',//meix
  	'DB_USER'=>'root',//root
  	'DB_PWD'=>'789456',//789456
  	'DB_PORT'=>'3306',
  	'DB_PREFIX'=>'mx_',
);
?>