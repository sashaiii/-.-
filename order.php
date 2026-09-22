<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
if ($room_id <= 0) {
    redirect('index.php');
}

// Получаем информацию о номере
$stmt = $pdo->prepare("SELECT r.*, c.name as cat_name, c.price FROM rooms r JOIN room_categories c ON r.category_id = c.id WHERE r.id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    $_SESSION['message'] = 'Номер не найден.';
    $_SESSION['message_type'] = 'danger';
    redirect('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $phone = sanitize($_POST['phone']);
    $email = sanitize($_POST['email']);
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];

    // Валидация на стороне сервера
    if (empty($first_name)) $errors[] = 'Введите имя.';
    if (empty($last_name)) $errors[] = 'Введите фамилию.';
    if (empty($phone)) $errors[] = 'Введите телефон.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email.';
    
    $today = date('Y-m-d');
    if (empty($check_in) || $check_in < $today) $errors[] = 'Дата заезда не может быть в прошлом.';
    if (empty($check_out) || $check_out <= $check_in) $errors[] = 'Дата выезда должна быть позже даты заезда.';

    if (empty($errors)) {
        $sql = "INSERT INTO bookings (user_id, room_id, first_name, last_name, phone, email, check_in, check_out) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$_SESSION['user_id'], $room_id, $first_name, $last_name, $phone, $email, $check_in, $check_out])) {
            $_SESSION['message'] = 'Заявка успешно отправлена! Ожидайте подтверждения.';
            $_SESSION['message_type'] = 'success';
            redirect('index.php');
        } else {
            $errors[] = 'Ошибка при отправке заявки. Попробуйте позже.';
        }
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">
    <title>Бронирование</title>
</head>
<body class="container">
<header class="d-flex flex-wrap justify-content-center py-3">
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
        <span class="fs-4 mx-2 fw-medium">Светлые Сны</span>
    </a>
    <ul class="nav">
        <li class="nav-item"><a href="#" class="nav-link">Приезжайте как гости, уезжайте как друзья!</a></li>
    </ul>
</header>

<main>
    <div class="d-flex justify-content-between flex-wrap align-items-center">
        <h1>Бронирование номера "<?= sanitize($room['cat_name']) ?>" (№<?= sanitize($room['room_number']) ?>)</h1>
    </div>
    <p>Цена: <?= number_format($room['price'], 0, '.', ' ') ?> ₽ / сутки</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="row g-3 needs-validation my-2" method="post">
        <div class="col-md-4">
            <label for="first_name" class="form-label">Имя</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= isset($_POST['first_name']) ? sanitize($_POST['first_name']) : '' ?>" required>
        </div>
        <div class="col-md-4">
            <label for="last_name" class="form-label">Фамилия</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= isset($_POST['last_name']) ? sanitize($_POST['last_name']) : '' ?>" required>
        </div>
        <div class="col-md-4">
            <label for="phone" class="form-label">Телефон</label>
            <input type="text" class="form-control" id="phone" name="phone" value="<?= isset($_POST['phone']) ? sanitize($_POST['phone']) : '' ?>" required>
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">Почта</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= isset($_POST['email']) ? sanitize($_POST['email']) : '' ?>" required>
        </div>
        <div class="col-md-3">
            <label for="check_in" class="form-label">Дата заезда</label>
            <input type="date" class="form-control" id="check_in" name="check_in" value="<?= isset($_POST['check_in']) ? sanitize($_POST['check_in']) : '' ?>" required>
        </div>
        <div class="col-md-3">
            <label for="check_out" class="form-label">Дата выезда</label>
            <input type="date" class="form-control" id="check_out" name="check_out" value="<?= isset($_POST['check_out']) ? sanitize($_POST['check_out']) : '' ?>" required>
        </div>
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Отправить заявку</button>
        </div>
    </form>
</main>

<footer class="py-2 my-2">
    <ul class="nav justify-content-between align-items-center">
        <li class="nav-item"><a href="#" class="nav-link text-body-secondary">ул. г.Москва, ул. Ивовая, 48</a></li>
        <li class="nav-item"><a href="#" class="nav-link text-body-secondary">Время работы: Пн-Пт, с 8:00-17:00</a></li>
        <li class="nav-item"><a href="tel:88005553535" class="nav-link text-body-secondary">тел. 8 (800) 555 - 35 - 35</a></li>
        <li class="nav-item"><a href="mailto:обращения@СветлыеСны.рф" class="nav-link text-body-secondary">Email: обращения@СветлыеСны.рф</a></li>
    </ul>
</footer>
<script src="js/jquery-3.7.1.slim.min.js"></script>
<script src="js/jquery.inputmask.min.js"></script>
<script>
    $(document).ready(function(){
        $('#phone').inputmask({"mask": "+7(999)999-99-99"});
    });
</script>
</body>
</html>
