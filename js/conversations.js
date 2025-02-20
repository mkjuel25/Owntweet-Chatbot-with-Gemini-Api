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
