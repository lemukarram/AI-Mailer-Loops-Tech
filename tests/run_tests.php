<?php

/**
 * Full Unit Test Suite for aiMailSaas
 */

require_once __DIR__ . '/CampaignTest.php';
require_once __DIR__ . '/AuthTest.php';
require_once __DIR__ . '/LLMTest.php';
require_once __DIR__ . '/MailerTest.php';
require_once __DIR__ . '/DatabaseTest.php';

$tests = [
    new CampaignTest(),
    new AuthTest(),
    new LLMTest(),
    new MailerTest(),
    new DatabaseTest(),
];

$totalPassed = 0;
$totalFailed = 0;

echo "--- Running AI Mailer Unit Tests ---\n\n";

foreach ($tests as $test) {
    $test->run();
    $summary = $test->getSummary();
    $totalPassed += $summary['passed'];
    $totalFailed += $summary['failed'];
    echo "\n";
}

echo "--- Test Summary ---\n";
echo "Total Passed: $totalPassed\n";
echo "Total Failed: $totalFailed\n";

if ($totalFailed > 0) {
    exit(1);
} else {
    exit(0);
}
