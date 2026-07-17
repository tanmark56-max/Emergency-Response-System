<?php
require_once '../config/db_connect.php';
require_once '../config/constants.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Get all calls with filters
if ($method === 'GET' && !isset($_GET['id']) && !isset($_GET['action'])) {
    $status = $_GET['status'] ?? '';
    $limit = $_GET['limit'] ?? 50;
    $offset = $_GET['offset'] ?? 0;
    
    $sql = "SELECT * FROM emergency_calls WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($status)) {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = (int)$limit;
    $params[] = (int)$offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $calls = [];
    while ($row = $result->fetch_assoc()) {
        $calls[] = $row;
    }
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM emergency_calls";
    $count_result = $conn->query($count_sql);
    $total = $count_result->fetch_assoc()['total'];
    
    echo json_encode([
        'success' => true,
        'data' => $calls,
        'total' => (int)$total,
        'limit' => (int)$limit,
        'offset' => (int)$offset
    ]);
    exit();
}

// Get single call
if ($method === 'GET' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM emergency_calls WHERE call_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $call = $result->fetch_assoc();
    
    if ($call) {
        echo json_encode(['success' => true, 'data' => $call]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Call not found']);
    }
    exit();
}

// Create new call
if ($method === 'POST') {
    $caller_name = $input['caller_name'] ?? '';
    $contact_number = $input['contact_number'] ?? '';
    $emergency_type = $input['emergency_type'] ?? '';
    $incident_location = $input['incident_location'] ?? '';
    $incident_details = $input['incident_details'] ?? '';
    $latitude = $input['latitude'] ?? null;
    $longitude = $input['longitude'] ?? null;
    
    if (empty($caller_name) || empty($contact_number) || empty($emergency_type) || empty($incident_location)) {
        echo json_encode(['success' => false, 'error' => 'All required fields must be filled']);
        exit();
    }
    
    $sql = "INSERT INTO emergency_calls 
            (caller_name, contact_number, emergency_type, incident_location, 
             incident_details, latitude, longitude, barangay_area, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Camarin North', 'Pending')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssdd", $caller_name, $contact_number, $emergency_type, 
                      $incident_location, $incident_details, $latitude, $longitude);
    
    if ($stmt->execute()) {
        $call_id = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Emergency call created',
            'call_id' => $call_id
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create call: ' . $stmt->error]);
    }
    exit();
}

// Update call
if ($method === 'PUT' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $input['status'] ?? null;
    $priority_level = $input['priority_level'] ?? null;
    $assigned_responder = $input['assigned_responder'] ?? null;
    
    $updates = [];
    $params = [];
    $types = "";
    
    if ($status !== null) {
        $updates[] = "status = ?";
        $params[] = $status;
        $types .= "s";
    }
    if ($priority_level !== null) {
        $updates[] = "priority_level = ?";
        $params[] = $priority_level;
        $types .= "s";
    }
    if ($assigned_responder !== null) {
        $updates[] = "assigned_responder = ?";
        $params[] = $assigned_responder;
        $types .= "i";
    }
    
    if (empty($updates)) {
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        exit();
    }
    
    $sql = "UPDATE emergency_calls SET " . implode(", ", $updates) . " WHERE call_id = ?";
    $params[] = $id;
    $types .= "i";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Call updated']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update call']);
    }
    exit();
}

// Delete call
if ($method === 'DELETE' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM emergency_calls WHERE call_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Call deleted']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete call']);
    }
    exit();
}

// Get statistics
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'stats') {
    $stats = [];
    
    // Total calls
    $result = $conn->query("SELECT COUNT(*) as count FROM emergency_calls");
    $stats['total_calls'] = (int)$result->fetch_assoc()['count'];
    
    // Pending
    $result = $conn->query("SELECT COUNT(*) as count FROM emergency_calls WHERE status = 'Pending'");
    $stats['pending'] = (int)$result->fetch_assoc()['count'];
    
    // In progress
    $result = $conn->query("SELECT COUNT(*) as count FROM emergency_calls WHERE status IN ('Dispatched', 'In-Progress')");
    $stats['in_progress'] = (int)$result->fetch_assoc()['count'];
    
    // Resolved
    $result = $conn->query("SELECT COUNT(*) as count FROM emergency_calls WHERE status IN ('Resolved', 'Closed')");
    $stats['resolved'] = (int)$result->fetch_assoc()['count'];
    
    // Critical
    $result = $conn->query("SELECT COUNT(*) as count FROM emergency_calls WHERE priority_level = 'Critical'");
    $stats['critical'] = (int)$result->fetch_assoc()['count'];
    
    // Today's calls
    $result = $conn->query("SELECT COUNT(*) as count FROM emergency_calls WHERE DATE(created_at) = CURDATE()");
    $stats['today_calls'] = (int)$result->fetch_assoc()['count'];
    
    // Responders
    $result = $conn->query("SELECT COUNT(*) as count FROM responders WHERE availability = 'Available'");
    $stats['available_responders'] = (int)$result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM responders");
    $stats['total_responders'] = (int)$result->fetch_assoc()['count'];
    
    // AI stats
    $result = $conn->query("SELECT AVG(priority_score) as avg_score, COUNT(*) as total_ai FROM ai_response_logs");
    $ai = $result->fetch_assoc();
    $stats['avg_ai_score'] = round($ai['avg_score'] ?? 0);
    $stats['total_ai'] = (int)($ai['total_ai'] ?? 0);
    
    // Priority distribution
    $priorities = ['Critical', 'High', 'Medium', 'Low'];
    $stats['priority_distribution'] = [];
    foreach ($priorities as $p) {
        $result = $conn->query("SELECT COUNT(*) as count FROM emergency_calls WHERE priority_level = '$p'");
        $stats['priority_distribution'][$p] = (int)$result->fetch_assoc()['count'];
    }
    
    // Emergency type distribution
    $types = ['Fire', 'Medical', 'Crime', 'Accident', 'Natural Disaster', 'Flood', 'Other'];
    $stats['type_distribution'] = [];
    foreach ($types as $t) {
        $result = $conn->query("SELECT COUNT(*) as count FROM emergency_calls WHERE emergency_type = '$t'");
        $stats['type_distribution'][$t] = (int)$result->fetch_assoc()['count'];
    }
    
    // Responder status distribution
    $statuses = ['Available', 'Busy', 'Off-Duty'];
    $stats['responder_status'] = [];
    foreach ($statuses as $s) {
        $result = $conn->query("SELECT COUNT(*) as count FROM responders WHERE availability = '$s'");
        $stats['responder_status'][$s] = (int)$result->fetch_assoc()['count'];
    }
    
    // Recent calls
    $result = $conn->query("SELECT * FROM emergency_calls ORDER BY created_at DESC LIMIT 5");
    $stats['recent_calls'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['recent_calls'][] = [
            'id' => $row['call_id'],
            'caller_name' => $row['caller_name'],
            'emergency_type' => $row['emergency_type'],
            'incident_location' => $row['incident_location'],
            'priority' => $row['priority_level'] ?? 'Medium',
            'status' => $row['status'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $stats]);
    exit();
}

// If no route matched
echo json_encode(['success' => false, 'error' => 'Invalid request']);
exit();
?>