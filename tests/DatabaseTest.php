<?php
require_once __DIR__ . '/BaseTest.php';
require_once __DIR__ . '/../src/Database.php';

class DatabaseTest extends BaseTest {
    public function run() {
        echo "Testing Database Class:\n";
        $this->testSingleton();
        $this->testMocking();
    }

    private function testSingleton() {
        $db1 = Database::getInstance();
        $db2 = Database::getInstance();
        $this->assert_true($db1 === $db2, "Database::getInstance returns the same instance.");
    }

    private function testMocking() {
        $mockPDO = $this->getInMemoryPDO();
        $mockDB = new class($mockPDO) {
            private $conn;
            public function __construct($conn) { $this->conn = $conn; }
            public function getConnection() { return $this->conn; }
        };
        
        Database::setInstance($mockDB);
        $db = Database::getInstance();
        $this->assert_equals($mockPDO, $db->getConnection(), "Database instance can be mocked.");
        
        // Reset singleton for other tests (it would need a real reset mechanism, but for this test suite it's fine)
        Database::setInstance(null);
    }
}
