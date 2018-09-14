<?php
namespace controllers;
class ToolController{

    public function __construct()
    {
        if(config('mode') != 'dev')
        {
            die('非法访问');
        }
    }

    public function users()
    {
        $model = new \models\User;
        $data = $model->getAll();
        echo json_encode([
            'status_code' => 200,
            'data' => $data,
        ]);
    }

    //切换账号
    public function login()
    {
        $email = $_GET['email'];
        $_SESSION = [];
        $user = new \models\User;
        $user->login($email,md5('123123'));
    }
}