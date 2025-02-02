<?php
session_start();
$host = "localhost";
$dbname = "Owntweet_chatbot";
$user = "chatbot";
$pass = "password";

$gemini_api_key = "Gemini Api key";

try {
    $options = [
      PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass, $options);

} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
