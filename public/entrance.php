<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 上午10:53
 */

$app_path = dirname(__DIR__);

define('APPPATH',$app_path);
define('SQLOUT',0);

$host = $_SERVER['HTTP_HOST'];

$_C = include_once APPPATH.'/config/config.php';

define('EVN',$_C['evn']);

include_once APPPATH.'/vendor/autoload.php';

include_once APPPATH.'/model/index.php';
include_once APPPATH.'/api/index.php';