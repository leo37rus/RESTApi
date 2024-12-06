<?php

// получаем соединение с базой данных
include_once "../config/database.php";
include_once "../objects/product.php";
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

    // Создание объекта "Product"
    $product = new Product($db);


    // Получение информации о товаре
    // GET /product/{id}
    if ($method === 'GET' && count($urlData) === 1) {

        $stmt = $product->searchById($urlData[0]);
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

    // Получение информации о товаре c возможностью указать категорию
    // GET /product?category={id}
    if ($method === 'GET' && count($urlData) === 0 ) {

        // Получаем id товара
        $stmt = $product->searchCategoryId(@$formData['category']);
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


    // Добавление нового товара
    // POST /product
    if ($method === 'POST' && empty($urlData)) {

        //Проверка авторизации
        if ($auth) {

            // Получаем id товара для редактирования
            $data = json_decode(file_get_contents("php://input"));

            // Выводим ответ клиенту
            if (!empty($data->name) &&
                !empty($data->category_id)) {

                // Устанавливаем значения свойств товара
                $product->name = $data->name;
                $product->category_id = $data->category_id;

                // Создание товара
                if ($product->create()) {

                    // Установим код ответа - 201 создано
                    http_response_code(201);

                    // Сообщим пользователю
                    echo json_encode(array("message" => "Товар был создан."), JSON_UNESCAPED_UNICODE);

                } else { // Если не удается создать товар, сообщим пользователю

                    // Установим код ответа - 503 сервис недоступен
                    http_response_code(503);

                    // Сообщим пользователю
                    echo json_encode(array("message" => "Невозможно создать товар."), JSON_UNESCAPED_UNICODE);
                }
            } else { // Сообщим пользователю что данные неполные

                // Установим код ответа - 400 неверный запрос
                http_response_code(400);

                // Сообщим пользователю
                echo json_encode(array("message" => "Невозможно создать товар. Данные неполные."), JSON_UNESCAPED_UNICODE);
            }

            return;
        }else{
            // Сообщим пользователю
            echo json_encode(array("message" => "Пользователь не авторизован, создание товара невозможно."), JSON_UNESCAPED_UNICODE);
        }
    }



    // Частичное обновление данных товара
    // PATCH /product/{id}
    if ($method === 'PATCH' && count($urlData) === 1) {

        //Проверка авторизации
        if($auth) {

            // Получаем id товара для редактирования
            $data = json_decode(file_get_contents("php://input"));

            // Установим id свойства товара для редактирования
            $product->id = $data->id;

            // Установим значения свойств товара
            $product->name = $data->name;
            $product->category_id = $data->category_id;

            // Обновление товара
            if ($product->update()) {
                // Установим код ответа - 200 ok
                http_response_code(200);

                // Сообщим пользователю
                echo json_encode(array("message" => "Товар был обновлён"), JSON_UNESCAPED_UNICODE);
            } else {  // Если не удается обновить товар, сообщим пользователю

                // Код ответа - 503 Сервис не доступен
                http_response_code(503);

                // Сообщение пользователю
                echo json_encode(array("message" => "Невозможно обновить товар"), JSON_UNESCAPED_UNICODE);
            }

            return;

        }else{
            // Сообщим пользователю
            echo json_encode(array("message" => "Пользователь не авторизован, обновление товара невозможно."), JSON_UNESCAPED_UNICODE);
        }
    }

    // Удаление товара
    // DELETE /product/{id}
    if ($method === 'DELETE' && count($urlData) === 1) {

        //Проверка авторизации
        if($auth) {

            // Получаем id товара
            $data = json_decode(file_get_contents("php://input"));

            // Установим id товара для удаления
            $product->id = $data->id;

            // Удаление товара
            if ($product->delete()) {

                // Код ответа - 200 ok
                http_response_code(200);

                // Сообщение пользователю
                echo json_encode(array("message" => "Товар был удалён"), JSON_UNESCAPED_UNICODE);
            } else {  // Если не удается удалить товар

                // Код ответа - 503 Сервис не доступен
                http_response_code(503);

                // Сообщим об этом пользователю
                echo json_encode(array("message" => "Не удалось удалить товар"));
            }

            return;
        }else{
            // Сообщим пользователю
            echo json_encode(array("message" => "Пользователь не авторизован, удаление товара невозможно."), JSON_UNESCAPED_UNICODE);
        }
    }


    // Возвращаем ошибку
    header('HTTP/1.0 400 Bad Request');
    echo json_encode(array(
        'error' => 'Bad Request'
    ));

}
