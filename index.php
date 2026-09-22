<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

// Фильтрация на стороне сервера
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$sql = "SELECT r.id, r.room_number, r.image, c.name as category_name, c.price, c.description 
        FROM rooms r 
        JOIN room_categories c ON r.category_id = c.id";

if ($category_filter > 0) {
    $sql .= " WHERE c.id = :cat_id";
}
$sql .= " ORDER BY c.price ASC";

$stmt = $pdo->prepare($sql);
if ($category_filter > 0) {
    $stmt->execute(['cat_id' => $category_filter]);
} else {
    $stmt->execute();
}
$rooms = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM room_categories")->fetchAll();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">
    <title>Светлые Сны - Каталог</title>
</head>
<body class="container">
<header class="d-flex flex-wrap justify-content-center py-3">
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
        <span class="fs-4 mx-2 fw-medium">Светлые Сны</span>
    </a>
    <ul class="nav">
        <?php if (isAdmin()): ?>
            <li class="nav-item"><a href="admin.php" class="nav-link">Панель админа</a></li>
        <?php endif; ?>
        <?php if (isLoggedIn()): ?>
            <li class="nav-item"><a href="logout.php" class="nav-link">Выйти (<?= $_SESSION['username'] ?>)</a></li>
        <?php else: ?>
            <li class="nav-item"><a href="login.php" class="nav-link">Войти</a></li>
        <?php endif; ?>
        <li class="nav-item"><a href="#" class="nav-link">Приезжайте как гости, уезжайте как друзья!</a></li>
    </ul>
</header>

<?php displayMessage(); ?>

<main>
    <div class="d-flex justify-content-between flex-wrap">
        <h1>Каталог номеров</h1>
        <form method="GET" action="index.php" class="d-flex align-items-center">
            <div class="dropdown me-2">
                <select name="category" class="form-select">
                    <option value="0">Все категории</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>>
                            <?= sanitize($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Применить</button>
            <a href="index.php" class="btn btn-danger ms-2">Сбросить</a>
        </form>
    </div>

    <div class="d-flex justify-content-around flex-wrap align-items-center">
        <?php if (empty($rooms)): ?>
            <p>Нет доступных номеров по вашему запросу.</p>
        <?php else: ?>
            <?php foreach ($rooms as $room): ?>
                <div class="card">
                    <img src="<?= sanitize($room['image']) ?>" class="card-img-top" alt="<?= sanitize($room['category_name']) ?>">
                    <div class="card-body">
                        <h3>Категория: <?= sanitize($room['category_name']) ?></h3>
                        <h5>Номер: <?= sanitize($room['room_number']) ?></h5>
                        <h5>Цена: <?= number_format($room['price'], 0, '.', ' ') ?> ₽ / сутки</h5>
                        <h5>Характеристики:</h5>
                        <ul class="list-group">
                            <li class="list-group-item"><?= sanitize($room['description']) ?></li>
                        </ul>
                    </div>
                    <div class="d-grid gap-2">
                        <?php if (isLoggedIn()): ?>
                            <a href="order.php?room_id=<?= $room['id'] ?>" class="btn btn-success">Забронировать</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-success">Войдите для бронирования</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<footer class="py-2 my-2">
    <ul class="nav justify-content-between align-items-center">
        <li class="nav-item"><a href="#" class="nav-link text-body-secondary">ул. г.Москва, ул. Ивовая, 48</a></li>
        <li class="nav-item"><a href="#" class="nav-link text-body-secondary">Время работы: Пн-Пт, с 8:00-17:00</a></li>
        <li class="nav-item"><a href="tel:88005553535" class="nav-link text-body-secondary">тел. 8 (800) 555 - 35 - 35</a></li>
        <li class="nav-item"><a href="mailto:обращения@СветлыеСны.рф" class="nav-link text-body-secondary">Email: обращения@СветлыеСны.рф</a></li>
    </ul>
</footer>
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
