<?php
require_once '../config/db_connect.php';
require_once '../config/constants.php';

// Get dashboard statistics
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $response = [];
    
    // Emergency call statistics
    $queries = [
        'total_calls' => "SELECT COUNT(*) as count FROM emergency_calls",
        'pending' => "SELECT COUNT(*) as count FROM emergency_calls WHERE status = 'Pending'",
        'in_progress' => "SELECT COUNT(*) as count FROM emergency_calls WHERE status IN ('Dispatched', 'In-Progress')",
        'resolved' => "SELECT COUNT(*) as count FROM emergency_calls WHERE status IN ('Resolved', 'Closed')",
        'critical' => "SELECT COUNT(*) as count FROM emergency_calls WHERE priority_level = 'Critical'",
        'today_calls' => "SELECT COUNT(*) as count FROM emergency_calls WHERE DATE(created_at) = CURDATE()",
    ];
    
    foreach ($queries as $key => $sql) {
        $result = $conn->query($sql);
        $response[$key] = (int)$result->fetch_assoc()['count'];
    }
    
    // Responder stats
    $result = $conn->query("SELECT COUNT(*) as count FROM responders WHERE availability = 'Available'");
    $response['available_responders'] = (int)$result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM responders");
    $response['total_responders'] = (int)$result->fetch_assoc()['count'];
    
    // AI stats
    $result = $conn->query("SELECT AVG(priority_score) as avg_score, COUNT(*) as total_ai FROM ai_response_logs");
    $ai = $result->fetch_assoc();
    $response['avg_ai_score'] = round($ai['avg_score'] ?? 0);
    $response['total_ai'] = (int)($ai['total_ai'] ?? 0);
    
    // Priority distribution
    $priorities = ['Critical', 'High', 'Medium', 'Low'];
    $response['priority_distribution'] = [];
    foreach ($priorities as $p) {
        $result = $conn->query("SELECT COUNT(*) as count FROM emergency_calls WHERE priority_level = '$p'");
        $response['priority_distribution'][$p] = (int)$result->fetch_assoc()['count'];
    }
    
    // Emergency type distribution
    $types = ['Fire', 'Medical', 'Crime', 'Accident', 'Natural Disaster', 'Flood', 'Other'];
    $response['type_distribution'] = [];
    foreach ($types as $t) {
        $result = $conn->query("SELECT COUNT(*) as count FROM emergency_calls WHERE emergency_type = '$t'");
        $response['type_distribution'][$t] = (int)$result->fetch_assoc()['count'];
    }
    
    // Responder status distribution
    $statuses = ['Available', 'Busy', 'Off-Duty'];
    $response['responder_status'] = [];
    foreach ($statuses as $s) {
        $result = $conn->query("SELECT COUNT(*) as count FROM responders WHERE availability = '$s'");
        $response['responder_status'][$s] = (int)$result->fetch_assoc()['count'];
    }
    
    // Recent calls
    $result = $conn->query("SELECT * FROM emergency_calls ORDER BY created_at DESC LIMIT 5");
    $response['recent_calls'] = [];
    while ($row = $result->fetch_assoc()) {
        $response['recent_calls'][] = [
            'id' => $row['call_id'],
            'caller_name' => $row['caller_name'],
            'emergency_type' => $row['emergency_type'],
            'incident_location' => $row['incident_location'],
            'priority' => $row['priority_level'] ?? 'Medium',
            'status' => $row['status'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $response]);
    exit();
}
?>