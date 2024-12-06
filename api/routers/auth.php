<?php

// Получаем соединение с базой данных
include_once "../config/database.php";
include_once "../objects/user.php";

// Роутер
function route($method, $urlData, $formData) {


    // Подключение к базе данных
    $database = new Database();
    $db = $database->getConnection();

    // Создание объекта "User"
    $user = new User($db);

    //Проверка авторизации, если токен получен, пользователь авторизован
    if (isset($_SERVER['HTTP_AUTHORIZATION']) && (substr($_SERVER['HTTP_AUTHORIZATION'],0,7) == 'Bearer ')) {
       //Получение токена
        $token = substr($_SERVER['HTTP_AUTHORIZATION'],7);
        //Поиск пользователя
        $alogin = $user.read($token);
        $auth = !is_null($alogin);
    } else {
        $auth = false;
    }

    // Аутентификация пользователя
    // POST /auth
    if ($method === 'POST' && empty($urlData)) {

        // Получаем данные
        $data = json_decode(file_get_contents("php://input"));

        // Устанавливаем значения
        $user->login = $data->login;
        $user->password = $data->password;

        // Создание пользователя
        if (!empty($user->login) &&
            !empty($user->password) &&
            ($token = $user->auth($user->login,$user->password))) {
            // Устанавливаем код ответа
            http_response_code(200);

            // Покажем сообщение о том, что пользователь был создан
            echo json_encode(array("message" => "Пользователь авторизован","token" => $token));
        } else {  // Сообщение, если не удаётся создать пользователя

            // Устанавливаем код ответа
            http_response_code(400);

            // Покажем сообщение о том, что создать пользователя не удалось
            echo json_encode(array("message" => "Невозможно создать пользователя"));
        }

        return;
    }


    // Удаление пользователя
    // DELETE /users
    if ($method === 'DELETE' && count($urlData) === 1) {


        // Удаление авторизации пользователя
        if ($auth && $user->delete($token)) {

            // Код ответа - 200 ok
            http_response_code(200);

            // Сообщение пользователю
            echo json_encode(array("message" => "Пользователь был удалён"), JSON_UNESCAPED_UNICODE);
        }else{  // Если не удается удалить пользователя

            // Код ответа - 503 Сервис не доступен
            http_response_code(503);

            // Сообщим об этом пользователю
            echo json_encode(array("message" => "Не удалось удалить пользователя"));
        }

        return;
    }


    // Получение информации о пользователе
    // GET /users/{id}
    if ($method === 'GET' && count($urlData) === 1) {

        if ($auth) {

            //Данные авторизованного пользователя
            $user_arr = array(
                "login" => $alogin
            );

            // Устанавливаем код ответа - 200 OK
            http_response_code(200);

            // Выводим данные о товаре в формате JSON
            echo json_encode($user_arr);
        }else{

            // Установим код ответа - 404 Не найдено
            http_response_code(404);

            // Сообщаем пользователю, что товары не найдены
            echo json_encode(array("message" => "Пользователь не найден."), JSON_UNESCAPED_UNICODE);
        }

        return;
    }




    // Возвращаем ошибку
    header('HTTP/1.0 400 Bad Request');
    echo json_encode(array(
        'error' => 'Bad Request'
    ));

}