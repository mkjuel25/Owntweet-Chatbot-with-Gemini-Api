<?php
require 'config.php'; // Include database configuration
require 'auth.php';  // Include authentication functions

// Check if user is logged in (optional, you might want to restrict this page to admins only)
if (!isset($_SESSION['user_id'])) {
    header("Location: guest.php");
    exit;
}

try {
    // --- Fetch User Count ---
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM users");
    $stmtCount->execute();
    $totalUsers = $stmtCount->fetchColumn();

    // --- Fetch Users Registered in Last 7 Days Count ---
    $stmtLastWeekCount = $pdo->prepare("SELECT COUNT(*) FROM users WHERE created_at >= DATE(NOW()) - INTERVAL 7 DAY");
    $stmtLastWeekCount->execute();
    $lastWeekUsers = $stmtLastWeekCount->fetchColumn();

    // --- Fetch User List ---
    $stmtUsers = $pdo->prepare("SELECT username, created_at FROM users ORDER BY created_at DESC");
    $stmtUsers->execute();
    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error fetching data.";
    // Log the error for debugging: error_log("Database error in user_list.php: " . $e->getMessage());
    $users = [];
    $totalUsers = 0;
    $lastWeekUsers = 0; // Initialize counts to 0 in case of error
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User List</title>
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

        /* --- Unique Design Styles --- (Same as before) */
        .unique-header {
            background: linear-gradient(to right, #3B82F6, #6366F1); /* Gradient Header */
            padding: 1.5rem 2rem; /* Increased padding */
            border-radius: 1.5rem; /* More rounded header */
            margin-bottom: 2rem; /* Increased bottom margin */
            box-shadow: 0 4px 8px rgba(0,0,0,0.2); /* Added shadow */
        }

        .unique-card {
            background-color: #2D3748; /* Darker card background */
            border-width: 1px; /* Thinner border */
            border-color: #4A5568; /* Darker border color */
            border-radius: 1rem; /* Slightly less rounded cards */
            box-shadow: 0 2px 4px rgba(0,0,0,0.15); /* Subtler shadow */
        }

        .unique-card-hover:hover {
            box-shadow: 0 8px 16px rgba(0,0,0,0.3); /* Stronger hover shadow */
        }

        .stat-card {
            background-color: #2D3748;
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #CBD5E0; /* Lighter text for value */
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: #718096; /* Grayish label text */
        }


    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="container mx-auto max-w-5xl w-full">
        <div class="bg-gray-800/70 backdrop-blur-lg rounded-3xl shadow-2xl p-6 sm:p-8 animate-slide-in">

            <!-- Unique Header Section (Same as before) -->
            <div class="unique-header text-white mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">User Management</h1>
                        <p class="text-gray-200 mt-2">Overview of registered users and statistics</p>
                    </div>
                    <div>
                        <a href="index.php" class="p-2 rounded-lg bg-gray-700 hover:bg-gray-600 transition-colors">
                            <i class='bx bx-arrow-back text-2xl text-blue-300'></i>
                        </a>
                    </div>
                </div>

                <!-- User Statistics Cards (Same as before) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-4 mt-6">
                    <div class="stat-card">
                        <div class="stat-value"><?= $totalUsers ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $lastWeekUsers ?></div>
                        <div class="stat-label">Users Last 7 Days</div>
                    </div>
                    <!-- You can add more stat cards here if needed -->
                </div>
            </div>


            <!-- Error Message (if any) (Same as before) -->
            <?php if(isset($error)): ?>
                <div class="bg-red-500/20 p-4 rounded-xl mb-6 border border-red-500/30 flex items-center space-x-3">
                    <i class='bx bx-x-circle text-red-400'></i>
                    <span class="text-red-300"><?= $error ?></span>
                </div>
            <?php endif; ?>

            <!-- User List Table -->
            <div class="unique-card unique-card-hover overflow-x-auto">
                <table class="w-full text-left text-gray-400">
                    <thead class="text-gray-300 uppercase">
                        <tr>
                            <th scope="col" class="py-3 px-4">No.</th> <!-- New Serial No. Header -->
                            <th scope="col" class="py-3 px-4">Username</th>
                            <th scope="col" class="py-3 px-4">Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php $serialNumber = 1; // Initialize serial number counter ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="border-b border-gray-700">
                                    <td class="py-4 px-4"><?= $serialNumber++ ?></td> <!-- Display serial number and increment -->
                                    <td class="py-4 px-4"><?= htmlspecialchars($user['username']) ?></td>
                                    <td class="py-4 px-4">
                                        <?php
                                            if (isset($user['created_at'])) {
                                                echo date("Y-m-d H:i:s", strtotime($user['created_at']));
                                            } else {
                                                echo 'N/A';
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="py-4 px-4 text-center">No users found.</td> <!-- colspan updated to 3 -->
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</body>
</html>
