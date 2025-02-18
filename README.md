# Owntweet-Chatbot-with-Gemini-Api

Description:
This is a fully open-source AI Chatbot project created by Jewel, with credit to Owntweet social.
It utilizes the Google Gemini API to provide conversational responses within a web-based chat interface.

Features:

* User Authentication: Secure registration, login, and logout functionality to protect user data and chat history.
* Chat History: Persistent chat logs are stored and retrieved, ensuring users can access their previous conversations.
* Google Gemini Integration: Leverages the power of the Google Gemini API for intelligent and engaging conversational responses.
* Real-Time Experience:
    * Typing indicators simulate real-time interaction as the AI generates responses.
    * Dynamic chat interface updates seamlessly with new messages for a smooth conversation flow.
* Markdown Support: AI responses are parsed using Markdown to render styled text formatting (e.g., bold, italics, lists).
* Code Highlighting: Code snippets within AI responses are automatically syntax-highlighted using Highlight.js for readability.
* User-Friendly Tools:
    * Copy Message: Easily copy specific AI responses for quick sharing or reference.
    * Copy All Chat: Quickly grab the entire conversation history for record-keeping or sharing.
    * Delete Chat History: Allows users to remove unwanted conversations and manage their data.
* Responsive Design: Built with Tailwind CSS for a mobile-friendly and responsive interface that works well on various devices.

Technologies Used:

* Backend: PHP, MySQL
* Frontend: HTML, CSS (Tailwind CSS), JavaScript
* APIs/Libraries: Google Gemini API, Highlight.js, Marked.js

Setup Instructions:

Before you begin, ensure you have:
* A web server running PHP.
* MySQL database server.
* A Google Gemini API key.

Follow these steps to set up the Owntweet Chatbot:

1. Database Setup:
   * Access your MySQL database server (e.g., using phpMyAdmin, MySQL command line, or a database management tool).
   * Create a new database named: Owntweet_chatbot
   * Create a new database user named: chatbot
   * Set the password for the 'chatbot' user to: password
   * Grant the 'chatbot' user ALL PRIVILEGES to the 'Owntweet_chatbot' database.
   * Create the necessary tables by executing the following SQL statements. You can use phpMyAdmin's SQL tab or a similar tool:

     -- users table
     CREATE TABLE users (
         id INT AUTO_INCREMENT PRIMARY KEY,
         username VARCHAR(255) UNIQUE,
         password VARCHAR(255),
         email VARCHAR(255) UNIQUE,
         first_name VARCHAR(255), -- Optional, for profile details
         last_name VARCHAR(255)   -- Optional, for profile details
     );

     -- messages table
     CREATE TABLE messages (
         id INT AUTO_INCREMENT PRIMARY KEY,
         user_id INT,
         message TEXT,
         response TEXT,
         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
     );

     -- remember_me_tokens table (for "Remember Me" functionality)
     CREATE TABLE remember_me_tokens (
         id INT AUTO_INCREMENT PRIMARY KEY,
         user_id INT,
         token VARCHAR(255) UNIQUE,
         expiry_time DATETIME
     );

     -- saved_prompts table (for user-specific system prompts)
     CREATE TABLE saved_prompts (
         id INT AUTO_INCREMENT PRIMARY KEY,
         user_id INT UNIQUE,
         system_prompt_text TEXT
     );


2. Obtain Google Gemini API Key:
   * Go to the Google Cloud Console (console.cloud.google.com).
   * Create or select a project.
   * Enable the Gemini API for your project.
   * Create API credentials (API key).
   * Copy your generated Gemini API key.

3. Configuration:
   * Place all the files from the Owntweet-Chatbot-with-Gemini-Api project into a directory on your web server (e.g., `/var/www/html/chatbot/` or `/chatbot/` in your web root).
   * Open the `config.php` file in a text editor.
   * Update the following variables with your database and API key information:

     ```php
     <?php
     session_start();
     $host = "localhost";       // Your MySQL server host (usually localhost)
     $dbname = "Owntweet_chatbot"; // The database name you created
     $user = "chatbot";          // The database user you created
     $pass = "password";        // The database password you set

     $gemini_api_key = "YOUR_GEMINI_API_KEY"; // Replace with your actual Gemini API key

     // ... (rest of the config.php code)
     ?>
     ```
     * Replace `YOUR_GEMINI_API_KEY` with the Gemini API key you obtained in step 2.
     * Ensure the database credentials (`$host`, `$dbname`, `$user`, `$pass`) match your MySQL setup.

4. Access the Chatbot:
   * Open your web browser and navigate to the `guest.php` file in your chatbot directory. For example, if you placed the files in `/chatbot/` under your web root and your domain is `example.com`, you would go to: `http://example.com/chatbot/guest.php`
   * On the `guest.php` page, you can register a new account or log in if you already have one.
   * After logging in, you will be redirected to the main chat interface (`index.php`) where you can start chatting.

How to Work/Use:

1. Registration and Login:
   * If you are a new user, click on the "Register" button on the `guest.php` page.
   * Fill in the registration form with a username (maximum 10 characters), email, and password.
   * Click "Register" to create your account.
   * If you already have an account, click on the "Login" button.
   * Enter your username or email and password in the login form.
   * You can check "Remember Me" to stay logged in for future sessions.
   * Click "Login" to access the chatbot.

2. Chatting:
   * Once logged in, you will see the chat interface on `index.php`.
   * Type your message in the "Message something..." input area at the bottom of the screen.
   * Press Enter or click the send icon (paper airplane) to send your message.
   * The chatbot will display your message and then generate a response from the Gemini API.
   * AI responses will appear in the chat interface, often formatted with Markdown and code highlighting.
   * Scroll up and down in the chat area to view the conversation history.

3. User Profile and Settings:
   * Click on the "Profile" link in the header to go to the `profile.php` page.
   * On the profile page, you can:
     * View your username and email.
     * Edit your profile details (username, first name, last name, email).
     * Change your password.
     * Customize the AI's behavior by setting a "Custom System Prompt". This prompt allows you to guide the AI's responses. Leave it blank to use the default system prompt.
   * Click "Save AI Preferences" to apply your custom system prompt.
   * Click "Update Profile" to save changes to your profile details.
   * Click "Change Password" to update your password.

4. Chat Actions:
   * Copy Message: For AI responses, you'll see a copy icon (clipboard) next to the timestamp. Click it to copy the specific AI message to your clipboard.
   * Delete Chat History: Click the trash can icon in the header to delete your entire chat history. You will be asked to confirm this action.
   * Logout: Click the logout icon (log out symbol) in the header to log out of the chatbot.

Important Notes:

* Security: Ensure you keep your Gemini API key secure and do not expose it in client-side code. It is stored in `config.php` which should be kept outside the web-accessible directory if possible in a production environment.
* Error Logging: The application includes basic error logging. Check your web server's error logs (or PHP error logs) if you encounter issues.
* Rate Limits: Be mindful of the usage limits of the Google Gemini API, especially if you are using a free tier. Excessive use might lead to rate limiting or charges.

License:
This software is licensed to Jewel, the owner of [Owntweet ](https://owntweet.com).
All rights are reserved. No redistribution, modification, or commercial use of this software is allowed without explicit permission from the license holder.

Enjoy using the Owntweet Chatbot powered by Gemini API!
