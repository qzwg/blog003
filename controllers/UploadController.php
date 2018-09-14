<?php
namespace controllers;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use models\Blog;
class UploadController{
    // public function upload()
    // {
    //     $file = $_FILES['image'];
    //     $name = time();
    //     move_uploaded_file($file['tmp_name'] , ROOT . 'public/uploads/' . $name . '.png');

    //     echo json_encode([
    //         'success'=>true,
    //         'file_path'=>'/public/uploads/' . $name . '.png',
    //     ]);
    // }

    //上传图片
    public function upload()
    {
        view('upload.single');
    }

    public function uploadImg()
    {
        $uploadDir = ROOT . 'upload';
        var_dump($uploadDir);
        $date = date('Y-m-d');
        //创建目录
        if(!is_dir($uploadDir . '/' . $date))
        {
            mkdir($uploadDir . '/' . $date,0777);
        }
        //获取文件扩展名
        $ext = strrchr($_FILES['image']['name'],'.');
        //拼接新文件名
        $name = md5(time() . rand(1,9999));
       
        $fullName = $uploadDir . '/' . $date . '/' . $name . $ext;
        var_dump($_FILES['image']['tmp_name']);
        var_dump($fullName);
        //保存图片
        
        move_uploaded_file($_FILES['image']['tmp_name'],$fullName);
    }

    //批量上传
    public function multipelImg()
    {
        view('upload.multiple');
    }

    public function multiple()
    {
        $root = ROOT . 'upload/';
        $date = date("Ymd");
        if(!is_dir($root . $date))
        {
            mkdir($root . $date ,0777);
        }
        var_dump($_FILES);
        foreach($_FILES['images']['name'] as $k => $v)
        {
            $name = md5(time() . rand(1,9999));
            $ext = strrchr($v,'.');
            $name = $name . $ext;
            move_uploaded_file($_FILES['images']['tmp_name'][$k],$root . $date . '/' . $name);
            echo $root .$date . '/' . $name . '<hr>';
        }
    }

    //上传大文件
    public function bigImg()
    {
        view('upload.big');
    }
    public function Big()
    {
       
        $count = $_POST['count'];
        $i = $_POST['i'];
        $size = $_POST['size'];
        $name = 'big_img_' . $_POST['img_name'];
        $img = $_FILES['img'];
        move_uploaded_file($img['tmp_name'],ROOT.'tmp/' . $i);

        $redis = \libs\Redis::getInstance();
        var_dump($name);
        $uploadedCount = $redis->incr($name);
        if($uploadedCount == $count)
        {
            $fp = fopen(ROOT . 'upload/big/' . $name . '.png','a');
            for($i=0;$i<$count;$i++)
            {
                fwrite($fp,file_get_contents(ROOT . 'tmp/' . $i));
                unlink(ROOT . 'tmp/' . $i);
            }

            fclose($fp);
            $redis->del($name);

        }
        
    }

    //Excel 生成并下载
    public function makeExcel()
    {
        // 获取当前标签页
        $spreadsheet = new Spreadsheet();
        
        // 获取当前工作
        $sheet = $spreadsheet->getActiveSheet();

        // 设置第1行内容
        $sheet->setCellValue('A1', '标题');
        $sheet->setCellValue('B1', '内容');
        $sheet->setCellValue('C1', '发表时间');
        $sheet->setCellValue('D1', '是发公开');

        // 取出数据库中的日志
        $model = new Blog;
     
        // 获取最新的20个日志
        $blogs = $model->getNew();
      
        $i=2; // 第几行
        foreach($blogs as $v)
        {
            $sheet->setCellValue('A'.$i, $v['title']);
            $sheet->setCellValue('B'.$i, $v['content']);
            $sheet->setCellValue('C'.$i, $v['created_at']);
            $sheet->setCellValue('D'.$i, $v['is_show']);
            $i++;
        }

        $date = date('Ymd');

        $writer = new Xlsx($spreadsheet);
        $writer->save(ROOT . 'excel/' . $date . '.xlsx');

     
        //Excel下载
        $file = ROOT . 'excel/' . $date . '.xlsx';
        $fileName = '最新10条日志-' . $date . '.xlsx';
        // 告诉浏览器这是一个二进程文件流    
        Header ( "Content-Type: application/octet-stream" ); 
        // 请求范围的度量单位  
        Header ( "Accept-Ranges: bytes" );  
        // 告诉浏览器文件尺寸    
        Header ( "Accept-Length: " . filesize ( $file ) );  
        // 开始下载，下载时的文件名
        Header ( "Content-Disposition: attachment; filename=" . $fileName );    

        // 读取服务器上的一个文件并以文件流的形式输出给浏览器
        readfile($file);



    }
}