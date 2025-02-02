<?php
class Gemini {
    private $api_key;
    private $api_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent";

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    public function generateResponse($messages) {
      $prompt = "";
      foreach($messages as $message) {
        $prompt .= $message['is_user'] ? "User: " : "AI: ";
        $prompt .= $message['text']."\n";
      }
      
      $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

      $ch = curl_init($this->api_url . "?key=" . $this->api_key);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
      curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

      $response = curl_exec($ch);
      curl_close($ch);

      $result = json_decode($response, true);
      return $result['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, I couldn't process that.";
    }
}
?>
