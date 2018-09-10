<?php
namespace models;
use PDO;
require ROOT . 'vendor/autoload.php';
class Blog extends BaseModel
{
    public $tableName = 'blogs'; 

    public $pdo;

    public function __construct()
    {
        $this->pdo = new PDO('mysql:host=127.0.0.1;dbname=basic_module', 'root', '123456');
        $this->pdo->exec('SET NAMES utf8');
    }

    //注册
    public function add($email,$password)
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (email,password) VALUES(?,?)");
        return $stmt->execute([
            $email,
            $password,
        ]);
    }

    public function search()
    {
        $where = 1;
        $value = [];

         //======搜索
         if(isset($_GET['keywords']) && $_GET['keywords'])
         {
             //关键字
             $where .= " AND (title LIKe ? OR content LIKE ?)";
             $value[] = '%' . $_GET['keywords'] . '%';
             $value[] = '%' . $_GET['keywords'] . '%';

         }
 
         //发表日期 起始
         if(isset($_GET['start_date']) && $_GET['start_date'])
         {
             $where .= " AND created_at >= ?";
             $value[] = $_GET['start_date'];
         }
 
         //末日期
         if(isset($_GET['end_date']) && $_GET['end_date'])
         {
             $where .= " AND created_at <= ?";
             $value[] = $_GET['end_date'];
         }
 
         //是否显示
         if(isset($_GET['is_show']) && ($_GET['is_show'] == 1 || $_GET['is_show'] === '0'))
         {
             $where .= " AND is_show = ?";
             $value[] = $_GET['is_show'];
         }
 
         //======排序
         //默认排序条件
         $odby = 'created_at';
         $odway = 'desc';
 
         if(isset($_GET['order_by']) && $_GET['order_by'] == 'display')
         {
             $odby = 'display';
         }
         
         if(isset($_GET['order_way']) && $_GET['order_way'] == 'asc')
         {
             $odway = 'asc';
         }
 
         //====翻页
         $perpage = 15;
         $page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
         $offset = ($page-1)*$perpage;
         $limit = $offset . ',' . $perpage;
         //=======显示翻页按钮
         //总记录数
         $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM blogs WHERE $where");
         $stmt->execute($value);
         $count = $stmt->fetch(PDO::FETCH_COLUMN);

         $pageCount = ceil($count/$perpage);
         $btns = '';
         for($i=1;$i<$pageCount;$i++)
         {   
             $params = getUrlParms(['page']);
          
             $class = $page == $i ? 'page_active' : '';
         
             $btns .= "<a class='$class' href='?{$params}&page=$i'>{$i}</a>";
         }

         //===执行
         $stmt = $this->pdo->prepare("SELECT * FROM blogs WHERE $where ORDER BY $odby $odway LIMIT $offset,$perpage");
         
         $stmt->execute($value);
         
         $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
         return [
             'btns'=>$btns,
             'blogs'=>$blogs,
         ];
    }

    //生成静态页
    public function content_to_html()
    {
        // echo "12";   
        $stmt = $this->pdo->query("SELECT * FROM blogs");
        $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //开启缓冲
        ob_start();
        //写入数据
        foreach($blogs as $v)
        {
            view('blogs.content',[
                'blog'=>$v,
            ]);

            //取出
            $str = ob_get_contents();
            file_put_contents(ROOT . 'public/contents/' . $v['id'] . '.html',$str);
            ob_clean();
        }

    }

    //静态化首页
    public function index2htm()
    {
        $stmt = $this->pdo->query("SELECT * FROM blogs WHERE is_show=1 ORDER BY id DESC LIMIT 20");
     
        $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        ob_start();
        view('index.index',[
            'blogs'=>$blogs,
        ]);

        $str = ob_get_contents();
        file_put_contents(ROOT . 'public/index.html',$str);
    }

    //浏览量
    public function browseNum($id)
    {
        $key = "blog-{$id}";
        $redis = new \Predis\Client([
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379,
        ]);

        
        if($redis->hexists('blog_num',$key))
        {
            $newNum = $redis->hincrby('blog_num',$key,1);
            return $newNum;
        }
        else
        {
            $stmt = $this->pdo->prepare('SELECT display FROM blogs WHERE id=?');
            $stmt->execute([$id]);
            $display = $stmt->fetch(PDO::FETCH_COLUMN);

            $display++;
            $redis->hset('blog_num',$key,$display);
            return $display;
        }
        
    }

    //同步浏览量
    public function displayToDb()
    {
        $redis = new \Predis\Client([
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379,
        ]);
    
        $data = $redis->hgetall('blog_num');
        
        foreach($data as $k => $v)
        {
            $id = str_replace('blog-','',$k);
            $sql = "UPDATE blogs SET display={$v} WHERE id = {$id}";
            $this->pdo->exec($sql);
        }
    }
}