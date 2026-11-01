<?php  //html sayfasının kişi ekleme , silme ve düzenleme yetkisi olan admin sayfası, login.php sayfası kontrolü sonrasında bu sayfaya geçiş sağlanıyor
include("vt.php"); // veritabanı bağlantımızı sayfamıza ekliyoruz. 
?>
<?php

// admin_anasayfa.php
session_start();
if (!isset($_SESSION['superadmin_role']) || $_SESSION['superadmin_role'] != "superadmin") {
    header("Location: login.php");
    exit();
}
?>
<?php
// Veritabanı bağlantısı
$servername = getenv("DB_HOST") ?: "localhost";
$username = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASSWORD") ?: "";
$dbname = getenv("DB_NAME") ?: "proje";

// Bağlantıyı oluştur
$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantıyı kontrol et
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// UTF-8 karakter setini ayarla
$conn->set_charset("utf8");

// Veritabanından kişi sayısını al
$person_query = "SELECT COUNT(*) as person_count FROM iletisim "; // 'kisi' tablosu kişi verilerini içerdiğini varsayıyorum
$person_result = mysqli_query($conn, $person_query);
$person_count = mysqli_fetch_assoc($person_result)['person_count'];

// Veritabanından kullanıcı sayısını al
$user_query = "SELECT COUNT(*) as user_count FROM users"; // 'users' tablosu kullanıcı verilerini içerdiğini varsayıyorum
$user_result = mysqli_query($conn, $user_query);
$user_count = mysqli_fetch_assoc($user_result)['user_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Anasayfa</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2>Menü</h2>
            </div>
            <ul class="sidebar-menu">
            <li><a href="admin_anasayfa.php">Anasayfa</a></li>
                              
                <li><a href="admin_kullanicilar.php">Kullanıcılar</a></li>
            </ul>
        </nav>
        <div class="main-content">
            <header class="topbar">
                <h1>Anasayfa</h1>
                <div class="user-info">
                    <p>Hoşgeldin, <?php echo $_SESSION['username']; ?>!</p>
                    <a href="logout.php">Logout</a>
                </div>
            </header>
            <div class="content">
             
                <div class="cards">
                    <div class="card">
                        <h3>Kişi Listesi</h3>
                        <p>Toplam Kişi Sayısı: <?php echo $person_count; ?></p>
                    </div>
                    <div class="card">
                        <h3>Kullanıcılar</h3>
                        <p>Toplam Kullanıcı Sayısı: <?php echo $user_count; ?></p>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
