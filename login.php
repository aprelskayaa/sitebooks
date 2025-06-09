<?php
session_start();

// Подключение к базе данных
$host = 'localhost';
$db = 'my_BD1';  // Имя вашей базы данных
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Проверяем отправку формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Проверяем, существует ли пользователь с таким email
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Если пароль верен, создаем сессию
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];  // Сохраняем ID пользователя в сессию
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Сохраняем роль пользователя в сессии

        // Перенаправляем пользователя на главную страницу
        header('Location: index.php');
        exit;
    } else {
        echo "Неверные данные для входа.";
    }
}
?>
