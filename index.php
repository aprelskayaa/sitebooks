<?php
require_once 'db_connect.php';
session_start(); // Запуск сессии

// Получаем выбранные значения фильтров
$selected_genre = isset($_GET['genre']) && $_GET['genre'] !== 'all' ? $_GET['genre'] : null;
$selected_year = isset($_GET['year']) && $_GET['year'] !== 'all' ? $_GET['year'] : null;
$selected_availability = isset($_GET['availability']) && $_GET['availability'] !== 'all' ? $_GET['availability'] : null;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : null; // Получаем поисковый запрос

// Основной SQL-запрос на получение книг вместе с жанрами
$sql = "SELECT books.*, genres.genre_name FROM books 
        LEFT JOIN genres ON books.genre_id = genres.id 
        WHERE 1=1";

// Массив для фильтров
$filters = [];

// Фильтр по названию книги (поиск)
if ($search_query) {
    $sql .= " AND books.title LIKE :search";
    $filters[':search'] = '%' . $search_query . '%';
}

// Фильтр по жанру
if ($selected_genre) {
    $sql .= " AND books.genre_id = :genre";
    $filters[':genre'] = $selected_genre;
}

// Фильтр по году издания
if ($selected_year) {
    $sql .= " AND books.published_year = :year";
    $filters[':year'] = $selected_year;
}

// Фильтр по доступности
if ($selected_availability) {
    if ($selected_availability === 'available') {
        $sql .= " AND books.availability_status = 'available'";
    } else {
        $sql .= " AND books.availability_status != 'available'";
    }
}

// Выполняем запрос с применением фильтров
$stmt = $pdo->prepare($sql);
$stmt->execute($filters);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем последние 5 добавленных книг
$sql_recent = "SELECT id, cover_image, title FROM books ORDER BY id DESC LIMIT 5";
$stmt_recent = $pdo->prepare($sql_recent);
$stmt_recent->execute();
$recent_books = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

// Получаем все жанры из базы данных для фильтрации
$sql = "SELECT id, genre_name FROM genres";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$genres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library</title>
    <link rel="stylesheet" href="index-styles.css">
</head>

<style>

html::-webkit-scrollbar {
    display: none;
}

/* Скрываем ползунки в Firefox */
html {
    scrollbar-width: none; /* Firefox */
}

/* Скрываем ползунки в IE и Edge */
html, body {
    -ms-overflow-style: none;  /* IE и Edge */
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    padding-top: 50px;
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;  
}

@media (max-width: 768px) {
    body{
        padding-top: 0px;

    }
    
}

header {
    position: fixed; /* Закрепляем хедер */
    top: 0; /* Позиционируем его в самом верху */
    left: 0; /* Привязываем к левому краю */
    width: 100%; /* Растягиваем на всю ширину экрана */
    z-index: 1000; /* Устанавливаем высокий z-index, чтобы хедер был поверх другого контента */
    
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

.search-bar {
    position: relative; /* Для позиционирования иконки */
}

.search-bar input {
    padding: 12px 40px 12px 20px; /* Увеличиваем отступы: слева больше места для текста, справа - для иконки */
    width: 500px;
    border: none;
    border-radius: 25px; /* Более закругленные углы */
    background-color: #f0f0f0; /* Светло-серый фон */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Тень для объема */
    transition: background-color 0.3s, box-shadow 0.3s; /* Плавный переход */
    font-size: 16px; /* Увеличенный шрифт для удобства */
    outline: none; /* Убираем стандартную обводку при фокусе */
}

.search-bar input::placeholder {
    color: #888; /* Цвет для текста-подсказки */
}

/* Эффект при наведении */
.search-bar input:hover {
    background-color: #e0e0e0; /* Темнее при наведении */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Увеличенная тень */
}

/* Эффект при фокусе */
.search-bar input:focus {
    background-color: #fff; /* Светлый фон при фокусе */
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); /* Более сильная тень */
}

/* Иконка поиска */
.search-bar::before {
    content: "\1F50D"; /* Символ иконки лупы */
    position: absolute;
    right: 15px; /* Расположение иконки справа внутри поля */
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    color: #888;
    pointer-events: none; /* Иконка не кликабельна */
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

    .search-bar {
        margin-bottom: 15px; /* Добавим отступ под строкой поиска */
        width: 100%; /* Строка поиска займет всю ширину */
        text-align: center; /* Выровняем содержимое по центру */
        position: relative;
    }

    .search-bar input {
        width: 90%; /* Ограничиваем ширину поля ввода до 90% */
        padding: 12px 40px 12px 20px; /* Отступы: справа для иконки, слева для текста */
        border-radius: 25px; /* Закругленные углы для мобильной версии */
        background-color: #f0f0f0; /* Цвет фона */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Уменьшаем тень на мобильной версии */
        font-size: 16px; /* Немного уменьшенный шрифт */
    }

    .search-bar::before {
        content: "\1F50D"; /* Иконка лупы */
        position: absolute;
        right: 8%; /* Меньший отступ для мобильных устройств */
        top: 50%;
        transform: translateY(-50%);
        font-size: 18px;
        color: #888;
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

/* Контейнер для блока с популярными книгами */
.popular-books-container {
    display: flex; /* Горизонтальное выравнивание книг */
    justify-content: space-around; /* Распределение книг с равными отступами */
    align-items: center; /* Вертикальное выравнивание по центру */
    width: 850px; /* Ширина контейнера */
    height: 300px; /* Высота контейнера */
    position: absolute;
    top: 10%; /* Отступ сверху для позиционирования */
    left: 10%; /* Отступ слева для позиционирования */
    
}

@media (max-width: 768px) {
    .popular-books-container {
        display: none;
    }
}

@media (max-width: 1440px) {
    .popular-books-container {
        width: 700px; /* Ширина контейнера */
        height: 250px; /* Высота контейнера */
        top: 18%; /* Отступ сверху для позиционирования */
        left: 7%; /* Отступ слева для позиционирования */
    }
    
}

@media (max-width: 1024px) {
    .popular-books-container {
        width: 500px; /* Ширина контейнера */
        height: 250px; /* Высота контейнера */
        top: 18%; /* Отступ сверху для позиционирования */
        left: 1.5%; /* Отступ слева для позиционирования */
    }
    
}



/* Стили для карточек книг */
.popular-book {
    text-align: center;
}

.popular-book img {
    width: 150px; /* Ширина изображения книги */
    height: 250px; /* Высота изображения книги */
   
    object-fit: cover;
    border-radius: 8px;
}

@media (max-width: 1440px) {
    .popular-book img {
        width: 130px; /* Ширина изображения книги */
        height: 180px; /* Высота изображения книги */

    }
    
}

@media (max-width: 1024px) {
    .popular-book img {
        width: 90px; /* Ширина изображения книги */
        height: 130px; /* Высота изображения книги */

    }
    
}


.popular-book p {
    margin-top: 10px;
    font-size: 16px;
    color: #333;
}

/* Стилизуем текст "Книги месяца" */
.books-of-the-month {
    position: absolute;
    top: 300px; /* Расположим текст под контейнером с книгами */
    font-size: 24px;
    font-weight: bold;
    color: #000000;
}

@media (max-width: 1440px) {
    .books-of-the-month {
        top: 250px;

    }
    
}

@media (max-width: 1440px) {
    .books-of-the-month {
        top: 220px;
    }
    
}

.hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #e8e8e8;
    padding: 20px;
    background-image: url('Bg_head.png');
    background-size: 100% 500px; /* Растягивает изображение на всю ширину и фиксированную высоту */
    background-position: center;
    background-repeat: no-repeat;
    height: 409px; /* Фиксированная высота секции */
}

@media (max-width: 1024px) {
    .hero {
        background-image: url('Bg_head_1024.png');
        height: 400px; /* Фиксированная высота секции */
        background-size: 100% 400px; /* Растягивает изображение на всю ширину и фиксированную высоту */
        
    }
    
}

@media (max-width: 768px) {
    .hero {
        background-image: url('Bg_head_768.png');
        height: 385px; /* Фиксированная высота секции */
        background-size: 100% 385px; /* Растягивает изображение на всю ширину и фиксированную высоту */
        
    }
    
}

@media (max-width: 425px) {
    .hero {
        background-image: url('Bg_head_425.png');
        height: 300px; /* Фиксированная высота секции */
        background-size: 100% 300px; /* Растягивает изображение на всю ширину и фиксированную высоту */
    }
    
}

@media (max-width: 768px) {
    .best-books {
        display: none;
    }
}

.best-books img {
    height: 150px;
    margin-right: 10px;
}


.book-list {
    padding: 20px;
    margin-left: 70px;

}

@media (max-width: 768px) {
    .book-list { margin-left: 0px;
        
    }

}

.book-list h2 {
    font-size: 28px;
    margin-bottom: 30px;

}

.filter-form {
    display: flex;
    flex-direction: column;
}

.filter-form label {
    margin-bottom: 10px;
    font-weight: bold;
}

.filter-form select, 
.filter-form button {
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    width: 100%;
}

.filter-form button {
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.filter-form button:hover {
    background-color: #0056b3;
}

.book-item {
    display: flex;
    background-color: white;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    max-width: 1400px; /* Ограничение максимальной ширины блока */
    max-height: 420px; /* Ограничение максимальной высоты блока */
}




.book-image-container {
    width: 192px; /* Ограничение ширины контейнера */
    height: 288px; /* Ограничение высоты контейнера */
    overflow: hidden; /* Скрываем изображение, выходящее за пределы контейнера */
    margin-right: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #ccc; /* Можно добавить рамку для контейнера */
    border-radius: 15px;
}

@media (max-width: 768px) {
    .book-image-container{
        width: 166px; /* Ограничение ширины контейнера */
        height: 250px; /* Ограничение высоты контейнера */
    }
}

@media (max-width: 425px) {
    .book-image-container{
        width: 120px; /* Ограничение ширины контейнера */
        height: 180px; /* Ограничение высоты контейнера */
    }
}

.book-image-container img {
    
    max-width: 100%;
    max-height: 100%;
    object-fit: cover; /* Изображение подгоняется под контейнер */
}


.book-info {
    flex: 1;
    overflow: hidden;
    word-wrap: break-word; /* Перенос слов, если они слишком длинные */
    overflow-wrap: break-word; /* Современная альтернатива для переноса */
    overflow: hidden; /* Обрезает текст, если он выходит за пределы */
    text-overflow: ellipsis; /* Добавляет троеточие в конце обрезанного текста */
}

.book-info h3 {
    font-size: 24px;
    margin-bottom: 10px;
}

@media (max-width: 768px) {
    .book-info h3{
        font-size: 18px;
    }
}

.book-info p {
    font-size: 20px;
    margin-bottom: 8px;
    color: #555;
}

@media (max-width: 768px) {
    .book-info p{
        font-size: 14px;
    }
}
.book-info a {
    color: #007bff;
    text-decoration: none;
}

.book-info a:hover {
    text-decoration: underline;
}



footer {
    background-color: #444;
    color: white;
    padding: 20px;
    text-align: center;
}

footer p {
    margin-bottom: 10px;
}

/* Контейнер для двух колонок */
.page-container {
    display: grid;
    grid-template-columns: 1fr 330px; /* Левая колонка 1fr, правая фиксированная 250px */
    gap: 20px; /* Отступ между колонками */
    padding: 20px;
}

@media (max-width: 768px) {
    .page-container{
        padding: 0px;
        grid-template-columns: 1fr;
    }
}

/* Фильтры справа */
.right-column {
    
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    max-height: 355px;
    width: 300px; /* Обеспечиваем фиксированную ширину для правой колонки */
    position: relative;
    margin-top:102px;
}
@media (max-width: 768px) {
    .right-column{
        display: none;
    }
}


.right-column h3 {
    margin-bottom: 15px;
}

.right-column select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
    border: 1px solid #ccc;
}

@media (max-width: 425px) {
    .right-column {
        display: none; /* Скрыть фильтры на мобильных устройствах */
    }
}

</style>

<body>
<header>
    <div class="logo">
        <a href="index.php">Library</a>
    </div>
    <div class="search-bar">
        <!-- Форма поиска с фильтрами -->
        <form method="GET" action="index.php">
            <input type="text" name="search" placeholder="Введите название книги..." value="<?php echo htmlspecialchars($search_query); ?>">
            
        </form>
    </div>
    <div class="menu">
        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
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

<!-- Слоган и лучшие книги месяца -->
<section class="hero">
    <div class="popular-books-container">
        <?php foreach ($recent_books as $book): ?>
            <div class="popular-book">
                <a href="about.php?id=<?php echo htmlspecialchars($book['id']); ?>">
                    <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                </a>
            </div>
        <?php endforeach; ?>
        <p class="books-of-the-month">Новинки</p>
    </div>
</section>

<!-- Список книг -->
<div class="page-container">
    <div class="left-column">
        <div id="catalog" class="book-list">
            <h2>Все книги</h2>
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $book): ?>
                    <div class="book-item">
                        <div class="book-image-container">
                            <a href="about.php?id=<?php echo $book['id']; ?>">
                                <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Обложка книги">
                            </a>
                        </div>
                        <div class="book-info">
                            <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p><strong>Автор:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
                            <p><strong>Год издания:</strong> <?php echo htmlspecialchars($book['published_year']); ?></p>
                            <p><strong>Жанр:</strong> <?php echo htmlspecialchars($book['genre_name']); ?></p>
                            <p><strong>Бронь:</strong> <?php echo $book['availability_status'] === 'available' ? 'Книга доступна для бронирования' : 'Книга уже занята'; ?></p>
                            <p><?php echo mb_strimwidth(htmlspecialchars($book['description']), 0, 535, "..."); ?></p>
                            <a href="about.php?id=<?php echo $book['id']; ?>">Подробнее</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Нет книг, соответствующих запросу.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Правая колонка с фильтрами -->
    <div class="right-column">
        <h3>Фильтры</h3>
        <form method="GET" action="index.php">
            <label for="genre">Жанры</label>
            <select name="genre" id="genre">
                <option value="all">Все жанры</option>
                <?php foreach ($genres as $genre): ?>
                    <option value="<?php echo $genre['id']; ?>" <?php if (isset($_GET['genre']) && $_GET['genre'] == $genre['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($genre['genre_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="year">Год издания</label>
            <select name="year" id="year">
                <option value="all">Все года</option>
                <!-- Автоматически извлекаем года из таблицы books -->
                <?php 
                $sql_years = "SELECT DISTINCT published_year FROM books ORDER BY published_year DESC";
                $stmt_years = $pdo->prepare($sql_years);
                $stmt_years->execute();
                $years = $stmt_years->fetchAll(PDO::FETCH_ASSOC);
                foreach ($years as $year): ?>
                    <option value="<?php echo $year['published_year']; ?>" <?php if (isset($_GET['year']) && $_GET['year'] == $year['published_year']) echo 'selected'; ?>>
                        <?php echo $year['published_year']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Наличие</label>
            <select name="availability">
                <option value="all" <?php if (!isset($_GET['availability']) || $_GET['availability'] === 'all') echo 'selected'; ?>>Все</option>
                <option value="available" <?php if (isset($_GET['availability']) && $_GET['availability'] === 'available') echo 'selected'; ?>>Доступно</option>
                <option value="unavailable" <?php if (isset($_GET['availability']) && $_GET['availability'] === 'unavailable') echo 'selected'; ?>>Занято</option>
            </select>

            <button type="submit">Применить фильтры</button>
        </form>
    </div>
</div>

<!-- Нижняя панель -->
<footer>
    <div class="footer-content">
        <p>Служба поддержки: 8 800 777 77 77 | support@library.ru</p>
        <p>Адрес: Улица Книжная, 12, город Знаний, 123456, Россия</p>
    </div>
</footer>
</body>
</html>