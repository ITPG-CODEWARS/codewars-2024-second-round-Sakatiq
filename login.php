<?php
session_start();
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'urls';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Неуспешно свързване: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            echo "Добре дошли, " . $_SESSION['username'];
            header('Location: dashboard.php'); 
        } else {
            echo "Невалидна парола!";
        }
    } else {
        echo "Потребителят не е намерен!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
</head>
<body>
    <h2>Вход</h2>
    <form method="POST">
        <p>Потребителско име: <input type="text" name="username" required></p>
        <p>Парола: <input type="password" name="password" required></p>
        <p><input type="submit" value="Вход"></p>
    </form>
</body>
</html>
