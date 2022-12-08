<?php

session_start();
$dbservername = 'localhost';
$dbname = 'main_db';
$dbusername = 'ChiehYun';
$dbpassword = 'yunchieh';

$conn = new PDO ("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>