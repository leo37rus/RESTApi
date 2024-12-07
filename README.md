# RESTApi
## Задание
Необходимо реализовать API для работы с базой данных продуктов компании. База данных размещена под управлением СУБД MySQL. API должен быть RESTful, с возможностью создания, редактирования и удаления записей в базе данных.

### Требования:
1. Аутентификация и авторизация должны быть реализованы.
2. Запросы на получение данных доступны всем пользователям.
3. Запросы на создание, редактирование и удаление данных доступны только авторизованным пользователям.

---

## Используемые инструменты
- Язык программирования: PHP 8.1.31
- СУБД: MySQL на базе Open Server

---

## Структура данных
В базе данных используется 4 таблицы:
- products — продукты
- categories — категории продуктов
- users — пользователи
- tokens — токены пользователей

Таблицы products и categories содержат поле status (0 — не отображается/пользователь заблокирован, 1 — опубликовано/активен), для таблицы users это поле active. Продукты и категории с состоянием 0 не возвращаются для неавторизованных пользователей.

---

## API
### Категории продуктов:
- POST /api/v1/categories — Создает категорию.
- GET /api/v1/categories — Возвращает все категории.
- GET /api/v1/categories/{id} — Возвращает категорию по ID.
- PATCH /api/v1/categories/{id} — Изменяет категорию по ID (продукты в категории не редактируются этим запросом).
- DELETE /api/v1/categories/{id} — Удаляет категорию по ID.

### Продукты:
- POST /api/v1/products — Создает продукт.
- GET /api/v1/products?category={id} — Возвращает список продуктов (с фильтром по категории).
- GET /api/v1/products/{id} — Возвращает продукт по ID.
- PATCH /api/v1/products/{id} — Изменяет продукт по ID.
- DELETE /api/v1/products/{id} — Удаляет продукт по ID.

### Аутентификация:
- POST /api/v1/auth — Аутентификация пользователя. В теле запроса передаются логин и пароль.
- DELETE /api/v1/auth — Сброс аутентификации, выход из учетной записи.
- GET /api/v1/auth — Получение данных текущего авторизованного пользователя (его логин).

---

 ### Запуск API:
1. Сохранить и распаковать архив с файлами.
2. Переименовать файл 123.htaccess в файл вида .htaccess
3. Установить СУБД MySQL, на веб-сервере Apache, установить PHP, настроить Apache для использование PHP (например через mod_php), включить для директории AllowOverride yes
4. Данные для подключения к БД описаны в файле database.php
5. Используя PhpMyAdmin, создать новую базу данных TestDb
6. Создать 4 таблицы products,categories,users,tokens, таблица(products) должна содержать 4 столбца(id,name,category_id,status), таблица (categories) должна содержать 3 столбца(id,name,status),таблица(users) должна содержать 4 столбца(id,login,password,activ),таблица(tokens) должна содержать 3 столбца(id,login,token), заполнить столбцы данными
7. Прописать данные подключения к БД в файле database.php
8. При запущенном сервере обращаться к API последству URL или cURL запросов вида http://..TestRESTApi-main/api/v1/products, так же можно использовать программу Postman, при вызове методов передавать данные в формате JSON, возвращаемые данные так же в формате JSON, авторизация просходит при вызове метода POST /api/vl/auth и передаче данных login и password в формате JSON(данные пользователя должны быть описаны в таблице users).

---

 ### Примечания:
- Все запросы и ответы должны быть в формате JSON.
- Авторизация происходит через метод POST /api/v1/auth, передавая логин и пароль в формате JSON.
- Пользователь должен быть зарегистрирован в таблице users для успешной аутентификации.


