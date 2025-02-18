<?php
require 'config.php';
require 'Gemini.php';

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("API Error: Unauthorized access attempt.");
    http_response_code(401); // Unauthorized
    die(json_encode(['error' => 'Unauthorized']));
}

// Handle delete chat history request
if (isset($_GET['delete_history'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        try {
            $stmt = $pdo->prepare("DELETE FROM messages WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            http_response_code(200); // Success
            die();
        } catch (PDOException $e) {
            error_log("API Error (delete_history): PDOException - " . $e->getMessage());
            http_response_code(500);
            die(json_encode(['error' => 'Internal server error - Database deletion failed.']));
        }
    } else {
        error_log("API Error (delete_history): Invalid request method.");
        http_response_code(400);
        die(json_encode(['error' => 'Invalid request method.']));
    }
}

// Handle get messages request
if(isset($_GET['get_messages'])) {
      try {
            $stmt = $pdo->prepare("SELECT message, response, user_id, created_at FROM messages WHERE user_id = ? ORDER BY id ASC");
            $stmt->execute([$_SESSION['user_id']]);
             $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("API Success: Retrieved messages for user id: ".$_SESSION['user_id']);
             echo json_encode($messages);
            die();
        } catch (PDOException $e) {
            error_log("API Error (get_messages): PDOException - " . $e->getMessage());
             http_response_code(500);
             die(json_encode(['error' => 'Internal server error - could not retrieve messages']));
        }
}

// Process new message
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['message'])) {
     error_log("API Error: No message received.");
    http_response_code(400);
    die(json_encode(['error' => 'No message received.']));
}

// $message = filter_var($data['message'], FILTER_SANITIZE_STRING);
$message = $data['message'];
$context_size = 10;

// Default system prompt
$default_system_prompt = "Must Important before you write:

You are a human and friendly person, writing system must Human 100% all the text, write in the same language as the user writes. Must focus human touch ouch write. Human thought & Reddit style.";

try {
    error_log("API Request: Processing new message for user id: ".$_SESSION['user_id']);

    // Fetch user's saved system prompt
    $stmtPrompt = $pdo->prepare("SELECT system_prompt_text FROM saved_prompts WHERE user_id = ?");
    $stmtPrompt->execute([$_SESSION['user_id']]);
    $savedPromptResult = $stmtPrompt->fetch(PDO::FETCH_ASSOC);
    $userSystemPrompt = $savedPromptResult ? $savedPromptResult['system_prompt_text'] : null;

    // Use user's prompt if available, otherwise use default
    $system_prompt_to_use = $userSystemPrompt ?: $default_system_prompt;


    // Fetch recent messages
    $stmt = $pdo->prepare("SELECT message, response FROM messages WHERE user_id = ? ORDER BY id DESC LIMIT " . $context_size);
      $stmt->execute([$_SESSION['user_id']]);
     $recentMessages = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    error_log("API Request: Retrieved recent messages ".print_r($recentMessages, true));

    $messagesForGemini = [];
    //Add system prompt as context
    $messagesForGemini[] = ['is_user' => false, 'text' => $system_prompt_to_use]; // Use the selected system prompt

     foreach($recentMessages as $msg) {
        //User messages
        $messagesForGemini[] = ['is_user' => true, 'text' => $msg['message']];
        //AI messages
        $messagesForGemini[] = ['is_user' => false, 'text' => $msg['response']];
    }

    //Current user message
    $messagesForGemini[] = ['is_user' => true, 'text' => $message];
    error_log("API Request: Messages for Gemini ".print_r($messagesForGemini, true));


    $gemini = new Gemini($gemini_api_key);
    error_log("API Request: Gemini object created");
    $response = $gemini->generateResponse($messagesForGemini);
    error_log("API Request: Gemini response " . print_r($response, true));

    // Save to database
    $stmt = $pdo->prepare("INSERT INTO messages (user_id, message, response) VALUES (?, ?, ?)");
     $stmt->execute([$_SESSION['user_id'], $message, $response]);
     error_log("API Request: Message saved to database.");

    // Respond with the AI response
    echo json_encode(['response' => $response]);
    error_log("API Success: Response sent.");

} catch (PDOException $e) {
    error_log("API Error: PDOException - " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error - Database operation failed.']);
}
catch (Exception $e) {
    error_log("API Error: Exception - " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error - AI response failed.']);
}
?>
