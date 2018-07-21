<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 下午2:25
 */

namespace api\common;
use \ctexthuang\api\base;

class check extends base{

}

namespace api\common\admin;
use \ctexthuang\api\base;
class check extends base{
    protected $admin;
    protected $adminId;

    protected function init(){
        $token = $this->must('authToken');
        $this->admin = S('auth\admin','check',$token);

        $this->adminId = $this->admin['adminId'];
        $this->delParam('authToken');
    }
}