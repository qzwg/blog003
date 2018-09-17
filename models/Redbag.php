<?php
namespace models;
use PDO;
class Redbag extends Base
{
    public function create($userId)
    {
        $stmt = self::$pdo->prepare("INSERT INTO redbags(user_id) VALUES(?)");
        $stmt->execute([$userId]);
    }
}