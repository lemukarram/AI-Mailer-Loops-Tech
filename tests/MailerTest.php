<?php
require_once __DIR__ . '/BaseTest.php';
require_once __DIR__ . '/../src/Mailer.php';

class MailerTest extends BaseTest {
    public function run() {
        echo "Testing Mailer Class:\n";
        $this->testMailerStructure();
    }

    private function testMailerStructure() {
        $mailer = new Mailer('smtp.example.com', 587, 'user@example.com', 'password');
        $this->assert_true(method_exists($mailer, 'send'), "Mailer has send() method.");
        $this->assert_true(is_object($mailer), "Mailer class can be instantiated.");
    }
}
