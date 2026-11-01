<?php
session_start();
include 'vt.php'; // Veritabanı bağlantısını içerir

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // SQL Injection'dan korunmak için prepared statements kullanıyoruz
    $stmt = $baglanti->prepare("SELECT id, username, password, role  FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Parola doğrulaması (şifrelenmişse password_verify kullanılır)
        if ($password == $user['password']) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Role göre yönlendirme
            if ($_SESSION["role"] == "admin") {
                $_SESSION['admin_role'] = "admin";
                header("Location: anasayfa.php");
            } elseif ($_SESSION["role"] == "superadmin") {
                $_SESSION['superadmin_role'] = "superadmin";
                header("Location: admin_anasayfa.php");
            }
            exit();
        } else {
            echo "Yanlış kullanıcı adı veya şifre.";
        }
    } else {
        echo "Kullanıcı bulunamadı.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Dedem Rehber</title>

    <style>
     
     .centered-div {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
} 
.center {
    display: block;
    margin-left: auto;
    height: auto; 
    margin-right: auto;
  	max-width: 100%;
    }

        form{
            width: 300px;
            margin: 0 auto;
            text-align: center;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        div {
            border: 0cap;
            
            width:100%;
            height: 100%;
        }
        header {
            width: 100%;
            height: 250px;
        }
        img {
            width: 300px;
            max-height: 150px;
            margin-top: 20px;
            margin-left: 850px;
        }
        input{
            margin-top: 1px;
        }
        input[name="username"] {
            margin-top: 40px;
            width:55%;
            height: 40px;
            border-radius: 20px;
            border-style: groove;
            background-size: 30px;
            background-repeat: no-repeat;
            background-position: left;
            border:0cap;
            padding-left: 40px;
            border: 1px solid #ddd;
        }
        input[name="password"] {
            margin-top: 15px;
            width:55%;
            height: 40px;
            border-radius: 20px;
            border-style: groove;
            border:0cap;
            padding-left: 40px;
            background-size: 30px;
            background-repeat: no-repeat;
            background-position: left;
            border: 1px solid #ddd;
        }
        button[type="submit"]{
            margin-top: 10px;
            width: 80%;
            border-radius: 20px;
            border-style: groove;
            height: 40px;
            background-color: black;
            border:0cap;
        }
        select[name="role"]{
            height:40px ;
            border-radius: 20px;
            border-style: groove;
            border:0cap;
            margin-top: 10px;
            border: 1px solid #ddd;
        }  
        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://i.ibb.co/VxgrhWT/1.png'); 
            background-size: cover; 
            background-position: center; 
            background-repeat: no-repeat; 
            opacity: 0.3;
            z-index: -1; 
        }
        .container {
        width: 350px; /* Örneğin container genişliğini ayarlayabilirsiniz */
        margin: 0 auto; /* Ortalamak için */
        background-color: #ffffff; /* Container arka plan rengi */
        padding: 20px; /* İçeriği container'dan uzaklaştırmak için */
        border-radius: 10px; /* Kenar yumuşatma */
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Hafif gölge efekti */
        border: 1px solid #ddd;
        width: 375px;
        height:450px;
       
    }
    .p1 {
  font-family: "Times New Roman", Times, serif;
  font-weight:bold;
  font-size:20px;
  
}
    </style>
</head>
<body>
    <div class="background"></div>
    <div>
        <header>

        </header>
        <section>
        
        
        
    <form action="login.php" method="post">

        <div class="container centered-div">

            <img src="https://i.ibb.co/fD4Sjw2/dede-m.png" alt="dedem" class="center"><br>
            <p class="p1">REHBER GİRİŞ </p>
        <input type="text" id="username" name="username" placeholder="Kullanıcı Adı" required><br>
         <input type="password" id="password" name="password" placeholder="Şifre" required><br>
      <br>
        <button type="submit" style="color: AntiqueWhite;" > Giriş Yap</button>

     
    </form>
    </section>
    </div>
</body>
</html>