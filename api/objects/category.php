<?php

class Category
{
    // Подключение к базе данных
    private $connect;

    // Свойства объекта
    public $id;
    public $name;
    public $auth;


    // Конструктор для соединения с базой данных
    public function __construct($db)
    {
        $this->connect = $db;
    }


    // Метод для получения всех категорий товаров
    // Возвращает PDOStatement
    function readAll()
    {
        $query = "SELECT id,
                         name
                  FROM categories
                  WHERE status=1 OR ?=1
                  ORDER BY  name";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        $av = $this->auth?1:0;
        $stmt->bindParam(1, $av);

        // Выполняем запрос
        $stmt->execute();

        // Выполняем запрос
        return $stmt;
    }


    // Метод для создания категории
    // Возвращает PDOStatement
    function create()
    {
        // Запрос для вставки (создания) записей
        $query = "INSERT INTO categories
                  SET id=:id, name=:name";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Очистка
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->name = htmlspecialchars(strip_tags($this->name));

        // Привязка значений
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":id", $this->id);

        // Выполняем запрос
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Метод для обновления категории
    // Возвращает PDOStatement
    function update()
    {
        // Запрос для обновления записи (товара)
        $query = "UPDATE categories
        SET name = :name
        WHERE id = :id";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Очистка
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Привязываем значения
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":id", $this->id);

        // Выполняем запрос
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Метод для удаления товара
    // Возвращает PDOStatement
    function delete()
    {
        // Запрос для удаления записи (товара)
        $query = "DELETE FROM categories WHERE id = ?";

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


    // Метод поиска категории по id
    /*
    int $id - передаваемое пользователем число
    Возвращает PDOStatement
    */
    function searchCategoryId($id)
    {
        // Поиск записей (товаров) по "id категории"
        $query = "SELECT categories.name as category_name,
           products.id,
           products.name
        FROM products
        LEFT JOIN categories  ON products.category_id = categories.id
        WHERE (? is null or categories.id = ?) AND (status=1 OR ?=1)
        ORDER BY products.created DESC";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Привязка
        $stmt->bindParam(1, $id);
        $stmt->bindParam(2, $id);
        $stmt->bindParam(3, $this->auth?1:0);

        // Выполняем запрос
        $stmt->execute();

        return $stmt;
    }

    /*// Метод для поиска товаров
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
    }*/

}
