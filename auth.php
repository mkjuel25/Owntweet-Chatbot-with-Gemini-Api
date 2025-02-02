<?php

if(isset($_POST['register'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        require 'config.php';
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        header("Location: index.php");
    } catch(PDOException $e) {
        $error = "Username already exists!";
    }
}

if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
   try {
        require 'config.php';
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
             header("Location: index.php");
        } else {
            $error = "Invalid credentials!";
        }
   } catch(PDOException $e) {
       $error = 'There was a problem with the database, try again later';
    }

}

if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
}
?>
