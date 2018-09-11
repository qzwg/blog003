<?php
ini_set('session.save_handler','redis');
ini_set('session.save_path','tcp://127.0.0.1:6379?database:3');
ini_set('session.gcmaxlifetime',600);
session_start();

//防csrf攻击
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!isset($_POST['_token']))
        die('违法操作！');
    if($_POST['_token'] != $_SESSION['token'])
        die('违法操作！');
}


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

//编辑器过滤 xss
function hpe($content)
{
    static $purifier = null;
    if($purifier === null)
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'utf-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('Cache.SerializerPath', ROOT.'cache');
        $config->set('HTML.Allowed', 'div,b,strong,i,em,a[href|title],ul,ol,ol[start],li,p[style],br,span[style],img[width|height|alt|src],*[style|class],pre,hr,code,h2,h3,h4,h5,h6,blockquote,del,table,thead,tbody,tr,th,td');
        $config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,margin,width,height,font-family,text-decoration,padding-left,color,background-color,text-align');
        $config->set('AutoFormat.AutoParagraph', TRUE);
        $config->set('AutoFormat.RemoveEmpty', TRUE);
        $purifier = new \HTMLPurifier($config);
    }

    return $purifier->purify($content);
}

//csrf 过滤
function csrf()
{
    if(!isset($_SESSION['token']))
    {
        $token = md5( rand(1,99999) . microtime() );
        $_SESSION['token'] = $token;
    }
    return $_SESSION['token'];
}

//生成令牌隐藏域
function csrf_field()
{
    $csrf = isset($_SESSION['token']) ? $_SESSION['token'] : csrf();
    echo "<input type='hidden' name='_token' value='{$csrf}'>";
}