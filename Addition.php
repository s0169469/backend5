<?php
header('Content-Type: text/html; charset=UTF-8');
$user = 'u53298'; 
$pass = '8737048'; 
$db = new PDO('mysql:host=localhost;dbname=u53298', $user, $pass,
  [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); 
// Начинаем сессию.
session_start();
?>
