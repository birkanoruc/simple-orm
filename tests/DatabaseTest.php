<?php

use PHPUnit\Framework\TestCase;
use Birkanoruc\SimpleOrm\Database;

class DatabaseTest extends TestCase
{
    protected $db;

    protected function setUp(): void
    {
        $this->db = new Database([
            'host' => '127.0.0.1',
            'port' => 3306,
            'dbname' => 'test_php',
            'charset' => 'utf8'
        ], 'root', '');
    }

    public function testConnectionIsEstablished()
    {
        $this->assertInstanceOf(PDO::class, $this->db->connection);
    }

    public function testQueryReturnsResults()
    {
        $this->db->query("DROP TABLE IF EXISTS users");
        $this->db->query("CREATE TABLE users (id INTEGER PRIMARY KEY AUTO_INCREMENT, name TEXT)");


        $this->db->query("INSERT INTO users (name) VALUES (:name)", ['name' => 'Birkan']);

        $result = $this->db->query("SELECT * FROM users WHERE name = :name", ['name' => 'Birkan'])->find();
        $this->assertEquals('Birkan', $result['name']);
    }
}
