<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

if (!isAdmin()) {
    redirect('index.php');
}

// Обработка действий администратора
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int)$_POST['booking_id'];
    $action = $_POST['action'];

    if ($booking_id > 0 && in_array($action, ['approve', 'delete'])) {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'approved' WHERE id = ?");
            $stmt->execute([$booking_id]);
            $_SESSION['message'] = 'Заявка одобрена.';
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$booking_id]);
            $_SESSION['message'] = 'Заявка удалена.';
        }
        $_SESSION['message_type'] = 'success';
        redirect('admin.php');
    }
}

// Получаем все заявки
$sql = "SELECT b.*, r.room_number, c.name as category_name 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        JOIN room_categories c ON r.category_id = c.id 
        ORDER BY b.created_at DESC";
$bookings = $pdo->query($sql)->fetchAll();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">
    <title>Панель администратора</title>
</head>
<body class="container">
<header class="d-flex flex-wrap justify-content-center py-3">
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
        <span class="fs-4 mx-2 fw-medium">Светлые Сны</span>
    </a>
    <ul class="nav">
        <li class="nav-item"><a href="logout.php" class="nav-link">Выйти</a></li>
        <li class="nav-item"><a href="#" class="nav-link">Приезжайте как гости, уезжайте как друзья!</a></li>
    </ul>
</header>

<main>
    <div class="d-flex justify-content-between flex-wrap align-items-center">
        <h1>Панель администратора</h1>
    </div>
    
    <?php displayMessage(); ?>

    <div class="d-flex justify-content-around flex-wrap align-items-center">
        <?php if (empty($bookings)): ?>
            <p>Нет активных заявок.</p>
        <?php else: ?>
            <?php foreach ($bookings as $b): ?>
                <div class="card">
                    <div class="card-body">
                        <h5>Фамилия: <?= sanitize($b['last_name']) ?></h5>
                        <h5>Имя: <?= sanitize($b['first_name']) ?></h5>
                        <h5>Телефон: <?= sanitize($b['phone']) ?></h5>
                        <h5>Email: <?= sanitize($b['email']) ?></h5>
                        <p>Номер: <?= sanitize($b['category_name']) ?> (№<?= sanitize($b['room_number']) ?>)</p>
                        <ul class="list-group">
                            <li class="list-group-item">Дата заезда: <?= date('d.m.Y', strtotime($b['check_in'])) ?></li>
                            <li class="list-group-item">Дата выезда: <?= date('d.m.Y', strtotime($b['check_out'])) ?></li>
                            <li class="list-group-item">Статус: 
                                <?php 
                                    if ($b['status'] == 'pending') echo '<span class="badge bg-warning text-dark">Ожидает</span>';
                                    elseif ($b['status'] == 'approved') echo '<span class="badge bg-success">Одобрено</span>';
                                    else echo '<span class="badge bg-danger">Отклонено</span>';
                                ?>
                            </li>
                        </ul>
                    </div>
                    <div class="d-grid gap-2">
                        <?php if ($b['status'] == 'pending'): ?>
                            <form method="post" class="d-grid gap-2">
                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-success">Одобрить</button>
                                <button type="submit" name="action" value="delete" class="btn btn-danger">Удалить</button>
                            </form>
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
