<?php

namespace Alex;

use PDO;
use Exception;

abstract class Model
{
    public static $database = null;
    public $host = 'localhost';
    public $dbName = 'orm';
    public $username = 'homestead';
    public $password = 'secret';
    public static $data = [];
    public static $tableName = 'table';

    public function __construct()
    {
        try {
            static::$database = new PDO(
                'mysql:host='.$this->host
                .';dbname='.$this->dbName,
                $this->username,
                $this->password
            );

            static::$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " .$exception->getMessage();
        }
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, static::$data)) {
            static::$data[$name] = $value;
        }
    }

    public function __get($name)
    {
        if (array_key_exists($name, static::$data)) {
            return static::$data[$name];
        } else {
            throw new Exception('Sorry no such value exists');
        }
    }

    public function save()
    {
        $bind = static::$data;
        $fields = array_keys($bind);
        $fieldlist = implode(',', $fields);
        $qs = str_repeat('?,', count($fields) - 1);
        $table = static::$tableName;
        $sql = "INSERT INTO ". $table ."($fieldlist) values(${qs}?)";
        $stmt = static::$database->prepare($sql);
        $stmt->execute(array_values($bind));
    }

    public static function getAll()
    {
        $table = static::$tableName;
        $sql = "SELECT * FROM `".$table."`";
        $stmt = static::$database->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($results)) {
            throw new Exception('Sorry you have nothing in the database');
        } else {
            return $results;
        }
    }

    public static function destroy($id)
    {
        $table = static::$tableName;
        $sql = "DELETE FROM " . $table . " WHERE id=$id";
        var_dump($sql);
        static::$database->exec($sql);
        $sql = "UPDATE " . $table . " SET ";
        $bind = static::$data;
        $count = 0;
        foreach ($bind as $key => $value) {
            $count++;
            $sql .= "$key = NULL";
            if ($count < count($bind)) {
                $sql .= ", ";
            }
        }
        $sql .= " WHERE id = " . $id;
        var_dump($sql);
        $stmt = static::$database->prepare($sql);
        $stmt->execute(array_values($bind));
    }

    public static function find($id)
    {
        $table = static::$tableName;
        $sql = "SELECT * FROM " . $table . " WHERE id=$id";
        $stmt = static::$database->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    public function update()
    {
        $bind = static::$data;
        $table = static::$tableName;
        $sql = "UPDATE " . $table . " SET ";
        $count = 0;
        foreach ($bind as $key => &$value) {
            $count++;
            $sql .= "$key = '$value'";
            if ($count < count($bind)) {
                $sql .= ", ";
            }
        }
        $sql .= " WHERE id = " . static::$data['id'];
        $stmt = static::$database->prepare($sql);
        $stmt->execute();

    }

    public function truncate()
    {
        $table = static::$tableName;
        $sql = "TRUNCATE " . $table ;
        $stmt = static::$database->prepare($sql);
        $stmt->execute();
    }
}
