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

    //用户排行
    public function computeActiveUsers()
    {
        //取日志分值
        $stmt = slef::$pdo->query('SELECT user_id,COUNT(*)*5 fz 
                                    FROM blogs 
                                        WHERE created_at >=DATE_SUB(CURDATE(),INTERVAL 1 WEEK) 
                                            GROUP BY user_id');
        $data1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //获取评论分值
        $stmt = self::$pdo->query('SELECT user_id,COUNT(*)*3 fz
                                    FROM comments
                                        WHERE created_at >=DATE_SUB(CURDATE(),INTERVAL 1 WEEK) 
                                            GROUP BY user_id');
        $data2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //取点赞分值
        $stmt = self::$pdo->query('SELECT user_id,COUNT(*) fz
                                    FROM blog_agrees
                                        WHERE created_at >=DATE_SUB(CURDATE(),INTERVAL 1 WEEK) 
                                            GROUP BY user_id');
        $data3 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $arr = [];

        foreach($data1 as $v)
        {
            $arr[$v['user_id']] = $v['fz'];
        }

        foreach($data2 as $v)
        {
            if(isset($arr[$v['user_id']]))
                $arr[$v['user_id']] += $v['fz'];
            else
                $arr[$v['user_id']] = $v['fz'];
        }

        foreach($data3 as $v)
        {
            if(isset($arr[$v['user_id']]))
                $arr[$v['user_id']] += $v['fz'];
            else
                $arr[$v['user_id']] = $v['fz'];
        }

        arsort($arr);
        $data = array_slice($arr,0,20,TRUE);

        $usersIds = array_keys($data);
        $userIds = implode(',',$userIds);

        $sql = "SELECT id,email,avatar FROM users WHERE id IN($userIds)";
        $stmt = self::$pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $redis = \libs\Redis::getInstance();
        $redis->set('active_users',json_encode($data));
                                    
    }

    public function getActiveUsers()
    {
        $redis = \libs\Redis::getInstance();
        $data = $redis->get('active_users');
        return json_decode($data,true);
    }

    

}
