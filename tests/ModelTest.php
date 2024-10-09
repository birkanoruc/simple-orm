<?php

use PHPUnit\Framework\TestCase;
use Birkanoruc\SimpleOrm\Database;
use Birkanoruc\SimpleOrm\Model;

class User extends Model
{
    protected $table = 'users';
}

class ModelTest extends TestCase
{
    protected $db;

    protected function setUp(): void
    {
        $config = [
            'host' => '127.0.0.1',
            'port' => 3306,
            'dbname' => 'test_php',
            'charset' => 'utf8'
        ];

        $this->db = new Database($config, 'root', '');

        $this->db->query("DROP TABLE IF EXISTS users");
        $this->db->query("CREATE TABLE users (id INTEGER PRIMARY KEY AUTO_INCREMENT, name TEXT)");

        $this->db->query("INSERT INTO users (name) VALUES (:name)", ['name' => 'Birkan']);
    }

    public function testModelAllMethodReturnsAllRecords()
    {
        $userModel = new User($this->db);
        $users = $userModel->all();

        $this->assertCount(1, $users);
        $this->assertEquals('Birkan', $users[0]['name']);
    }

    public function testModelFindMethodReturnsSingleRecord()
    {
        $userModel = new User($this->db);
        $user = $userModel->find(1);

        $this->assertEquals('Birkan', $user['name']);
    }
}
