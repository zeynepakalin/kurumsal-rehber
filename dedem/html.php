<?php  //önceki html sayfasının veri tabanı ile bağlanmış ve bazı özellikler eklenmiş hali
include("vt.php"); // veritabanı bağlantımızı sayfamıza ekliyoruz. 

function html_departmanlari_al($baglanti, $tablo) {
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
    $departmanlar_dedem = html_departmanlari_al($baglanti, 'iletisim');
    $departmanlar_decons = html_departmanlari_al($baglanti, 'iletisim_decons');
    $departmanlar_temcon = html_departmanlari_al($baglanti, 'iletisim_temcon');
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

    header('Content-Type: text/vcard; charset=utf-8');
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
            } else {
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

function html_rehber_email_hucre($email) {
    $e = trim((string)$email);
    if ($e === '') {
        return '';
    }
    $h = htmlspecialchars($e, ENT_QUOTES, 'UTF-8');
    return '<a href="mailto:' . $h . '">' . $h . '</a>';
}

function html_rehber_yazi($v) {
    if ($v === null || (is_string($v) && trim($v) === '')) {
        return '';
    }
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dedem Rehber</title>
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
        .footer {
            background-color:white;
            color: black;
            text-align: center;
            width:100%;
        }
        .header {
          text-align: center;
          padding: 10px;
          border-radius: 5px;
        }
        .login-button {
            display: inline-block;
            margin-left: 20px;
            padding: 8px 18px;
            font-size: 14px;
            border-radius: 20px;
            border: 1px solid #4CAF50;
            background-color: transparent;
            color: #4CAF50;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }
        .login-button:hover {
            background-color: #4CAF50;
            color: #ffffff;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.15);
        }
        .tab-buttons {
            margin-top: 20px;
        }
        .tab-button {
            border: 2px solid #43a047;
            padding: 10px 20px;
            margin: 0 5px;
            cursor: pointer;
            border-radius: 5px;
            background-color: #f4faf4;
            color: #2e7d32;
            box-sizing: border-box;
        }
        .tab-button:hover {
            background-color: #e8f5e9;
        }
        .tab-button.active {
            background-color: #4CAF50;
            color: white;
            border-color: #2e7d32;
            box-shadow: 0 0 0 1px rgba(46, 125, 50, 0.35);
        }
        .rehber-container {
            display: none;
        }
        .rehber-container.active {
            display: block;
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

<body>

  <div class="background"></div>
    <div class="logo-wrap"><a id="sirketLogoLink" href="https://dedemmekatronik.com/" target="_blank"><img id="sirketLogoImg" class="logo-sirket-dedem" src="https://i.ibb.co/fD4Sjw2/dede-m.png" alt="Dedem Mekatronik" border="0"></a></div>
    <div align="center" class="tab-buttons">
        <button class="tab-button active" onclick="showRehber('dedem')">Dedem</button>
        <button class="tab-button" onclick="showRehber('decons')">Decons</button>
        <button class="tab-button" onclick="showRehber('temcon')">Temcon</button>
        <a id="downloadBtn" class="login-button" href="html.php?download=dedem">Dedem Rehber İndir</a>
        <a class="login-button" href="html.php?download=all">Tüm Rehberi İndir</a>

    </div>
<br>
    <div   align="center">
    <label for="search">İsim Soyisim Ara:</label>
    <input type="text" id="search" onkeyup="searchFunction()">
</div>
<br>
    <div align="center">
    <label for="searchKisakod">Kısa Kod Ara:</label>
    <input type="text" id="searchKisakod" onkeyup="searchKisakodFunction()">
</div>
<br>
<div align="center">
    <label for="departFilter">Departman Filtrele:</label>
    <select id="departFilter" onchange="filterByDepartment()">
        <option value="">Tümü</option>
    </select>
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
            <td><?php echo html_rehber_email_hucre($email); ?></td>
            <td><?php echo html_rehber_yazi($telefon); ?></td>
            <td><?php echo html_rehber_yazi($kisakod); ?></td>
            
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
        </tr>
        <?php 
        // Decons şirketi için rehber tablosu (iletisim_decons)
        // Bu tabloyu veritabanınızda oluşturup verileri eklemeniz gerekir.
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
            <td><?php echo html_rehber_email_hucre($email); ?></td>
            <td><?php echo html_rehber_yazi($telefon); ?></td>
            <td><?php echo html_rehber_yazi($kisakod); ?></td>
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
        </tr>
        <?php 
        // Temcon şirketi için rehber tablosu (iletisim_temcon)
        // Bu tabloyu veritabanınızda oluşturup verileri eklemeniz gerekir.
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
            <td><?php echo html_rehber_email_hucre($email); ?></td>
            <td><?php echo html_rehber_yazi($telefon); ?></td>
            <td><?php echo html_rehber_yazi($kisakod); ?></td>
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

        // Rehber İndir butonunun yazısını ve linkini güncelle
        var downloadBtn = document.getElementById('downloadBtn');
        if (downloadBtn) {
            if (tip === 'dedem') {
                downloadBtn.textContent = 'Dedem Rehber İndir';
                downloadBtn.href = 'html.php?download=dedem';
            } else if (tip === 'decons') {
                downloadBtn.textContent = 'Decons Rehber İndir';
                downloadBtn.href = 'html.php?download=decons';
            } else if (tip === 'temcon') {
                downloadBtn.textContent = 'Temcon Rehber İndir';
                downloadBtn.href = 'html.php?download=temcon';
            }
        }

        // Arama ve filtre kutularını sıfırla
        document.getElementById('search').value = '';
        document.getElementById('searchKisakod').value = '';
        document.getElementById('departFilter').value = '';
        fillDepartFilterDepartments(tip);

        // Aktif tabloda tüm satırları tekrar göster
        var table = getActiveTable();
        if (!table) return;
        var tr = table.getElementsByTagName('tr');
        for (var j = 1; j < tr.length; j++) {
            tr[j].style.display = '';
        }
      }

      document.addEventListener('DOMContentLoaded', function () {
        fillDepartFilterDepartments('dedem');
      });
    </script>

</body>
</html>
