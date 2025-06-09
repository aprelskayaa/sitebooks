<?php
// Подключение к базе данных
require_once 'db_connect.php';

// Если форма отправлена и указано действие "delete"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $book_id = $_POST['book_id'];

    // Запрос на удаление книги
    $sql = "DELETE FROM books WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$book_id]);

    // Перенаправляем обратно на страницу управления книгами после удаления
    header('Location: manage_books.php');
    exit;
}

// Запрос с JOIN для получения данных книг и жанров
$sql = "SELECT books.*, genres.genre_name FROM books 
        LEFT JOIN genres ON books.genre_id = genres.id";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление книгами</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
        }

        .button-container {
            display: flex;
            justify-content: flex-end;
            gap: 500px;
            margin-bottom: 20px;
        }

        .add-book-btn, .back-btn {
            padding: 12px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            display: inline-block;
            text-align: center;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .back-btn {
            background-color: #007bff;
        }

        .add-book-btn:hover, .back-btn:hover {
            background-color: #218838;
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .back-btn:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 15px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        td {
            background-color: #f9f9f9;
        }

        button {
            padding: 8px 15px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .edit-btn {
            background-color: #007bff;
            color: white;
        }

        .edit-btn:hover {
            background-color: #0056b3;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .button-container {
                display: block;
                text-align: center;
                gap: 20px;
            }

            table, th, td {
                font-size: 14px;
            }

            th, td {
                padding: 10px;
            }

            .add-book-btn, .back-btn {
                display: block;
                margin: 10px auto;
                padding: 10px 15px;
                width: 80%;
                text-align: center;
            }
        }

        @media (max-width: 425px) {
            th, td {
                padding: 8px;
            }

            table, th, td {
                font-size: 12px;
            }

            .add-book-btn, .back-btn {
                padding: 8px;
                width: 100%;
                font-size: 14px;
            }
        }

    </style>
</head>
<body>

<div class="container">
    <h1>Управление книгами</h1>

    <!-- Кнопки для добавления книги и возврата -->
    <div class="button-container">
        <a href="add_book.php" class="add-book-btn">Добавить новую книгу</a>
        <a href="admin.php" class="back-btn">Вернуться на страницу администратора</a>
    </div>

    <h2>Существующие книги</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Автор</th>
                    <th>Жанр</th>
                    <th>Год издания</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['genre_name']); // Отображение жанра ?></td>
                        <td><?php echo htmlspecialchars($book['published_year']); ?></td>
                        <td>
                            <!-- Кнопка для редактирования -->
                            <form action="edit_book.php" method="GET" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
                                <button type="submit" class="edit-btn">Редактировать</button>
                            </form>

                            <!-- Кнопка для удаления -->
                            <form action="manage_books.php" method="POST" style="display:inline;">
                                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="delete-btn" onclick="return confirm('Вы уверены, что хотите удалить книгу?');">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
