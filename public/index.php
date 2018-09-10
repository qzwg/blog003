<?php
ini_set('session.save_handler','redis');
ini_set('session.save_path','tcp://127.0.0.1:6379?database:3');
ini_set('session.gcmaxlifetime',600);
session_start();

define("ROOT",dirname(__FILE__) . '/../');

//类的自动加载
function autoLoadClass($class)
{
    require ROOT . str_replace('\\','/',$class) . '.php';
}

spl_autoload_register('autoLoadClass');
//URL的获取

if(php_sapi_name() == 'cli')
{
    $controller = ucfirst($argv[1]) . 'Controller';
    $action = $argv[2];
}
else
{
    if(isset($_SERVER['PATH_INFO']))
    {
        $pathInfo = $_SERVER['PATH_INFO'];
        $pathInfo = explode('/',$pathInfo);
        $controller = ucfirst($pathInfo[1]) . 'Controller';
        $action = $pathInfo[2];
    }
    else
    {
        $controller = 'IndexController';
        $action = 'index';
    }  
}
    




//请求分发

$controllers = "controllers\\$controller";
$_C = new $controllers;
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
    
    foreach($except as $k => $v)
    {  
       
        unset($_GET[$v]);
    }
    
    foreach($_GET as $k=>$v)
    {
        $ret .= "&$k=$v";
    }
    
    return $ret;
}

function config($name)
{
    static $config = null;
    if($config === null)
    {
        $config = require(ROOT . 'config.php');
    }
    return $config[$name];
}

function redirect($route)
{
    header('Location:'.$route);
    exit;
}
function back()
{
    redirect($_SERVER['HTTP_REFERER']);
}

function message($message,$type,$url,$seconds = 5)
{
    if($type ==0)
    {
        echo "<script>alert('{$message}');location.href='{$url}';</script>";
        exit;
    }
    else if($type == 1)
    {
        view('common.success',[
            'message'=>$message,
            'url'=>$url,
            'seconds'=>$seconds
        ]);
    }
    else if($type==2)
    {
        $_SESSION['_MESS_'] = $message;
        redirect($url);
    }
}