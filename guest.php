<?php
require 'config.php';
require 'auth.php';

// Redirect to index.php if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login/Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        @keyframes typing {
            0% { opacity: 0.4; transform: translateY(0); }
            50% { opacity: 1; transform: translateY(-3px); }
            100% { opacity: 0.4; transform: translateY(0); }
        }
        .typing-dot { animation: typing 1.5s infinite; }

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
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5); /* Blue focus ring */
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 h-screen flex items-center justify-center p-4">
    <div class="container mx-auto max-w-md w-full">
        <div class="form-card bg-gray-800/70 backdrop-blur-md rounded-3xl shadow-2xl p-6 sm:p-8 animate-fade-in">
            <h1 class="text-3xl font-bold mb-8 text-center text-white animate-pulse">
                <i class='bx bxl-xing text-blue-500 align-middle'></i> <span class="align-middle">Gemini Chat</span>
            </h1>

            <?php if(isset($error)): ?>
                <div class="bg-red-500/20 p-4 rounded-xl mb-6 text-red-300 border border-red-500/30 flex items-center space-x-2">
                    <i class='bx bx-error-circle text-red-400'></i>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row gap-3 mb-8">
                <button onclick="showForm('login')" id="loginButton"
                        class="flex-1 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-colors font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Login
                </button>
                <button onclick="showForm('register')" id="registerButton"
                        class="flex-1 py-3 rounded-xl bg-gray-700 text-gray-200 hover:bg-gray-600 transition-colors font-medium focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                    Register
                </button>
            </div>

            <div class="p-6 sm:p-8 rounded-2xl border border-gray-700/50">
                <form id="loginForm" method="POST" class="block space-y-5 animate-fade-in">
                    <input type="hidden" name="login" value="1">
                    <div>
                        <label for="login_identifier" class="block mb-2 text-gray-300 font-medium">Username or Email</label>
                        <input type="text" id="login_identifier" name="login_identifier"
                               class="input-focus w-full p-3 border border-gray-600 rounded-xl bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                               placeholder="Enter your username or email">
                    </div>
                    <div>
                        <label for="login_password" class="block mb-2 text-gray-300 font-medium">Password</label>
                        <input type="password" id="login_password" name="password"
                               class="input-focus w-full p-3 border border-gray-600 rounded-xl bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                               placeholder="Enter your password">
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="remember_me" name="remember_me" value="1"
                                   class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-2 focus:ring-offset-gray-800">
                            <label for="remember_me" class="ml-2 text-sm font-medium text-gray-400">Remember Me</label>
                        </div>
                        <a href="#" class="text-sm text-blue-400 hover:text-blue-300 transition-colors">Forgot Password?</a>
                    </div>
                    <button type="submit"
                            class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-3 rounded-xl font-medium transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        Login
                    </button>
                </form>

                <form id="registerForm" method="POST" class="hidden space-y-5 animate-fade-in">
                    <input type="hidden" name="register" value="1">
                    <div>
                        <label for="register_username" class="block mb-2 text-gray-300 font-medium">Username (Max 10 chars)</label>
                        <input type="text" id="register_username" name="username"
                               class="input-focus w-full p-3 border border-gray-600 rounded-xl bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                               placeholder="Choose a username" maxlength="10">
                        <p id="username-error" class="text-red-500 text-sm mt-1 hidden">Username must be at most 10 characters long.</p>
                    </div>
                    <div>
                        <label for="register_email" class="block mb-2 text-gray-300 font-medium">Email</label>
                        <input type="email" id="register_email" name="email"
                               class="input-focus w-full p-3 border border-gray-600 rounded-xl bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                               placeholder="Enter your email">
                    </div>
                    <div>
                        <label for="register_password" class="block mb-2 text-gray-300 font-medium">Password</label>
                        <input type="password" id="register_password" name="password"
                               class="input-focus w-full p-3 border border-gray-600 rounded-xl bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                               placeholder="Create a password">
                    </div>
                    <div>
                        <label for="confirm_password" class="block mb-2 text-gray-300 font-medium">Confirm Password</label>
                        <input type="password" id="confirm_password" name="password_confirmation"
                               class="input-focus w-full p-3 border border-gray-600 rounded-xl bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                               placeholder="Confirm your password">
                        <p id="password-error" class="text-red-500 text-sm mt-1 hidden">Passwords do not match.</p>
                    </div>
                    <button type="submit" id="register-button"
                            class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-3 rounded-xl font-medium transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        Register
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const loginButton = document.getElementById('loginButton');
        const registerButton = document.getElementById('registerButton');

        function showForm(formType) {
            if (formType === 'login') {
                loginForm.classList.remove('hidden');
                loginForm.classList.add('animate-fade-in');
                registerForm.classList.add('hidden');
                registerForm.classList.remove('animate-fade-in');
                loginButton.classList.remove('bg-gray-700', 'text-gray-200', 'hover:bg-gray-600');
                loginButton.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700');
                registerButton.classList.remove('bg-blue-600', 'text-white', 'hover:bg-blue-700');
                registerButton.classList.add('bg-gray-700', 'text-gray-200', 'hover:bg-gray-600');


            } else if (formType === 'register') {
                registerForm.classList.remove('hidden');
                registerForm.classList.add('animate-fade-in');
                loginForm.classList.add('hidden');
                loginForm.classList.remove('animate-fade-in');
                registerButton.classList.remove('bg-gray-700', 'text-gray-200', 'hover:bg-gray-600');
                registerButton.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700');
                loginButton.classList.remove('bg-blue-600', 'text-white', 'hover:bg-blue-700');
                loginButton.classList.add('bg-gray-700', 'text-gray-200', 'hover:bg-gray-600');
            }
        }
        // Show login form by default
        showForm('login');

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
                event.preventDefault(); // Prevent form submission if validation fails
            }
        });
    </script>
</body>
</html>
