<?php

class LLM {
    private $apiKey;
    private $model;
    private $provider;
    private $mockResponse = null;

    public function __construct($provider, $apiKey, $model = null) {
        $this->provider = $provider;
        $this->apiKey = $apiKey;
        
        if ($model) {
            $this->model = $model;
        } else {
            $this->model = ($provider === 'openai') ? 'gpt-5' : 'gemini-2.5-flash';
        }
    }

    public function generateEmail($basePrompt, $contactInfo) {
        $systemContext = "Write with a 100% humanized, soft tone. Do not use emojis. Never use the long dash character; use a period instead. Ensure it is clear, catchy, and highly readable so no one can predict it is AI-written. Return a strict JSON object containing 'subject', 'body', and 'footer'.";
        
        $userPrompt = "Base Prompt: " . $basePrompt . "\n";
        $userPrompt .= "Contact Name: " . $contactInfo['contact_name'] . "\n";
        $userPrompt .= "Company: " . ($contactInfo['company'] ?? 'N/A') . "\n";
        $userPrompt .= "Designation: " . ($contactInfo['designation'] ?? 'N/A') . "\n";
        $userPrompt .= "Company Type: " . ($contactInfo['company_type'] ?? 'N/A') . "\n";
        
        if ($this->provider === 'openai') {
            return $this->callOpenAI($systemContext, $userPrompt);
        } else {
            return $this->callGemini($systemContext, $userPrompt);
        }
    }

    private function callOpenAI($systemContext, $userPrompt) {
        $url = "https://api.openai.com/v1/chat/completions";
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->apiKey
        ];
        
        $data = [
            "model" => $this->model,
            "messages" => [
                ["role" => "system", "content" => $systemContext],
                ["role" => "user", "content" => $userPrompt]
            ],
            "response_format" => ["type" => "json_object"]
        ];

        return $this->makeRequest($url, $headers, $data);
    }

    private function callGemini($systemContext, $userPrompt) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . $this->model . ":generateContent?key=" . $this->apiKey;
        $headers = [
            "Content-Type: application/json"
        ];
        
        $data = [
            "contents" => [
                ["parts" => [["text" => $systemContext . "\n\n" . $userPrompt]]]
            ],
            "generationConfig" => [
                "response_mime_type" => "application/json"
            ]
        ];

        return $this->makeRequest($url, $headers, $data);
    }

    public function makeRequest($url, $headers, $data) {
        if ($this->mockResponse !== null) {
            return $this->mockResponse;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception("CURL Error: " . curl_error($ch));
        }
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
        if ($this->provider === 'openai') {
            return json_decode($decoded['choices'][0]['message']['content'], true);
        } else {
            // Gemini response structure
            $text = $decoded['candidates'][0]['content']['parts'][0]['text'];
            return json_decode($text, true);
        }
    }

    private $mockResponse = null;
    public function setMockResponse($response) {
        $this->mockResponse = $response;
    }
}
