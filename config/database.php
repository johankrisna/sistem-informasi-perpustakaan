<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'perpustakaan';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            // Jika database tidak ada, buat otomatis
            if ($exception->getCode() == 1049) {
                $this->createDatabase();
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                    $this->username, 
                    $this->password,
                    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
                );
                $this->conn->exec("set names utf8");
            } else {
                echo "Connection error: " . $exception->getMessage();
            }
        }
        return $this->conn;
    }

    private function createDatabase() {
        try {
            $temp_conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            $temp_conn->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name);
        } catch(PDOException $e) {
            die("Error creating database: " . $e->getMessage());
        }
    }
}
?>