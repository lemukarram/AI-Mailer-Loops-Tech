<?php
require_once __DIR__ . '/BaseTest.php';
require_once __DIR__ . '/../src/LLM.php';

class LLMTest extends BaseTest {
    public function run() {
        echo "Testing LLM Class:\n";
        $this->testOpenAIGenerate();
        $this->testGeminiGenerate();
    }

    private function testOpenAIGenerate() {
        $llm = new LLM('openai', 'fake_key');
        $mockRes = [
            'subject' => 'Hello',
            'body' => 'Test body',
            'footer' => 'Best, Me'
        ];
        $llm->setMockResponse($mockRes);

        $result = $llm->generateEmail("Test prompt", ['contact_name' => 'John']);
        $this->assert_equals($mockRes, $result, "OpenAI generation with mock response works.");
    }

    private function testGeminiGenerate() {
        $llm = new LLM('gemini', 'fake_key');
        $mockRes = [
            'subject' => 'Hi from Gemini',
            'body' => 'Gemini body',
            'footer' => 'Regards, G'
        ];
        $llm->setMockResponse($mockRes);

        $result = $llm->generateEmail("Test prompt", ['contact_name' => 'Jane']);
        $this->assert_equals($mockRes, $result, "Gemini generation with mock response works.");
    }
}
