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
    <title>Gemini Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/6.0.0/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
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
<body class="bg-gradient-to-br from-gray-900 to-gray-800 h-screen">
    <div class="flex flex-col h-screen">
        <!-- Header -->
         <div class="bg-gray-800 border-b border-gray-700 p-4 flex justify-between items-center">
                <h1 class="text-xl font-semibold text-white">Gemini Chat</h1>
                  <div class="space-x-3 flex items-center">
                      <a href="profile.php" title="Profile" class="text-gray-400 hover:text-gray-300 flex items-center">
                          <i class='bx bx-user-circle text-2xl mr-1' ></i>
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
            <?php foreach($messages as $msg): ?>
                <div class="flex <?= ($msg['user_id'] == $_SESSION['user_id']) ? 'justify-end' : 'justify-start' ?> mb-4">
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
  
  <!-- Duplicate response show because don't showing response when page refresh 😞 -->
            <div class="flex justify-start mb-4">
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

        </div>

        <!-- Input Area -->
        <div class="bg-gray-800/50 backdrop-blur-sm border-t border-gray-700 p-4">
            <form id="chat-form" class="flex gap-3 items-center">
                <div class="flex-1 relative">
                    <textarea id="message-input"
                        class="w-full bg-gray-700/50 border border-gray-600 rounded-2xl py-3 px-5 pr-12
                                text-white placeholder-gray-400 focus:outline-none focus:border-blue-500
                                transition-colors resize-none overflow-hidden"
                        placeholder="Message Gemini..."
                        autocomplete="off"
                        rows="2"
                        style="max-height: 150px;"
                        required></textarea>
                       <div class="absolute right-3 bottom-3 flex items-center gap-2">
                          <!-- REMOVED CLEAR INPUT BUTTON HERE -->
                         <button type="submit" class="text-blue-400 hover:text-blue-300">
                            <i class='bx bx-send text-xl'></i>
                         </button>
                      </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const chatContainer = document.getElementById('chat-container');
        const form = document.getElementById('chat-form');
        const input = document.getElementById('message-input');

        // Add new message to chat
        function addMessage(message, isUser = true, status = null) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message-animation';
            const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            let messageContentHTML = ''; // Changed variable name to clarify HTML context
            let responseDiv = '';

            if (isUser) {
                messageContentHTML = message; // Assign message to HTML variable
            } else if (status === 'thinking') {
                messageContentHTML = '<div class="typing-indicator"></div>'; // Placeholder for 'thinking'
            } else {
                messageContentHTML = '<div class="message-content">' + marked.parse(message) + '</div>'; // For AI response, use message-content div and marked.parse
            }

            messageDiv.innerHTML = `
                <div class="flex ${isUser ? 'justify-end' : 'justify-start'} mb-4">
                    <div class="max-w-[90%] md:max-w-[70%]">
                        <div class="${isUser ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-100'}
                            px-4 py-3 rounded-2xl ${isUser ? 'rounded-br-none' : 'rounded-bl-none'} message-container">  <!-- Added class message-container -->
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

            // Set textContent for user messages to ensure plain text rendering
            if (isUser) {
                messageDiv.querySelector('.message-container').textContent = messageContentHTML; // Use textContent for user message
            } else {
                messageDiv.querySelector('.message-container').innerHTML = messageContentHTML; // Use innerHTML for AI response (Markdown parsing)
            }


            chatContainer.appendChild(messageDiv);
            messageDiv.scrollIntoView({ behavior: 'smooth' });
            if (!isUser && status !== 'thinking') {
                hljs.highlightAll();
            }
        }
        // Show typing indicator
         function showTypingIndicator() {
            const typingIndicator = document.createElement('div');
            typingIndicator.className = 'flex gap-1 px-4 py-3';
            typingIndicator.innerHTML = `
                <div class="typing-dot" style="animation-delay: 0s"></div>
                <div class="typing-dot" style="animation-delay: 0.2s"></div>
                <div class="typing-dot" style="animation-delay: 0.4s"></div>
            `;

             const lastMessage = chatContainer.lastElementChild;
             if(lastMessage) {
              lastMessage.querySelector('.typing-indicator').appendChild(typingIndicator);
          }
         }

        function addMessageToChat(message, response, user_id, created_at) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message-animation';
             const timestamp = created_at ? new Date(created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            let responseDiv = '';
            if(user_id != '<?=$_SESSION['user_id']?>'){
                responseDiv = '<div class="message-content">' +  marked.parse(response) + '</div>';
           }

             messageDiv.innerHTML = `
            <div class="flex ${user_id == '<?=$_SESSION['user_id']?>' ? 'justify-end' : 'justify-start'} mb-4">
                <div class="max-w-[90%] md:max-w-[70%]">
                    <div class="${user_id == '<?=$_SESSION['user_id']?>' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-100'}
                        px-4 py-3 rounded-2xl ${user_id == '<?=$_SESSION['user_id']?>' ? 'rounded-br-none' : 'rounded-bl-none'}">
                           ${user_id == '<?=$_SESSION['user_id']?>' ? message : ''}
                           ${responseDiv}
                    </div>
                      <div class="text-xs text-gray-400 mt-1 ${user_id == '<?=$_SESSION['user_id']?>' ? 'text-right' : ''}">
                        ${timestamp}
                          ${user_id !== '<?=$_SESSION['user_id']?>' ? `<button onclick="copyMessage(this)" class="inline-block ml-2 text-gray-500 hover:text-gray-400">
                            <i class='bx bx-copy'></i>
                         </button>` : ''}
                    </div>
                </div>
            </div>
         `;
             chatContainer.appendChild(messageDiv);
              messageDiv.scrollIntoView({ behavior: 'smooth' });
           // Apply syntax highlighting
             hljs.highlightAll();
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
                      lastMessage.querySelector('.bg-gray-700').innerHTML =
                        "Sorry, I'm having trouble responding right now. Please try again.";
                       return;
                    }

                  const data = await response.json();
                const lastMessage = chatContainer.lastElementChild;

            chatContainer.removeChild(lastMessage);
             addMessage(data.response, false);

            } catch (error) {
                 console.error('Error:', error);
                    const lastMessage = chatContainer.lastElementChild;
                    lastMessage.querySelector('.bg-gray-700').innerHTML =
                    "Sorry, I'm having trouble responding right now. Please try again.";
            }
        });

        function copyMessage(button) {
    const messageContent = button.closest('.flex').querySelector('.message-content');
    const text = messageContent.textContent.trim(); // Remove extra spaces
    navigator.clipboard.writeText(text).then(() => {

    }).catch(err => {
        console.error('Failed to copy message: ', err);
    });
}


       async function deleteChatHistory() {
        if (confirm("Are you sure you want to delete all chat history?")) {
            try {
                const response = await fetch('api.php?delete_history=1', { method: 'DELETE' });
                 if (response.ok) {
                     chatContainer.innerHTML = ''; // Clear the chat container
                     location.reload(); // Option reload page
                      console.log('Chat history deleted.');
                } else {
                    console.error('Failed to delete chat history.');
                 }
             } catch (error) {
                 console.error('Error deleting chat history:', error);
            }
        }
      }

         function clearInput() {
            input.value = '';
              input.style.height = 'auto'; // Reset height to allow shrinking
         }

        input.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    </script>
</body>
</html>
