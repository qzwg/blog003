<?php
namespace controllers;
class IndexController
{
    public function index()
    {
        $blog = new \models\Blog;
        $blogs = $blog->getNew();
        
        $user = new \models\User;
        $users = $user->getActiveUsers();

        View('index.idnex',[
            'blogs' => $blogs,
            'users' => $users,
        ]);
    }
}