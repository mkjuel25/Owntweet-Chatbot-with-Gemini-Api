<?php
session_start();
$host = "localhost";
$dbname = "Owntweet_chatbot";
$user = "chatbot";
$pass = "password";

$gemini_api_key = "Gemini Api key";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
