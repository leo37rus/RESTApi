<?php

// Заголовки
header("Access-Control-Allow-Origin: http://restapi.api/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Файлы необходимые для соединения с БД
include_once "../config/database.php";
include_once "../objects/user.php";

// Получаем соединение с базой данных
$database = new Database();
$db = $database->getConnection();

// Создание объекта "User"
$user = new User($db);

// Получаем данные
$data = json_decode(file_get_contents("php://input"));

// Устанавливаем значения
$user->login = $data->login;
$login_exists = $user->loginExists();


// Существует ли логин соответствует ли пароль тому, что находится в базе данных
if ($login_exists) {

    $token = array(
        "data" => array(
            "id" => $user->id,
            "login" => $user->login
        )
    );

    // Код ответа
    http_response_code(200);

    echo json_encode(
        array(
            "message" => "Успешный вход в систему"
        )
    );
}
// Если логин не существует или пароль не совпадает,
// Сообщим пользователю, что он не может войти в систему
else {

    // Код ответа
    http_response_code(401);

    // Скажем пользователю что войти не удалось
    echo json_encode(array("message" => "Ошибка входа"));
}
?>
