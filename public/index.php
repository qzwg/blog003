<?php
define("ROOT",dirname(__FILE__) . '/../');

//类的自动加载
function autoLoadClass($class)
{
    require ROOT . str_replace('\\','/',$class) . '.php';
}

spl_autoload_register('autoLoadClass');
//URL的获取
function route()
{
    $url = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
    $defaultController = 'IndexController';
    $defaultAction = 'index';
    if($url == '/')
    {
        return [
            $defaultController,
            $defaultAction
        ];
    }
    else if(strpos($url,'/',1) !== FALSE)
    {
        $url = ltrim($url,'/');
        $route = explode('/',$url);
        $route[0] = ucfirst($route[0]) . 'Controller';
        return $route;
    }
    else
    {
        die("请求的URL不正确");
    }
}

$route = route();
//请求分发

$controller = "controllers\\{$route[0]}";
$action = $route[1];

$_C = new $controller;
$_C->$action();


//view方法
function view($file,$data=[])
{
    if($data)
        extract($data);
        require ROOT . 'views/' . str_replace('.','/',$file) . '.html';
}

//获取get参数
function getUrlParms($except = [])
{
    $ret = '';

    foreach($except as $v)
    {
        unset($_GET[$v]);
    }
    
    foreach($_GET as $k=>$v)
    {
        $ret .= "&$k=$v";
    }
    return $ret;
}