<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); 
    exit;
}

echo "Добре дошли, " . $_SESSION['username'];
?>

<a href="logout.php">Изход</a>
