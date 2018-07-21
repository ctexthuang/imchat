<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 下午2:44
 */

namespace ctexthuang\db;

class mysqlDB{
    private $conn;
    protected $qRs;
    public $_rs;

    function __construct($host,$dbuser,$dnpwd,$dbname){
        $this->conn = mysqli_connect($host,$dbuser,$dnpwd,$dbname);

        if (mysqli_connect_error()){
            printf("数据库连接失败:%s\n",mysqli_connect_error());
            exit();
        }

        mysqli_set_charset($this->conn,'UTF8');
    }

    function __destruct(){
        mysqli_close($this->conn);
    }

    function query($sqlText){
        return self::runQuery($sqlText);
    }

    function checkRecord($sqlText){
        self::runQuery($sqlText);

        if ($this->_rs = mysqli_fetch_assoc($this->qRs)){
            return true;
        }else{
            return false;
        }
    }

    function getOneRow($sqlText){
        self::runQuery($sqlText);

        $this->_rs = mysqli_fetch_assoc($this->qRs);
        return $this->_rs;
    }

    function getFirstValue($sqlText){
        self::runQuery($sqlText);

        $this->_rs = mysqli_fetch_assoc($this->qRs);

        if($this->_rs){
            $this->_rs = current($this->_rs);
            return $this->_rs;
        }else{
            return false;
        }
    }

    function affectedRows(){
        $affect = mysqli_affected_rows($this->conn);
        return $affect ;
    }

    function last_Insert_ID(){
        $ID = mysqli_insert_id($this->conn);
        return $ID;
    }

    function getRowsCount(){
        return mysqli_num_rows($this->qRs);
    }

    function getNextRow(){
        $this->_rs = mysqli_fetch_assoc($this->qRs);
        return is_array($this->_rs);
    }

    function getCount($tableHeading,$where = 1){
        $sqlText = "select count(*) from $tableHeading ";

        if(!empty($where)){
            $sqlText.=" where $where ";
        }
        return $this->getFirstValue($sqlText);
    }

    function recordToArray(){
        $Result = array();
        $ColCount = mysqli_field_count($this->conn);
        $Row = mysqli_fetch_assoc($this->qRs);

        while($Row){
            if(1 == $ColCount)
                $Result[] = $Row[0];
            elseif(2 == $ColCount){
                $Values = array_values($Row);
                $Result[$Values[0]] = $Values[1];
            }elseif(2 < $ColCount){
                $Key = array_shift( $Row );
                $Result[$Key] = $Row;
            }
            $Row = mysqli_fetch_assoc($this->qRs);
        }
        return $this->_rs = $Result;
//        unset($Result);
    }

    private function runQuery($sqlText){
        $this->qRs = mysqli_query($this->conn,$sqlText);

        if( mysqli_errno($this->conn)){
            echo $sqlText.mysqli_error($this->conn);
            exit();
        }
        return $this->qRs;
    }

    function recordToArrayDig(){   //数字key数组
        $Result = [];
        $ColCount = mysqli_field_count($this->conn);
        $Row = mysqli_fetch_assoc($this->qRs);

        $i=0;
        while($Row){
            $Result[$i] = $Row;
            $Row = mysqli_fetch_assoc($this->qRs);
            $i++;
        }

        return $this->_rs = $Result;
//        unset($Result);
    }
}