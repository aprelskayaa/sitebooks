<?php
session_start();
require_once 'db_connect.php'; // Подключение к базе данных

// Проверка авторизации пользователя
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php'); // Перенаправляем на страницу входа, если пользователь не авторизован
    exit;
}

$user_id = $_SESSION['user_id'];

// Если данные формы отправлены
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = $_POST['address'];
    $city = $_POST['city']; // Добавляем город

    // Проверка совпадения паролей
    if ($password && $password !== $confirm_password) {
        echo "Пароли не совпадают!";
    } else {
        try {
            // Если введен новый пароль, обновляем его
            if ($password) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username = ?, email = ?, password = ?, address = ?, city = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $email, $hashed_password, $address, $city, $user_id]);
            } else {
                // Обновляем без пароля
                $sql = "UPDATE users SET username = ?, email = ?, address = ?, city = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $email, $address, $city, $user_id]);
            }

             // Перенаправляем на страницу профиля после успешного обновления
             header('Location: prof.php');
        } catch (PDOException $e) {
            echo "Ошибка при обновлении данных: " . $e->getMessage();
        }
    }
}

// Получаем текущие данные пользователя из базы
$sql = "SELECT username, email, address, city FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Пользователь не найден.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование профиля</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        /* Основные стили для страницы */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Минимальная высота экрана */
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
            width: 100%;
        }

        @media (max-width: 768px) {
            header {
                position: static;
            }
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

        .profile-edit-container {
            width: 100%;
            max-width: 500px;
            background-color: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 100px auto; /* Центрирование контейнера по горизонтали */
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }


        h2 {
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            display: block;
            width: 100%;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
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
</head>
<body>

    <header>
        <div class="logo">
            <a href="index.php">Library</a>
        </div>
        <div class="menu">
            <a href="admin.php">Админ</a>
            <a href="prof.php">Профиль</a>
            <a href="index.php#catalog">Каталог</a>
        </div>
    </header>

    <div class="profile-edit-container">
        <h2>Редактировать профиль</h2>

        <!-- Форма редактирования профиля -->
        <form action="prof_redact.php" method="post">
            <!-- Имя пользователя -->
            <div class="form-group">
                <label for="name">Имя</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <!-- Email пользователя -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <!-- Пароль -->
            <div class="form-group">
                <label for="password">Новый пароль</label>
                <input type="password" id="password" name="password" placeholder="Введите новый пароль">
            </div>

            <!-- Подтверждение пароля -->
            <div class="form-group">
                <label for="confirm_password">Подтвердите новый пароль</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Подтвердите новый пароль">
            </div>

            <!-- Адрес -->
            <div class="form-group">
                <label for="address">Адрес</label>
                <textarea id="address" name="address" rows="3" placeholder="Введите ваш адрес"><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>

            <!-- Город -->
            <div class="form-group">
                <label for="city">Город</label>
                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required>
            </div>

            <!-- Кнопка сохранения -->
            <button type="submit">Сохранить изменения</button>

        </form>
    </div>

    <footer>
    <div class="footer-content">
        <p>Служба поддержки: 8 800 777 77 77 | support@library.ru</p>
        <p>Адрес: Улица Книжная, 12, город Знаний, 123456, Россия</p>
    </div>
</footer>

</body>
</html>
