<?php

// Получаем соединение с базой данных
include_once "../config/database.php";
include_once "../objects/category.php";
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
    // Создание объекта "Category"
    $categories = new Category($db);

    // Получение всех категорий
    // GET /categories
    if ($method === 'GET' && count($urlData) === 0) {  // под вопросом 0 или 1


        $stmt = $categories->readAll();
        $num = $stmt->rowCount();

        if ($num > 0) {

            // Массив для записей
            $categories_arr = array();
            $categories_arr["records"] = array();

            // Получим содержимое нашей таблицы
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                // Извлекаем строку
                extract($row);
                $category_item = array(
                    "id" => $id,
                    "name" => $name
                );
                array_push($categories_arr["records"], $category_item);
            }
            // Код ответа - 200 OK
            http_response_code(200);

            // Покажем данные категорий в формате json
            echo json_encode($categories_arr);
        }else{

            // Код ответа - 404 Ничего не найдено
            http_response_code(404);

            // Сообщим пользователю, что категории не найдены
            echo json_encode(array("message" => "Категории не найдены"), JSON_UNESCAPED_UNICODE);
        }

        return;
    }

    // Получение информации о товаре c возможностью указать категорию
    // GET /categories{id}
    if ($method === 'GET' && count($urlData) === 1 ) {
        // Получаем id категории

        $stmt = $categories->searchCategoryId($urlData[0]);
        $num = $stmt->rowCount();

        if ($num > 0) {

            // Массив товаров
            $products_arr = array();
            $products_arr["records"] = array();

            // Получаем содержимое нашей таблицы
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Извлекаем строку
                extract($row);
                $product_item = array(
                    "id" => $id,
                    "name" => $name,
                    "category_id" => $category_id,
                    "category_name" => $category_name
                );
                array_push($products_arr["records"], $product_item);
            }

            // Устанавливаем код ответа - 200 OK
            http_response_code(200);

            // Выводим данные о товаре в формате JSON
            echo json_encode($products_arr);
        }else{

            // Установим код ответа - 404 Не найдено
            http_response_code(404);

            // Сообщаем пользователю, что товары не найдены
            echo json_encode(array("message" => "Товары не найдены."), JSON_UNESCAPED_UNICODE);
        }


        return;
    }


    // Добавление новой категории
    // POST /categories
    if ($method === 'POST' && empty($urlData)) {

        //Проверка авторизации
        if($auth) {

            // Получаем id категории для добавления

            $data = json_decode(file_get_contents("php://input"));
            // Выводим ответ клиенту
            if (!empty($data->name) &&
                !empty($data->id)) {

                // Устанавливаем значения свойств категории
                $categories->name = $data->name;
                $categories->id = $data->id;

                // Создание категории
                if ($categories->create()) {

                    // Установим код ответа - 201 создано
                    http_response_code(201);

                    // Сообщим пользователю
                    echo json_encode(array("message" => "Категория была создана."), JSON_UNESCAPED_UNICODE);

                } else { // Если не удается создать категорию, сообщим пользователю

                    // Установим код ответа - 503 сервис недоступен
                    http_response_code(503);

                    // Сообщим пользователю
                    echo json_encode(array("message" => "Невозможно создать категорию."), JSON_UNESCAPED_UNICODE);
                }
            } else { // Сообщим пользователю что данные неполные

                // Установим код ответа - 400 неверный запрос
                http_response_code(400);

                // Сообщим пользователю
                echo json_encode(array("message" => "Невозможно создать категорию. Данные неполные."), JSON_UNESCAPED_UNICODE);
            }

            return;
        }else{
            // Сообщим пользователю
            echo json_encode(array("message" => "Пользователь не авторизован, удаление категории невозможно."), JSON_UNESCAPED_UNICODE);
        }
    }



    // Частичное обновление данных категории
    // PATCH /categories/{id}
    if ($method === 'PATCH' && count($urlData) === 1) {

        //Проверка авторизации
        if($auth) {

            // Получаем id категории для редактирования
            $data = json_decode(file_get_contents("php://input"));

            // Установим id свойства категории для редактирования
            $categories->id = $data->id;

            // Установим значения свойств категории
            $categories->name = $data->name;

            // Обновление категории
            if ($categories->update()) {
                // Установим код ответа - 200 ok
                http_response_code(200);

                // Сообщим пользователю
                echo json_encode(array("message" => "Категория была обновлена"), JSON_UNESCAPED_UNICODE);
            } else {  // Если не удается обновить категорию, сообщим пользователю

                // Код ответа - 503 Сервис не доступен
                http_response_code(503);

                // Сообщение пользователю
                echo json_encode(array("message" => "Невозможно обновить категорию"), JSON_UNESCAPED_UNICODE);
            }

            return;
        }else{
            // Сообщим пользователю
            echo json_encode(array("message" => "Пользователь не авторизован, обновление категории невозможно."), JSON_UNESCAPED_UNICODE);
        }
    }

    // Удаление категории
    // DELETE /categories/{id}
    if ($method === 'DELETE' && count($urlData) === 1) {

        //Проверка авторизации
        if($auth) {

            // Получаем id категории
            $data = json_decode(file_get_contents("php://input"));

            // Установим id категории для удаления
            $categories->id = $data->id;

            // Удаление категории
            if ($categories->delete()) {

                // Код ответа - 200 ok
                http_response_code(200);

                // Сообщение пользователю
                echo json_encode(array("message" => "Категория была удалена"), JSON_UNESCAPED_UNICODE);
            } else {  // Если не удается удалить категорию

                // Код ответа - 503 Сервис не доступен
                http_response_code(503);

                // Сообщим об этом пользователю
                echo json_encode(array("message" => "Не удалось удалить категорию"));
            }

            return;
        }else{
            // Сообщим пользователю
            echo json_encode(array("message" => "Пользователь не авторизован, удаление категории невозможно."), JSON_UNESCAPED_UNICODE);
        }
    }


    // Возвращаем ошибку
    header('HTTP/1.0 400 Bad Request');
    echo json_encode(array(
        'error' => 'Bad Request'
    ));

}