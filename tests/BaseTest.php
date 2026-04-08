<?php

class BaseTest {
    protected $passed = 0;
    protected $failed = 0;

    protected function assert_true($condition, $message) {
        if ($condition) {
            echo "  [PASS] $message\n";
            $this->passed++;
        } else {
            echo "  [FAIL] $message\n";
            $this->failed++;
        }
    }

    protected function assert_equals($expected, $actual, $message) {
        if ($expected === $actual) {
            echo "  [PASS] $message\n";
            $this->passed++;
        } else {
            echo "  [FAIL] $message (Expected: " . var_export($expected, true) . ", Actual: " . var_export($actual, true) . ")\n";
            $this->failed++;
        }
    }

    public function getSummary() {
        return ['passed' => $this->passed, 'failed' => $this->failed];
    }

    protected function getInMemoryPDO() {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}
