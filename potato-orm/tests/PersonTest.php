<?php
namespace Alex;

use Alex\Model;
use PHPUnit_Framework_TestCase;

class PersonTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->person = new Person();

    }

    public function tearDown()
    {

    }

    public function testSave()
    {
        
    }

    public static function testGetAll()
    {
        
    }

    public static function testFind()
    {
        
    }

    public function testUpdate()
    {
        
    }

    public function testDestroy()
    {
        $this->person = Person::destroy();
    }
}
