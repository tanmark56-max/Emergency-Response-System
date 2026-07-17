<?php
require_once '../config/db_connect.php';
require_once '../config/constants.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Handle CORS preflight
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Login endpoint
if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'login') {
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Email and password required']);
        exit();
    }
    
    // Check login attempts
    if (!isset($_SESSION['attempts'])) {
        $_SESSION['attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }
    
    // Check lockout
    if ($_SESSION['attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $elapsed = time() - $_SESSION['last_attempt_time'];
        if ($elapsed < LOCKOUT_TIME) {
            $remaining = ceil((LOCKOUT_TIME - $elapsed) / 60);
            echo json_encode([
                'success' => false, 
                'error' => "Too many attempts. Try again in $remaining minute(s)."
            ]);
            exit();
        } else {
            $_SESSION['attempts'] = 0;
        }
    }
    
    $stmt = $conn->prepare("SELECT user_id, full_name, role, password, is_active FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (!$user['is_active']) {
            echo json_encode(['success' => false, 'error' => 'Account is deactivated']);
            exit();
        }
        
        if (password_verify($password, $user['password'])) {
            // Generate OTP
            $otp = random_int(100000, 999999);
            $otp_hash = password_hash($otp, PASSWORD_DEFAULT);
            $expiry = date("Y-m-d H:i:s", strtotime("+" . OTP_EXPIRY . " minutes"));
            
            $update = $conn->prepare("UPDATE users SET otp_code=?, otp_expiry=? WHERE user_id=?");
            $update->bind_param("ssi", $otp_hash, $expiry, $user['user_id']);
            $update->execute();
            
            // Send OTP via email (simplified - use PHPMailer in production)
            // For now, return the OTP for testing
            $_SESSION['temp_user_id'] = $user['user_id'];
            $_SESSION['temp_email'] = $email;
            $_SESSION['attempts'] = 0;
            
            echo json_encode([
                'success' => true,
                'message' => 'OTP sent to your email',
                'requires_otp' => true,
                'otp' => $otp, // Remove this in production!
                'temp_user_id' => $user['user_id']
            ]);
            exit();
        } else {
            $_SESSION['attempts']++;
            $_SESSION['last_attempt_time'] = time();
            echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
            exit();
        }
    } else {
        $_SESSION['attempts']++;
        $_SESSION['last_attempt_time'] = time();
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
        exit();
    }
}

// Verify OTP endpoint
if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'verify-otp') {
    $otp = $input['otp'] ?? '';
    $temp_user_id = $input['temp_user_id'] ?? $_SESSION['temp_user_id'] ?? 0;
    
    if (empty($otp) || !$temp_user_id) {
        echo json_encode(['success' => false, 'error' => 'OTP and user ID required']);
        exit();
    }
    
    $stmt = $conn->prepare("SELECT user_id, full_name, role, otp_code, otp_expiry FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $temp_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit();
    }
    
    // Check OTP expiry
    if (strtotime($user['otp_expiry']) < time()) {
        echo json_encode(['success' => false, 'error' => 'OTP has expired']);
        exit();
    }
    
    // Verify OTP
    if (password_verify($otp, $user['otp_code'])) {
        // Clear OTP
        $clear = $conn->prepare("UPDATE users SET otp_code=NULL, otp_expiry=NULL WHERE user_id=?");
        $clear->bind_param("i", $temp_user_id);
        $clear->execute();
        
        // Set session
        $_SESSION['user_id'] = $temp_user_id;
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_email']);
        
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['user_id'],
                'full_name' => $user['full_name'],
                'role' => $user['role']
            ]
        ]);
        exit();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid OTP']);
        exit();
    }
}

// Get current user endpoint
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'me') {
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT user_id, full_name, role, email FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
        exit();
    } else {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit();
    }
}

// Logout endpoint
if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out']);
    exit();
}
?>