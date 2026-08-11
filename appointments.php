<?php
header('Content-Type: application/json');
require_once '../db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleRead($pdo);
        break;
    case 'POST':
        handleCreate($pdo);
        break;
    case 'DELETE':
        handleDelete($pdo);
        break;
    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        break;
}

function handleCreate($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    $customer_id      = filter_var($data['customer_id'] ?? null, FILTER_VALIDATE_INT);
    $staff_id         = filter_var($data['staff_id'] ?? null, FILTER_VALIDATE_INT);
    $service_id       = filter_var($data['service_id'] ?? null, FILTER_VALIDATE_INT);
    $appointment_date = trim($data['appointment_date'] ?? '');
    $start_time       = trim($data['start_time'] ?? '');

    if (!$customer_id || !$staff_id || !$service_id || empty($appointment_date) || empty($start_time)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'All fields are required and must be valid.']);
        return;
    }

    $checkSql = "SELECT COUNT(*) FROM appointments 
                 WHERE staff_id = :staff_id 
                 AND appointment_date = :appointment_date 
                 AND start_time = :start_time 
                 AND status != 'Cancelled'";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([
        ':staff_id' => $staff_id,
        ':appointment_date' => $appointment_date,
        ':start_time' => $start_time
    ]);

    if ($checkStmt->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'The selected staff member is already booked at this time.']);
        return;
    }

    $sql = "INSERT INTO appointments (customer_id, staff_id, service_id, appointment_date, start_time) 
            VALUES (:customer_id, :staff_id, :service_id, :appointment_date, :start_time)";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([
        ':customer_id' => $customer_id,
        ':staff_id' => $staff_id,
        ':service_id' => $service_id,
        ':appointment_date' => $appointment_date,
        ':start_time' => $start_time
    ]);

    if ($success) {
        http_response_code(201);
        echo json_encode(['status' => 'success', 'message' => 'Appointment booked successfully!']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to create appointment.']);
    }
}

function handleRead($pdo) {
    $sql = "SELECT a.appointment_id, CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
                   CONCAT(s.first_name, ' ', s.last_name) AS staff_name,
                   srv.service_name, srv.price, a.appointment_date, a.start_time, a.status
            FROM appointments a
            JOIN customers c ON a.customer_id = c.customer_id
            JOIN staff s ON a.staff_id = s.staff_id
            JOIN services srv ON a.service_id = srv.service_id
            ORDER BY a.appointment_date DESC, a.start_time ASC";

    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $results]);
}

function handleDelete($pdo) {
    parse_str($_SERVER['QUERY_STRING'], $queryParams);
    $id = filter_var($queryParams['id'] ?? null, FILTER_VALIDATE_INT);

    if (!$id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid or missing Appointment ID.']);
        return;
    }

    $stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = :id");
    if ($stmt->execute([':id' => $id])) {
        echo json_encode(['status' => 'success', 'message' => 'Appointment cancelled successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database update failed.']);
    }
}
?>