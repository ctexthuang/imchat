<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 下午3:10
 */

namespace api\account;

use \ctexthuang\api\base;

class auth extends base{
    protected $jwtObj;

    function init(){

    }
}


namespace api\account\auth;

use \ctexthuang\api\base;

class login extends base{
    function entry(){
        $account = $this->must('account');
        $password = md5($this->must('password'));

        $user = M('user','one',[
            'account' => $account
        ]);

        if(empty($user)){
            return error('001','用户不存在');
        }

        if($password != $user['password']){
            return error('001','密码错误');
        }

        if (2 != $user['check_email']){
            return error('002','该账号未确认邮箱');
        }

        $now_date = date("Y-m-d H:i:s",time());
        $timestamp = strtotime($now_date);
        $token = hash("sha256",$timestamp.'admin'.$user['account']);

        return success([
            'token' => $token,
            'account' => $user['account']
        ]);
    }
}

namespace api\account\reg;

use \ctexthuang\api\base;

class register extends base{
    function regs(){
        $account = $this->must('account');
        $this->must('email');

        $user=M('user','one',[
            'account' => $account
        ]);

        if(!empty($user)){
            return  error('001','登录账号已存在');
        }
        $_C = include APPPATH.'/config/config.php';
        $sum = bin2hex(make_string(40));

        $token = str_shuffle($sum.$_C['des_key']);

        $data = $this->pick(array('account','password','email'));

        $data['password'] = md5($data['password']);
        $data['token'] = $token;
        $data['check_email'] = 1;
        $data['create_time'] = date("Y-m-d H:i:s");
        $data['update_time'] = date("Y-m-d H:i:s");

        $res = M('user','add',$data);

        $activation = 'http://'.$_SERVER['SERVER_NAME'].'/public/api/v1/account/check_email?token='.$token;
        $content = '点击链接激活邮箱:<a href='.$activation.'>'.$activation.'</a>';

        include_once APPPATH.'/vendor/ctexthuang/src/sendEmail.php';
        $email = new \sendEamil();

        $red = $email->sendEamilAliyun($data['email'],'imchat注册邮箱激活',$content);
        if (!isset($red['EnvId'])){
            return success([
                'data' => $res,
                'msg' => '注册成功,验证邮箱发送失败'
            ]);
        }else{
            return success([
                'data' => $res,
                'msg' => '注册成功,邮箱发送成功'
            ]);
        }
    }

    function checkemail(){
        if (isset($_GET['token'])){
            $token = $_GET['token'];
        }else{
            return error('002','email验证失败，请重新验证');
        }

        $user=M('user','one',[
            'token' => $_GET['token']
        ]);

        if (1 != $user['check_email']){
            if (2 == $user['check_email']){
                return error('003','该账号已激活');
            }else{
                return error('004','该账号异常');
            }
        }
        $_C = include APPPATH.'/config/config.php';
        $sum = bin2hex(make_string(40));
        $tokenupdate = str_shuffle($sum.$_C['des_key']);

        if ($token != $user['token']){
            return error('002','该账号验证邮箱失败，请重新验证');
        }

        $data = [
            'id' => $user['id'],
            'token' => $tokenupdate,
            'check_email' => 2
        ];

        $res = M('user','save',$data);
        return success($res);
    }
}