<?php
namespace controllers;
use models\User;
class TestController
{
    public function insert()
    {
        $user = new User;
        $user->insert([
            'name'=>'tom',
            'age'=>10,
        ]);

        $user->insert([
            'name'=>'jack',
            'age'=>12,
        ]);
    }

    public function update()
    {
        $user = new User;

        $user->update([
            'age'=>20,
        ], "name='tom'");
    }

    public function get()
    {
        $user = new User;

        $data = $user->get('SELECT * FROM users');
        var_dump($data);

        $data = $user->find(2);
        var_dump($data);

        $data = $user->count();
        var_dump($data);
    }

    public function delete()
    {
        $user = new User;

        $user->delete("name='jack'");
    }
}