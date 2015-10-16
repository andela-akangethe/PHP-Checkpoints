<?php
namespace Alex;

/**
 * This is a test for Person.php
 *
 * @author Alex Kangethe
 */

use Alex\Model;
use PHPUnit_Framework_TestCase;
use PDO;
use Exception;

class PersonTest extends PHPUnit_Framework_TestCase
{
    public static $database = null;
    public $host = 'localhost';
    public $dbName = 'orm';
    public $username = 'homestead';
    public $password = 'secret';

    /**
     * This is a method is used to setup the database connection for the tests
     *
     */

    public function setUp()
    {
        try {
            static::$database = new PDO(
                'mysql:host='.$this->host
                .';dbname='.$this->dbName,
                $this->username,
                $this->password
            );
            static::$database
            ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " .$exception->getMessage();
        }

        static::$database->exec("DROP TABLE IF EXISTS `users`");
        static::$database->exec(
            "CREATE TABLE `users` (" .
            "`id` INTEGER NULL AUTO_INCREMENT," .
            "`name` VARCHAR(250) NULL DEFAULT NULL," .
            "`birthday` VARCHAR(250) NULL DEFAULT NULL," .
            "PRIMARY KEY (`id`) )"
        );
        static::$database
        ->exec(
            "INSERT INTO `users` (name,birthday) VALUES ('Alex', '1990-01-01')"
        );
    }

    /**
     * This is a method is used to close the database
     *
     */

    public function tearDown()
    {
        static::$database = null;
    }

    /**
     * This is a method is used to test save method
     *
     */

    public function testSave()
    {
        $user = new Person();
        $user->name = "thepadawan";
        $user->birthday = '100-100-1890';
        $user->save();
        $stmt = static::$database->query("SELECT * FROM users");
        $this->assertCount(2, $stmt->fetchAll());
        $stmt = static::$database->query("TRUNCATE users");
    }

    /**
     * This is a method is used to test the getAll method
     *
     */

    public function testGetAll()
    {
        $stmt = static::$database->query("SELECT * FROM users");
        $this->assertCount(1, $stmt->fetchAll());
        $stmt = static::$database->query("TRUNCATE users");
    }

    /**
     * This is a method is used to test the find method
     *
     */

    public function testFind()
    {
        $stmt = static::$database->query("SELECT * FROM users");
        $result = $stmt->fetchAll();
        $id = $result[0]['id'];
        $user = Person::find(1);
        $this->assertEquals($id, $user[0]['id']);
    }

    /**
     * This is a method is used to test the update method
     *
     */

    public function testUpdate()
    {
        $user = new Person();
        $user->id = 1;
        $user->name = "thepadawan";
        $user->birthday = "1990-01-01";
        $user->update();
        $stmt = static::$database->query("SELECT * FROM users");
        $result = $stmt->fetchAll();
        $this->assertTrue("thepadawan" === $result[0]['name']);
    }

    /**
     * This is a method is used to test the destroy method
     *
     */

    public function testDestroy()
    {
        $stmt = static::$database->query("SELECT * FROM users");
        $result = $stmt->fetchAll();
        Person::destroy(1);
        $this->assertCount(0, $stmt->fetchAll());
    }
}
