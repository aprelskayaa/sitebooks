<?php
session_start();
require_once 'db_connect.php'; // Подключение к базе данных

// Проверка авторизации администратора
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); // Перенаправляем на страницу входа, если администратор не авторизован
    exit;
}

// Проверяем, передан ли параметр user_id
if (!isset($_GET['user_id'])) {
    echo "ID пользователя не указан.";
    exit;
}

// Получаем ID пользователя
$user_id = $_GET['user_id'];

// Получаем информацию о пользователе
$sql = "SELECT username, email FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Пользователь не найден.";
    exit;
}

// Получаем книги, забронированные пользователем
$sql = "SELECT books.title, books.author, books.cover_image, books.published_year 
        FROM books 
        WHERE books.borrower_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Забронированные книги пользователя</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
        }

        .book-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        .book-item {
            width: 200px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .book-item img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .book-item h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .book-item p {
            font-size: 14px;
            color: #555;
        }

        .back-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            transition: background-color 0.3s;
            text-align: center;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }

    </style>
</head>
<body>

<div class="container">
    <h1>Забронированные книги пользователя: <?php echo htmlspecialchars($user['username']); ?></h1>

    <!-- Кнопка для возврата на страницу управления пользователями -->
    <a href="manage_users.php" class="back-btn">Вернуться к пользователям</a>

    <?php if (!empty($books)): ?>
        <div class="book-list">
            <?php foreach ($books as $book): ?>
                <div class="book-item">
                    <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                    <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                    <p>Автор: <?php echo htmlspecialchars($book['author']); ?></p>
                    <p>Год издания: <?php echo htmlspecialchars($book['published_year']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Пользователь не забронировал ни одной книги.</p>
    <?php endif; ?>
</div>

</body>
</html>
