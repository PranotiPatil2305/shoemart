<?php
include('db.php');

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] === 'admin') {
    header("Location: admin_home.php");
    exit;
} elseif ($_SESSION['role'] === 'user') {
    header("Location: user_home.php");
    exit;
} else {
    echo "Invalid role.";
}
?>
