<?php
namespace models;
class User extends BaseModel
{
    public function getName()
    {
        return 'tom';
    }

    public $tableName = 'users';

}
