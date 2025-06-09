<?php
session_start();
require_once 'db_connect.php'; // Подключение к базе данных

// Проверка авторизации пользователя
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php'); // Перенаправляем на страницу входа, если пользователь не авторизован
    exit;
}

// Получаем ID пользователя из сессии
$user_id = $_SESSION['user_id'];

// Выполняем запрос к базе данных для получения информации о пользователе
$sql = "SELECT username, email, city, address FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Пользователь не найден.";
    exit;
}

// Получаем список забронированных книг для пользователя
$sql = "SELECT title, author, cover_image, id FROM books WHERE borrower_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$borrowed_books = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль пользователя</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Основные стили для страницы */
        html, body {
            height: 100%;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
        }

        body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        header { 
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #444;
            color: white;
            padding: 10px 20px;
        }

        .logo a {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.3s ease, text-shadow 0.3s ease;
        }

        .menu a {
            display: inline-block;
            padding: 10px 20px;
            color: white;
            background-color: #555555;
            text-decoration: none;
            margin: 0 5px;
            font-size: 16px;
            border-radius: 25px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .menu a:hover {
            background-color: #666;
            transform: translateY(-3px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 1024px) {
            header {
                flex-direction: column; /* Меняем расположение на вертикальное */
                align-items: center; /* Выровняем элементы по центру */
            }

            .logo {
                margin-bottom: 15px; /* Добавим отступ под логотипом */
            }

            .menu {
                display: flex;
                justify-content: center; /* Кнопки будут выровнены по центру */
                gap: 10px; /* Добавим отступ между кнопками */
                width: 100%;
            }

            .menu a {
                text-align: center;
                display: block;
                padding: 10px 20px;
            }
        }

        .profile-container {
            max-width: 1200px;
            margin: 100px auto 50px;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            flex-grow: 1;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .profile-info h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .profile-info p {
            margin-bottom: 5px;
            color: #666;
        }

        .profile-actions {
            margin-top: 20px;
            display: flex;
            gap: 15px;
        }

        .profile-actions a, .profile-actions button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .profile-actions a:hover, .profile-actions button:hover {
            background-color: #0056b3;
        }

        .profile-actions button {
            background-color: #ff4d4d;
        }

        .profile-actions button:hover {
            background-color: #d43f3f;
        }

        .profile-content {
            margin-top: 40px;
        }

        .book-list {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .book-item {
            width: 150px;
            text-align: center;
        }

        .book-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }

        .book-item p {
            margin-top: 10px;
            font-size: 14px;
            color: #333;
        }

        footer {
            background-color: #444;
            color: white;
            text-align: center;
            padding: 15px;
            width: 100%;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <a href="index.php">Library</a>
    </div>
    <div class="menu">
        <?php
        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true):
        ?>
            <a href="prof.php">Профиль (<?php echo $_SESSION['username']; ?>)</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php">Админ</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="log_form.php">Вход</a>
        <?php endif; ?>
        <a href="index.php#catalog">Каталог</a>
    </div>
</header>

<div class="profile-container">
    <!-- Информация о пользователе -->
    <div class="profile-header">
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            <p>Город: <?php echo htmlspecialchars($user['city']); ?></p>
            <p>Адрес: <?php echo htmlspecialchars($user['address']); ?></p>
        </div>
    </div>

    <div class="profile-actions">
        <a href="prof_redact.php">Редактировать профиль</a>

        <form action="logout.php" method="post" style="display: inline;">
            <button type="submit">Выйти</button>
        </form>
    </div>

    <!-- Список забронированных книг -->
    <div class="profile-content">
        <h2>Мои забронированные книги</h2>
        <?php if (count($borrowed_books) > 0): ?>
            <div class="book-list">
                <?php foreach ($borrowed_books as $book): ?>
                    <div class="book-item">
                        <a href="about.php?id=<?php echo $book['id']; ?>">
                            <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                        </a>
                        <p><?php echo htmlspecialchars($book['title']); ?></p>
                        <p><?php echo htmlspecialchars($book['author']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>У вас нет забронированных книг.</p>
        <?php endif; ?>
    </div>
</div>

<footer>
    <div class="footer-content">
        <p>Служба поддержки: 8 800 777 77 77 | support@library.ru</p>
        <p>Адрес: Улица Книжная, 12, город Знаний, 123456, Россия</p>
    </div>
</footer>

</body>
</html>
