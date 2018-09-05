<?php
namespace models;
use PDO;
class BaseModel
{  
    private static $_pdo = null;
    private $_dbname = 'basic_module';
    private $_host = '127.0.0.1';
    private $_user = 'root';
    private $_password = '123456';

    public function __construct()
    {
        if(self::$_pdo === null)
        {
            self::$_pdo = new PDO('mysql:dbname='.$this->_dbname.';host='.$this->_host, 
                                $this->_user, 
                                $this->_password);
            self::$_pdo->exec('SET NAMES utf8');
        }
    }

    public function exec($sql)
    {
        $ret = self::$_pdo->exec($sql);
        if($ret === false)
        {
            echo $sql,'<hr>';
            $error = self::$_pdo->errorInfo();
            die($error[2]);
        }
        return $ret;
    }

    public function insert($data)
    {
        $keys = array_keys($data);
        $values = array_values($data);
       
        $keyString = implode(',',$keys);
        $valueString = implode("','",$values);
        $sql = "INSERT INTO {$this->tableName} ($keyString) VALUES('$valueString')";
        $this->exec($sql);

        return self::$_pdo->lastInsertId();

    }

    public function update($data,$where)
    {
        $_arr = [];
        foreach($data as $k => $v)
        {
            $_arr[] = "$k='$v'";
        }

        $sets = implode(',',$_arr);

    $sql = "UPDATE {$this->tableName} SET $sets WHERE $where";

    return $this->exec($sql);
    }

    public function delete($where)
    {
    $sql = "DELETE FROM {$this->tableName} WHERE $where";
    return $this->exec($sql);
    }

    public function query($sql)
    {
        $ret = self::$_pdo->query($sql);
        if($ret === false)
        {
            echo $sql,'<hr>';
            $error = self::$_pdo->errorInfo();
            die($error[2]);
        }

        $ret->setFetchMode(PDO::FETCH_ASSOC);
        return $ret;
    }

    public function get($sql)
    {
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }

    public function getRow($sql)
    {
        $stmt = $this->query($sql);
        return $stmt->fetch();
    }

    public function getOne($sql)
    {
        $stmt = $this->query($sql);
        return $stmt->fetchColumn();
    }

    public function count($where = 1)
    {
    $sql = "SELECT COUNT(*) FROM {$this->tableName} WHERE $where";
    return $this->getOne($sql);
    }

    public function find($id,$select='*')
    {
        $sql = "SELEcT {$select} FROM {$this->tableName} WHERE id={$id}";
        return $this->getRow($sql);
    }

    
}