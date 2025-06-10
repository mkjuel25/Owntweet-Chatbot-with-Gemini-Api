<?php
class DeepSeek {
    private $api_key;
    private $api_url = "https://api.deepseek.com/chat/completions";

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    public function generateResponse($messages) {
        $formatted_messages = [];
        foreach($messages as $message) {
            $role = $message['is_user'] ? "user" : "assistant";
            $formatted_messages[] = ["role" => $role, "content" => $message['text']];
        }
        
        $data = [
            "model" => "deepseek-chat",
            "messages" => $formatted_messages,
            "stream" => false
        ];

        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ]);

        // Add these lines for timeouts
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Max time in seconds to connect
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);       // Max time in seconds for the entire cURL operation

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            error_log("cURL Error: " . $error_msg);
            return "Sorry, there was a network error.";
        }

        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['error'])) {
            error_log("DeepSeek API Error: " . ($result['error']['message'] ?? json_encode($result)));
            return "Sorry, there was an issue with the AI service: " . ($result['error']['message'] ?? "Unknown API error.");
        }

        return $result['choices'][0]['message']['content'] ?? "Sorry, I couldn't process that.";
    }
}
?>