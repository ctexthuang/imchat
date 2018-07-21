<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/19
 * Time: 下午4:54
 */

require_once __DIR__.'/../common/Toolkit.php';
$config = require_once __DIR__.'/config.php';

$mysql_conf = $config['datebase'];
$mysqli = @new mysqli($mysql_conf['host'], $mysql_conf['db_user'], $mysql_conf['db_pwd']);
if ($mysqli->connect_errno) {
    die("could not connect to the database:\n" . $mysqli->connect_error);//诊断连接错误
}
$mysqli->query("set names 'utf8';");//编码转化
$select_db = $mysqli->select_db($mysql_conf['db']);
if (!$select_db) {
    die("could not connect to the db:\n" .  $mysqli->error);
}