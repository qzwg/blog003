<?php
namespace controllers;
use models\Blog;
use PDO;
class BlogController
{
    public function mock()
    {
        $user = new Blog;
        for($i=0; $i<100; $i++)
        {
            $user->insert([
                'title' => $this->getChar(30),
                'content' => $this->getChar(200),
                'short_content' => $this->getChar(200),
                'display' => rand(5,1000),
                'is_show' => rand(0,1),
                'created_at' => date('Y-m-d H:i:s', rand(1000000000, 1535013653)),
            ]);   
        }
        echo 'ok';
    }

    function getChar($num)  // $num为生成汉字的数量
    {
        
    $b = '';
    for ($i=0; $i<$num; $i++) {
        // 使用chr()函数拼接双字节汉字，前一个chr()为高位字节，后一个为低位字节
        $a = chr(mt_rand(0xB0,0xD0)).chr(mt_rand(0xA1, 0xF0));
        // 转码
        $b .= iconv('GB2312', 'UTF-8', $a);
    }
    return $b;
    }
    public function create()
    {
        view('blogs.create');
    }

    public function index()
    {
        $blog = new Blog;
        $data = $blog->search();
   

        view('blogs.index',$data);
    }

    //详情页
    public function detail()
    {
        $id = $_GET['id'];
        $model = new Blog;
        $blog = $model->find($id);

        $blog['display']++;
        $model->update([
            'display'=>$blog['display']
        ],'id='.$id);

        view('blogs.detail',[
            'blog'=>$blog
        ]);
    }

    //生成静态页
    public function content_to_html()
    {
        $blog = new Blog;
        $blog->content_to_html();
    }

    //静态化首页
    public function index2htm()
    {
        $blog = new Blog;
        $blog->index2htm();
    }

    // 浏览量
    public function display()
    {
        $id = (int)$_GET['id'];
        $blog = new Blog;
        echo $blog->browseNum($id);
    }

    //browse_num回显数据路
    public function displayToDb()
    {
        $blog = new Blog;
        $blog->displayToDb();
    }

    //发表日志
    public function store()
    {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];

        $blog = new Blog;
        $blog->add($title,$content,$is_show);

        message('发表成功',2,'/blog/index');
    }
    
    
}