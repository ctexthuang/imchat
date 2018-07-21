<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 下午3:55
 */

namespace ctexthuang\sql;

class mysql{
    public $where;
    public $order;
    public $limit;
    public $table;
    public $field;
    public $pageIndex;   //页数
    public $reVal;
    public $sqlText;
    public $connect;

    function __construct($connect = null){
        $debug=false;

        $this->where = '';
        $this->order = '';
        $this->limit = '';
        $this->pageIndex = 0;
        $this->sqlText = '';
        $this->reVal = array();

        if($connect != null){
            $this->connect = $connect;
        }else{
            global $connect;

            if(!empty($connect)){
                $this->connect = $connect;
            }else{
                exit('数据库连接失败');
            }
        }
    }

    function __destruct(){

    }

    function whereAnd($key,$val,$tag = '='){
        if(!isNum($val)){
            $val=addslashes($val);
        }

        switch($tag){
            case "=":
                $this->whereAnd[] = $key.'="'.$val.'"  ';
                break;
            case "in":
                $val = implode(',',$val);
                $this->whereAnd[] = $key.' in ('.$val.')  ';
                break;
            case ">":
                $this->whereAnd[] = $key.' > "'.$val.'"  ';
                break;

            case "is":
                $this->whereAnd[] = $key.' is '.$val.'  ';
                break;

            case "<":
                if(is_numeric($val)){
                    $this->whereAnd[] = $key.' < '.$val.'  ';
                }else{
                    $this->whereAnd[] = $key.' < "'.$val.'"  ';
                }
                break;

            case "like":
                $this->whereAnd[] = $key.' like "%'.$val.'%"  ';
                break;
        }

    }

    function whereOr($key,$val,$tag = '='){
        if(!isNum($val)){
            $val=addslashes($val);
        }

        switch($tag){
            case "=":
                $this->whereOr[] = $key.'="'.$val.'"  ';
                break;
            case "in":
                $val = implode(',',$val);
                $this->whereOr[] = $key.' in ('.$val.')  ';
                break;
            case ">":
                $this->whereOr[] = $key.' > "'.$val.'"  ';
                break;
            case "is":
                $this->whereOr[] = $key.' is '.$val.'  ';
                break;
            case "<":
                if(is_numeric($val)){
                    $this->whereOr[] = $key.' < '.$val.'  ';
                }else{
                    $this->whereOr[] = $key.' < "'.$val.'"  ';
                }
                break;
            case "like":
                $this->whereOr[] = $key.' like "%'.$val.'%"  ';
                break;
        }
    }

    function field($field){
        $this->field[] = $field;
        return $this;
    }

    function order($key,$order = 'asc'){
        $this->order = ' order by '.$key.' '.$order;
        return $this;
    }

    function limit($begin,$limitSize){
        $this->limit = ' limit '.$begin.','.$limitSize;
        return $this;
    }

    function page($pageIndex,$maxSize = 10){
        $this->pageIndex = $pageIndex;
        $this->maxSize = $maxSize;
        return $this;
    }

    function calculatePage(){
        if($this->pageIndex<=0){
            return false;
        }

        $totalRecords = $this->connect->getCount($this->table, $this->where);

        $totalPage = $totalRecords / $this->maxSize;

        if($totalPage <= 1){
            $totalPage = 1;
        }else if($totalRecords % $this->maxSize != 0){
            $totalPage = (int)$totalPage+1;
        }

        $this->reVal['page']['pageIndex'] = $this->pageIndex;
        $this->reVal['page']['totalPages'] = $totalPage;
        $this->reVal['page']['totalRecores'] = $totalRecords;
        $this->reVal['page']['maxSize'] = $this->maxSize;

        $begin=($this->pageIndex-1)*$this->maxSize;
        $this->limit($begin,$this->maxSize);
    }

    function buil(){
        if(isset($this->field) && is_array($this->field)){
            $this->field = implode(',',$this->field);
        }else{
            $this->field = '*';
        }

        if(isset($this->whereAnd) && is_array($this->whereAnd)){
            $this->where = implode(' and ',$this->whereAnd);
        }else{
            $this->where = ' 1 ';
        }

        if(isset($this->whereOr) && is_array($this->whereOr)){
            if($this->where == 1){
                $this->where = implode(' or ',$this->whereOr);
            }else{
                $this->where .= 'and ('.implode(' or ',$this->whereOr).')';
            }
        }
    }


    function counts(){
        $this->genQuerySql();
        return 	$this->connect->getCount($this->table, $this->where);
    }

    function genQuerySql(){
        $this->buil();

        $this->sqlText='select '.$this->field.' from '.$this->table;
        if(!empty($this->where))$this->sqlText .= ' where '.$this->where;
        if(!empty($this->order))$this->sqlText .= $this->order;

    }

    function select($result_model=0){

        $this->genQuerySql();

        /*******执行分页操作********/

        $this->calculatePage();

        if(!empty($this->limit))$this->sqlText .=$this->limit;

        //echo $this->sqlText;
        $this->connect->query($this->sqlText);
        if($result_model==0)$this->reVal['rows']= $this->connect->recordToArrayDig();
        else $this->reVal['rows']= $this->connect->recordToArray();

        //print_r($this->reVal);

        return $this->reVal;

    }

    function find(){
        $this->genQuerySql();
        $this->sqlText .= ' limit 0,1 ';
        if(defined('SQLOUT') &&  SQLOUT )	echo $this->sqlText.'<br />';
        //$this->reVal=$this->connect->getOneRow($this->sqlText);
        return $this->connect->getOneRow($this->sqlText);
    }




    function insert($param){
        $sqlText=$this->parseArrayToSql($this->table,array('id'),$param);

        if($this->connect->query($sqlText)){
            return $this->connect->last_Insert_ID();
        }else{
            return false;
        }
    }

    function  update($param){

        //print_r($param);
        $sqlText=$this->parseArrayToSql($this->table,$param['keys'],$param['field'],2);
        //echo $sqlText;exit();
        if($this->connect->query($sqlText)){
            return true;
        }else{
            return false;
        }
        //return $param;

    }



    function delete(){
        $this->buil();
        $sqlText=' delete from '.$this->table;
        if(!empty($this->where))$sqlText .=' where '.$this->where;

        if($this->connect->query($sqlText)){
            return true;
        }else{
            return false;
        }

    }


    //根据ID 某个字段值的剩余值
    function calWorth($param){

        //	print_r($param);
        $id=$param['id'];
        $key=$param['key'];
        $worth=$param['worth'];
        $sqlText='update '.$this->table.' set '.$key.'='.$key.$worth.' where id='.$id;

        return $this->connect->query($sqlText);

    }


    //行集合
    function sets($key){
        $this->buil();
        $this->sqlText='select group_concat('.$key.') from '.$this->table;
        if(!empty($this->where))$this->sqlText .=' where '.$this->where;

        return $this->connect->getFirstValue($this->sqlText);
    }

    //字段结构
    function  showColumns(){

        $sqlText='SHOW FULL COLUMNS FROM '.$this->table;
        $this->connect->query($sqlText);
        return $this->connect->recordToArray();

    }
    //组件

    private function parseArrayToSql($TableHeading, $aKeys ,$pArray, $mode=1,$updateStr=''){   //默认返回添加的sql
        if($mode==1){    //如果是 $mode==1 删除相关的主键
            foreach ($aKeys as $ukey){
                if(array_key_exists( $ukey, $pArray ))unset($pArray[$ukey]);
            }
        }
        /*if($mode==1){
            $pArray=array_filter($pArray,"clearNullString");  //删除空函数
        }*/
        $aKey = array_keys( $pArray );
        $fKey = join( $aKey, ',' );
        $fValue = ''; //values
        $keyAndvalue = '';   //key=value
        $get_magic=get_magic_quotes_gpc();    //检查是否开心了服务器的$_POST的值检查，对特殊字符加上反斜杠
        foreach( $pArray as $key=>$value ){

            if(!$get_magic)$value=addslashes($value);   //默认情况下
            if(is_array($value))$value=implode(',', $value);
            $fValue .= ",'" . $value . "'";
            if( !in_array( $key, $aKeys ) ){
                $keyAndvalue .= ',' . $key . "='" . $value . "'";
            }
            else{
                $keyString= ',' . $key . "='" . $value . "'";
            }
        }
        $fValue=substr($fValue, 1);
        $keyAndvalue=substr($keyAndvalue, 1);

        if($mode==1){
            $sqlText= sprintf('insert into %s(%s) values(%s)',$TableHeading, $fKey,$fValue);
        }else if($mode==2){
            $keyString=substr($keyString, 1);
            $sqlText= sprintf('update  %s  set %s where %s',$TableHeading ,$keyAndvalue,$keyString);
        }else if($mode==3){
            $sqlText= sprintf('insert into %s(%s) values(%s) on duplicate key update  %s', $TableHeading, $fKey,$fValue,$keyAndvalue);
        } else if($mode==4){
            $sqlText= sprintf('insert into %s(%s) values(%s) on duplicate key update  %s', $TableHeading, $fKey,$fValue,$updateStr);
        }
        return $sqlText;
        //insert into lee_froums(fname,id) values('08908','3') on duplicate key update fname='08908'
        //$this->_="insert into $TableHeading($str1) values($str2) on duplicate key update  $str3";
    }
}