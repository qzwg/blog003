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
            $_SESSION['money'] = $user['money'];
            return TRUE;
        }
        else
            return FALSE;
    }

    public function addMoney($money,$userId)
    {
        $stmt = self::$pdo->prepare("UPDATE　users SET money=money+? WHERE id=?");
        $stmt->execute([
            $money,
            $userId
        ]);

        $_SESSION['money'] += $money;
    }

    public function getMoney()
    {
        $id = $_SESSION['id'];
        $stmt = self::$pdo->prepare('SELECT money FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $money = $stmt->fetch(PDO::FETCH_COLOUM);
        $_SESSION['money'] = $money;
        return $money;
    }

    public function setAvatar($path)
    {
        $smtt = self::$pdo->prepare('UPDATE users SET avatar=? WHERE id=?');
        $stmt->execute([
            $path,
            $_SESSION['id']
        ]);

    }

    //获取所有账号方法
    public function getAll()
    {
        $stmt = self::$pdo->query('SELECT * FROM users');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    

}
