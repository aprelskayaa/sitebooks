<?php
// Подключение к базе данных
require_once 'db_connect.php';

// Проверка, что передан ID книги
if (!isset($_GET['id'])) {
    header('Location: manage_books.php');
    exit;
}

$book_id = $_GET['id'];

// Получение данных о книге
$sql = "SELECT * FROM books WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    echo "Книга не найдена!";
    exit;
}

// Обновление данных книги
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $genre = $_POST['genre'];
    $published_year = $_POST['published_year'];
    $isbn = $_POST['isbn'];
    $page_count = $_POST['page_count'];
    $publisher = $_POST['publisher'];
    $description = $_POST['description'];

    // Обработка загруженного файла обложки
    $cover_image = $book['cover_image']; // Предыдущее изображение
    if (!empty($_FILES['cover_image']['name'])) {
        $uploadDir = 'uploads/';
        $fileName = basename($_FILES['cover_image']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        $allowedTypes = array('jpg', 'png', 'jpeg', 'gif');

        if (in_array($fileType, $allowedTypes)) {
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetFilePath)) {
                $cover_image = $targetFilePath;
            }
        }
    }

    // Обновляем информацию о книге в базе данных
    $sql = "UPDATE books SET title = ?, author = ?, genre = ?, published_year = ?, isbn = ?, page_count = ?, publisher = ?, description = ?, cover_image = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $author, $genre, $published_year, $isbn, $page_count, $publisher, $description, $cover_image, $book_id]);

    // Перенаправляем обратно на страницу управления книгами после редактирования
    header('Location: manage_books.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать книгу</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 16px;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            resize: vertical;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
        }

        button, .back-btn {
            padding: 12px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            text-decoration: none;
        }

        button:hover, .back-btn:hover {
            background-color: #218838;
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .back-btn {
            background-color: #007bff;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }

        .current-image {
            margin-top: 15px;
        }

        .current-image img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Редактировать книгу</h1>

    <form action="edit_book.php?id=<?php echo $book_id; ?>" method="POST" enctype="multipart/form-data">
        <label for="title">Название книги</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required>

        <label for="author">Автор</label>
        <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required>

        <label for="genre">Жанр</label>
        <input type="text" id="genre" name="genre" value="<?php echo htmlspecialchars($book['genre']); ?>" required>

        <label for="published_year">Год издания</label>
        <input type="number" id="published_year" name="published_year" value="<?php echo htmlspecialchars($book['published_year']); ?>" required>

        <label for="isbn">ISBN</label>
        <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>" required>

        <label for="page_count">Количество страниц</label>
        <input type="number" id="page_count" name="page_count" value="<?php echo htmlspecialchars($book['page_count']); ?>" required>

        <label for="publisher">Издательство</label>
        <input type="text" id="publisher" name="publisher" value="<?php echo htmlspecialchars($book['publisher']); ?>" required>

        <label for="description">Описание</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($book['description']); ?></textarea>

        <label for="cover_image">Обложка книги (оставьте пустым, если не хотите изменять)</label>
        <input type="file" id="cover_image" name="cover_image">

        <!-- Отображение текущей обложки книги -->
        <div class="current-image">
            <p>Текущая обложка:</p>
            <?php if (!empty($book['cover_image'])): ?>
                <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Обложка книги">
            <?php else: ?>
                <p>Обложка не установлена.</p>
            <?php endif; ?>
        </div>

        <!-- Контейнер для кнопок "Сохранить изменения" и "Назад" -->
        <div class="button-container">
            <button type="submit">Сохранить изменения</button>
            <a href="manage_books.php" class="back-btn">Назад</a>
        </div>
    </form>
</div>

</body>
</html>
