<?php

class Category
{
    // соединение с БД и таблицей "categories"
    private $connect;

    // свойства объекта
    public $id;
    public $name;


    public function __construct($db)
    {
        $this->connect = $db;
    }


    // метод для получения всех категорий товаров
    function readAll()
    {
        $query = "SELECT id,
                         name
                  FROM categories
                  ORDER BY  name";

        // подготовка запроса
        $stmt = $this->connect->prepare($query);
        $stmt->execute();

        // выполняем запрос
        return $stmt;
    }


    // метод для создания категории
    function create()
    {
        // запрос для вставки (создания) записей
        $query = "INSERT INTO categories
                  SET id=:id, name=:name";

        // подготовка запроса
        $stmt = $this->connect->prepare($query);

        // очистка
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->name = htmlspecialchars(strip_tags($this->name));

        // привязка значений
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":id", $this->id);

        // выполняем запрос
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // метод для обновления категории
    function update()
    {
        // запрос для обновления записи (товара)
        $query = "UPDATE categories
        SET name = :name
        WHERE id = :id";

        // подготовка запроса
        $stmt = $this->connect->prepare($query);

        // очистка
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // привязываем значения
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":id", $this->id);

        // выполняем запрос
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // метод для удаления товара
    function delete()
    {
        // запрос для удаления записи (товара)
        $query = "DELETE FROM categories WHERE id = ?";

        // подготовка запроса
        $stmt = $this->connect->prepare($query);

        // очистка
        $this->id = htmlspecialchars(strip_tags($this->id));

        // привязываем id записи для удаления
        $stmt->bindParam(1, $this->id);

        // выполняем запрос
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // метод для поиска товаров
    function searchByName($keywords)
    {
        // поиск записей (товаров) по "названию товара", "описанию товара", "названию категории"
        $query = "SELECT categories.name as category_name,
           products.id,
           products.name,
           products.description,
           products.price,
           products.category_id,
           products.created
        FROM products
        LEFT JOIN categories  ON products.category_id = categories.id
        WHERE products.name = ? OR products.description = ? OR categories.name = ?
        ORDER BY products.created DESC";

        // подготовка запроса
        $stmt = $this->connect->prepare($query);

        // очистка
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";

        // привязка
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);

        // выполняем запрос
        $stmt->execute();

        return $stmt;
    }

    function searchCategoryId($id)
    {
        // поиск записей (товаров) по "id категории"
        $query = "SELECT categories.name as category_name,
           products.id,
           products.name
        FROM products
        LEFT JOIN categories  ON products.category_id = categories.id
        WHERE ? is null or categories.id = ?
        ORDER BY products.created DESC";

        // подготовка запроса
        $stmt = $this->connect->prepare($query);

        // привязка
        $stmt->bindParam(1, $id);
        $stmt->bindParam(2, $id);

        // выполняем запрос
        $stmt->execute();

        return $stmt;
    }

    public function count()
    {
        $query = "SELECT COUNT(*) as total_rows FROM products";

        $stmt = $this->connect->prepare($query);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row["total_rows"];
    }
}
