<?php
require_once __DIR__ . '/BaseTest.php';
require_once __DIR__ . '/../src/Campaign.php';

class CampaignTest extends BaseTest {
    public function run() {
        echo "Testing Campaign Class:\n";
        $this->testVariableReplacement();
        $this->testMissingVariables();
    }

    private function testVariableReplacement() {
        $template = "Hello [contact_name], welcome to [company]!";
        $data = ['contact_name' => 'John Doe', 'company' => 'TechCorp'];
        $result = Campaign::replaceVariables($template, $data);
        $this->assert_equals("Hello John Doe, welcome to TechCorp!", $result, "Campaign variable replacement works correctly.");

        $template2 = "Dear [designation] from [company_type].";
        $data2 = ['designation' => 'Manager', 'company_type' => 'Retail'];
        $result2 = Campaign::replaceVariables($template2, $data2);
        $this->assert_equals("Dear Manager from Retail.", $result2, "Campaign designation and company type replacement works.");
    }

    private function testMissingVariables() {
        $template = "Hello [contact_name], welcome to [company]!";
        $data = ['contact_name' => 'John Doe'];
        $result = Campaign::replaceVariables($template, $data);
        $this->assert_equals("Hello John Doe, welcome to !", $result, "Campaign handles missing variables gracefully.");
    }
}
