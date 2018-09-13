<?php
namespace models;
class Order extends Base
{
    public  function create($money)
    {
        $flake = new \libs\Snowflake;
        $stmt = self::$pdo->prepare('INSERT INTO orders(user_id,money,sn) VALUES(?,?,?)');
        $stmt->execute([
            $_SESSION['id'],
            $money,
            $flake->nextId()
        ]);
    }

    //搜索订单
    public function search()
    {
        $where = 'user_id=' . $_SESSION['id'];
        /***************** 排序 ********************/
        // 默认排序
        $odby = 'created_at';
        $odway = 'desc';

        /****************** 翻页 ****************/
        $perpage = 15; // 每页15
        $page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
        $offset = ($page-1)*$perpage;

        // 制作按钮
        // 取出总的记录数
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM orders WHERE $where");
        $stmt->execute();
        $count = $stmt->fetch( PDO::FETCH_COLUMN );
        // 计算总的页数（ceil：向上取整（天花板）， floor：向下取整（地板））
        $pageCount = ceil( $count / $perpage );

        $btns = '';
        for($i=1; $i<=$pageCount; $i++)
        {
            // 先获取之前的参数
            $params = getUrlParams(['page']);

            $class = $page==$i ? 'active' : '';
            $btns .= "<a class='$class' href='?{$params}page=$i'> $i </a>";
            
        }

        /*************** 执行 sqL */
        // 预处理 SQL
        $stmt = self::$pdo->prepare("SELECT * FROM orders WHERE $where ORDER BY $odby $odway LIMIT $offset,$perpage");
        // 执行 SQL
        $stmt->execute();

        // 取数据
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'btns' => $btns,
            'data' => $data,
        ];

    }

    public function findBySn($sn)
    {
        $stmt = self::$pdo->prepare('SELECT * FROM orders WHERE sn=?');
        $stmt->execute([
            $sn
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function setPaid($sn)
    {
        $stmt = self::$pdo->prepare("UPDATE orders SET status=1,pay_time=now() WHERE sn=?");

        $stmt->execute([
            $sn
        ]);
    }
}