<?php
require_once 'db_connect.php'; // Подключение к базе данных

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $genre_name = $_POST['genre_name']; // Получаем название жанра из формы
    $published_year = $_POST['published_year'];
    $isbn = $_POST['isbn'];
    $page_count = $_POST['page_count'];
    $publisher = $_POST['publisher'];
    $description = $_POST['description'];

    // Обработка загруженного файла обложки
    $cover_image = '';
    if (!empty($_FILES['cover_image']['name'])) {
        $fileName = basename($_FILES['cover_image']['name']);
        $targetFilePath = 'uploads/' . $fileName;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetFilePath)) {
            $cover_image = $targetFilePath;
        }
    }

    // Проверяем, существует ли введенный жанр в таблице genres
    $sql = "SELECT id FROM genres WHERE genre_name = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$genre_name]);
    $genre = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$genre) {
        // Если жанр не найден, добавляем его в таблицу genres
        $sql = "INSERT INTO genres (genre_name) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$genre_name]);
        $genre_id = $pdo->lastInsertId(); // Получаем ID нового жанра
    } else {
        $genre_id = $genre['id']; // Используем существующий ID жанра
    }

    // Добавляем новую книгу в таблицу books
    $sql = "INSERT INTO books (title, author, genre_id, published_year, isbn, page_count, publisher, description, cover_image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $author, $genre_id, $published_year, $isbn, $page_count, $publisher, $description, $cover_image]);

    // Перенаправляем на страницу с книгами
    header('Location: manage_books.php');
    exit;
}
?>



<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить книгу</title>
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
    </style>
</head>
<body>

<div class="container">
    <h1>Добавить новую книгу</h1>

    <form action="add_book.php" method="POST" enctype="multipart/form-data">
        <label for="title">Название книги</label>
        <input type="text" id="title" name="title" required>

        <label for="author">Автор</label>
        <input type="text" id="author" name="author" required>

        <label for="genre">Жанр</label>
        <input type="text" id="genre" name="genre_name" required> 

        <label for="published_year">Год издания</label>
        <input type="number" id="published_year" name="published_year" required>

        <label for="isbn">ISBN</label>
        <input type="text" id="isbn" name="isbn" required>

        <label for="page_count">Количество страниц</label>
        <input type="number" id="page_count" name="page_count" required>

        <label for="publisher">Издательство</label>
        <input type="text" id="publisher" name="publisher" required>

        <label for="description">Описание</label>
        <textarea id="description" name="description" required></textarea>

        <label for="cover_image">Обложка книги</label>
        <input type="file" id="cover_image" name="cover_image" required>

        <div class="button-container">
            <button type="submit">Добавить книгу</button>
            <a href="manage_books.php" class="back-btn">Назад</a>
        </div>
    </form>
</div>


</body>
</html>
