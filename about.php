<?php
session_start();
// Подключаемся к базе данных
require_once 'db_connect.php';

// Проверка авторизации
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: log_form.php'); // Перенаправление на страницу входа
    exit;
}

$user_id = $_SESSION['user_id']; // ID текущего пользователя

// Проверяем, передан ли параметр id книги
if (isset($_GET['id'])) {
    $book_id = $_GET['id'];

    // Получаем данные книги по её id с использованием JOIN для жанра
    $sql = "SELECT books.*, genres.genre_name 
            FROM books 
            LEFT JOIN genres ON books.genre_id = genres.id 
            WHERE books.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    // Если книга не найдена, показываем сообщение и прекращаем выполнение скрипта
    if (!$book) {
        echo "Книга не найдена.";
        exit;
    }
} else {
    // Если параметр id не передан, показываем сообщение и прекращаем выполнение скрипта
    echo "ID книги не указан.";
    exit;
}

// Переменная для сообщений об успехе/ошибке
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Если книга уже забронирована
    if (isset($_POST['action']) && $_POST['action'] === 'cancel_booking') {
        // Проверяем, что книгу бронировал текущий пользователь
        if ($book['borrower_id'] === $user_id) {
            // Отменяем бронирование, сбрасываем borrower_id и статус книги
            $sql = "UPDATE books SET availability_status = 'available', borrower_id = NULL WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$book_id]);
            
            // Обновляем данные книги
            $sql = "SELECT books.*, genres.genre_name FROM books LEFT JOIN genres ON books.genre_id = genres.id WHERE books.id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$book_id]);
            $book = $stmt->fetch();

            $message = 'Бронирование успешно отменено.';
        } else {
            $message = 'Вы не можете отменить бронирование этой книги, так как она забронирована другим пользователем.';
        }
    } elseif ($book['availability_status'] !== 'available') {
        $message = 'Эта книга уже забронирована.';
    } else {
        // Если книга доступна для бронирования, перенаправляем на страницу подтверждения бронирования
        header("Location: booking_confirm.php?book_id=" . $book_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Название книги</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<style>
/* Стили как в вашем предыдущем коде */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    display: flex;
    flex-direction: column;
    min-height: 100vh; /* Минимальная высота для body — высота экрана */
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
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

main {
    flex-grow: 1; /* Этот элемент будет заполнять все доступное пространство */
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

h1 {
    font-size: 36px;
    margin-bottom: 20px;
    text-align: center;
}

.book-details {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    justify-content: center;
}

.book-cover {
    width: 250px;
    margin: 0 auto;
}

.book-cover img {
    width: 100%;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.book-info {
    flex: 1;
    margin-top: 20px;
}

.book-info p {
    font-size: 18px;
    margin-bottom: 10px;
}

.book-info h3 {
    font-size: 24px;
    margin-bottom: 10px;
}

.book-description {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #ccc;
}

.book-description h3 {
    font-size: 24px;
    margin-bottom: 10px;
}

.book-description p {
    font-size: 16px;
    line-height: 1.8;
    color: #555;
}

.availability {
    margin-top: 30px;
    font-size: 18px;
}

.availability h3 {
    margin-bottom: 10px;
    font-size: 20px;
}

.booking {
    margin-top: 20px;
}

.booking button {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

.booking button:hover {
    background-color: #0056b3;
}

.message {
    margin-top: 20px;
    padding: 10px;
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    border-radius: 5px;
    text-align: center;
}

footer {
    background-color: #444;
    color: white;
    padding: 20px;
    text-align: center;
    width: 100%; /* Футер занимает всю ширину страницы */
}

footer p {
    margin-bottom: 10px;
}

</style>

<header>
    <div class="logo">
        <a href="index.php">Library</a>
    </div>
    <div class="menu">
        <?php
        // Если пользователь авторизован
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

<main>
    <div class="book-details">
        <div class="book-cover">
            <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
        </div>
        
        <div class="book-info">
            <h3><?php echo htmlspecialchars($book['title']); ?></h3>
            <p><strong>Автор:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
            <p><strong>Год издания:</strong> <?php echo htmlspecialchars($book['published_year']); ?></p>
            <p><strong>Жанр:</strong> <?php echo htmlspecialchars($book['genre_name']); ?></p>
            <p><strong>Количество страниц:</strong> <?php echo htmlspecialchars($book['page_count']); ?></p>
            <p><strong>Издательство:</strong> <?php echo htmlspecialchars($book['publisher']); ?></p>
            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>

            <div class="availability">
                <h3>Статус книги:</h3>
                <p><?php echo $book['availability_status'] === 'available' ? 'Книга доступна для бронирования' : 'Книга уже занята'; ?></p>
            </div>

            <!-- Кнопки бронирования и отмены бронирования -->
            <div class="booking">
                <?php if ($book['availability_status'] === 'available'): ?>
                    <!-- Форма для бронирования книги -->
                    <form action="booking_confirm.php" method="post">
                        <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($book_id); ?>"> <!-- Передаем ID книги -->
                        <button type="submit" name="confirm_booking">Забронировать книгу</button>
                    </form>
                <?php elseif ($book['borrower_id'] === $user_id): ?>
                    <!-- Форма для отмены бронирования (только для пользователя, который забронировал книгу) -->
                    <form action="about.php?id=<?php echo $book_id; ?>" method="post">
                        <input type="hidden" name="action" value="cancel_booking">
                        <button type="submit">Отменить бронирование</button>
                    </form>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Вывод сообщения -->
    <?php if (!empty($message)): ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="book-description">
        <h3>Описание книги</h3>
        <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
    </div>

</main>
<footer>
    <div class="footer-content">
        <p>Служба поддержки: 8 800 777 77 77 | support@library.ru</p>
        <p>Адрес: Улица Книжная, 12, город Знаний, 123456, Россия</p>
    </div>
</footer>
</body>
</html>
