<?php
session_start();
$_SESSION = array();
session_destroy();
if(isset($_COOKIE["language"]) && $_COOKIE["language"] == "en") setcookie("language", "bg", time() + (86400 * 365), "/");
else setcookie("language", "en", time() + (86400 * 365), "/");
header("location: login.php");
exit;
?>