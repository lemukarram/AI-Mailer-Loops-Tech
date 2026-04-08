<?php

class Crypto {
    // Encryption method: AES-256-CBC
    private static $method = 'AES-256-CBC';

    // Get key from config
    private static function getKey() {
        require_once __DIR__ . '/../config/config.php';
        return hash('sha256', ADMIN_KEY);
    }

    public static function encrypt($data) {
        if (empty($data)) return $data;
        $ivSize = openssl_cipher_iv_length(self::$method);
        $iv = openssl_random_pseudo_bytes($ivSize);
        $encrypted = openssl_encrypt($data, self::$method, self::getKey(), 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    public static function decrypt($data) {
        if (empty($data)) return $data;
        $decoded = base64_decode($data);
        if ($decoded === false || strpos($decoded, '::') === false) return $data; // Maybe plain text
        
        list($encrypted, $iv) = explode('::', $decoded, 2);
        
        $decrypted = openssl_decrypt($encrypted, self::$method, self::getKey(), 0, $iv);
        return $decrypted !== false ? $decrypted : $data;
    }
}
