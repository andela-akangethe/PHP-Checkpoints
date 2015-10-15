<?php

namespace Alex;

//use Alex\Model;
include "Model.php";

class Person extends Model
{
    public static $tableName = 'users';

    public static $data = [
        'id' => null,
        'name' => null,
        'birthday' => null,
    ];
}

$user2 = new Person();
//$user2->id = 6;
// $user2->name = 'eeeeeeeeeeeeeeee';
// $user2->birthday = '100-100-1890';


$user2->name = 'ANTO ANDELA 2';
$user2->birthday = '100-100-1890';
$user2->save();
//$user2->find(6);
