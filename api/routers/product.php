<?php

include_once "../config/database.php";

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

        $database = new Database();
        $db = $database->getConnection();
        $product = new Product($db);

        $stmt = $product->read();
        $num = $stmt->rowCount();

        // Получение информации о товаре
        // GET /product/{id}
        if ($method === 'GET' && count($urlData) === 1) {

            if ($num > 0) {

                // массив товаров
                $products_arr = array();
                $products_arr["records"] = array();

                // получаем содержимое нашей таблицы
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // извлекаем строку
                    extract($row);
                    $product_item = array(
                        "id" => $id,
                        "name" => $name,
                        "description" => html_entity_decode($description),
                        "price" => $price,
                        "category_id" => $category_id,
                        "category_name" => $category_name
                    );
                    array_push($products_arr["records"], $product_item);
                }

                // устанавливаем код ответа - 200 OK
                http_response_code(200);

                // выводим данные о товаре в формате JSON
                echo json_encode($products_arr);
            }else{

                // установим код ответа - 404 Не найдено
                http_response_code(404);

                // сообщаем пользователю, что товары не найдены
                echo json_encode(array("message" => "Товары не найдены."), JSON_UNESCAPED_UNICODE);
            }

            return;
        }

        // Получение информации о товаре c возможностью указать категорию
        // GET /product?category={id}
        if ($method === 'GET' && count($urlData) === 1) {
            // Получаем id товара
            $product = $urlData[0];

            // Вытаскиваем товар из базы...

            // Выводим ответ клиенту
            echo json_encode(array(
                'method' => 'GET',
                'id' => $product,
                'good' => 'phone',
                'price' => 10000
            ));

            return;
        }


        // Добавление нового товара
        // POST /product
        if ($method === 'POST' && empty($urlData)) {

            // Добавляем товар в базу...
            $database = new Database();
            $db = $database->getConnection();
            $product = new Product($db);

            $data = json_decode(file_get_contents("php://input"));
            // Выводим ответ клиенту
            if (!empty($data->name) &&
                !empty($data->price) &&
                !empty($data->description) &&
                !empty($data->category_id)) {

                // устанавливаем значения свойств товара
                $product->name = $data->name;
                $product->price = $data->price;
                $product->description = $data->description;
                $product->category_id = $data->category_id;

                // создание товара
                if ($product->create()) {

                    // установим код ответа - 201 создано
                    http_response_code(201);

                    // сообщим пользователю
                    echo json_encode(array("message" => "Товар был создан."), JSON_UNESCAPED_UNICODE);

                }else{ // если не удается создать товар, сообщим пользователю

                    // установим код ответа - 503 сервис недоступен
                    http_response_code(503);

                    // сообщим пользователю
                    echo json_encode(array("message" => "Невозможно создать товар."), JSON_UNESCAPED_UNICODE);
                }
            }else { // сообщим пользователю что данные неполные

                // установим код ответа - 400 неверный запрос
                http_response_code(400);

                // сообщим пользователю
                echo json_encode(array("message" => "Невозможно создать товар. Данные неполные."), JSON_UNESCAPED_UNICODE);
            }

            return;
        }



        // Частичное обновление данных товара
        // PATCH /product/{id}
        if ($method === 'PATCH' && count($urlData) === 1) {
            // Получаем id товара
            $product = $urlData[0];
            // получаем соединение с базой данных
            $database = new Database();
            $db = $database->getConnection();

            // подготовка объекта
            $product = new Product($db);

            // получаем id товара для редактирования
            $data = json_decode(file_get_contents("php://input"));

            // установим id свойства товара для редактирования
            $product->id = $data->id;

            // установим значения свойств товара
            $product->name = $data->name;
            $product->price = $data->price;
            $product->description = $data->description;
            $product->category_id = $data->category_id;

            // обновление товара
            if ($product->update()) {
                // установим код ответа - 200 ok
                http_response_code(200);

                // сообщим пользователю
                echo json_encode(array("message" => "Товар был обновлён"), JSON_UNESCAPED_UNICODE);
            }else{  // если не удается обновить товар, сообщим пользователю

                // код ответа - 503 Сервис не доступен
                http_response_code(503);

                // сообщение пользователю
                echo json_encode(array("message" => "Невозможно обновить товар"), JSON_UNESCAPED_UNICODE);
            }

            return;
        }

        // Удаление товара
        // DELETE /product/{id}
        if ($method === 'DELETE' && count($urlData) === 1) {
            // Получаем id товара
            $product = $urlData[0];

            // получаем соединение с БД
            $database = new Database();
            $db = $database->getConnection();

            // подготовка объекта
            $product = new Product($db);

            // получаем id товара
            $data = json_decode(file_get_contents("php://input"));

            // установим id товара для удаления
            $product->id = $data->id;

            // удаление товара
            if ($product->delete()) {

                // код ответа - 200 ok
                http_response_code(200);

                // сообщение пользователю
                echo json_encode(array("message" => "Товар был удалён"), JSON_UNESCAPED_UNICODE);
            }else{  // если не удается удалить товар

                // код ответа - 503 Сервис не доступен
                http_response_code(503);

                // сообщим об этом пользователю
                echo json_encode(array("message" => "Не удалось удалить товар"));
            }

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
        SET name=:name, price=:price, description=:description, category_id=:category_id";

        // подготовка запроса
        $stmt = $this->connect->prepare($query);

        // очистка
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));

        // привязка значений
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category_id", $this->category_id);

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
