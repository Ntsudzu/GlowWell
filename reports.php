<?php
// api/reports.php
header('Content-Type: application/json');
require_once '../db.php';

// Generate summary report for Admin Dashboard
$sql = "SELECT 
            COUNT(appointment_id) AS total_bookings,
            SUM(CASE WHEN status = 'Completed' OR status = 'Confirmed' THEN srv.price ELSE 0 END) AS projected_revenue,
            SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) AS total_cancelled
        FROM appointments a
        JOIN services srv ON a.service_id = srv.service_id";

$stmt = $pdo->query($sql);
$report = $stmt->fetch();

echo json_encode(['status' => 'success', 'report' => $report]);
?>