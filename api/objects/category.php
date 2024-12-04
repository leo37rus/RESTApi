<?php

class Category
{
    // соединение с БД и таблицей "categories"
    private $connect;

    // свойства объекта
    public $id;
    public $name;
    public $description;
    public $created;

    public function __construct($db)
    {
        $this->connect = $db;
    }

    public function readAll()
    {
        $query = "SELECT id, 
            name,
            description
            FROM categories                
            ORDER BY name";

        $stmt = $this->connect->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
