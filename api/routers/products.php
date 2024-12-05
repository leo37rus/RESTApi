<?php

include_once "../config/database.php";
include_once "../objects/product.php";

// Роутер
function route($method, $urlData, $formData) {

    $database = new Database();
    $db = $database->getConnection();
    $product = new Product($db);

//    $stmt = $product->search();
//    $num = $stmt->rowCount();

    // Получение информации о товаре
    // GET /product/{id}
    if ($method === 'GET' && count($urlData) === 1) {

        $stmt = $product->searchById($urlData[0]);
        $num = $stmt->rowCount();

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
    if ($method === 'GET' && count($urlData) === 0 ) {
        // Получаем id товара

        $stmt = $product->searchCategoryId(@$formData['category']);
        $num = $stmt->rowCount();

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


    // Добавление нового товара
    // POST /product
    if ($method === 'POST' && empty($urlData)) {

        // Добавляем товар в базу...

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
