# Gemini-Chatbot-By-Jewel

This project is a simple web-based chatbot application that utilizes the Google Gemini API to generate conversational responses. It allows users to interact with an AI assistant through a chat interface, complete with user authentication, chat history, and various user-friendly features.

## Features

*   **User Authentication:** Secure registration, login, and logout functionality.
*   **Chat History:** Messages are stored and retrieved, providing a persistent chat log for each user.
*   **Google Gemini Integration:** Leverages the Google Gemini API for intelligent, conversational responses.
*   **Real-Time Experience:**
    *   Typing indicators provide a real-time feel during AI responses.
    *   The chat interface dynamically updates with new messages.
*   **Markdown Support:**
    *   AI responses are parsed using Markdown to render styled text.
*   **Code Highlighting:**
    *   Code snippets in AI responses are syntax-highlighted using Highlight.js.
*   **User-Friendly Tools:**
    *   Copy message functionality to quickly grab specific AI responses.
    *   Copy all chat functionality to quickly grab whole conversations.
    *   Delete chat history to remove unwanted conversations.
*   **Responsive Design:** Utilizes Tailwind CSS for a mobile-friendly and responsive interface.

## Technologies

*   **Backend:**
    *   PHP
    *   MySQL
*   **Frontend:**
    *   HTML
    *   CSS (Tailwind CSS)
    *   JavaScript
*   **APIs/Libraries:**
    *   Google Gemini API
    *   Highlight.js
    *   Marked.js
## File Structure
content_copy
download
Use code with caution.

/chatbot/
├── auth.php # Handles user authentication logic (login, register, logout)
├── Gemini.php # Class for interacting with the Google Gemini API
├── index.php # Main chat interface
├── guest.php # Login and registration page for guest users
├── config.php # Configuration file for database and API keys
├── api.php # Handles API requests for messages
├── Readme.txt # Documentation file


## Setup Instructions

1.  **Database Setup:**
    *   Ensure you have a MySQL database server running.
    *   Create a database named `Owntweet_chatbot`.
    *   Create a database user `chatbot` with password `password`, and grant it all privileges on the `Owntweet_chatbot` database.
    *   Create the necessary tables:

        *   `users`:
            *   `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
            *   `username` (VARCHAR(255), UNIQUE)
            *   `password` (VARCHAR(255))
        *   `messages`:
            *   `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
            *   `user_id` (INT)
            *   `message` (TEXT)
            *   `response` (TEXT)
            *   `created_at` (TIMESTAMP DEFAULT CURRENT_TIMESTAMP)
2.  **API Key:**
    *   Obtain a Google Gemini API key.
3.  **Configuration:**
    *   Place all files inside of your `/chatbot/` directory on your server.
    *   Open the `config.php` file and update the following variables to match your environment:
        *   `$host`: Your MySQL server host (e.g., `localhost`).
        *   `$dbname`: The name of your MySQL database (should be `Owntweet_chatbot`).
        *   `$user`: Your MySQL database user (`chatbot`).
        *   `$pass`: The password for your MySQL database user (should be `password`).
        *   `$gemini_api_key`: Your Google Gemini API Key.
4.  **Access:** Navigate to `guest.php` in your web browser to register or login and begin using the chatbot.

## License

This software is licensed to Jewel, the owner of [Owntweet](https://owntweet.com).
All rights are reserved. No redistribution, modification, or commercial
use of this software is allowed without explicit permission from the license holder.

## Contributing

Since this is a personal project and license is restricted, contributions are not accepted.
content_copy
download
Use code with caution.

 tells GitHub that it's a Markdown file.

Copy and Paste: Paste the entire content from the above text into your new README.md file.

Commit Changes: Commit the README.md file to your repository.

Preview: GitHub will automatically render the Markdown into nicely formatted documentation on your project's main page.

This well-structured description will make your GitHub project much more accessible and informative for other developers.

