<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Database.php';

Auth::requireLogin();
$user_id = Auth::getUserId();
$db = Database::getInstance()->getConnection();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $error = "File upload failed.";
        } else {
            $file = $_FILES['excel_file'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($file_ext !== 'csv') {
                $error = "Only CSV files are supported in this core version. Please convert your Excel to CSV.";
            } else {
                // Fetch admin limits
                $stmt = $db->query("SELECT * FROM admin_limits LIMIT 1");
                $limits = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($file['size'] > ($limits['max_file_upload_size'] ?? 5242880)) {
                    $error = "File size exceeds the limit.";
                } else {
                    // Start processing CSV
                    if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
                        // Clear existing list for this user
                        $db->prepare("DELETE FROM mailing_list WHERE user_id = ?")->execute([$user_id]);

                        $header = fgetcsv($handle, 1000, ",");
                        // Mapping columns (Required: contact name, email)
                        $col_map = array_flip(array_map('strtolower', array_map('trim', $header)));
                        
                        $count = 0;
                        $max_rows = $limits['max_excel_rows'] ?? 1000;

                        $stmt = $db->prepare("INSERT INTO mailing_list (user_id, contact_name, email, company, designation, company_type) VALUES (?, ?, ?, ?, ?, ?)");

                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            if ($count >= $max_rows) break;

                            $name = $data[$col_map['contact name'] ?? $col_map['name'] ?? 0] ?? '';
                            $email = $data[$col_map['email'] ?? 1] ?? '';
                            $company = $data[$col_map['company'] ?? -1] ?? '';
                            $designation = $data[$col_map['designation'] ?? -1] ?? '';
                            $company_type = $data[$col_map['company type'] ?? -1] ?? '';

                            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $stmt->execute([$user_id, $name, $email, $company, $designation, $company_type]);
                                $count++;
                            }
                        }
                        fclose($handle);
                        $message = "Upload successful! Imported $count contacts.";
                    } else {
                        $error = "Error reading CSV file.";
                    }
                }
            }
        }
    } else {
        $error = "Invalid CSRF token.";
    }
}

// Redirect back with status
$redirect_url = "dashboard.php?" . ($message ? "msg=" . urlencode($message) : "err=" . urlencode($error));
header("Location: $redirect_url");
exit();
