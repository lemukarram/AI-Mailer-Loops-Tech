<?php
require_once __DIR__ . '/BaseTest.php';
require_once __DIR__ . '/../src/Mailer.php';

class MailerTest extends BaseTest {
    public function run() {
        echo "Testing Mailer Class:\n";
        $this->testSendReturnsTrue();
    }

    private function testSendReturnsTrue() {
        $mailer = new Mailer('smtp.example.com', 587, 'user@example.com', 'password');
        $result = $mailer->send('recipient@example.com', 'Subject', 'Body', 'Footer');
        $this->assert_true($result === true, "Mailer::send returns true (mocked success).");
    }
}
