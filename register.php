<?php
session_start();

// Подключение к базе данных
$host = 'localhost';
$db = 'my_BD1';
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

// Проверяем, отправлена ли форма
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Проверка совпадения паролей
    if ($password !== $confirm_password) {
        echo "Пароли не совпадают!";
        exit;
    }

    // Хешируем пароль
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Проверяем, существует ли уже такой email
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Если пользователь уже существует
        echo "Пользователь с таким email уже зарегистрирован.";
    } else {
        // Вставляем нового пользователя в базу данных
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hashed_password]);

        // Создаем сессию для авторизованного пользователя
        $_SESSION['user_logged_in'] = true;
        $_SESSION['username'] = $name;

        echo "Регистрация успешна!";
        header('Location: index.php');
    }
}
?>
