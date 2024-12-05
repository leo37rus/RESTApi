<?php
// Заголовок
header("Content-Type: application/json");

// Файлы необходимые для соединения с БД
include_once "./config/database.php";
include_once "./objects/user.php";

// Получаем соединение с базой данных
$database = new Database();
$db = $database->getConnection();

// Создание объекта "User"
$user = new User($db);

// Методы класса "User"
$request_method = $_SERVER["REQUEST_METHOD"];


switch($request_method) {
    case 'GET':
        $stmt = $user->read();
        $num = $stmt->rowCount();

        if($num > 0) {
            $users_arr = array();
            $users_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $user_item = array(
                    "id" => $id,
                    "name" => $name,
                    "password" => $password
                );

                array_push($users_arr["records"], $user_item);
            }

            http_response_code(200);
            echo json_encode($users_arr);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "No users found."]);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->login) && !empty($data->password)) {
            $user->login = $data->login;
            $user->password = $data->password;

            if($user->create()) {
                http_response_code(201);
                echo json_encode(["message" => "User was created."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to create user."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Unable to create user. Data is incomplete."]);
        }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->id) && !empty($data->login) && !empty($data->password)) {
            $user->id = $data->id;
            $user->login = $data->login;
            $user->password = $data->password;


            if($user->update()) {
                http_response_code(200);
                echo json_encode(["message" => "User was updated."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to update user."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Unable to update user. Data is incomplete."]);
        }
        break;
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->id)) {
            $user->id = $data->id;

            if($user->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "User was deleted."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to delete user."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Unable to delete user. Data is incomplete."]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}
?>
