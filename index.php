<?php
session_start();

$servername = 'localhost'; 
$username = 'root';       
$password = '';          
$dbname = 'urls';          
$base_url = 'http://localhost/URLshortener/'; 

$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Неуспешно свързване: " . $mysqli->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    echo "<a href='login.php'>Вход</a> | <a href='register.php'>Регистрация</a>";
    exit;
}

if (isset($_GET['url']) && $_GET['url'] != "") {
    $url = urldecode($_GET['url']);
    
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            die("Неуспешно свързване: " . $conn->connect_error);
        }

        $slug = GetShortUrl($url);
        $conn->close();

        echo "Вашият кратък URL е: <br><a href='" . $base_url . $slug . "?redirect=" . $slug . "'>" . $base_url . $slug . "</a><br>";
         generateQRCode($base_url . $slug);
        
    } else {
        die("$url - това не е валиден URL");
    }
} else { ?>
    <center>
    <h1>Въведете вашия URL тук</h1>
    <form method="get" action="">
        <p><input style="width:500px" type="url" name="url" required /></p>
        <p><input type="submit" value="Съкратете URL" /></p>
    </form>
    </center>
<?php }

function GetShortUrl($url) {
    global $conn;
    $query = "SELECT * FROM url_shorten WHERE url = '".$url."'"; 
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['short_code'];
    } else {
        $short_code = generateUniqueID();
        $sql = "INSERT INTO url_shorten (url, short_code, hits) VALUES ('".$url."', '".$short_code."', '0')";
        
        if ($conn->query($sql) === TRUE) {
            return $short_code;
        } else { 
            die("Възникна грешка");
        }
    }
}

function generateUniqueID() {
    global $conn; 
    $token = substr(md5(uniqid(rand(), true)), 0, 6); 
    $query = "SELECT * FROM url_shorten WHERE short_code = '".$token."' ";
    $result = $conn->query($query); 
    
    if ($result->num_rows > 0) {
        return generateUniqueID();
    } else {
        return $token;
    }
}

function generateQRCode($url) {
    $qrCodeFile = 'qrcodes/' . md5($url) . '.png';
    
    require_once('phpqrcode/qrlib.php');
    QRcode::png($url, $qrCodeFile, 'L', 4, 4); 

    echo "Сканирайте QR кода за бърз достъп до URL:<br><img src='".$qrCodeFile."' alt='QR код' />";
}

if (isset($_GET['redirect']) && $_GET['redirect'] != "") {
    $slug = urldecode($_GET['redirect']);
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Неуспешно свързване: " . $conn->connect_error);
    }

    $url = GetRedirectUrl($slug);
    $conn->close();

    header("Location: " . $url);
    exit;
}

function GetRedirectUrl($slug) {
    global $conn;
    $query = "SELECT * FROM url_shorten WHERE short_code = '" . addslashes($slug) . "'"; 
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hits = $row['hits'] + 1;
        $sql = "UPDATE url_shorten SET hits='" . $hits . "' WHERE id='" . $row['id'] . "'";
        $conn->query($sql);
        return $row['url']; 
    } else { 
        die("Невалиден линк!");
    }
}
?>
