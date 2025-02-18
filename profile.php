<?php
require 'config.php'; // Include database configuration
require 'auth.php';  // Include authentication functions

// Check if user is logged in. If not, redirect to guest.php (login/register page)
if (!isset($_SESSION['user_id'])) {
    header("Location: guest.php");
    exit;
}

// Fetch user data (username, first name, last name, email)
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, first_name, last_name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Handle case where user data cannot be found
    $error = "Error fetching user data.";
    session_destroy();
    header("Location: guest.php?error=" . urlencode($error));
    exit;
}

$username = htmlspecialchars($user['username']);
$first_name = htmlspecialchars($user['first_name'] ?? ''); // Use null coalescing operator for optional fields
$last_name = htmlspecialchars($user['last_name'] ?? '');
$email = htmlspecialchars($user['email']);

// Initialize messages
$prompt_success_message = null;
$prompt_error_message = null;
$profile_success_message = null;
$profile_error_message = null;
$password_success_message = null;
$password_error_message = null;


// Handle saving system prompt
if (isset($_POST['save_prompt'])) {
    $new_prompt = filter_input(INPUT_POST, 'system_prompt_text', FILTER_SANITIZE_STRING);

    try {
        $stmtCheckPrompt = $pdo->prepare("SELECT id FROM saved_prompts WHERE user_id = ?");
        $stmtCheckPrompt->execute([$user_id]);
        $existingPrompt = $stmtCheckPrompt->fetch(PDO::FETCH_ASSOC);

        if ($existingPrompt) {
            // Update existing prompt
            $stmtUpdatePrompt = $pdo->prepare("UPDATE saved_prompts SET system_prompt_text = ? WHERE user_id = ?");
            $stmtUpdatePrompt->execute([$new_prompt, $user_id]);
        } else {
            // Insert new prompt
            $stmtInsertPrompt = $pdo->prepare("INSERT INTO saved_prompts (user_id, system_prompt_text) VALUES (?, ?)");
            $stmtInsertPrompt->execute([$user_id, $new_prompt]);
        }
        $prompt_success_message = "System prompt saved successfully!";
        // $savedSystemPrompt = $new_prompt; // No need to refetch, already updated
    } catch (PDOException $e) {
        $prompt_error_message = "Error saving system prompt.";
    }
}

// Fetch user's saved system prompt (after potentially saving it)
$stmtPrompt = $pdo->prepare("SELECT system_prompt_text FROM saved_prompts WHERE user_id = ?");
$stmtPrompt->execute([$user_id]);
$savedPromptResult = $stmtPrompt->fetch(PDO::FETCH_ASSOC);
$savedSystemPrompt = $savedPromptResult ? $savedPromptResult['system_prompt_text'] : '';


// Handle update profile details
if (isset($_POST['edit_profile'])) {
    $edit_username = filter_input(INPUT_POST, 'edit_username', FILTER_SANITIZE_STRING);
    $edit_first_name = filter_input(INPUT_POST, 'edit_first_name', FILTER_SANITIZE_STRING);
    $edit_last_name = filter_input(INPUT_POST, 'edit_last_name', FILTER_SANITIZE_STRING);
    $edit_email = filter_input(INPUT_POST, 'edit_email', FILTER_SANITIZE_EMAIL);

    if (!filter_var($edit_email, FILTER_VALIDATE_EMAIL)) {
        $profile_error_message = "Invalid email format.";
    } else {
        try {
            $stmtUpdateProfile = $pdo->prepare("UPDATE users SET username = ?, first_name = ?, last_name = ?, email = ? WHERE id = ?");
            $stmtUpdateProfile->execute([$edit_username, $edit_first_name, $edit_last_name, $edit_email, $user_id]);
            $profile_success_message = "Profile updated successfully!";

            // Refetch user data to update displayed values
            $stmt = $pdo->prepare("SELECT username, first_name, last_name, email FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $username = htmlspecialchars($user['username']);
            $first_name = htmlspecialchars($user['first_name'] ?? '');
            $last_name = htmlspecialchars($user['last_name'] ?? '');
            $email = htmlspecialchars($user['email']);


        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'username')) {
                $profile_error_message = "Username already exists.";
            } elseif ($e->getCode() == 23000 && strpos($e->getMessage(), 'email')) {
                $profile_error_message = "Email already exists.";
            } else {
                $profile_error_message = "Error updating profile.";
            }
        }
    }
}


// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    if ($new_password !== $confirm_new_password) {
        $password_error_message = "New passwords do not match.";
    } else {
        try {
            $stmtCheckPassword = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmtCheckPassword->execute([$user_id]);
            $db_password_hash = $stmtCheckPassword->fetchColumn();

            if (password_verify($current_password, $db_password_hash)) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmtUpdatePassword = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmtUpdatePassword->execute([$hashed_new_password, $user_id]);
                $password_success_message = "Password changed successfully!";
            } else {
                $password_error_message = "Incorrect current password.";
            }
        } catch (PDOException $e) {
            $password_error_message = "Error changing password.";
        }
    }
}


// Handle logout request
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: guest.php");
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">
    <style>
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .animate-slide-in {
            animation: slideIn 0.6s ease-out forwards;
        }

        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }

        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="container mx-auto max-w-2xl w-full">
        <div class="bg-gray-800/70 backdrop-blur-lg rounded-3xl shadow-2xl p-6 sm:p-8 animate-slide-in">
            <!-- Header Section -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Profile Settings</h1>
                    <p class="text-gray-400 mt-2">Welcome back, <?= $username ?></p>
                </div>
                <div class="flex space-x-3">
                    <a href="index.php" class="p-2 rounded-lg bg-gray-700 hover:bg-gray-600 transition-colors">
                        <i class='bx bx-robot text-2xl text-blue-400'>
                        
                <i class='bx bxl-xing text-blue-500 align-middle'></i> 
                     </i>
                    </a>
                    <a href="?logout=1" class="p-2 rounded-lg bg-gray-700 hover:bg-gray-600 transition-colors">
                        <i class='bx bx-log-out text-2xl text-red-400'></i>
                    </a>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if(isset($prompt_success_message)): ?>
                <div class="bg-emerald-500/20 p-4 rounded-xl mb-6 border border-emerald-500/30 flex items-center space-x-3">
                    <i class='bx bx-check-circle text-emerald-400'></i>
                    <span class="text-emerald-300"><?= $prompt_success_message ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Repeat similar blocks for other messages -->

            <!-- Profile Details Card -->
            <div class="card-hover bg-gray-700/30 rounded-2xl p-6 mb-6 border border-gray-600/30">
                <div class="grid grid-cols-2 gap-4 text-gray-300">
                    <div class="flex items-center space-x-3">
                        <i class='bx bx-user text-blue-400'></i>
                        <span><?= $username ?></span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class='bx bx-envelope text-blue-400'></i>
                        <span><?= $email ?></span>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Form -->
            <div class="card-hover bg-gray-700/30 rounded-2xl p-6 mb-6 border border-gray-600/30">
                <form method="POST">
                    <input type="hidden" name="edit_profile" value="1">
                    <h2 class="text-xl font-semibold text-white mb-6 flex items-center space-x-2">
                        <i class='bx bx-edit-alt text-blue-400'></i>
                        <span>Edit Profile</span>
                    </h2>
                    
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Username</label>
                            <input type="text" name="edit_username" value="<?= $username ?>" 
                                   class="w-full bg-gray-800/50 border border-gray-600/30 rounded-xl px-4 py-3 text-white input-focus">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Email</label>
                            <input type="email" name="edit_email" value="<?= $email ?>"
                                   class="w-full bg-gray-800/50 border border-gray-600/30 rounded-xl px-4 py-3 text-white input-focus">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">First Name</label>
                            <input type="text" name="edit_first_name" value="<?= $first_name ?>"
                                   class="w-full bg-gray-800/50 border border-gray-600/30 rounded-xl px-4 py-3 text-white input-focus">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Last Name</label>
                            <input type="text" name="edit_last_name" value="<?= $last_name ?>"
                                   class="w-full bg-gray-800/50 border border-gray-600/30 rounded-xl px-4 py-3 text-white input-focus">
                        </div>
                    </div>
                    
                    <button type="submit" class="mt-6 w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-3 px-6 rounded-xl font-medium transition-all">
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- Password Change Form -->
            <div class="card-hover bg-gray-700/30 rounded-2xl p-6 mb-6 border border-gray-600/30">
                <form method="POST">
                    <input type="hidden" name="change_password" value="1">
                    <h2 class="text-xl font-semibold text-white mb-6 flex items-center space-x-2">
                        <i class='bx bx-lock-alt text-blue-400'></i>
                        <span>Change Password</span>
                    </h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Current Password</label>
                            <input type="password" name="current_password"
                                   class="w-full bg-gray-800/50 border border-gray-600/30 rounded-xl px-4 py-3 text-white input-focus">
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">New Password</label>
                                <input type="password" name="new_password"
                                       class="w-full bg-gray-800/50 border border-gray-600/30 rounded-xl px-4 py-3 text-white input-focus">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Confirm Password</label>
                                <input type="password" name="confirm_new_password"
                                       class="w-full bg-gray-800/50 border border-gray-600/30 rounded-xl px-4 py-3 text-white input-focus">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="mt-6 w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-3 px-6 rounded-xl font-medium transition-all">
                        Change Password
                    </button>
                </form>
            </div>

            <!-- System Prompt Section -->
            <div class="card-hover bg-gray-700/30 rounded-2xl p-6 border border-gray-600/30">
                <form method="POST">
                    <input type="hidden" name="save_prompt" value="1">
                    <h2 class="text-xl font-semibold text-white mb-6 flex items-center space-x-2">
                        <i class='bx bx-message-alt-edit text-blue-400'></i>
                        <span>AI Preferences</span>
                    </h2>
                    
                    <div class="mb-4">
                        <label class="block text-sm text-gray-400 mb-2">Custom System Prompt</label>
                        <textarea name="system_prompt_text" rows="4"
                                  class="w-full bg-gray-800/50 border border-gray-600/30 rounded-xl px-4 py-3 text-white input-focus"><?= $savedSystemPrompt ?></textarea>
                        <p class="text-gray-500 text-sm mt-2">This prompt helps guide the AI's behavior. Leave blank for default settings.</p>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-3 px-6 rounded-xl font-medium transition-all">
                        Save AI Preferences
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
