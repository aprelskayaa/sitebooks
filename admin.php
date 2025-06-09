<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: log_form.php'); // Перенаправление на страницу входа
    exit;
}

// Проверка роли "admin"
if ($_SESSION['role'] !== 'admin') {
    echo "Доступ запрещён. У вас недостаточно прав.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Минимальная высота экрана */
        }

        header {
          
            
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #444;
            color: white;
            padding: 10px 20px;
        }

        @media (max-width: 768px) {
            header {
                position: static; /* Отменяем фиксированное позиционирование */
        
            }
        }



        .logo a {
            font-size: 32px; /* Увеличиваем размер для большей выразительности */
            font-weight: bold; /* Жирный шрифт для акцента */
            color: #007bff; /* Привлекательный цвет, например, синий */
            text-decoration: none; /* Убираем подчеркивание у ссылки */
            cursor: pointer; /* Указывает, что элемент кликабелен */
            transition: color 0.3s ease, text-shadow 0.3s ease; /* Плавные переходы для эффектов */
        }

        .logo a:hover {
            color: #045ebe; /* Цвет при наведении — немного темнее */
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Добавляем легкую тень для объема при наведении */
        }

        .logo a:active {
            color: #064d94; /* Цвет при нажатии */
        }

        .menu a {
    display: inline-block; /* Чтобы кнопки были блочными */
    padding: 10px 20px; /* Отступы внутри кнопок */
    color: white; /* Цвет текста */
    background-color: #555555; /* Основной цвет фона */
    text-decoration: none; /* Убираем подчеркивание */
    margin: 0 5px; /* Отступ между кнопками */
    font-size: 16px;
    border-radius: 25px; /* Закругленные углы */
    transition: background-color 0.3s ease, transform 0.3s ease; /* Плавные переходы для фона и анимации */
}

/* Эффект при наведении */
.menu a:hover {
    background-color: #666; /* Изменение цвета фона при наведении */
    transform: translateY(-3px); /* Поднимаем кнопку при наведении */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Добавляем тень */
}

/* Эффект при нажатии */
.menu a:active {
    transform: translateY(0); /* Убираем поднятие при нажатии */
    box-shadow: 0 2px 3px rgba(0, 0, 0, 0.2); /* Уменьшаем тень при нажатии */
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

        .admin-container {
            flex-grow: 1; /* Заставляем основной контент расти, заполняя доступное пространство */
            max-width: 1200px;
            margin: 50px auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .admin-section {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .admin-card {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            width: calc(50% - 10px); /* Две карточки на строку */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .admin-card h3 {
            margin-bottom: 15px;
        }

        .admin-card p {
            margin-bottom: 15px;
            color: #666;
        }

        .admin-card a {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .admin-card a:hover {
            background-color: #0056b3;
        }

        footer {
    background-color: #444;
    color: white;
    padding: 20px;
    text-align: center;
    position: relative; /* Убираем фиксированное позиционирование */
    bottom: 0; /* Это правило уже не нужно */
    width: 100%; /* Футер всегда будет занимать всю ширину экрана */
}

footer p {
    margin-bottom: 10px;
}
    </style>
</head>
<body>

    <header>
        <div class="logo">
            <a href="index.php">Library Admin</a>
        </div>
        <div class="menu">
            <a href="index.php">Выход</a>
        </div>
    </header>

    <div class="admin-container">
        <h2>Панель администратора</h2>

        <div class="admin-section">
            <!-- Управление пользователями -->
            <div class="admin-card">
                <h3>Управление пользователями</h3>
                <p>Просмотр, добавление, редактирование и удаление пользователей.</p>
                <a href="manage_users.php">Перейти</a>
            </div>

            <!-- Управление книгами -->
            <div class="admin-card">
                <h3>Управление книгами</h3>
                <p>Добавление новых книг, редактирование и удаление книг.</p>
                <a href="manage_books.php">Перейти</a>
            </div>

           
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
