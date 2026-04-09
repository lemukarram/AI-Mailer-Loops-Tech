<?php
require_once __DIR__ . '/LLM.php';

class Campaign {
    public static function replaceVariables($template, $data) {
        $variables = [
            '[contact_name]' => $data['contact_name'] ?? '',
            '[email]' => $data['email'] ?? '',
            '[company]' => $data['company'] ?? '',
            '[designation]' => $data['designation'] ?? '',
            '[company_type]' => $data['company_type'] ?? ''
        ];
        return str_replace(array_keys($variables), array_values($variables), $template);
    }

    public static function generateAIEmail($user_settings, $contact_data, $base_prompt) {
        require_once __DIR__ . '/Crypto.php';
        require_once __DIR__ . '/Database.php';

        $user_id = $user_settings['user_id'];
        $db = Database::getInstance()->getConnection();
        
        // Fetch sender profile
        $stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $sender_profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $provider = $user_settings['preferred_llm'] ?? 'openai';
        $encKey = ($provider === 'openai') ? ($user_settings['openai_api_key'] ?? '') : ($user_settings['gemini_api_key'] ?? '');
        $apiKey = Crypto::decrypt($encKey);
        
        if (empty($apiKey)) {
            throw new Exception("API Key for $provider is missing or invalid.");
        }

        $llm = new LLM($provider, $apiKey);
        return $llm->generateEmail($base_prompt, $contact_data, $sender_profile);
    }
}
