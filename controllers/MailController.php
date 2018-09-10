<?php
namespace controllers;
use libs\Mail;
require ROOT . 'vendor/autoload.php';
class MailController
{
    public function send()
    {
        $redis = \libs\Redis::getInstace();

        $mailer = new Mail;
        ini_set('default_socket_timeout',-1);
        echo '发邮件队列启动成功';

        while(true)
        {
            $data = $redis->brpop('email',0);
            var_dump($data);
            $message = json_decode($data[1],TRUE);
            
            $mailer->send($message['title'],$message['content'],$message['from']);

            echo "发送邮件成功!继续等待下一个。\r\n";
        }

    }
}