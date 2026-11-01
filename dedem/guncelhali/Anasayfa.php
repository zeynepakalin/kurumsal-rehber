<?php  //html sayfasının kişi ekleme , silme ve düzenleme yetkisi olan admin sayfası, login.php sayfası kontrolü sonrasında bu sayfaya geçiş sağlanıyor
include("vt.php"); // veritabanı bağlantımızı sayfamıza ekliyoruz. 

// anasayfa.php
session_start();
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] != "admin") {
    header("Location: login.php");
    exit();
}

function departmanlari_al($baglanti, $tablo) {
    $list = [];
    $q = $baglanti->query("SELECT DISTINCT depart FROM `$tablo` WHERE depart IS NOT NULL AND depart <> '' ORDER BY depart");
    if ($q) {
        while ($r = $q->fetch_assoc()) {
            $list[] = $r['depart'];
        }
    }
    return $list;
}

if (!isset($_GET['download'])) {
    $departmanlar_dedem = departmanlari_al($baglanti, 'iletisim');
    $departmanlar_decons = departmanlari_al($baglanti, 'iletisim_decons');
    $departmanlar_temcon = departmanlari_al($baglanti, 'iletisim_temcon');
}

// Eğer download parametresi geldiyse, ilgili şirket rehberini VCF olarak indir
if (isset($_GET['download'])) {
    $company = $_GET['download'];

    switch ($company) {
        case 'all':
            $table = null; // özel durum
            $filename = 'tum-rehber.vcf';
            $orgName = 'Tüm Şirketler';
            break;
        case 'decons':
            $table = 'iletisim_decons';
            $filename = 'decons-rehber.vcf';
            $orgName = 'Decons';
            break;
        case 'temcon':
            $table = 'iletisim_temcon';
            $filename = 'temcon-rehber.vcf';
            $orgName = 'Temcon';
            break;
        case 'dedem':
        default:
            $table = 'iletisim';
            $filename = 'dedem-rehber.vcf';
            $orgName = 'Dedem Mekatronik';
            break;
    }

if ($company === 'all') {
    $sorgu = $baglanti->query("
        SELECT *, 'Dedem Mekatronik' AS sirket FROM iletisim
        UNION ALL
        SELECT *, 'Decons' AS sirket FROM iletisim_decons
        UNION ALL
        SELECT *, 'Temcon' AS sirket FROM iletisim_temcon
        ORDER BY adsoy
    ");
} else {
    $sorgu = $baglanti->query("SELECT * FROM `$table` ORDER BY `adsoy`");
}

    // Hiç kayıt yoksa kullanıcıya uyarı göster ve indirime geçme
    if (!$sorgu || $sorgu->num_rows === 0) {
        echo "<script>alert('Bu şirket için indirilecek kişi bulunamadı.'); window.history.back();</script>";
        exit;
    }

    if (ob_get_length()) { ob_clean(); }  // çıktı varsa temizle (VCF bozulmasın)
    header('Content-Type: text/vcard; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    if ($sorgu) {
        while ($row = $sorgu->fetch_assoc()) {
            $adsoy   = $row['adsoy'];
            $depart  = $row['depart'];
            $unvan   = $row['unvan'];
            $email   = $row['email'];
            $telefon = $row['telefon'];

            $fn    = str_replace(array("\r", "\n"), ' ', $adsoy);
            if ($company === 'all') {
            $org   = str_replace(array("\r", "\n"), ' ', $row['sirket'].' - '.$depart);
            }else {
            $org   = str_replace(array("\r", "\n"), ' ', $orgName.' - '.$depart);
            }
            $title = str_replace(array("\r", "\n"), ' ', $unvan);

            echo "BEGIN:VCARD\r\n";
            echo "VERSION:3.0\r\n";
            echo "FN:".$fn."\r\n";
            echo "ORG:".$org."\r\n";
            echo "TITLE:".$title."\r\n";
            if (!empty($telefon)) {
                echo "TEL;TYPE=CELL:".$telefon."\r\n";
            }
            if (!empty($email)) {
                echo "EMAIL;TYPE=INTERNET:".$email."\r\n";
            }
            echo "END:VCARD\r\n";
        }
    }

    exit;
}
?>


<!doctype html>
<html>
<head>
    
<meta charset="utf8_turkish_ci">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

   
<title>Dedem Rehber Admin</title>

<style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            
            
        }
        .search-container, .filter-container {
            margin-bottom: 20px;
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

        .content {
            z-index: 1; 
            color: white; 
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        
        .header {
          text-align: center;
          padding: 10px;
          border-radius: 5px;
        }
        .button {
            border: none;
            padding: 14px 28px;
            font-size: 16px;
            cursor: pointer;
            background-color: ForestGreen ; 
            color: black;
            border: 1px solid #ddd;
        }
        .download-button {
            display: inline-block;
            text-decoration: none;
        }
        .cıkıs_button{
            
            border: none;
            color: white;
            padding: 14px 28px;
            font-size: 16px;
            cursor: pointer;
            background-color: FireBrick ; 
            color: black;
            border: 1px solid #ddd;
  
        }
        
        .button:hover {
            background-color: #f1f1f1;
        } 

        .footer {
            background-color:white;
            color: black;
            text-align: center;
          
            width:100%;
        }
        .logo-map-zone {
            min-height: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 0 auto 6px;
            padding: 4px 0 2px;
            position: relative;
            z-index: 1;
        }
        .tab-buttons {
            margin-top: 8px;
            text-align: center;
        }
        .panel-toolbar {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin: 0 auto 14px;
            max-width: 1200px;
            position: relative;
            z-index: 1;
        }
        .downloads-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .filters-inline {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 10px 18px;
        }
        .filter-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .filter-item label {
            white-space: nowrap;
        }
        .filter-item input[type="text"],
        .filter-item select {
            vertical-align: middle;
        }
        .ekle-row {
            margin: 0;
        }
        .tab-button {
            border: 2px solid #2e7d32;
            padding: 10px 20px;
            margin: 0 5px;
            cursor: pointer;
            border-radius: 5px;
            background-color: #f4faf4;
            color: #1b3d1b;
            box-sizing: border-box;
        }
        .tab-button:hover {
            background-color: #e8f5e9;
        }
        .tab-button.active {
            background-color: ForestGreen;
            color: white;
            border-color: #1b5e20;
            box-shadow: 0 0 0 1px rgba(27, 94, 32, 0.35);
        }
        .rehber-container {
            display: none;
        }
        .rehber-container.active {
            display: block;
        }
        .hosgeldin_container {
            text-align: right;
            margin-bottom: 20px;
        }
        .logo-wrap {
            height: 124px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 2px;
        }
        #sirketLogoImg {
            width: auto;
            height: auto;
            display: block;
            object-fit: contain;
        }
        #sirketLogoImg.logo-sirket-dedem {
            max-height: 112px;
            max-width: min(90vw, 700px);
        }
        #sirketLogoImg.logo-sirket-decons {
            max-height: 96px;
            max-width: min(88vw, 640px);
        }
        #sirketLogoImg.logo-sirket-temcon {
            max-height: 122px;
            max-width: min(94vw, 740px);
        }
       
       
</style>
</head>
<script>
      
        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
        }

        function capitalizeFormFields() {
            var adsoyField = document.getElementById('adsoy');
            var unvanField = document.getElementById('unvan');
            var emailField = document.getElementById('email');
            var telefonField = document.getElementById('telefon');
            var kisakodField = document.getElementById('kisakod');
            var departField = document.getElementById('depart');
            if (adsoyField) adsoyField.value = adsoyField.value.split(' ').map(capitalizeFirstLetter).join(' ');
            if (unvanField) unvanField.value = capitalizeFirstLetter(unvanField.value);
            if (emailField) emailField.value = emailField.value.toLowerCase();
            if (telefonField) telefonField.value = telefonField.value;
            if (kisakodField) kisakodField.value = kisakodField.value.toUpperCase();
            if (departField) departField.value = departField.value.split(' ').map(capitalizeFirstLetter).join(' ');
        }

        document.addEventListener('DOMContentLoaded', function() {
            var form = document.querySelector('form');
            if (!form) return;
            form.addEventListener('submit', function(event) {
                capitalizeFormFields();
            });
        });
       
        function confirmDelete(id, sirket) {
    var confirmDelete = confirm('Bu kaydı silmek istediğinizden emin misiniz?');
    if (confirmDelete) {
        window.location.href = 'sil.php?id=' + id + '&sirket=' + encodeURIComponent(sirket); // Silme işlemi için yönlendirme
    }
}


    </script>
<body>
<div class="background"></div>
<br><br>
<h1> Hoşgeldin, <?php echo $_SESSION['username']; ?>!</h1>
<div class="hosgeldin_container">

    <a href="logout.php" class=cıkıs_button>Çıkış Yap</a></div>
<div class="logo-map-zone">
    <div class="logo-wrap">
    <a id="sirketLogoLink" href="https://dedemmekatronik.com/" target="_blank">
        <img id="sirketLogoImg" class="logo-sirket-dedem" src="https://i.ibb.co/fD4Sjw2/dede-m.png" alt="Dedem Mekatronik" border="0">
    </a>
    </div>
<div class="tab-buttons">
    <button class="tab-button active" onclick="showRehber('dedem')">Dedem</button>
    <button class="tab-button" onclick="showRehber('decons')">Decons</button>
    <button class="tab-button" onclick="showRehber('temcon')">Temcon</button>
</div>
</div>
<div class="panel-toolbar">
    <div class="downloads-row">
        <a id="downloadBtn" class="button download-button" href="anasayfa.php?download=dedem">Dedem Rehber İndir</a>
        <a id="downloadAllBtn" class="button download-button" href="anasayfa.php?download=all">Tüm Rehberi İndir</a>
    </div>
    <div class="filters-inline">
        <span class="filter-item">
            <label for="search">İsim Soyisim Ara:</label>
            <input type="text" id="search" onkeyup="searchFunction()">
        </span>
        <span class="filter-item">
            <label for="searchKisakod">Kısa Kod Ara:</label>
            <input type="text" id="searchKisakod" onkeyup="searchKisakodFunction()">
        </span>
        <span class="filter-item">
            <label for="departFilter">Departman Filtrele:</label>
            <select id="departFilter" onchange="filterByDepartment()">
                <option value="">Tümü</option>
            </select>
        </span>
    </div>
    <div class="ekle-row">
        <a class="button download-button" href="ekle.php">Yeni Kayıt Ekle</a>
    </div>
</div>

<br>
<div id="dedemRehber" class="rehber-container active">
    <table class="table" id="Table">
        <tr>
            <th style="display:none">No</th>     
            <th>İsim Soyisim</th>
            <th>Departman</th>
            <th>Unvan</th>
            <th>E-mail</th>
            <th>Telefon</th>
            <th>Kısa Kod</th>
            <th>Düzenle</th>
            <th>Sil</th>
        </tr>
        <?php 
        $sorgu = $baglanti->query("SELECT * FROM `iletisim` ORDER BY `iletisim`.`adsoy`");
        while ($sonuc = $sorgu->fetch_assoc()) { 
            $id = $sonuc['id'];
            $adsoy = $sonuc['adsoy'];
            $depart =$sonuc['depart'];
            $unvan = $sonuc['unvan'];
            $email = $sonuc['email'];
            $telefon = $sonuc['telefon'];
            $kisakod = $sonuc['kisakod'];
        ?>
        <tr>
            <td style="display:none"><?php echo $id; ?></td>
            <td><?php echo $adsoy; ?></td>
            <td><?php echo $depart; ?></td>
            <td><?php echo $unvan; ?></td>
            <td><?php
                $e = trim((string) $email);
                echo $e !== '' ? '<a href="mailto:' . htmlspecialchars($e) . '">' . htmlspecialchars($e) . '</a>' : '';
            ?></td>
            <td><?php echo htmlspecialchars(trim((string) $telefon)); ?></td>
            <td><?php
                $k = $kisakod;
                echo htmlspecialchars(($k === null || $k === '' || (int) $k === 0) ? '' : (string) $k);
            ?></td>
            <td><a href="duzenle.php?id=<?php echo $id; ?>&sirket=dedem" class="btn btn-primary">Düzenle</a></td>
            <td><a href="javascript:void(0);" onclick="confirmDelete(<?php echo $id; ?>, 'dedem')" class="btn btn-danger">Sil</a></td>
        </tr>
        <?php 
        }
        ?>
    </table>
</div>

<div id="deconsRehber" class="rehber-container">
    <table class="table" id="TableDecons">
        <tr>
            <th style="display:none">No</th>     
            <th>İsim Soyisim</th>
            <th>Departman</th>
            <th>Unvan</th>
            <th>E-mail</th>
            <th>Telefon</th>
            <th>Kısa Kod</th>
            <th>Düzenle</th>
            <th>Sil</th>
        </tr>
        <?php 
        // Decons şirketi için rehber tablosu (iletisim_decons)
        $sorguDecons = $baglanti->query("SELECT * FROM `iletisim_decons` ORDER BY `adsoy`");
        if ($sorguDecons) {
            while ($sonucDecons = $sorguDecons->fetch_assoc()) { 
                $id = $sonucDecons['id'];
                $adsoy = $sonucDecons['adsoy'];
                $depart = $sonucDecons['depart'];
                $unvan = $sonucDecons['unvan'];
                $email = $sonucDecons['email'];
                $telefon = $sonucDecons['telefon'];
                $kisakod = $sonucDecons['kisakod'];
        ?>
        <tr>
            <td style="display:none"><?php echo $id; ?></td>
            <td><?php echo $adsoy; ?></td>
            <td><?php echo $depart; ?></td>
            <td><?php echo $unvan; ?></td>
            <td><?php
                $e = trim((string) $email);
                echo $e !== '' ? '<a href="mailto:' . htmlspecialchars($e) . '">' . htmlspecialchars($e) . '</a>' : '';
            ?></td>
            <td><?php echo htmlspecialchars(trim((string) $telefon)); ?></td>
            <td><?php
                $k = $kisakod;
                echo htmlspecialchars(($k === null || $k === '' || (int) $k === 0) ? '' : (string) $k);
            ?></td>
            <td><a href="duzenle.php?id=<?php echo $id; ?>&sirket=decons" class="btn btn-primary">Düzenle</a></td>
            <td><a href="javascript:void(0);" onclick="confirmDelete(<?php echo $id; ?>, 'decons')" class="btn btn-danger">Sil</a></td>
        </tr>
        <?php 
            }
        }
        ?>
    </table>
</div>

<div id="temconRehber" class="rehber-container">
    <table class="table" id="TableTemcon">
        <tr>
            <th style="display:none">No</th>     
            <th>İsim Soyisim</th>
            <th>Departman</th>
            <th>Unvan</th>
            <th>E-mail</th>
            <th>Telefon</th>
            <th>Kısa Kod</th>
            <th>Düzenle</th>
            <th>Sil</th>
        </tr>
        <?php 
        // Temcon şirketi için rehber tablosu (iletisim_temcon)
        $sorguTemcon = $baglanti->query("SELECT * FROM `iletisim_temcon` ORDER BY `adsoy`");
        if ($sorguTemcon) {
            while ($sonucTemcon = $sorguTemcon->fetch_assoc()) { 
                $id = $sonucTemcon['id'];
                $adsoy = $sonucTemcon['adsoy'];
                $depart = $sonucTemcon['depart'];
                $unvan = $sonucTemcon['unvan'];
                $email = $sonucTemcon['email'];
                $telefon = $sonucTemcon['telefon'];
                $kisakod = $sonucTemcon['kisakod'];
        ?>
        <tr>
            <td style="display:none"><?php echo $id; ?></td>
            <td><?php echo $adsoy; ?></td>
            <td><?php echo $depart; ?></td>
            <td><?php echo $unvan; ?></td>
            <td><?php
                $e = trim((string) $email);
                echo $e !== '' ? '<a href="mailto:' . htmlspecialchars($e) . '">' . htmlspecialchars($e) . '</a>' : '';
            ?></td>
            <td><?php echo htmlspecialchars(trim((string) $telefon)); ?></td>
            <td><?php
                $k = $kisakod;
                echo htmlspecialchars(($k === null || $k === '' || (int) $k === 0) ? '' : (string) $k);
            ?></td>
            <td><a href="duzenle.php?id=<?php echo $id; ?>&sirket=temcon" class="btn btn-primary">Düzenle</a></td>
            <td><a href="javascript:void(0);" onclick="confirmDelete(<?php echo $id; ?>, 'temcon')" class="btn btn-danger">Sil</a></td>
        </tr>
        <?php 
            }
        }
        ?>
    </table>
</div>
<br><br><br>
  <div class="footer">
  <p>Versiyon 2.0 2026 © Bilgi Sistemleri Departmanı tarafından tasarlanmıştır.</p>
</div>

<script>
    var departBySirket = {
        dedem: <?php echo json_encode($departmanlar_dedem, JSON_UNESCAPED_UNICODE); ?>,
        decons: <?php echo json_encode($departmanlar_decons, JSON_UNESCAPED_UNICODE); ?>,
        temcon: <?php echo json_encode($departmanlar_temcon, JSON_UNESCAPED_UNICODE); ?>
    };

    function fillDepartFilterDepartments(sirket) {
        var sel = document.getElementById('departFilter');
        if (!sel) return;
        var data = departBySirket[sirket] || [];
        var cur = sel.value;
        sel.innerHTML = '';
        var o0 = document.createElement('option');
        o0.value = '';
        o0.textContent = 'Tümü';
        sel.appendChild(o0);
        for (var i = 0; i < data.length; i++) {
            var o = document.createElement('option');
            o.value = data[i];
            o.textContent = data[i];
            sel.appendChild(o);
        }
        if (cur === '' || data.indexOf(cur) !== -1) {
            sel.value = cur;
        } else {
            sel.value = '';
        }
    }

    function getActiveTable() {
        var containers = document.getElementsByClassName('rehber-container');
        for (var i = 0; i < containers.length; i++) {
            if (containers[i].style.display !== 'none' && containers[i].classList.contains('active')) {
                return containers[i].getElementsByTagName('table')[0];
            }
        }
        // Eğer class ile aktif işaretlenmediyse, görünür olanı bul
        for (var j = 0; j < containers.length; j++) {
            if (containers[j].style.display !== 'none') {
                return containers[j].getElementsByTagName('table')[0];
            }
        }
        return null;
    }

    function searchFunction() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById('search');
        filter = input.value.toUpperCase();
        table = getActiveTable();
        if (!table) return;
        tr = table.getElementsByTagName('tr');

        if (filter.length >= 3) {
            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName('td')[1];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }
        } else {
            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "";
            }
        }
    }

    function searchKisakodFunction() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById('searchKisakod');
        filter = input.value.toUpperCase();
        table = getActiveTable();
        if (!table) return;
        tr = table.getElementsByTagName('tr');

        if (filter.length >= 3) {
            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName('td')[6];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }
        } else {
            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "";
            }
        }
    }

    function filterByDepartment() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById('departFilter');
        filter = input.value.toUpperCase();
        table = getActiveTable();
        if (!table) return;
        tr = table.getElementsByTagName('tr');

        if (filter) {
            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName('td')[2];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }
        } else {
            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "";
            }
        }
    }

    function guncelleSirketLogosu(tip) {
        var tab = {
            dedem: ['https://i.ibb.co/fD4Sjw2/dede-m.png', 'https://dedemmekatronik.com/', 'Dedem Mekatronik', 1],
            decons: ['images/decons_logo.png', '#', 'Decons', 0],
            temcon: ['images/temcon_logo.png', '#', 'Temcon', 0]
        };
        var z = tab[tip] || tab.dedem;
        var a = document.getElementById('sirketLogoLink');
        var img = document.getElementById('sirketLogoImg');
        if (!img) return;
        img.src = z[0];
        img.alt = z[2];
        img.className = ({ dedem: 'logo-sirket-dedem', decons: 'logo-sirket-decons', temcon: 'logo-sirket-temcon' })[tip] || 'logo-sirket-dedem';
        if (a) {
            a.href = z[1];
            if (z[3]) a.setAttribute('target', '_blank');
            else a.removeAttribute('target');
        }
    }

    function showRehber(tip) {
        var containers = {
            dedem: document.getElementById('dedemRehber'),
            decons: document.getElementById('deconsRehber'),
            temcon: document.getElementById('temconRehber')
        };

        guncelleSirketLogosu(tip);

        for (var key in containers) {
            if (containers.hasOwnProperty(key) && containers[key]) {
                if (key === tip) {
                    containers[key].style.display = 'block';
                    containers[key].classList.add('active');
                } else {
                    containers[key].style.display = 'none';
                    containers[key].classList.remove('active');
                }
            }
        }

        // Butonların aktiflik durumunu güncelle
        var buttons = document.getElementsByClassName('tab-button');
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].classList.remove('active');
        }
        if (tip === 'dedem') {
            buttons[0].classList.add('active');
        } else if (tip === 'decons') {
            buttons[1].classList.add('active');
        } else if (tip === 'temcon') {
            buttons[2].classList.add('active');
        }

        // Rehber İndirndir butonunun yazısını ve linkini güncelle
        var downloadBtn = document.getElementById('downloadBtn');
        if (downloadBtn) {
            if (tip === 'dedem') {
                downloadBtn.textContent = 'Dedem Rehber İndir';
                downloadBtn.href = 'anasayfa.php?download=dedem';
            } else if (tip === 'decons') {
                downloadBtn.textContent = 'Decons Rehber İndir';
                downloadBtn.href = 'anasayfa.php?download=decons';
            } else if (tip === 'temcon') {
                downloadBtn.textContent = 'Temcon Rehber İndir';
                downloadBtn.href = 'anasayfa.php?download=temcon';
            }
        }

        fillDepartFilterDepartments(tip);

        // Arama ve filtre kutularını sıfırla
        document.getElementById('search').value = '';
        document.getElementById('searchKisakod').value = '';
        document.getElementById('departFilter').value = '';

        // Aktif tabloda tüm satırları tekrar göster
        var table = getActiveTable();
        if (!table) return;
        var tr = table.getElementsByTagName('tr');
        for (var j = 1; j < tr.length; j++) {
            tr[j].style.display = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        fillDepartFilterDepartments('dedem');
    });
</script>

</body>
</html>
