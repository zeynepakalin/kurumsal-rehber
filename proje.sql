-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 21 Ağu 2024, 14:23:31
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `proje`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `iletisim`
--

CREATE TABLE `iletisim` (
  `id` int(11) NOT NULL,
  `adsoy` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `depart` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `unvan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `telefon` varchar(20) NOT NULL,
  `kisakod` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Tablo döküm verisi `iletisim`
--

-- Örnek kişi ve kullanıcı verileri gizlilik için çıkarıldı.


-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` int(11) NOT NULL,
  `role` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Tablo döküm verisi `users`
--

-- Örnek kişi ve kullanıcı verileri gizlilik için çıkarıldı.


--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `iletisim`
--
ALTER TABLE `iletisim`
  ADD PRIMARY KEY (`id`);

--
-- Decons ve Temcon için iletisim tabloları
-- Mevcut `iletisim` tablosu ile aynı yapıda yeni tablolar oluşturulur
--

CREATE TABLE `iletisim_decons` LIKE `iletisim`;
CREATE TABLE `iletisim_temcon` LIKE `iletisim`;

-- DECONS kişileri
-- Örnek kişi ve kullanıcı verileri gizlilik için çıkarıldı.


-- TEMCON kişileri
-- Örnek kişi ve kullanıcı verileri gizlilik için çıkarıldı.



--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `iletisim`
--
ALTER TABLE `iletisim`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

-- Yeni tablolar için de AUTO_INCREMENT alanı ayarlanır
ALTER TABLE `iletisim_decons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `iletisim_temcon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- E-posta, telefon, kısa kod isteğe bağlı (boş = NULL)
ALTER TABLE `iletisim`
  MODIFY `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  MODIFY `telefon` varchar(20) NULL,
  MODIFY `kisakod` int(11) NULL;
ALTER TABLE `iletisim_decons`
  MODIFY `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  MODIFY `telefon` varchar(20) NULL,
  MODIFY `kisakod` int(11) NULL;
ALTER TABLE `iletisim_temcon`
  MODIFY `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  MODIFY `telefon` varchar(20) NULL,
  MODIFY `kisakod` int(11) NULL;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;