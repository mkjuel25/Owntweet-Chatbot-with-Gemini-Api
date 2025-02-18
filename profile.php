<?php
require 'config.php'; // Include database configuration
require 'auth.php';  // Include authentication functions

// Check if user is logged in. If not, redirect to guest.php (login/register page)
if (!isset($_SESSION['user_id'])) {
    header("Location: guest.php");
    exit;
}

// Fetch user data (for now, just username)
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?"); // Assuming your users table is named 'users' and has columns 'id' and 'username'
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Handle case where user data cannot be found (unlikely if session is valid, but good to have)
    $error = "Error fetching user data.";
    session_destroy(); // Clear session just in case
    header("Location: guest.php?error=" . urlencode($error)); // Redirect to guest page with an error message
    exit;
}

$username = $user['username']; // Get the username

// Handle logout request
if (isset($_GET['logout'])) {
    session_destroy(); // Clear the session
    header("Location: guest.php"); // Redirect to login/register page
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        /* Optional: Keep the typing animation style if you like */
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
            <h1 class="text-3xl font-bold mb-6 text-center text-white">Your Profile</h1>

            <?php if(isset($error)): ?>
                <div class="bg-red-100 p-3 rounded-lg mb-6 text-red-700">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="mb-8 text-center text-gray-300">
                <p>Welcome, <span class="font-semibold text-white"><?= htmlspecialchars($username) ?></span>!</p>
                <p class="mt-2">This is your profile page.</p>
            </div>

            <div class="p-4 sm:p-6 rounded-2xl border border-gray-700">
                <p class="text-gray-400 mb-4">Account Details:</p>
                <div class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <i class='bx bxs-user text-xl text-blue-500'></i>
                        <span class="text-gray-300 font-medium">Username:</span>
                        <span class="text-white"><?= htmlspecialchars($username) ?></span>
                    </div>
                    <!-- You can add more profile details here later -->
                </div>

                <div class="mt-8 text-center">
                    <a href="profile.php?logout=1" class="bg-red-600 text-white py-3 px-6 rounded-xl hover:bg-red-700 transition-colors font-medium inline-block">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
