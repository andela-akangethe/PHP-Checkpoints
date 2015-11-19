<?php

require 'vendor/autoload.php';

use Goutte\Client;

class NaiemojiTest extends PHPUnit_Framework_TestCase
{
    public $database = null;
    public $host = 'localhost';
    public $dbName = 'orm';
    public $username = 'homestead';
    public $password = 'secret';

    public function setUp()
    {
        $this->client = new Client();
    }
 
    public function testSetUp()
    {
        try {
            $this->database = new PDO(
                'mysql:host='. $this->host
                .';dbname='. $this->dbName,
                $this->username,
                $this->password
            );
            $this->database
            ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " .$exception->getMessage();
        }

        $this->database->exec("DROP TABLE IF EXISTS `users`");
        $this->database->exec(
            "CREATE TABLE IF NOT EXISTS `users` (".
            "`id` int(11) NOT NULL AUTO_INCREMENT,".
            "`name` varchar(250) DEFAULT NULL,".
            "`email` varchar(255) NOT NULL,".
            "`password` text NOT NULL,".
            "`token` varchar(32) NOT NULL,".
            "`created_at` timestamp not null default current_timestamp,".
            "`updated_at` timestamp not null default current_timestamp on update current_timestamp,".
            "PRIMARY KEY (`id`) )"
        );
        $this->database->exec("DROP TABLE IF EXISTS `emojis`");
        $this->database->exec(
            "CREATE TABLE IF NOT EXISTS `emojis` (".
            "`id` int(11) NOT NULL AUTO_INCREMENT,".
            "`name` varchar(32) NOT NULL,".
            "`keywords` varchar(32) NOT NULL,".
            "`emoji` varchar(32) NOT NULL,".
            "`category` varchar(32) NOT NULL,".
            "`user` varchar(32) NOT NULL,".
            "`created_at` timestamp not null default current_timestamp,".
            "`updated_at` timestamp not null default current_timestamp on update current_timestamp,".
            "PRIMARY KEY (`id`) )"
        );
    }

    /**
     * @depends testSetUp
     */

    public function testRegister()
    {
        $this->client->request(
            'POST',
            'http://nairo.app/register',
            ['name' =>'yourname','password'=>'yourpassword','email'=>'youremail']
        );

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatus());
    }

    /**
     * @depends testRegister
     */

    public function testLogin()
    {
        $this->client->request('POST', 'http://nairo.app/login', ['password'=>'yourpassword','email'=>'youremail']);

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatus());
    }

    /**
     * @depends testLogin
     */

    public function testGetEmojis()
    {
        $this->client->request('GET', 'http://nairo.app/emojis');
        $result =$this->client->getResponse()->getContent();
        $json_array  = json_decode($result, true);
        $this->assertCount(0, $json_array);
    }

    /**
     * @depends testGetEmojis
     */

    public function testAddEmoji()
    {
        $this->client->request('POST', 'http://nairo.app/emoji/yourname', ['emoji'=>'anunty']);
        $this->client->request('POST', 'http://nairo.app/emoji/yourname', ['emoji'=>'anunty']);
        $result =$this->client->getResponse()->getContent();
        $json_array  = json_decode($result, true);
        $result = $json_array['id'];
        $this->assertEquals(2, $result);
    }

    /**
     * @depends testAddEmoji
     */

    public function testUpdate()
    {
        $this->client->request('PUT', 'http://nairo.app/emoji/1/yourname', ['emoji'=>'seee']);
        $result =$this->client->getResponse()->getContent();
        $this->assertEquals(1, $result);
    }

    /**
     * @depends testUpdate
     */

    public function testPatch()
    {
        $this->client->request('PATCH', 'http://nairo.app/emoji/1/yourname', ['emoji'=>'bye']);
        $result =$this->client->getResponse()->getContent();
        $this->assertEquals(1, $result);
    }

    /**
     * @depends testUpdate
     */

    public function testDelete()
    {
        $this->client->request('DELETE', 'http://nairo.app/emoji/1/yourname');
        $result =$this->client->getResponse()->getContent();
        $this->assertEquals(1, $result);
    }

    /**
     * @depends testDelete
     */

    public function testLogout()
    {
        $this->client->request('POST', 'http://nairo.app/logout', ['email'=>'youremail']);

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatus());
    }
}
