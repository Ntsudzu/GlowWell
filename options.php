<?php
// api/options.php
header('Content-Type: application/json');
require_once '../db.php';

try {
    // Fetch Staff
    $staffStmt = $pdo->query("SELECT staff_id, CONCAT(first_name, ' ', last_name) AS name, specialization FROM staff");
    $staff = $staffStmt->fetchAll();

    // Fetch Services
    $srvStmt = $pdo->query("SELECT service_id, service_name, price, duration_minutes FROM services");
    $services = $srvStmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'staff' => $staff,
        'services' => $services
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to load options.']);
}
?>