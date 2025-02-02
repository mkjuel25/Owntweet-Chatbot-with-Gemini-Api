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
            0% { opacity: 0.4; }
            50% { opacity: 1; }
            100% { opacity: 0.4; }
        }
        .typing-dot { animation: typing 1.5s infinite; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 h-screen flex items-center justify-center p-4">
    <div class="container mx-auto max-w-md w-full">
        <div class="bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-2xl p-6 sm:p-8">
            <h1 class="text-3xl font-bold mb-6 text-center text-white">Gemini Chat</h1>

            <?php if(isset($error)): ?>
                <div class="bg-red-100 p-3 rounded-lg mb-6 text-red-700">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row gap-4 mb-8">
                <button onclick="showForm('login')"
                        class="flex-1 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-colors font-medium">
                    Login
                </button>
                <button onclick="showForm('register')"
                        class="flex-1 py-3 rounded-xl bg-gray-700 text-gray-200 hover:bg-gray-600 transition-colors font-medium">
                    Register
                </button>
            </div>

            <div class="p-4 sm:p-6 rounded-2xl border border-gray-700">
                <form id="loginForm" method="POST" class="block space-y-4 sm:space-y-5">
                    <input type="hidden" name="login" value="1">
                    <div>
                        <label class="block mb-2 text-gray-300 font-medium">Username</label>
                        <input type="text" name="username"
                               class="w-full p-3 border border-gray-600 rounded-xl bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                               placeholder="Enter your username">
                    </div>
                    <div>
                        <label class="block mb-2 text-gray-300 font-medium">Password</label>
                        <input type="password" name="password"
                               class="w-full p-3 border border-gray-600 rounded-xl bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                               placeholder="Enter your password">
                    </div>
                    <button type="submit"
                            class="w-full bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 transition-colors font-medium">
                        Login
                    </button>
                </form>

                <form id="registerForm" method="POST" class="hidden space-y-4 sm:space-y-5">
                    <input type="hidden" name="register" value="1">
                    <div>
                        <label class="block mb-2 text-gray-300 font-medium">Username</label>
                        <input type="text" name="username"
                               class="w-full p-3 border border-gray-600 rounded-xl bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                               placeholder="Choose a username">
                    </div>
                    <div>
                        <label class="block mb-2 text-gray-300 font-medium">Password</label>
                        <input type="password" name="password"
                               class="w-full p-3 border border-gray-600 rounded-xl bg-gray-700 text-white focus:border-blue-500 focus:outline-none" required
                               placeholder="Create a password">
                    </div>
                    <button type="submit"
                            class="w-full bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 transition-colors font-medium">
                        Register
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showForm(formType) {
            document.getElementById('loginForm').classList.toggle('hidden', formType !== 'login');
            document.getElementById('registerForm').classList.toggle('hidden', formType !== 'register');
        }
        // Show login form by default
        showForm('login');
    </script>
</body>
</html>
