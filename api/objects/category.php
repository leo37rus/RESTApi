<?php

class Category
{
    // соединение с БД и таблицей "categories"
    private $connect;

    // свойства объекта
    public $id;
    public $name;


    public function __construct($db)
    {
        $this->connect = $db;
    }



    // Роутер
    function route($method, $urlData, $formData) {


        // Добавление нового товара
        // POST /category
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


        // Получение информации о категории по id
        // GET /category/{id}
        if ($method === 'GET' && count($urlData) === 1) {
            // Получаем id товара
            $category_id = $urlData[0];

            // Вытаскиваем товар из базы...

            // Выводим ответ клиенту
            echo json_encode(array(
                'method' => 'GET',
                'id' => $category_id,
                'good' => 'phone',
                'price' => 10000
            ));

            return;
        }

        // Получение всех категорий
        // GET /category
        if ($method === 'GET' && empty($urlData)){
            // Получаем id товара
            $category_id = $urlData[0];

            // Вытаскиваем товар из базы...

            // Выводим ответ клиенту
            echo json_encode(array(
                'method' => 'GET',
                'id' => $category_id,
                'good' => 'phone',
                'price' => 10000
            ));

            return;
        }



        // Частичное обновление данных товара
        // PATCH /category/{id}
        if ($method === 'PATCH' && count($urlData) === 1) {
            // Получаем id товара
            $category_id = $urlData[0];

            // Обновляем только указанные поля товара в базе...

            // Выводим ответ клиенту
            echo json_encode(array(
                'method' => 'PATCH',
                'id' => $category_id,
                'formData' => $formData
            ));

            return;
        }

        // Удаление товара
        // DELETE /category/{id}
        if ($method === 'DELETE' && count($urlData) === 1) {
            // Получаем id товара
            $category_id = $urlData[0];

            // Удаляем товар из базы...

            // Выводим ответ клиенту
            echo json_encode(array(
                'method' => 'DELETE',
                'id' => $category_id
            ));

            return;
        }


        // Возвращаем ошибку
        header('HTTP/1.0 400 Bad Request');
        echo json_encode(array(
            'error' => 'Bad Request'
        ));

    }
}
