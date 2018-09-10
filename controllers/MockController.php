<?php
namespace controllers;
use Models\Base;
class MockController extends Base{
    //模拟200个账号
    public function users()
    {
        $pdo = new \PDO('mysql:host=127.0.0.1;dbname=basic_module', 'root', '123456');
        $pdo->exec('SET NAMES utf8');

        $pdo->exec('TRUNCATE users');

        for($i=0;$i<20;$i++)
        {
            $email = rand(50000,99999999999).'@126.com';
            $password = md5('123123');
            $pdo->exec("INSERT INTO users (email,password) VALUES('$email','$password')");
        }
    }
    //模拟300日志
    public function blog()
    {
        self::$pdo->exec('TRUNCATE blogs');
        for($i=0;$i<300;$i++)
            {
                $title = $this->getChar( rand(20,100) ) ;
                $content = $this->getChar( rand(100,600) );
                $display = rand(10,500);
                $is_show = rand(0,1);
                $date = rand(1233333399,1535592288);
                $date = date('Y-m-d H:i:s', $date);
                $user_id = rand(1,20);
                self::$pdo->exec("INSERT INTO blogs (title,content,display,is_show,created_at,user_id) VALUES('$title','$content',$display,$is_show,'$date',$user_id)");
            }
    }

    
}