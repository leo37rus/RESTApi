<?php

include_once "../config/database.php";

class Product
{

    // Подключение к базе данных
    private $connect;

    // Свойства объекта
    public $id;
    public $name;
    public $description;
    public $price;
    public $category_id;
    public $category_name;

    // Конструктор для соединения с базой данных
    public function __construct($db)
    {
        $this->connect = $db;
    }


    // Метод для получения товаров
    function read()
    {
        // Выбираем все записи
        $query = "SELECT categories.name as category_name,
        products.id,
        products.name,
        products.category_id
        FROM products
        LEFT JOIN categories  ON products.category_id = categories.id
        ORDER BY products.created DESC";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Выполняем запрос
        $stmt->execute();
        return $stmt;
    }

    // Метод для создания товаров
    function create()
    {
        // Запрос для вставки (создания) записей
        $query = "INSERT INTO products
        SET name=:name, category_id=:category_id";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Очистка
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));

        // Привязка значений
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":category_id", $this->category_id);

        // Выполняем запрос
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Метод для обновления товара
    function update()
    {
        // Запрос для обновления записи (товара)
        $query = "UPDATE products
                  SET name = :name,
                  category_id = :category_id
                  WHERE id = :id";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Очистка
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Привязываем значения
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":id", $this->id);

        // Выполняем запрос
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Метод для удаления товара
    function delete()
    {
        // Запрос для удаления записи (товара)
        $query = "DELETE FROM products WHERE id = ?";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Очистка
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Привязываем id записи для удаления
        $stmt->bindParam(1, $this->id);

        // Выполняем запрос
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Метод для поиска товаров
    function searchByName($keywords)
    {
        // Поиск записей (товаров) по "названию товара", "описанию товара", "названию категории"
        $query = "SELECT categories.name as category_name,
           products.id,
           products.name,
           products.category_id
        FROM products
        LEFT JOIN categories  ON products.category_id = categories.id
        WHERE products.name = ? OR categories.name = ?
        ORDER BY products.created DESC";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Очистка
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";

        // Привязка
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);

        // Выполняем запрос
        $stmt->execute();

        return $stmt;
    }

    function searchCategoryId($id)
    {
        // Поиск записей (товаров) по "id категории"
        $query = "SELECT categories.name as category_name,
           products.id,
           products.name,
           products.category_id           
        FROM products
        LEFT JOIN categories  ON products.category_id = categories.id
        WHERE ? is null or categories.id = ?
        ORDER BY products.created DESC";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Привязка
        $stmt->bindParam(1, $id);
        $stmt->bindParam(2, $id);

        // Выполняем запрос
        $stmt->execute();

        return $stmt;
    }

    function searchById($id)
    {
        // Поиск записей (товаров) по "id товара"
        $query = "SELECT categories.name as category_name,
           products.id,
           products.name,
           products.category_id
        FROM products
        LEFT JOIN categories  ON products.category_id = categories.id
        WHERE products.id = ?
        ORDER BY products.created DESC";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Привязка
        $stmt->bindParam(1, $id);

        // Выполняем запрос
        $stmt->execute();

        return $stmt;
    }

}
