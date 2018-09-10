<?php
namespace models;
class User extends Base
{
    public $tableName = 'users';

    public function getName()
    {
        return 'tom';
    }

    public function add($title,$content,$is_show)
    {
        $stmt = slef::$pdo->prepare("INSERT INTO users (title,content,is_show,user_id) VALUES(?,?,?,?)");
        $ret = $stmt->execute([
            $title,
            $content,
            $is_show,
            $_SESSION['id'],
        ]);

        if(!$ret)
        {
            echo '失败';
            $error = $stmt->errorInfo();
            echo '<pre>';
            var_dump($error);
            exit;
        }

        return self::$pdo->lastInsertId();

        
    }

    public function login($email,$password)
    {
        $stmt = self::$pdo->prepare('SELECT * FROM users WHERE email=? AND password=?');
        $stmt->execute([
            $email,
            $password
        ]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($user)
        {
            $_SESSION['id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            return TRUE;
        }
        else
            return FALSE;
    }
    

}
