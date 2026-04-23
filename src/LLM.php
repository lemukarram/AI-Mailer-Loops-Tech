<?php

class LLM {
    private $apiKey;
    private $model;
    private $provider;

    public function __construct($provider, $apiKey, $model = null) {
        $this->provider = $provider;
        $this->apiKey = $apiKey;
        
        if ($model) {
            $this->model = $model;
        } else {
            $this->model = ($provider === 'openai') ? 'gpt-5' : 'gemini-2.5-flash';
        }
    }

    /**
     * Specialized method for Master AI agent to write placeholder emails and prompts.
     */
    public function generateCampaignPlaceholder($purpose, $profile) {
        $purposeText = ($purpose === 'job_hunt') ? "Job Hunting (Personal Career)" : "Business Lead Generation (B2B/Services)";
        
        $systemContext = "You are a professional outreach architect. Write with a 100% humanized, soft, and authentic tone. Never use emojis. Avoid AI-sounding transitions like 'I hope this finds you well'. Ensure the tone is indistinguishable from a high-level professional human writer.from any point email should not look like written by AI.Email should be interesting and catchy so that the receiver must read it. write subject that include [company] placeholder to grab the receiver attention. in body user [company] place holder atleat once . Return a strict JSON object containing 'base_prompt', 'subject', and 'body'.";
        
        $userPrompt = "CONTEXT:\n";
        $userPrompt .= "User Purpose: $purposeText\n";
        $userPrompt .= "User Name: " . ($profile['full_name'] ?? 'N/A') . "\n";
        $userPrompt .= "User Current Role/Service: " . ($profile['designation'] ?? 'N/A') . "\n";
        $userPrompt .= "User Company/Background: " . ($profile['company_name'] ?? 'N/A') . "\n";
        $userPrompt .= "User Bio/Experience: " . ($profile['other_info'] ?? 'N/A') . "\n";
        
        if ($purpose === 'job_hunt' && !empty($profile['resume_text'])) {
            $userPrompt .= "User Resume Summary: " . substr($profile['resume_text'], 0, 2000) . "\n";
        } elseif ($purpose === 'business_leads' && !empty($profile['business_profile_text'])) {
            $userPrompt .= "Business Profile Summary: " . substr($profile['business_profile_text'], 0, 2000) . "\n";
        }

        $userPrompt .= "\nTASK:\n";
        $userPrompt .= "1. Write a 'base_prompt': This is the instruction the user will use for AI generation. It should be a clear, high-quality prompt that tells the AI how to behave for their specific case. Example for job: 'Write a warm, personalized reach out to hiring managers emphasizing my experience in [industry]...'\n";
        $userPrompt .= "2. Write a 'subject': A catchy, humanized subject line using the placeholder [company] if applicable.\n";
        $userPrompt .= "3. Write a 'body': A perfect sample email body using the [company] placeholder. It should be clear, soft-toned, and highly readable.\n\n";
        $userPrompt .= "Ensure the output is strictly JSON.";

        if ($this->provider === 'openai') {
            return $this->callOpenAI($systemContext, $userPrompt);
        } else {
            return $this->callGemini($systemContext, $userPrompt);
        }
    }

    public function generateEmail($basePrompt, $contactInfo, $senderProfile = []) {
        $systemContext = "Write with a 100% humanized, soft tone. Do not use emojis. Never use the long dash character; use a period instead. Ensure it is clear, catchy, and highly readable so no one can predict it is AI-written. Return a strict JSON object containing 'subject', 'body', and 'footer'. VERY IMPORTANT: The 'footer' MUST use the sequence '[BR]' (without quotes) to separate details like Name, Title, and Phone. Each detail MUST be on its own line using this [BR] separator.";
        
        $userPrompt = "### RECIPIENT INFO:\n";
        $userPrompt .= "Name: " . $contactInfo['contact_name'] . "\n";
        $userPrompt .= "Company: " . ($contactInfo['company'] ?? 'N/A') . "\n";
        $userPrompt .= "Designation: " . ($contactInfo['designation'] ?? 'N/A') . "\n";
        $userPrompt .= "Company Type: " . ($contactInfo['company_type'] ?? 'N/A') . "\n\n";

        if (!empty($senderProfile)) {
            $userPrompt .= "### SENDER INFO (YOU):\n";

            // Define the map of Label => Key
            $fields = [
                "Name"     => $senderProfile['full_name'] ?? null,
                "Position" => $senderProfile['designation'] ?? null,
                "Company"  => $senderProfile['company_name'] ?? null,
                "LinkedIn" => $senderProfile['linkedin_url'] ?? null,
                "Website"  => $senderProfile['website_url'] ?? null,
                "Phone"    => $senderProfile['phone'] ?? null,
                "Context"  => $senderProfile['other_info'] ?? null,
            ];

            foreach ($fields as $label => $value) {
                // Only append if the value is not empty and not 'N/A'
                if (!empty($value) && $value !== 'N/A') {
                    $userPrompt .= "$label: $value\n";
                }
            }

            $userPrompt .= "\n";
        }
        
        $userPrompt .= "### INSTRUCTION:\n";
        $userPrompt .= $basePrompt . "\n";
        $userPrompt .= "Focus on the recipient's name and company while authentically using the sender's details for the closing/signature. Ensure the footer/signature is professional and formatted with one detail per line by using '[BR]' as the line separator. If a sender detail is 'N/A', do not mention it.";

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
            if (!isset($decoded['choices'][0]['message']['content'])) {
                throw new Exception("OpenAI Error: " . ($decoded['error']['message'] ?? "Unknown error"));
            }
            return json_decode($decoded['choices'][0]['message']['content'], true);
        } else {
            // Gemini response structure
            if (!isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
                throw new Exception("Gemini Error: " . ($decoded['error']['message'] ?? "Unknown error"));
            }
            $text = $decoded['candidates'][0]['content']['parts'][0]['text'];
            return json_decode($text, true);
        }
    }

    private $mockResponse = null;
    public function setMockResponse($response) {
        $this->mockResponse = $response;
    }
}
