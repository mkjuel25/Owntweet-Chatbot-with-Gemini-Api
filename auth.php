<?php

// Function to generate a random token
function generateRememberMeToken() {
    return bin2hex(random_bytes(32)); // 64 characters long hex string
}

// Function to set remember me cookie
function setRememberMeCookie($token, $expiry) {
    setcookie('remember_me_token', $token, $expiry, '/', '', true, true); // HttpOnly and Secure
}

// Function to clear remember me cookie
function clearRememberMeCookie() {
    setcookie('remember_me_token', '', time() - 3600, '/', '', true, true);
}

// Check for remember me cookie on each page load (before login form processing)
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me_token'])) {
    $token = $_COOKIE['remember_me_token'];
    try {
        require 'config.php';
        $stmt = $pdo->prepare("SELECT users.* FROM users INNER JOIN remember_me_tokens ON users.id = remember_me_tokens.user_id WHERE remember_me_tokens.token = ? AND remember_me_tokens.expiry_time > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            // Regenerate session ID for security
            session_regenerate_id(true);
            header("Location: index.php");
            exit;
        }
    } catch (PDOException $e) {
        // Log error (optional)
    }
}

?>
<?php

if(isset($_POST['register'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];

    if (strlen($username) > 10) {
        $error = "Username must be at most 10 characters long.";
    } else if ($password !== $password_confirmation) {
        $error = "Passwords do not match!";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    }
     else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            require 'config.php';
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $email]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header("Location: index.php");
        } catch(PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'username')) {
                $error = "Username already exists!";
            } else if ($e->getCode() == 23000 && strpos($e->getMessage(), 'email')) {
                $error = "Email already exists!";
            }
             else {
                $error = "Registration error, please try again.";
            }
        }
    }
}

if(isset($_POST['login'])) {
    $login_identifier = $_POST['login_identifier'];
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']); // Check if remember_me is checked

   try {
        require 'config.php';
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$login_identifier, $login_identifier]);
        $user = $stmt->fetch();

        if($user && password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            session_regenerate_id(true); // Regenerate session ID after login

            if ($remember_me) {
                $token = generateRememberMeToken();
                $expiry = time() + (30 * 24 * 3600); // 30 days expiry

                // Store token in database
                $stmtToken = $pdo->prepare("INSERT INTO remember_me_tokens (user_id, token, expiry_time) VALUES (?, ?, FROM_UNIXTIME(?))");
                $stmtToken->execute([$user['id'], $token, $expiry]);

                setRememberMeCookie($token, $expiry); // Set cookie
            }

             header("Location: index.php");
             exit; // Ensure script stops here

        } else {
            $error = "Invalid credentials!";
        }
   } catch(PDOException $e) {
       $error = 'There was a problem with the database, try again later';
    }

}

if(isset($_GET['logout'])) {
    session_destroy();
    clearRememberMeCookie(); // Clear remember me cookie on logout
    header("Location: guest.php");
    exit; // Ensure script stops here
}
?>
