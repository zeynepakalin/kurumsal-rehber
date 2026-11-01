<?php //çıkış yap butonu için
session_start();
session_unset();
session_destroy();
header("Location: login.php");
?>
