<?php
if ($_GET) 
{
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

    // id'si seçilen veriyi silme sorgusu
    if ($baglanti->query("DELETE FROM $tablo WHERE id =".(int)$_GET['id'])) 
    {
        echo "<script>
                alert('Kayıt başarıyla silindi.');
                window.location.href = 'anasayfa.php';
              </script>";
    } else {
        echo "<script>
                alert('Kayıt silinirken bir hata oluştu.');
                window.location.href = 'anasayfa.php';
              </script>";
    }
}
?>
