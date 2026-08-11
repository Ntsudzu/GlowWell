<?php
// api/auth.php
header('Content-Type: application/json');
require_once '../db.php';
require_once '../auth_check.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($action === 'login') {
        handleLogin($pdo, $data);
    } elseif ($action === 'register') {
        handleRegister($pdo, $data);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}

function handleLogin($pdo, $data) {
    $email    = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = trim($data['password'] ?? '');

    if (!$email || empty($password)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email and password.']);
        return;
    }

    $sql = "SELECT u.user_id, u.email, u.password_hash, r.role_name, c.customer_id, s.staff_id,
                   COALESCE(c.first_name, s.first_name, 'Admin') AS first_name
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            LEFT JOIN customers c ON u.user_id = c.user_id
            LEFT JOIN staff s ON u.user_id = s.user_id
            WHERE u.email = :email";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        $_SESSION['user_id']     = $user['user_id'];
        $_SESSION['email']       = $user['email'];
        $_SESSION['role_name']   = $user['role_name'];
        $_SESSION['first_name']  = $user['first_name'];
        $_SESSION['customer_id'] = $user['customer_id'] ?? null;
        $_SESSION['staff_id']    = $user['staff_id'] ?? null;

        $redirect = ($user['role_name'] === 'Admin') ? 'admin.php' : 'index.php';

        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful!',
            'redirect' => $redirect
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
    }
}

function handleRegister($pdo, $data) {
    $email      = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password   = trim($data['password'] ?? '');
    $first_name = trim($data['first_name'] ?? '');
    $last_name  = trim($data['last_name'] ?? '');
    $phone      = trim($data['phone'] ?? '');

    if (!$email || strlen($password) < 6 || empty($first_name) || empty($last_name) || empty($phone)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'All fields required. Password must be at least 6 characters.']);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Check duplicate email
        $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
        $checkStmt->execute([':email' => $email]);
        if ($checkStmt->fetch()) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Email address is already registered.']);
            $pdo->rollBack();
            return;
        }

        // 1. Insert into users (Role ID 3 = Customer)
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $userStmt = $pdo->prepare("INSERT INTO users (email, password_hash, role_id) VALUES (:email, :hash, 3)");
        $userStmt->execute([':email' => $email, ':hash' => $hash]);
        $user_id = $pdo->lastInsertId();

        // 2. Insert into customers
        $custStmt = $pdo->prepare("INSERT INTO customers (user_id, first_name, last_name, phone) VALUES (:user_id, :first_name, :last_name, :phone)");
        $custStmt->execute([
            ':user_id'    => $user_id,
            ':first_name' => $first_name,
            ':last_name'  => $last_name,
            ':phone'      => $phone
        ]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Registration successful! You can now log in.']);

    } catch (\Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Registration failed due to a server error.']);
    }
}
?>