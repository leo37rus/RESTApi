<?php

class User
{
    // Подключение к БД таблице "users"
    private $connect;

    // Свойства
    public $id;
    public $login;
    public $password;

    // Конструктор класса User
    public function __construct($db)
    {
        $this->connect = $db;
    }


    // Роутер
    function route($method, $urlData, $formData) {

        // Получение всей информации о пользователе
        // GET /users
        if ($method === 'GET' && count($urlData) === 1) {
            // Получаем id пользователя
            $userId = $urlData[0];

            // Вытаскиваем все данные о пользователе из базы...

            // Выводим ответ клиенту
            echo json_encode(array(
                'method' => 'GET',
                'id' => $userId,
                'info' => array(
                    'email' => 'webdevkin@gmail.com',
                    'name' => 'Webdevkin'
                ),
                'orders' => array(
                    array(
                        'orderId' => 5,
                        'summa' => 2000,
                        'orderDate' => '12.01.2017'
                    ),
                    array(
                        'orderId' => 8,
                        'summa' => 5000,
                        'orderDate' => '03.02.2017'
                    )
                )
            ));

            return;
        }


        // Получение общей информации о пользователе
        // POST /users
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


        // Получение заказов пользователя
        // DELETE /users
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


    // Метод для создания нового пользователя
    function create() {

        // Запрос для добавления нового пользователя в БД
        $query = "INSERT INTO users
                  SET login = :login,
                      password = :password";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Инъекция
        $this->login = htmlspecialchars(strip_tags($this->login));
        $this->password = htmlspecialchars(strip_tags($this->password));

        // Привязываем значения
        $stmt->bindParam(":login", $this->login);

        // Для защиты пароля
        // Хешируем пароль перед сохранением в базу данных
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(":password", $password_hash);

        // Выполняем запрос
        // Если выполнение успешно, то информация о пользователе будет сохранена в базе данных
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Метод для просмотра пользователя
    function read() {

        // Запрос просмотра пользователя
        $query = "SELECT id,login
                  FROM users";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Выполняем запрос
        // Если выполнение успешно, то информация о пользователе будет прочитана
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Метод для обновления данных пользователя
    function update() {

        // Запрос для обновления пользователя
        $query = "UPDATE users SET login = :login, password = :password WHERE id = :id";

        // Подготовка запроса
        $stmt = $this->conn->prepare($query);

        // Инъекция
        $this->login = htmlspecialchars(strip_tags($this->login));
        $this->password = htmlspecialchars(strip_tags($this->password));

        // Привязываем значения
        $stmt->bindParam(":login", $this->login);

        // Для защиты пароля
        // Хешируем пароль перед сохранением в базу данных
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(":password", $password_hash);

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Выполняем запрос
        // Если выполнение успешно, то информация о пользователе будет прочитана
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Метод для удаления пользователя
    function delete() {

        // Запрос для удаления
        $query = "UPDATE users SET deleted = '1' WHERE id = ?";

        // Подготовка запроса
        $stmt = $this->conn->prepare($query);

        // Инъекция
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Подготовка запроса
        $stmt->bindParam(1, $this->id);

        // Выполняем запрос
        // Если выполнение успешно, то пользователь будет удален
        if($stmt->execute()) {
            return true;
        }

        return false;
    }


    function loginExists() {

        // Запрос, чтобы проверить, существует логин и пароль
        $query = "SELECT id, login,password
            FROM users
            WHERE login = ?
            LIMIT 0,1";

        // Подготовка запроса
        $stmt = $this->conn->prepare($query);

        // Инъекция
        $this->login=htmlspecialchars(strip_tags($this->login));

        // Привязываем значение логина
        $stmt->bindParam(1, $this->login);

        // Выполняем запрос
        $stmt->execute();

        // Получаем количество строк
        $num = $stmt->rowCount();

        // Если логин существует,
        // Присвоим значения свойствам объекта для легкого доступа и использования для php сессий
        if ($num > 0) {

            // Получаем значения
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Присвоим значения свойствам объекта
            $this->id = $row["id"];
            $this->login = $row["login"];
            $this->password = $row["password"];

            // Вернём "true", потому что в базе данных существует логин
            return true;
        }

        // Вернём "false", если логин не существует в базе данных
        return false;
    }


}
