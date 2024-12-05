<?php

include_once "../config/database.php";
include_once "../objects/user.php";

// Роутер
function route($method, $urlData, $formData) {

    // Получение всей информации о пользователе
    // GET /users
    $database = new Database();
    $db = $database->getConnection();

    // Создание объекта "User"
    $user = new User($db);

    // Аутентификация пользователя
    // POST /users
    if ($method === 'POST' && empty($urlData)) {

        // Получаем данные
        $data = json_decode(file_get_contents("php://input"));

        // Устанавливаем значения
        $user->login = $data->login;
        $user->password = $data->password;

        // Создание пользователя
        if (!empty($user->login) &&
            !empty($user->password) &&
            $user->auth($user->login,$user->password)) {
            // Устанавливаем код ответа
            http_response_code(200);

            // Покажем сообщение о том, что пользователь был создан
            echo json_encode(array("message" => "Пользователь был создан"));
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

        // Получаем данные
        $data = json_decode(file_get_contents("php://input"));

        $user->token = $data->token;

        // удаление товара
        if ($user->delete()) {

            // код ответа - 200 ok
            http_response_code(200);

            // сообщение пользователю
            echo json_encode(array("message" => "Пользователь был удалён"), JSON_UNESCAPED_UNICODE);
        }else{  // если не удается удалить пользователя

            // код ответа - 503 Сервис не доступен
            http_response_code(503);

            // сообщим об этом пользователю
            echo json_encode(array("message" => "Не удалось удалить пользователя"));
        }

        return;
    }


    // Получение информации о пользователе
    // GET /users/{id}
    if ($method === 'GET' && count($urlData) === 1) {

        $stmt = $user->read($formData['login']);
        $num = $stmt->rowCount();

        if ($num > 0) {

            // массив товаров
            $user_arr = array();
            $user_arr["records"] = array();

            // получаем содержимое нашей таблицы
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // извлекаем строку
                extract($row);
                $user_list = array(
                    "id" => $id,
                    "login" => $login,
                    "password" => $password
                );
                array_push($user_arr["records"], $user_list);
            }

            // устанавливаем код ответа - 200 OK
            http_response_code(200);

            // выводим данные о товаре в формате JSON
            echo json_encode($user_arr);
        }else{

            // установим код ответа - 404 Не найдено
            http_response_code(404);

            // сообщаем пользователю, что товары не найдены
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