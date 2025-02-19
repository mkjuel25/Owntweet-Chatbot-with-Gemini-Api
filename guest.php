<?php
require 'config.php';
require 'auth.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Handle login form submission
if (isset($_POST['login'])) {
    $login_identifier = $_POST['login_identifier'];
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']); // Check if remember_me checkbox is checked

    // Validate input (basic validation, you should add more robust validation)
    if (empty($login_identifier) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Determine if login_identifier is email or username (or phone number - you might need to adjust this logic)
        if (filter_var($login_identifier, FILTER_VALIDATE_EMAIL)) {
            $identifier_type = 'email';
            $email = $login_identifier;
            $username = null; // Not used for email login
        } else {
            $identifier_type = 'username';
            $username = $login_identifier;
            $email = null; // Not used for username login
        }

        // Prepare SQL query based on identifier type
        if ($identifier_type == 'email') {
            $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
        } else {
            $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ? OR phone_number = ?"); // Assuming phone_number is also an option
            $stmt->execute([$username, $username]); // Try username or phone number
        }

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];

                // Remember me functionality
                if ($remember_me) {
                    $cookie_expiry = time() + (30 * 24 * 3600); // 30 days
                    setcookie('remember_user', $user['id'], $cookie_expiry, '/', '', true, true); // HttpOnly and Secure (if using HTTPS)
                }

                header("Location: index.php");
                exit;
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "User not found.";
        }
    }
}

// Handle registration form submission
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];

    // Validate registration input
    if (strlen($username) > 10) {
        $error = "Username must be at most 10 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $password_confirmation) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt_check->execute([$username, $email]);
        $count = $stmt_check->fetchColumn();
        if ($count > 0) {
            $error = "Username or email already taken.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt_insert->execute([$username, $email, $hashed_password])) {
                // Registration successful, you might want to log the user in directly or redirect to login page
                $success_message = "Registration successful. Please log in.";
                // Optionally log in user directly after registration
                $stmt_login = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt_login->execute([$email]);
                if ($stmt_login->rowCount() > 0) {
                    $user_login = $stmt_login->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['user_id'] = $user_login['id'];
                    header("Location: index.php"); // Redirect to index after successful registration and login
                    exit;
                }

            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}


// Check for remember me cookie on page load (in auth.php is better but for this single file example)
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {
    $user_id = $_COOKIE['remember_user'];
    $stmt_cookie_login = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt_cookie_login->execute([$user_id]);
    if ($stmt_cookie_login->rowCount() > 0) {
        $user_cookie = $stmt_cookie_login->fetch(PDO::FETCH_ASSOC);
        $_SESSION['user_id'] = $user_cookie['id'];
        header("Location: index.php"); // Auto login and redirect to index
        exit;
    } else {
        // Cookie is invalid, maybe user deleted or does not exist, clear the cookie
        setcookie('remember_user', '', time() - 3600, '/');
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log in or sign up</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .form-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .form-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }

        .input-focus:focus {
            box-shadow: 0 0 0 2px rgba(132, 185, 255, 0.5);
            border-color: #84b9ff !important;
        }

        /* Preloader Styles */
        #preloader-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #111827;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 1;
            transition: opacity 0.3s ease-out;
        }

        #preloader-animation.fade-out {
            opacity: 0;
            pointer-events: none;
        }

        .loader {
            border: 8px solid #374151;
            border-top: 8px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #preloader-text {
            margin-top: 20px;
            color: #ddd;
        }
    </style>
</head>
<body class="bg-gray-900 min-h-screen flex flex-col items-center p-4 justify-center font-sans text-white">

    <!-- Pre-loader Animation Container -->
    <div id="preloader-animation">
        <div class="loader"></div>
        <div id="preloader-text">Loading...</div>
    </div>

    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <h1 class="text-xl font-semibold text-white">
                <i class='bx bxl-xing text-blue-500 align-middle'></i>
                Owntweet Chat
            </h1>
            <p class="text-gray-400 text-sm">Start conversation with Owntweet Chatbot</p>
        </div>

        <div class="form-card bg-gray-800 rounded-lg shadow-2xl p-6 sm:p-8 animate-fade-in border border-gray-700">

            <?php if(isset($error)): ?>
                <div class="bg-red-900 border border-red-700 text-red-400 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"><?= $error ?></span>
                </div>
            <?php endif; ?>

            <?php if(isset($success_message)): ?>
                <div class="bg-green-900 border border-green-700 text-green-400 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline"><?= $success_message ?></span>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" class="space-y-4 animate-fade-in block">
                <input type="hidden" name="login" value="1">
                <div>
                    <input type="text" id="login_identifier" name="login_identifier"
                           class="input-focus w-full p-3 border border-gray-700 rounded-md bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                           placeholder="Email or phone number">
                </div>
                <div>
                    <input type="password" id="login_password" name="password"
                           class="input-focus w-full p-3 border border-gray-700 rounded-md bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                           placeholder="Password">
                </div>
                <div class="flex items-center mb-4">
                    <input id="remember_me" type="checkbox" name="remember_me" value="1" class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-2 ">
                    <label for="remember_me" class="ml-2 text-sm font-medium text-gray-400">Remember me</label>
                </div>
                <button type="submit"
                        class="w-full bg-blue-600 text-white py-3 rounded-md font-semibold hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Log In
                </button>
                <div class="text-center">
                    <a href="#" class="text-blue-500 text-sm hover:underline">Forgotten password?</a>
                </div>
                <hr class="my-4 border-gray-700" />
                <div class="text-center">
                    <button onclick="showRegisterForm()" type="button"
                            class="bg-green-500 hover:bg-green-600 text-white py-3 px-6 rounded-md font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                        Create new account
                    </button>
                </div>
            </form>

            <form id="registerForm" method="POST" class="hidden space-y-4 animate-fade-in">
                <input type="hidden" name="register" value="1">
                <div>
                    <label for="register_username" class="block mb-1 text-gray-300 font-medium text-sm">Username (Max 10 chars)</label>
                    <input type="text" id="register_username" name="username"
                           class="input-focus w-full p-3 border border-gray-700 rounded-md bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                           placeholder="Username" maxlength="10">
                    <p id="username-error" class="text-red-500 text-sm mt-1 hidden">Username must be at most 10 characters long.</p>
                </div>
                <div>
                    <label for="register_email" class="block mb-1 text-gray-300 font-medium text-sm">Email</label>
                    <input type="email" id="register_email" name="email"
                           class="input-focus w-full p-3 border border-gray-700 rounded-md bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                           placeholder="Email">
                </div>
                <div>
                    <label for="register_password" class="block mb-1 text-gray-300 font-medium text-sm">Password</label>
                    <input type="password" id="register_password" name="password"
                           class="input-focus w-full p-3 border border-gray-700 rounded-md bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                           placeholder="New password">
                </div>
                <div>
                    <label for="confirm_password" class="block mb-1 text-gray-300 font-medium text-sm">Confirm Password</label>
                    <input type="password" id="confirm_password" name="password_confirmation"
                           class="input-focus w-full p-3 border border-gray-700 rounded-md bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                           placeholder="Confirm password">
                    <p id="password-error" class="text-red-500 text-sm mt-1 hidden">Passwords do not match.</p>
                </div>
                <button type="submit" id="register-button"
                        class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-md font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                    Sign Up
                </button>
                <div class="mt-4 text-center text-gray-400">
                    Already have an account?
                    <button type="button" onclick="showLoginForm()" class="text-blue-500 hover:underline font-medium">Log in</button>
                </div>
            </form>


        </div>


        <footer class="mt-12 text-center text-gray-600 text-xs">
            <p class="mb-2">Owntweet Chatbot is open source on <a href="https://github.com/mkjuel25/Owntweet-Chatbot-with-Gemini-Api" target="_blank" class="text-blue-500 hover:underline">GitHub</a> <span class="text-red-500">❤️</span></p>
            <p>Made with <i class='bx bxs-coffee-alt'></i> and <i class='bx bxs-heart text-red-500'></i> for the community</p>
        </footer>
    </div>


    <script>
        const preloader = document.getElementById('preloader-animation');
        window.addEventListener('load', function() {
            setTimeout(function(){
                preloader.classList.add('fade-out');
            }, 50); // 0.05 seconds = 50 milliseconds
        });


        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');


        function showRegisterForm() {
            loginForm.classList.add('hidden');
            loginForm.classList.remove('animate-fade-in');
            registerForm.classList.remove('hidden');
            registerForm.classList.add('animate-fade-in');
        }

        function showLoginForm() {
            registerForm.classList.add('hidden');
            registerForm.classList.remove('animate-fade-in');
            loginForm.classList.remove('hidden');
            loginForm.classList.add('animate-fade-in');
        }


        const registerButtonSubmit = document.getElementById('register-button');
        const usernameInput = document.getElementById('register_username');
        const passwordInput = document.getElementById('register_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const usernameError = document.getElementById('username-error');
        const passwordError = document.getElementById('password-error');

        registerForm.addEventListener('submit', function(event) {
            let isValid = true;

            if (usernameInput.value.length > 10) {
                usernameError.classList.remove('hidden');
                isValid = false;
            } else {
                usernameError.classList.add('hidden');
            }

            if (passwordInput.value !== confirmPasswordInput.value) {
                passwordError.classList.remove('hidden');
                isValid = false;
            } else {
                passwordError.classList.add('hidden');
            }

            if (!isValid) {
                event.preventDefault();
            }
        });

        showLoginForm();
    </script>
</body>

</html>
