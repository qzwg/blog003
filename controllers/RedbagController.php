<?php
namespace controllers;
class RedbagController
{
    public function init()
    {
        $redis = \libs\Redis::getInstance();
        $redis->set('redbag_stock',20);
        $key = 'redbag_' . data('Ymd');
        $redis->sadd($key, '-1');
        $redis->expire($key,3900);
    }

    //有 新的数据就生成订单
    public function makeOrder()
    {
        $redis = \libs\Redis::getInstance();
        $model = new \models\Redbag;

        ini_set('default_socket_timeout',-1);
        echo '开始监听红包';

        while(true)
        {
            $data = $redis->brpop('redbag_orders',0);
            $userId = $data[1];

            $model->create($userId);

            echo '======有人抢了红包! \r\n';
        }
    }

    //抢红包
    public function rob()
    {
        //判断是否登陆
        if(!isset($_SESSION['id']))
        {
            echo json_encode([
                'status_code' => '401',
                'message' => '未登录'
            ]);
            exit;
        }
        //判断是否是当前9-10点之间
        if(data('H')<9 || date('H')>20)
        {
            echo json_encode([
                'status_code' => '403',
                'message' => '时间段不允许！'
            ]);
            exit;
        }

        //判断今天是否已经抢过
        $key = 'redba_' . date('Ymd');
        $redis = \libs\Redis::getInstance();
        $existe = $redis->sismember($key,$_SESSION['id']);
        if($exists)
        {
            echo json_encode([
                'status_code' => '403',
                'message' => '今天已经抢过了~'
            ]);
            exit;
        }
        //减少库存量
        $stock = $redis->decr('redbag_stock');
        if($stock<0)
        {
            echo json_encode([
                'status_code' => '403',
                'message' => '今天的红包已经减完了'
            ]);
            exit;
        }
        //下单
        $redis->lpush('redbag_orders',$_SESSION['id']);
        $redis->sadd($key,$_SESSION['id']);

        echo json_encode([
            'status_code' => '200',
            'message' => '恭喜你，抢到本站红包'
        ]);
        //把ID放到集合中

    }

    //显示
    public function rob_view()
    {
        view('redbag/rob');
    }
}