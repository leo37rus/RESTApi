<?php

class User
{
    // Подключение к базе данных
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



    // Метод для просмотра пользователя
    /*
    string $login - передаваемая пользователем строка с логином
    string $password - передаваемая пользователем строка с паролем
    Возвращает PDOStatement
    */
    function auth($login,$password) {

        // Запрос просмотра пользователя
        $query = "SELECT id,login,password
                  FROM users
                  WHERE login = ? and password = ? and activ = 1";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Привязка
        $stmt->bindParam(1, $login);
        $stmt->bindParam(2, $password);

        // Выполняем запрос
        // Если выполнение успешно, то информация о пользователе будет сохранена в базе данных
        if ($stmt->execute()) {

            //Присваиваем случайное количество байт в токен
            $token = bin2hex(random_bytes(10));

            // Запрос для добавления токена пользователя
            $query = "INSERT INTO tokens (token,login)values (:token,:login)";

            // Подготовка запроса
            $stmt = $this->connect->prepare($query);

            // Привязываем значения
            $stmt->bindParam(":token", $token);
            $stmt->bindParam(":login", $login);

            $stmt->execute();

            return $token;
        }

        return false;
    }



    // Метод для просмотра пользователя
    /*
    string $login - передаваемая пользователем строка с логином
    string $password - передаваемая пользователем строка с паролем
    Возвращает PDOStatement
    */
    function read($token) {

        // Запрос просмотра пользователя
        $query = "SELECT login
                  FROM tokens
                  WHERE token = ?";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Привязка
        $stmt->bindParam(1, $token);

        // Выполняем запрос
        if ($stmt->execute()) {

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row['login'];
        }

        return null;
    }


    // Метод для удаления пользователя
    /*
    string $login - передаваемая пользователем строка с логином
    string $password - передаваемая пользователем строка с паролем
    Возвращает PDOStatement
    */
    function delete($token) {

        // Запрос для удаления
        $query = "DELETE FROM tokens  WHERE token = ?";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Подготовка запроса
        $stmt->bindParam(1, $token);

        // Выполняем запрос
        // Если выполнение успешно, то пользователь будет удален
        if($stmt->execute()) {
            return true;
        }

        return false;
    }


    /*// Метод для создания нового пользователя
   function create($login,$password) {

       // Запрос для добавления нового пользователя в БД
       $query = "INSERT INTO tokens
               (:lo)
                 LEFT JOIN users on users.token = tokens.id
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
   }*/

    /* // Метод для обновления данных пользователя
    function update() {

        // Запрос для обновления пользователя
        $query = "UPDATE users SET login = :login, password = :password WHERE id = :id";

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

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

        // Выполняем запрос
        // Если выполнение успешно, то информация о пользователе будет прочитана
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
*/

/*
   //Метод для проверки существования логина и пароля в базе
    function loginExists() {

        // Запрос, чтобы проверить, существует логин и пароль
        $query = "SELECT id, login,password
            FROM users
            WHERE login = ?
            LIMIT 0,1";

        // Подготовка запроса
        $stmt = $this->connect->prepare($query);

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
    }*/


}
