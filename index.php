<?php
require 'config.php';
require 'auth.php';

// Redirect to guest.php if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: guest.php");
    exit;
}

$messages = [];
// Fetch messages for current user on server side
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT message, response, user_id, created_at FROM messages WHERE user_id = ? ORDER BY id ASC");
        $stmt->execute([$_SESSION['user_id']]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching messages: " . $e->getMessage());
        $messages = []; // Initialize messages to prevent errors
    }
} else {
    $messages = []; // Initialize messages to prevent errors if the user is not logged in
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owntweet Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/6.0.0/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">
    <style>
        @keyframes typing {
            0% { opacity: 0.4; }
            50% { opacity: 1; }
            100% { opacity: 0.4; }
        }
        .typing-dot { animation: typing 1.5s infinite; }

        /* Custom Animations */
        @keyframes slide-in-right {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slide-in-left {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* General message styles */
        .message-animation {
            animation-duration: 0.5s; /* Increased duration to 0.5s for better visibility on page load */
            animation-fill-mode: both;
        }

        /* Specific message animations */
        .user-message {
            animation-name: slide-in-right;
        }

        .bot-message {
            animation-name: slide-in-left;
        }


        /* Typing Indicator Styles */
        .typing-indicator {
            display: flex;
            align-items: center;
        }
         .typing-indicator .typing-dot {
            width: 8px;
            height: 8px;
            background-color: #aaa;
            border-radius: 50%;
            margin: 0 2px;
            animation: typing 1.2s infinite;
            animation-delay: 0s;
        }
          .typing-indicator .typing-dot:nth-child(2) {
              animation-delay: 0.2s;
          }
         .typing-indicator .typing-dot:nth-child(3) {
              animation-delay: 0.4s;
          }

        /* Sidebar Styles */
        aside {
            height: 100vh; /* Full height */
            position: fixed; /* Fixed sidebar for desktop */
            top: 0;
            left: 0;
            z-index: 30; /* Higher z-index for sidebar */
            width: 250px; /* Adjust sidebar width as needed */
            border-right: 1px solid #4B5563; /* Border color from tailwind gray-700 */
            transform: translateX(-100%); /* Hide sidebar off-screen initially on mobile */
            transition: transform 0.3s ease-in-out; /* Smooth transition for mobile sidebar */
        }

        aside.open {
            transform: translateX(0); /* Slide in sidebar when open class is added */
        }

        /* Sidebar Backdrop for Mobile */
        #sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black backdrop */
            z-index: 25; /* Below sidebar, above main content */
            display: none; /* Hidden by default */
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        #sidebar-backdrop.open {
            display: block; /* Show backdrop when sidebar is open */
            opacity: 1;
        }


        /* Flex container for body to hold sidebar and content side by side */
        body.flex {
            display: flex;
        }

        .flex-1 { /* If not already defined */
            flex: 1;
        }

        /* Responsive adjustments for smaller screens */
        @media (min-width: 769px) { /* Desktop styles */
            aside {
                position: sticky; /* Make sidebar sticky on desktop */
                transform: translateX(0); /* Always show sidebar on desktop */
            }
            #sidebar-backdrop {
                display: none !important; /* Never show backdrop on desktop */
            }
        }

        @media (max-width: 768px) { /* Mobile styles */
            #chat-container {
                margin-left: 0; /* Reset margin for small screens */
                padding-top: 80px; /* Adjusted padding for fixed header */
                padding-bottom: 100px; /* Adjusted padding for fixed input area */
            }
             body.sidebar-open #chat-container {
                margin-left: 0; /* No margin on mobile when sidebar is open, it overlays */
            }
        }

        /* Short Pre-loader Animation Styles */
        #preloader-animation {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #111827; /* bg-gray-900 from tailwind */
            z-index: 9999; /* Make sure it's on top */
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column; /* To center text below spinner */
            animation: fadeOutPreloader 0.5s forwards 0.5s; /* Fade out after 0.5s delay, total 1s */
            opacity: 1; /* Start as fully opaque */
        }

        .loader {
            border: 8px solid #f3f3f3; /* Light grey */
            border-top: 8px solid #3498db; /* Blue */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
            margin-bottom: 20px; /* Space between spinner and text */
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #preloader-text {
            color: #fff; /* White text color */
            font-size: 1rem;
            font-weight: bold;
        }

        @keyframes fadeOutPreloader {
            to {
                opacity: 0;
                visibility: hidden; /* To fully remove from layout after animation */
            }
        }

        /* Fixed Header and Input Styles */
        .header-fixed {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 20;
        }

        .input-area-fixed {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 20;
            background-color: rgba(31, 41, 55, 0.5); /* bg-gray-800/50 fallback if backdrop-blur is not supported */
            backdrop-filter: blur(10px); /* backdrop-blur-sm equivalent */
        }

        #chat-container {
            padding-top: 80px; /* Adjust based on header height */
            padding-bottom: 100px; /* Adjust based on input area height */
        }


    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 h-screen flex">

    <!-- Pre-loader Animation Container (will be added by JS) -->
    <div id="preloader-animation">
        <div class="loader"></div>
        <div id="preloader-text">Loading...</div>
    </div>

     <!-- Sidebar Backdrop (for mobile) -->
    <div id="sidebar-backdrop"></div>

    <!-- Left Sidebar -->
    <aside class="bg-gray-800 border-r border-gray-700 flex-col">
        <div class="p-4 flex items-center justify-center border-b border-gray-700">
            <h1 class="text-xl font-semibold text-white">
                <i class='bx bxl-xing text-blue-500 align-middle'></i>
                Owntweet Chat
            </h1>
        </div>
        <nav class="flex-1 p-4">
            <ul class="space-y-2">
                <li>
                    <a href="#" class="block p-2 rounded hover:bg-gray-700 flex items-center text-gray-400 hover:text-gray-300">
                        <i class='bx bx-home align-middle mr-2'></i> Home
                    </a>
                </li>
                <li>
                    <a href="profile.php" class="block p-2 rounded hover:bg-gray-700 flex items-center text-gray-400 hover:text-gray-300">
                        <i class='bx bx-user-circle align-middle mr-2'></i> Profile
                    </a>
                </li>
                 <li>
                    <button onclick="deleteChatHistory()" class="block p-2 rounded hover:bg-gray-700 flex items-center text-red-500 hover:text-red-300 w-full text-left">
                        <i class='bx bx-trash align-middle mr-2'></i> Delete Chat
                    </button>
                </li>
                <li>
                    <a href="index.php?logout=1" class="block p-2 rounded hover:bg-gray-700 flex items-center text-gray-400 hover:text-gray-300">
                        <i class='bx bx-log-out align-middle mr-2'></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex flex-col h-screen flex-1">
        <!-- Header -->
        <div class="bg-gray-800 border-b border-gray-700 p-4 flex justify-between items-center header-fixed">
            <div class="flex items-center">
                <button id="sidebar-toggle" class="text-gray-400 hover:text-gray-300 mr-4 md:hidden">  <!-- Hidden on medium and up -->
                    <i class='bx bx-menu text-2xl'></i>
                </button>
                <h2 class="text-xl font-semibold text-white">Chat</h2>
            </div>
            <div class="space-x-3 flex items-center">
                <a href="profile.php" title="Profile" class="text-gray-400 hover:text-gray-300 flex items-center">
                    <i class='bx bx-user-circle text-2xl mr-1'></i>
                    <span>Profile</span>
                </a>
                <button onclick="deleteChatHistory()" title="Delete All" class="text-red-500 hover:text-red-300">
                    <i class='bx bx-trash text-2xl'></i>
                </button>
                <a href="index.php?logout=1" title="Logout" class="text-gray-400 hover:text-gray-300">
                    <i class='bx bx-log-out text-2xl'></i>
                </a>
            </div>
        </div>

        <!-- Chat Container -->
        <div id="chat-container" class="flex-1 overflow-y-auto p-4 space-y-4 scroll-smooth">
            <!-- Messages will be dynamically added here -->
            <?php foreach($messages as $msg): ?>
                <div class="message-animation flex <?= ($msg['user_id'] == $_SESSION['user_id']) ? 'justify-end user-message' : 'justify-start bot-message' ?> mb-4">
                    <div class="max-w-[90%] md:max-w-[70%]">
                        <div class="<?= ($msg['user_id'] == $_SESSION['user_id']) ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-100' ?> px-4 py-3 rounded-2xl <?= ($msg['user_id'] == $_SESSION['user_id']) ? 'rounded-br-none' : 'rounded-bl-none' ?> message-container">
                             <?php if($msg['user_id'] == $_SESSION['user_id']): ?>
                                  <?= htmlspecialchars($msg['message']) ?>
                               <?php else: ?>
                                    <div class="message-content"><?= htmlspecialchars($msg['response']) ?></div>
                              <?php endif; ?>
                       </div>
                        <div class="text-xs text-gray-400 mt-1 <?= ($msg['user_id'] == $_SESSION['user_id']) ? 'text-right' : '' ?>">
                               <?=  date('h:i A', strtotime($msg['created_at'])) ?>
                               <?php if($msg['user_id'] != $_SESSION['user_id']): ?>
                                  <button onclick="copyMessage(this)" class="inline-block ml-2 text-gray-500 hover:text-gray-400">
                                     <i class='bx bx-copy'></i>
                                  </button>
                               <?php endif; ?>
                        </div>
                    </div>
                </div>
<!-- Don't Remove this Duplicate response, it's showing need-->
            <div class="flex justify-start mb-4 message-animation bot-message">  <!-- Added message-animation and bot-message classes here -->
                <div class="max-w-[90%] md:max-w-[70%]">
                    <div class="bg-gray-700 text-gray-100 px-4 py-3 rounded-2xl rounded-bl-none">
                       <div class="message-content">
                       <?php if(isset($msg['response'])): ?>
                           <?= htmlspecialchars($msg['response']) ?>
                       <?php endif; ?>
                       </div>
                    </div>
                     <div class="text-xs text-gray-400 mt-1 ">
                          <?=  date('h:i A', strtotime($msg['created_at'])) ?>
                            <button onclick="copyMessage(this)" class="inline-block ml-2 text-gray-500 hover:text-gray-400">
                                  <i class='bx bx-copy'></i>
                           </button>
                     </div>
                </div>
            </div>

            <?php endforeach; ?>
              <div id="typing-indicator-container"></div> <!-- Container for typing indicator -->

        </div>

        <!-- Input Area -->
        <div class="bg-gray-800/50 backdrop-blur-sm border-t border-gray-700 p-4 input-area-fixed">
            <form id="chat-form" class="flex gap-3 items-center">
                <div class="flex-1 relative">
                    <textarea id="message-input"
                        class="w-full bg-gray-700/50 border border-gray-600 rounded-2xl py-3 px-5 pr-12
                                text-white placeholder-gray-400 focus:outline-none focus:border-blue-500
                                transition-colors resize-none overflow-hidden"
                        placeholder="Message something..."
                        autocomplete="off"
                        rows="2"
                        style="max-height: 150px;"
                        required></textarea>
                    <div class="absolute right-3 bottom-3 flex items-center gap-2">
                         <button type="submit" class="text-blue-400 hover:text-blue-300">
                            <i class='bx bx-send text-xl'></i>
                         </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-800 border-t border-gray-700 p-2 text-center text-gray-400 text-xs">
            Owntweet Chatbot can make mistakes.
        </footer>
    </div>

       <script>
    const chatContainer = document.getElementById('chat-container');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('message-input');
    const sidebarToggle = document.getElementById('sidebar-toggle'); // Sidebar toggle button
    const body = document.querySelector('body'); // Body element for sidebar toggle class
    const preloaderAnimation = document.getElementById('preloader-animation'); // Preloader element
    const sidebar = document.querySelector('aside'); // Sidebar element
    const sidebarBackdrop = document.getElementById('sidebar-backdrop'); // Sidebar backdrop


    // Sidebar toggle functionality
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent body click event from firing immediately
            sidebar.classList.toggle('open'); // Toggle open class on sidebar
            sidebarBackdrop.classList.toggle('open'); // Toggle open class on backdrop
            body.classList.toggle('sidebar-open'); // Toggle sidebar-open on body for potential CSS adjustments
        });
    }

    // Close sidebar when clicking outside on mobile
    sidebarBackdrop.addEventListener('click', () => {
        sidebar.classList.remove('open');
        sidebarBackdrop.classList.remove('open');
        body.classList.remove('sidebar-open');
    });

    // Prevent backdrop from closing sidebar when clicking inside sidebar
    sidebar.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent click from propagating to backdrop
    });


    // Add new message to chat
    function addMessage(message, isUser = true, status = null) {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message-animation');
        messageDiv.classList.add(isUser ? 'user-message' : 'bot-message');  // Apply animation class

        const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        let messageContentHTML = '';
        if (isUser) {
            messageContentHTML = message;
        } else if (status === 'thinking') {
            messageContentHTML = '<div class="typing-indicator"><span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span></div>';
        } else {
            messageContentHTML = '<div class="message-content">' + marked.parse(message) + '</div>'; // Apply marked.parse here
        }

        messageDiv.innerHTML = `
            <div class="flex ${isUser ? 'justify-end' : 'justify-start'} mb-4">
                <div class="max-w-[90%] md:max-w-[70%]">
                    <div class="${isUser ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-100'}
                        px-4 py-3 rounded-2xl ${isUser ? 'rounded-br-none' : 'rounded-bl-none'} message-container">
                    </div>
                    <div class="text-xs text-gray-400 mt-1 ${isUser ? 'text-right' : ''}">
                        ${timestamp}
                        ${!isUser ? `<button onclick="copyMessage(this)" class="inline-block ml-2 text-gray-500 hover:text-gray-400">
                                    <i class='bx bx-copy'></i>
                                </button>` : ''}
                    </div>
                </div>
            </div>
        `;

        if (isUser) {
            messageDiv.querySelector('.message-container').textContent = messageContentHTML;
        } else {
            messageDiv.querySelector('.message-container').innerHTML = messageContentHTML;
        }


        chatContainer.appendChild(messageDiv);
        // Ensure scroll to bottom after message is added
        chatContainer.scrollTop = chatContainer.scrollHeight;


        // Apply syntax highlighting after adding the message
        if (!isUser && status !== 'thinking') {
            messageDiv.querySelectorAll('pre code').forEach(el => {
                hljs.highlightElement(el);
            });
        }
    }


    function copyMessage(button) {
        const messageContent = button.closest('.flex').querySelector('.message-content');
        const text = messageContent.textContent.trim();
        navigator.clipboard.writeText(text).then(() => {
            // Optional: Provide user feedback (e.g., tooltip)
        }).catch(err => {
            console.error('Failed to copy message: ', err);
        });
    }


    // Handle form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = input.value.trim();
        if(!message) return;

        input.value = ''; // Clear input
        input.style.height = 'auto'; // Reset height to allow shrinking

        addMessage(message, true);
         addMessage('', false, 'thinking'); // Add 'thinking' message

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message })
            });

               if (!response.ok) {
                    const lastMessage = chatContainer.lastElementChild;
                  lastMessage.querySelector('.message-container').innerHTML =
                    "Sorry, I'm having trouble responding right now. Please try again.";
                     chatContainer.removeChild(lastMessage);
                     addMessage("Sorry, I'm having trouble responding right now. Please try again.", false);
                   return;
                }

              const data = await response.json();
            const lastMessage = chatContainer.lastElementChild;

        chatContainer.removeChild(lastMessage);
         addMessage(data.response, false); // response show
        } catch (error) {
              console.error('Error:', error);
              const lastMessage = chatContainer.lastElementChild;
               lastMessage.querySelector('.message-container').innerHTML =
               "Sorry, I'm having trouble responding right now. Please try again.";
                chatContainer.removeChild(lastMessage);
               addMessage("Sorry, I'm having trouble responding right now. Please try again.", false);
        }
    });

    async function deleteChatHistory() {
        if (confirm("Are you sure you want to delete all chat history?")) {
            try {
                const response = await fetch('api.php?delete_history=1', { method: 'DELETE' });
                if (response.ok) {
                    chatContainer.innerHTML = '';
                    location.reload();
                } else {
                    console.error('Failed to delete chat history.');
                }
            } catch (error) {
                console.error('Error deleting chat history:', error);
            }
        }
    }


    input.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // initial highlight when page load and remove pre-loader
     document.addEventListener('DOMContentLoaded', (event) => {
         hljs.highlightAll();
         // Scroll to bottom after initial load
         chatContainer.scrollTop = chatContainer.scrollHeight;
         setTimeout(() => {
             preloaderAnimation.style.display = 'none'; // Hide preloader after 1s (animation duration)
         }, 1000);
     });
    </script>
</body>
</html>
