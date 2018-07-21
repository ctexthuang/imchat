<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 上午11:06
 */

return [
    //登录
    'account/login' => ['account\auth\login','entry'],
    //注册
    'account/register' => ['account\reg\register','regs'],
    'account/check_email' => ['account\reg\register','checkemail']
];