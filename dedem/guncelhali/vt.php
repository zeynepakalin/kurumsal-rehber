<?php // bu sayfa projenin veritabanı bağlantısını sağlıyor diğer kodların başına linkliyoruz.

$servername = getenv("DB_HOST") ?: "localhost";
$username = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASSWORD") ?: "";
$dbname = getenv("DB_NAME") ?: "proje";
$baglanti = new mysqli($servername, $username, $password, $dbname);

    if(mysqli_connect_error()) //Eğer hata varsa yazdırıyoruz 
    {
        echo mysqli_connect_error();
        exit; //eğer bağlantıda hata varsa PHP çalışmasını sonlandırıyoruz.
    }

    $baglanti->set_charset("utf8");
?>