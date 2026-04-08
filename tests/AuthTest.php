<?php
require_once __DIR__ . '/BaseTest.php';
require_once __DIR__ . '/../src/Auth.php';

class AuthTest extends BaseTest {
    public function run() {
        echo "Testing Auth Class:\n";
        $this->testCSRFToken();
    }

    private function testCSRFToken() {
        // Mock session
        if (session_status() === PHP_SESSION_NONE) {
            $_SESSION = [];
        }

        $token1 = Auth::generateCSRFToken();
        $this->assert_true(!empty($token1), "CSRF token generated.");
        
        $token2 = Auth::generateCSRFToken();
        $this->assert_equals($token1, $token2, "CSRF token persistent in session.");
        
        $this->assert_true(Auth::verifyCSRFToken($token1), "CSRF verification succeeds for correct token.");
        $this->assert_true(!Auth::verifyCSRFToken("wrong_token"), "CSRF verification fails for incorrect token.");
        
        // Test regeneration
        unset($_SESSION['csrf_token']);
        $token3 = Auth::generateCSRFToken();
        $this->assert_true($token1 !== $token3, "CSRF token regenerates after being cleared.");
    }
}
