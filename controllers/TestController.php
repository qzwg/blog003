<?php
namespace controllers;
// require ROOT . 'vendor/autoload.php';
class TestController
{
    public function register()
    {

        $redis = \libs\Redis::getInstace();
        
        $data = [
            'email'=>'17805202075@126.com',
            'title'=>'标题',
            'content'=>'内容',
        ];

        $data = json_encode($data);
        $redis->lpush('email',$data);

        echo '注册成功';
    }

    public function mail()
    {
        ini_set('default_socket_timeout',-1);
        echo '启动。。。。';
        $redis = \libs\Redis::getInstace();

        while(true)
        {
            $data = $redis->brpop('email',0);
            echo '开始发邮件';
            echo "发完邮件，继续等待 \r\n";
        }
    }

    public function testMail()
    {
        // 设置邮件服务器账号
        $transport = (new \Swift_SmtpTransport('smtp.126.com', 25))  // 邮件服务器IP地址和端口号
        ->setUsername('czxy_qz@126.com')       // 发邮件账号
        ->setPassword('12345678abcdefg');      // 授权码

        // 创建发邮件对象
        $mailer = new \Swift_Mailer($transport);

        // 创建邮件消息
        $message = new \Swift_Message();

        $message->setSubject('测试标题')   // 标题
                ->setFrom(['czxy_qz@126.com' => '全栈1班'])   // 发件人
                ->setTo(['fortheday@126.com', 'fortheday@126.com' => '你好'])   // 收件人
                ->setBody('Hello <a href="http://localhost:9999">点击激活</a> World ~', 'text/html');     // 邮件内容及邮件内容类型

        // 发送邮件
        $ret = $mailer->send($message);

        var_dump( $ret );
    }
}