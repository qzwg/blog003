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

        $display = $blog->getDisplay($id);

        echo json_encode([
            'display' => $display,
            'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '',
        ]);


        echo $blog->browseNum($id);
    }

    //browse_num回显数据路
    public function displayToDb()
    {
        $blog = new Blog;
        $blog->displayToDb();
    }

    //发表日志,
    public function store()
    {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];

        $blog = new Blog;
        $id = $blog->add($title,$content,$is_show);

        if($is_show == 1)
        {
            $blog->makeHtml($id);
        }
        else
        {
            $blog->deleteHtml($id);
        }

        

        message('发表成功',2,'/blog/index');
    }

    public function delete()
    {
        $id = $_GET['id'];
        $blog = new Blog;
        $blog->delete($id);

        message('删除成功',2,'/blog/index');
    }

    //修改日志
    public function update()
    {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];
        $id = $_POST['id'];

        $blog = new Blog;
        $blog->update($title, $content, $is_show, $id);
        message('修改成功！',0,'/blog/index');
    }

    //私有日志
    public function content()
    {
        $id = $_GET['id'];
        $model = new Blog;
        $blog = $model->find($id);

        //判断是否是本人日志
        if($_SESSION['id'] != $blog['user_id'])
            die('无权访问！');

        view('blogs.content',[
            'blog'=>$blog,
        ]);
    }

    //点赞
    public function agreements()
    {
        $id = $_GET['id'];
        //判断是否登陆
        if(!isset($_SESSION['id']))
        {
            echo json_encode([
                'status_code' => '403',
                'message'=>'必须先登陆'
            ]);

            exit;
        }

        //点赞
        $model = new \models\Blog;
        $ret = $model->agree($id);
        if($ret)
        {
            echo json_encode([
                'status_code' => '200',
            ]);
            exit;
        }
        else
        {
            echo json_encode([
                'status_code'=> '403',
                'message' => '已经点赞过了'
            ]);
            exit;
        }
    }

    //获取点赞用户数量
    public  function agreements_list()
    {
        $id = $_GET['id'];
        $model = new \models\Blog;
        $data = $model->agreeList($id);

        echo json_encode([
            'status_code' => 200,
            'data' => $data,
        ]);
    }

    
    
    
}