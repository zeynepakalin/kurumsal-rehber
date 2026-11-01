<?php  //bu sayfa ekle butonun veritabanı bağlantısı işlevini görüyor.
session_start();
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] != "admin") {
    header("Location: login.php");
    exit();
}
include("vt.php"); // veritabanı bağlantımızı sayfamıza ekliyoruz. 

// Departman ve ünvan önerileri (tüm şirketlerden)
$departmanOnerileri = $baglanti->query("
    SELECT DISTINCT depart FROM (
        SELECT depart FROM iletisim
        UNION
        SELECT depart FROM iletisim_decons
        UNION
        SELECT depart FROM iletisim_temcon
    ) AS tum_departmanlar
    WHERE depart IS NOT NULL AND depart <> ''
    ORDER BY depart
");

$unvanOnerileri = $baglanti->query("
    SELECT DISTINCT unvan FROM (
        SELECT unvan FROM iletisim
        UNION
        SELECT unvan FROM iletisim_decons
        UNION
        SELECT unvan FROM iletisim_temcon
    ) AS tum_unvanlar
    WHERE unvan IS NOT NULL AND unvan <> ''
    ORDER BY unvan
");
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Veritabanı İşlemleri</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
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
        position: relative;
        z-index: 1;
    }
    .side-logo {
        max-width: 100%;
        height: auto;
        margin-top: 20px;
    }
    #sirketLogoImg.side-logo {
        object-fit: contain;
        display: inline-block;
    }
    #sirketLogoImg.logo-sirket-dedem {
        max-height: 200px;
        max-width: 100%;
    }
    #sirketLogoImg.logo-sirket-decons {
        max-height: 170px;
        max-width: 100%;
    }
    #sirketLogoImg.logo-sirket-temcon {
        max-height: 210px;
        max-width: 100%;
    }
    .btn.btn-primary {
        background-color: ForestGreen;
        border-color: #1b5e20;
        color: #fff;
    }
    .btn.btn-primary:hover,
    .btn.btn-primary:focus {
        background-color: #2e7d32;
        border-color: #1b5e20;
        color: #fff;
    }
</style>
</head>
<body>
<div class="background"></div>
<div class="container">
<div class="row">
<div class="col-md-6">
<form action="" method="post">
    <table cellspacing="0" class="table">
        <tr>
            <td>Şirket</td>
            <td>
                <select name="sirket" class="form-control">
                    <option value="dedem" selected>Dedem</option>
                    <option value="decons">Decons</option>
                    <option value="temcon">Temcon</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Ad Soyad</td>
            <td><input type="text" name="adsoy" class="form-control" ></td>
        </tr>
        <br>
        <tr>
            <td>Departman</td>
            <td>
                <input type="text" name="depart" class="form-control" list="departmanListesi">
                <datalist id="departmanListesi">
                    <?php
                    if ($departmanOnerileri) {
                        while ($row = $departmanOnerileri->fetch_assoc()) {
                            ?>
                            <option value="<?php echo htmlspecialchars($row['depart']); ?>"></option>
                            <?php
                        }
                    }
                    ?>
                </datalist>
            </td>
        </tr>

        <br>
        <tr>
            <td>Ünvan</td>
            <td>
                <input type="text" name="unvan" class="form-control" list="unvanListesi">
                <datalist id="unvanListesi">
                    <?php
                    if ($unvanOnerileri) {
                        while ($row = $unvanOnerileri->fetch_assoc()) {
                            ?>
                            <option value="<?php echo htmlspecialchars($row['unvan']); ?>"></option>
                            <?php
                        }
                    }
                    ?>
                </datalist>
            </td>
        </tr>
        <br>
        <tr>
            <td>Email</td>
            <td><input type="text" name="email" class="form-control" ></td>
        </tr>
        <br>
        <tr>
            <td>Telefon</td>
            <td><input type="text" name="telefon" class="form-control" ></td>
        </tr>
        <br>
        <tr>
            <td>Kısa Kod</td>
            <td><input type="text" name="kisakod" class="form-control" ></td>
        </tr>
        <br>
        <tr>
            <td></td>
            <td><input class="btn btn-primary"  type="submit" value="Ekle"></td>
        </tr>
    </table>
</form>
<?php 
if ($_SERVER["REQUEST_METHOD"] == "POST") {// Sayfada post olup olmadığını kontrol ediyoruz.
    $adsoy = ucwords(strtolower($_POST['adsoy']));// Sayfa yenilendikten sonra post edilen değerleri değişkenlere atıyoruz
    $depart = $_POST['depart'];
    $unvan = ucwords(strtolower($_POST['unvan']));
    $email = strtolower(trim($_POST['email'] ?? ''));
    $telefon = trim($_POST['telefon'] ?? '');
    $kisakodInt = trim($_POST['kisakod'] ?? '') === '' ? 0 : (int) $_POST['kisakod'];
    
    // Şirket bilgisini al (zorunlu ve kontrollü)
    $sirket = isset($_POST['sirket']) ? strtolower(trim($_POST['sirket'])) : '';
    
    // Şirkete göre tablo adını belirle
    switch($sirket) {
        case 'decons':
            $tablo = 'iletisim_decons';
            break;
        case 'temcon':
            $tablo = 'iletisim_temcon';
            break;
        case 'dedem':
            $tablo = 'iletisim';
            break;
        default:
            $tablo = '';
            break;
    }
                                                                         

    if ($tablo === '') {
        echo "Geçersiz şirket seçimi.";
    } elseif ($adsoy<>"" && $depart<>"" && $unvan<>"") {
        // E-posta, telefon ve kısa kod isteğe bağlıdır.
        $adsoy = $baglanti->real_escape_string($adsoy);
        $depart = $baglanti->real_escape_string($depart);
        $unvan = $baglanti->real_escape_string($unvan);
        $email = $baglanti->real_escape_string($email);
        $telefon = $baglanti->real_escape_string($telefon);
        
        if ($baglanti->query("INSERT INTO $tablo (adsoy, depart, unvan, email, telefon, kisakod) VALUES ('$adsoy','$depart','$unvan','$email','$telefon',$kisakodInt)")) {
           echo "<script>window.location.href='anasayfa.php';</script>";
            exit();
        } else {
            echo "Veri eklenirken hata oluştu: " . $baglanti->error;
        }
    } else {
        echo "Lütfen isim soyisim, departman ve ünvan alanlarını doldurun.";
    }
}
?>
</div>
<div class="col-md-6 text-center">
    <img id="sirketLogoImg" class="side-logo logo-sirket-dedem" src="https://i.ibb.co/fD4Sjw2/dede-m.png" alt="Dedem Mekatronik">
</div>
</div>

<script>
var formSirketLogolar = {
    dedem: { src: 'https://i.ibb.co/fD4Sjw2/dede-m.png', alt: 'Dedem Mekatronik', cls: 'side-logo logo-sirket-dedem' },
    decons: { src: 'images/decons_logo.png', alt: 'Decons', cls: 'side-logo logo-sirket-decons' },
    temcon: { src: 'images/temcon_logo.png', alt: 'Temcon', cls: 'side-logo logo-sirket-temcon' }
};
function guncelleFormLogosu() {
    var sel = document.querySelector('select[name="sirket"]');
    if (!sel) return;
    var tip = sel.value || 'dedem';
    var m = formSirketLogolar[tip] || formSirketLogolar.dedem;
    var img = document.getElementById('sirketLogoImg');
    if (!img) return;
    img.src = m.src;
    img.alt = m.alt;
    img.className = m.cls;
}
document.addEventListener('DOMContentLoaded', function () {
    var sel = document.querySelector('select[name="sirket"]');
    if (sel) {
        sel.addEventListener('change', guncelleFormLogosu);
    }
});
</script>

</body>
</html>