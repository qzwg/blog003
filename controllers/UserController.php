<?php
namespace controllers;
use models\User;
use libs\Mail;
class UserController
{
    public function hello()
    {
        $user = new User;
        $name = $user->getName();

        return view('user.hello',[
            'name'=>$name,
        ]);
    }

    //注册
    public function register()
    {
        view('user.add');
    }

    //接受注册信息
    public function store()
    {
        $email = $_POST['email'];
        $password = md5($_POST['password']);

        
        //2、生成激活码
        $code = md5(rand(1,99999));
        //3.保存到REDIS
        $redis = \libs\Redis::getInstance();
        //序列化，数组转JSON字符串
        $value = json_encode([
            'email'=>$email,
            'password'=>$password,
        ]);

        //键名
        $key = "temp_user:{$code}";
        $redis->setex($key,300,$value);



        $user = new User;
        $ret = $user->add($email,$password);
        if(!$ret)
            die('注册失败');

        //激活码发送邮箱
        $name = explode('@',$email);
        $from = [$email,$name[0]];
        $message = [
            'title'=>'智聊系统-账号激活',
            'content'=>"点击以下链接进行激活：<br> 点击激活：
            <a href='http://localhost:9999/user/active_user?code={$code}'>
            http://localhost:9999/user/active_user?code={$code}</a><p>
            如果按钮不能点击，请复制上面链接地址，在浏览器中访问来激活账号！</p>",
            'from'=>$from,
        ];
        
        //把消息转成字符串

        $message = json_encode($message);

        $redis = \libs\Redis::getInstance(); 

        $redis->lpush('email',$message);

        echo 'ok';
    }

    //激活账号
    public function active_user()
    {
        //接收激活码
        $code = $_GET['code'];
        //到redis取出账号
        $redis = \libs\Redis::getInstance();
        $key = 'temp_user:'.$code;
        $data = $redis->get($key);
        //判断激活码是否有效
        if($data)
        {
            $redis->del($key);
            $data = json_decode($data,true);
            //插入到数据库
            $user->add($data['email'],$data['password']);
            header('Location:/user/login');
        }
        else
        {
            die('激活码无效');
        }
    }

    //登陆
    public function dologin()
    {
        $email = $_POST['email'];
        $password = md5($_POST['password']);

        $user = new User;
        if($user->login($email,$password))
        {
            message('登陆成功',2,'/blog/index');
        }
        else
        {
            message('用户名或者密码错误',1,'/user/login');
        }
    }

    //退出
    public function logout()
    {
        $_SESSION = [];
        die('退出成功');
    }

    
}