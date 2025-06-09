<?php
// Настройки подключения к базе данных
$host = 'localhost'; // Обычно localhost
$db = 'my_bd1'; // Имя вашей базы данных
$user = 'root'; // Имя пользователя (обычно root)
$pass = ''; // Пароль (по умолчанию для XAMPP обычно пустой)

// Настройки для подключения с помощью PDO
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Включить отображение ошибок
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Режим выборки по умолчанию (ассоциативный массив)
    PDO::ATTR_EMULATE_PREPARES => false, // Отключить эмуляцию подготовленных запросов
];

try {
    // Создаём новое PDO-соединение
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Если возникла ошибка подключения, выводим её
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}
?>
