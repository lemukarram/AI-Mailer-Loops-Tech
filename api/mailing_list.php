<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Database.php';

Auth::requireLogin();
$user_id = Auth::getUserId();
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT * FROM mailing_list WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode(['data' => $data]);
