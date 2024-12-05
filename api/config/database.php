<?php

class Database
{
    // укажите свои учетные данные базы данных
    private $host = "localhost";
    private $db_name = "TestDb";
    private $username = "root";
    private $password = "root";
    public $connect;

    // получаем соединение с БД
    public function getConnection()
    {
        $this->connect = null;

        try {
            $this->connect = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->connect->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Ошибка подключения: " . $exception->getMessage();
        }

        return $this->connect;
    }



}
