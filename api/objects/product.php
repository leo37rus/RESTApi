<?php

class Product
{
    // подключение к базе данных
    private $connect;

    // свойства объекта
    public $id;
    public $name;
    public $description;
    public $price;
    public $category_id;
    public $category_name;
    public $created;

    // конструктор для соединения с базой данных
    public function __construct($db)
    {
        $this->connect = $db;
    }

    // Роутер
    function route($method, $urlData, $formData) {

        // Получение информации о товаре
        // GET /product/{id}
        if ($method === 'GET' && count($urlData) === 1) {
            // Получаем id товара
            $product_id = $urlData[0];

            // Вытаскиваем товар из базы...

            // Выводим ответ клиенту
            echo json_encode(array(
                'method' => 'GET',
                'id' => $product_id,
                'good' => 'phone',
                'price' => 10000
            ));

            return;
        }

        // Получение информации о товаре c возможностью указать категорию
        // GET /product?category={id}
        if ($method === 'GET' && count($urlData) === 1) {
            // Получаем id товара
            $product_id = $urlData[0];

            // Вытаскиваем товар из базы...

            // Выводим ответ клиенту
            echo json_encode(array(
                'method' => 'GET',
                'id' => $product_id,
                'good' => 'phone',
                'price' => 10000
            ));

            return;
        }


        // Добавление нового товара
        // POST /product
        if ($method === 'POST' && empty($urlData)) {
            // Добавляем товар в базу...

            // Выводим ответ клиенту
            echo json_encode(array(
                'method' => 'POST',
                'id' => rand(1, 100),
                'formData' => $formData
            ));

            return;
        }



        // Частичное обновление данных товара
        // PATCH /product/{id}
        if ($method === 'PATCH' && count($urlData) === 1) {
            // Получаем id товара
            $goodId = $urlData[0];

            // Обновляем только указанные поля товара в базе...

            // Выводим ответ клиенту
            echo json_encode(array(
                'method' => 'PATCH',
                'id' => $goodId,
                'formData' => $formData
            ));

            return;
        }

        // Удаление товара
        // DELETE /product/{id}
        if ($method === 'DELETE' && count($urlData) === 1) {
            // Получаем id товара
            $goodId = $urlData[0];

            // Удаляем товар из базы...

            // Выводим ответ клиенту
            echo json_encode(array(
                'method' => 'DELETE',
                'id' => $goodId
            ));

            return;
        }


        // Возвращаем ошибку
        header('HTTP/1.0 400 Bad Request');
        echo json_encode(array(
            'error' => 'Bad Request'
        ));

    }



    // метод для получения товаров
    function read()
    {
        // выбираем все записи
        $query = "SELECT categories.name as category_name,
        products.id,
        products.name, 
        products.description,
        products.price,
        products.category_id,
        products.created
        FROM products
        LEFT JOIN categories  ON products.category_id = categories.id
        ORDER BY products.created DESC";

        // подготовка запроса
        $stmt = $this->connect->prepare($query);

        // выполняем запрос
        $stmt->execute();
        return $stmt;
    }

    // метод для создания товаров
    function create()
    {
        // запрос для вставки (создания) записей
        $query = "INSERT INTO products
        SET name=:name, price=:price, description=:description, category_id=:category_id, created=:created";

        // подготовка запроса
        $stmt = $this->connect->prepare($query);

        // очистка
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->created = htmlspecialchars(strip_tags($this->created));

        // привязка значений
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":created", $this->created);

        // выполняем запрос
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // метод для обновления товара
    function update()
    {
        // запрос для обновления записи (товара)
        $query = "UPDATE products
        SET name = :name,
            price = :price,
            description = :description,
            category_id = :category_id
        WHERE id = :id";

        // подготовка запроса
        $stmt = $this->connect->prepare($query);

        // очистка
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // привязываем значения
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category_id", $this->category_id);
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
        $query = "DELETE FROM products WHERE id = ?";

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
    function search($keywords)
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

    public function count()
    {
        $query = "SELECT COUNT(*) as total_rows FROM products";

        $stmt = $this->connect->prepare($query);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row["total_rows"];
    }
}
