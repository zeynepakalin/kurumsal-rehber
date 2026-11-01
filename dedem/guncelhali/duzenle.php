<?php  //bu sayfa düzenle butonun veritabanı bağlantısı işlevini görüyor.
session_start();
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] != "admin") {
    header("Location: login.php");
    exit();
}
include("vt.php"); // veritabanı bağlantımızı sayfamıza ekliyoruz. 

// Şirkete göre tabloyu belirle
$sirket = isset($_GET['sirket']) ? $_GET['sirket'] : 'dedem';
switch($sirket) {
    case 'decons':
        $tablo = 'iletisim_decons';
        break;
    case 'temcon':
        $tablo = 'iletisim_temcon';
        break;
    case 'dedem':
    default:
        $tablo = 'iletisim';
        $sirket = 'dedem';
        break;
}

$formSirketLogoAyarlari = [
    'dedem'  => ['src' => 'https://i.ibb.co/fD4Sjw2/dede-m.png', 'alt' => 'Dedem Mekatronik', 'cls' => 'side-logo logo-sirket-dedem'],
    'decons' => ['src' => 'images/decons_logo.png', 'alt' => 'Decons', 'cls' => 'side-logo logo-sirket-decons'],
    'temcon' => ['src' => 'images/temcon_logo.png', 'alt' => 'Temcon', 'cls' => 'side-logo logo-sirket-temcon'],
];
$formLogo = $formSirketLogoAyarlari[$sirket] ?? $formSirketLogoAyarlari['dedem'];

// Kişi listesi (autocomplete için)
$kisiListesiSorgu = $baglanti->query("SELECT id, adsoy FROM $tablo ORDER BY adsoy");

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

// Düzenlenecek kayıt
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sonuc = [
    'adsoy'   => '',
    'depart'  => '',
    'unvan'   => '',
    'email'   => '',
    'telefon' => '',
    'kisakod' => ''
];
if ($id > 0) {
    $sorgu = $baglanti->query("SELECT * FROM $tablo WHERE id =".$id); 
    if ($sorgu && $sorgu->num_rows > 0) {
        $sonuc = $sorgu->fetch_assoc();
    }
}

if ($_POST) {
    $adsoy   = $baglanti->real_escape_string($_POST["adsoy"]);
    $depart  = $baglanti->real_escape_string($_POST["depart"]);
    $unvan   = $baglanti->real_escape_string($_POST["unvan"]);
    $email   = $baglanti->real_escape_string(strtolower(trim($_POST["email"] ?? '')));
    $telefon = $baglanti->real_escape_string(trim($_POST["telefon"] ?? ''));
    $kisakodInt = trim($_POST["kisakod"] ?? '') === '' ? 0 : (int) $_POST["kisakod"];

    // Yeni seçilen şirket
    $yeniSirket = isset($_POST['sirket']) ? $_POST['sirket'] : $sirket;
    switch ($yeniSirket) {
        case 'decons':
            $yeniTablo = 'iletisim_decons';
            break;
        case 'temcon':
            $yeniTablo = 'iletisim_temcon';
            break;
        case 'dedem':
        default:
            $yeniTablo = 'iletisim';
            $yeniSirket = 'dedem';
            break;
    }

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id > 0) {
        if ($yeniTablo === $tablo) {
            // Aynı şirkette sadece güncelle
            if ($baglanti->query("UPDATE $tablo SET adsoy='$adsoy', depart='$depart', unvan='$unvan', email='$email', telefon='$telefon', kisakod=$kisakodInt WHERE id=".$id)) {
                header("location:anasayfa.php");
                exit();
            } else {
                echo "Hata oluştu: " . $baglanti->error;
            }
        } else {
            // Farklı şirkete taşınmak isteniyorsa: yeni tabloya ekle, eski tablodan sil
            $insertSql = "INSERT INTO $yeniTablo (adsoy, depart, unvan, email, telefon, kisakod) 
                          VALUES ('$adsoy', '$depart', '$unvan', '$email', '$telefon', $kisakodInt)";
            if ($baglanti->query($insertSql)) {
                $baglanti->query("DELETE FROM $tablo WHERE id=".$id);
                header("location:anasayfa.php");
                exit();
            } else {
                echo "Kayıt taşınırken hata oluştu: " . $baglanti->error;
            }
        }
    } else {
        echo "Geçersiz kayıt.";
    }
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Kayıt Düzenle</title>
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
.btn.btn-default {
    border-color: #43a047;
    color: #2e7d32;
}
.btn.btn-default:hover,
.btn.btn-default:focus {
    background-color: #e8f5e9;
    border-color: #2e7d32;
    color: #1b5e20;
}
</style>
</head>
<body>
<div class="background"></div>
<div class="container" style="margin-top:30px;">
<div class="row">
<div class="col-md-6">

<h3>Kayıt Düzenle 
    <small style="font-size:14px; color:#888;">
        (<?php echo $sirket === 'decons' ? 'Decons' : ($sirket === 'temcon' ? 'Temcon' : 'Dedem Mekatronik'); ?>)
    </small>
</h3>

<!-- Kişi seçimi için autocomplete alanı -->
<div class="form-group">
    <label for="kisiAra">Kişi Seç (<?php echo $sirket === 'decons' ? 'Decons' : ($sirket === 'temcon' ? 'Temcon' : 'Dedem'); ?>)</label>
    <input list="kisiListesi" id="kisiAra" class="form-control" placeholder="İsim yazmaya başlayın, listeden seçebilirsiniz">
    <datalist id="kisiListesi">
        <?php
        if ($kisiListesiSorgu) {
            while ($kisi = $kisiListesiSorgu->fetch_assoc()) {
                ?>
                <option value="<?php echo htmlspecialchars($kisi['adsoy']); ?>" data-id="<?php echo (int)$kisi['id']; ?>"></option>
                <?php
            }
        }
        ?>
    </datalist>
    <button type="button" class="btn btn-default" style="margin-top:10px;" onclick="kisiSec()">Seç</button>
    <input type="hidden" id="aktifSirket" value="<?php echo htmlspecialchars($sirket); ?>">
</div>

<form action="" method="post">
    <table class="table">
        <tr>
            <td>Şirket</td>
            <td>
                <select name="sirket" class="form-control">
                    <option value="dedem" <?php echo $sirket === 'dedem' ? 'selected' : ''; ?>>Dedem</option>
                    <option value="decons" <?php echo $sirket === 'decons' ? 'selected' : ''; ?>>Decons</option>
                    <option value="temcon" <?php echo $sirket === 'temcon' ? 'selected' : ''; ?>>Temcon</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Ad Soyad</td>
            <td><input type="text" name="adsoy" class="form-control" value="<?php echo htmlspecialchars($sonuc['adsoy']); ?>"></td>
        </tr>
        <tr>
            <td>Departman</td>
            <td>
                <input type="text" name="depart" class="form-control" list="departmanListesi" value="<?php echo htmlspecialchars($sonuc['depart']); ?>">
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
        <tr>
            <td>Ünvan</td>
            <td>
                <input type="text" name="unvan" class="form-control" list="unvanListesi" value="<?php echo htmlspecialchars($sonuc['unvan']); ?>">
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
        <tr>
            <td>Email</td>
            <td><input type="text" name="email" class="form-control" value="<?php echo htmlspecialchars($sonuc['email']); ?>"></td>
        </tr>
        <tr>
            <td>Telefon</td>
            <td><input type="text" name="telefon" class="form-control" value="<?php echo htmlspecialchars($sonuc['telefon']); ?>"></td>
        </tr>
        <tr>
            <td>Kısa Kod</td>
            <td><input type="text" name="kisakod" class="form-control" value="<?php
                $kk = $sonuc['kisakod'];
                echo htmlspecialchars(($kk === null || $kk === '' || (int) $kk === 0) ? '' : (string) $kk);
            ?>"></td>
        </tr>
        <tr>
            <td></td>
            <td><input type="submit" class="btn btn-primary" value="Kaydet"></td>
        </tr>
    </table>
</form>

</div>
<div class="col-md-6 text-center">
    <img id="sirketLogoImg" class="<?php echo htmlspecialchars($formLogo['cls']); ?>" src="<?php echo htmlspecialchars($formLogo['src']); ?>" alt="<?php echo htmlspecialchars($formLogo['alt']); ?>">
</div>
</div>
<script>
function kisiSec() {
    var input = document.getElementById('kisiAra');
    var list = document.getElementById('kisiListesi').options;
    var sirket = document.getElementById('aktifSirket').value || 'dedem';
    var id = null;
    for (var i = 0; i < list.length; i++) {
        if (list[i].value === input.value) {
            id = list[i].getAttribute('data-id');
            break;
        }
    }
    if (id) {
        window.location.href = 'duzenle.php?id=' + encodeURIComponent(id) + '&sirket=' + encodeURIComponent(sirket);
    } else {
        alert('Listeden bir kişi seçmediniz. Yazmaya devam edebilirsiniz, ancak bir kayıt yüklemek için listeden bir isim seçin.');
    }
}

// Enter tuşuna basıldığında da seçim yap
document.getElementById('kisiAra').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        kisiSec();
    }
});

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