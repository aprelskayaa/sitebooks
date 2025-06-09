<?php
session_start();
require_once 'db_connect.php'; // Подключение к базе данных

// Проверка авторизации пользователя
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Проверяем, был ли передан ID книги
if (!isset($_POST['book_id'])) {
    $error_message = "Ошибка: не передан ID книги.";
    $book = null; // Обнуляем переменную для вывода сообщения об ошибке
} else {
    // Получаем ID книги из запроса
    $book_id = $_POST['book_id'];

    // Получаем информацию о книге
    $sql = "SELECT title, author, availability_status FROM books WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    // Проверяем, найдена ли книга
    if (!$book) {
        $error_message = "Книга не найдена.";
    }
}

// Если книга найдена и данные формы отправлены
if ($book && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!empty($email) && !empty($password)) {
        // Проверяем email и пароль пользователя
        $sql = "SELECT password FROM users WHERE id = ? AND email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Проверяем доступность книги для бронирования
            if ($book['availability_status'] === 'available') {
                // Обновляем статус книги на "забронирована"
                $sql = "UPDATE books SET availability_status = 'reserved', borrower_id = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id, $book_id]);

                $success_message = "Книга успешно забронирована!";
            } else {
                $error_message = "Эта книга уже забронирована и недоступна для бронирования.";
            }
        } else {
            $error_message = "Неверный email или пароль.";
        }
    } else {
        $error_message = "Поля email и пароль обязательны для заполнения.";
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение бронирования</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .back-btn {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 15px;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Подтверждение бронирования</h2>

    <?php if (isset($error_message)): ?>
        <div class="message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
        <div class="success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if ($book): ?>
        <p><strong>Название книги:</strong> <?php echo htmlspecialchars($book['title']); ?></p>
        <p><strong>Автор:</strong> <?php echo htmlspecialchars($book['author']); ?></p>

        <form action="booking_confirm.php" method="post">
            <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
            <div class="form-group">
                <label for="email">Введите ваш email для подтверждения:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Введите ваш пароль для подтверждения:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" name="confirm_booking">Подтвердить бронирование</button>
        </form>
    <?php endif; ?>

    <a href="about.php?id=<?php echo $book_id; ?>" class="back-btn">Вернуться к книге</a>
</div>

</body>
</html>
