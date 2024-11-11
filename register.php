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
    $email = $_POST['email'];

 
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

   
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Потребител с това име вече съществува!";
    } else {
   
        $query = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sss', $user, $hashed_password, $email);
        if ($stmt->execute()) {
            echo "Регистрацията беше успешна!";
            header('Location: login.php'); 
        } else {
            echo "Възникна грешка при регистрацията.";
        }
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
    <title>Регистрация</title>
</head>
<body>
    <h2>Регистрация</h2>
    <form method="POST">
        <p>Потребителско име: <input type="text" name="username" required></p>
        <p>Парола: <input type="password" name="password" required></p>
        <p>Електронна поща: <input type="email" name="email"></p>
        <p><input type="submit" value="Регистрация"></p>
    </form>
</body>
</html>
