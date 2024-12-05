<?php

include_once "../config/database.php";
include_once "../objects/category.php";


// Роутер
function route($method, $urlData, $formData) {

    $database = new Database();
    $db = $database->getConnection();
    $categories = new Category($db);


    // Получение всех категорий
    // GET /categories
    if ($method === 'GET' && count($urlData) === 0) {  // под вопросом 0 или 1

        $stmt = $categories->readAll();
        $num = $stmt->rowCount();

        if ($num > 0) {

            // массив для записей
            $categories_arr = array();
            $categories_arr["records"] = array();

            // получим содержимое нашей таблицы
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                // извлекаем строку
                extract($row);
                $category_item = array(
                    "id" => $id,
                    "name" => $name
                );
                array_push($categories_arr["records"], $category_item);
            }
            // код ответа - 200 OK
            http_response_code(200);

            // покажем данные категорий в формате json
            echo json_encode($categories_arr);
        }else{

            // код ответа - 404 Ничего не найдено
            http_response_code(404);

            // сообщим пользователю, что категории не найдены
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


    // Добавление новой категории
    // POST /categories
    if ($method === 'POST' && empty($urlData)) {

        // получаем id категории для добавления

        $data = json_decode(file_get_contents("php://input"));
        // Выводим ответ клиенту
        if (!empty($data->name) &&
            !empty($data->id)) {

            // устанавливаем значения свойств категории
            $categories->name = $data->name;
            $categories->id = $data->id;

            // создание товара
            if ($categories->create()) {

                // установим код ответа - 201 создано
                http_response_code(201);

                // сообщим пользователю
                echo json_encode(array("message" => "Категория была создана."), JSON_UNESCAPED_UNICODE);

            }else{ // если не удается создать категорию, сообщим пользователю

                // установим код ответа - 503 сервис недоступен
                http_response_code(503);

                // сообщим пользователю
                echo json_encode(array("message" => "Невозможно создать категорию."), JSON_UNESCAPED_UNICODE);
            }
        }else { // сообщим пользователю что данные неполные

            // установим код ответа - 400 неверный запрос
            http_response_code(400);

            // сообщим пользователю
            echo json_encode(array("message" => "Невозможно создать категорию. Данные неполные."), JSON_UNESCAPED_UNICODE);
        }

        return;
    }



    // Частичное обновление данных категории
    // PATCH /categories/{id}
    if ($method === 'PATCH' && count($urlData) === 1) {

        // получаем id категории для редактирования
        $data = json_decode(file_get_contents("php://input"));

        // установим id свойства категории для редактирования
        $categories->id = $data->id;

        // установим значения свойств категории
        $categories->name = $data->name;

        // обновление категории
        if ($categories->update()) {
            // установим код ответа - 200 ok
            http_response_code(200);

            // сообщим пользователю
            echo json_encode(array("message" => "Категория была обновлена"), JSON_UNESCAPED_UNICODE);
        }else{  // если не удается обновить категорию, сообщим пользователю

            // код ответа - 503 Сервис не доступен
            http_response_code(503);

            // сообщение пользователю
            echo json_encode(array("message" => "Невозможно обновить категорию"), JSON_UNESCAPED_UNICODE);
        }

        return;
    }

    // Удаление категории
    // DELETE /categories/{id}
    if ($method === 'DELETE' && count($urlData) === 1) {

        // получаем id категории
        $data = json_decode(file_get_contents("php://input"));

        // установим id категории для удаления
        $categories->id = $data->id;

        // категории
        if ($categories->delete()) {

            // код ответа - 200 ok
            http_response_code(200);

            // сообщение пользователю
            echo json_encode(array("message" => "Категория была удалена"), JSON_UNESCAPED_UNICODE);
        }else{  // если не удается удалить категорию

            // код ответа - 503 Сервис не доступен
            http_response_code(503);

            // сообщим об этом пользователю
            echo json_encode(array("message" => "Не удалось удалить категорию"));
        }

        return;
    }


    // Возвращаем ошибку
    header('HTTP/1.0 400 Bad Request');
    echo json_encode(array(
        'error' => 'Bad Request'
    ));

}