<?php
require_once '../config/db_connect.php';
require_once '../config/constants.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    $call_id = $input['call_id'] ?? 0;
    
    if (!$call_id) {
        echo json_encode(['success' => false, 'error' => 'Call ID required']);
        exit();
    }
    
    // Get call details
    $stmt = $conn->prepare("SELECT * FROM emergency_calls WHERE call_id = ?");
    $stmt->bind_param("i", $call_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $call = $result->fetch_assoc();
    
    if (!$call) {
        echo json_encode(['success' => false, 'error' => 'Call not found']);
        exit();
    }
    
    // Prepare data for Gemini
    $incident_data = [
        'emergency_type' => $call['emergency_type'],
        'incident_location' => $call['incident_location'],
        'incident_details' => $call['incident_details'] ?? 'No details provided',
        'caller_name' => $call['caller_name'],
        'time' => date('Y-m-d H:i:s')
    ];
    
    // Call Gemini API
    $gemini_response = callGeminiAPI($incident_data);
    
    if ($gemini_response) {
        $analysis = parseGeminiResponse($gemini_response);
        
        // Update call with AI analysis
        $update = $conn->prepare("UPDATE emergency_calls SET 
            priority_level = ?,
            ai_priority_score = ?,
            status = 'Dispatched'
            WHERE call_id = ?");
        $update->bind_param("sdi", $analysis['priority'], $analysis['score'], $call_id);
        $update->execute();
        
        // Log AI response
        $log = $conn->prepare("INSERT INTO ai_response_logs 
            (call_id, request_data, response_data, priority_score, reasoning) 
            VALUES (?, ?, ?, ?, ?)");
        $request_json = json_encode($incident_data);
        $response_json = json_encode($gemini_response);
        $log->bind_param("issis", $call_id, $request_json, $response_json, $analysis['score'], $analysis['reasoning']);
        $log->execute();
        
        echo json_encode([
            'success' => true,
            'priority' => $analysis['priority'],
            'score' => $analysis['score'],
            'response_time' => $analysis['response_time'],
            'responders_needed' => $analysis['responders_needed'],
            'risk_level' => $analysis['risk_level'],
            'analysis' => $analysis['reasoning']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'AI analysis failed']);
    }
    exit();
}

// Gemini API functions
function callGeminiAPI($incident_data) {
    $api_key = GEMINI_API_KEY;
    $url = GEMINI_API_URL . '?key=' . $api_key;
    
    $prompt = "As an emergency response system for Barangay 178 Camarin North, Caloocan City, 
               analyze this emergency incident and provide:
               1. Priority Level (Critical, High, Medium, Low)
               2. Score (0-100)
               3. Recommended response time
               4. Suggested responders needed
               5. Risk assessment (1-5)
               
               Incident Details:
               - Type: {$incident_data['emergency_type']}
               - Location: {$incident_data['incident_location']}
               - Details: {$incident_data['incident_details']}
               - Caller: {$incident_data['caller_name']}
               - Time: " . date('Y-m-d H:i:s') . "
               
               Consider:
               - Barangay 178 is a densely populated area
               - Limited emergency resources
               - Traffic conditions in Camarin North
               - Accessibility of location
               - Risk to public safety
               
               Format your response as:
               Priority: [Critical/High/Medium/Low]
               Score: [0-100]
               Response Time: [X] minutes
               Responders Needed: [X]
               Risk Level: [1-5]
               Reasoning: [Your analysis]";
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 1024
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        return json_decode($response, true);
    }
    
    // Log error
    error_log("Gemini API error: " . $response);
    return null;
}

function parseGeminiResponse($response) {
    if (!$response || !isset($response['candidates'][0]['content']['parts'][0]['text'])) {
        return [
            'priority' => 'Medium',
            'score' => 50,
            'response_time' => '15 minutes',
            'responders_needed' => 2,
            'risk_level' => 3,
            'reasoning' => 'Default analysis due to API error'
        ];
    }
    
    $text = $response['candidates'][0]['content']['parts'][0]['text'];
    
    // Extract priority
    $priority = 'Medium';
    if (stripos($text, 'Critical') !== false) $priority = 'Critical';
    elseif (stripos($text, 'High') !== false) $priority = 'High';
    elseif (stripos($text, 'Medium') !== false) $priority = 'Medium';
    elseif (stripos($text, 'Low') !== false) $priority = 'Low';
    
    // Extract score
    preg_match('/Score:\s*(\d+)/i', $text, $score_match);
    $score = isset($score_match[1]) ? intval($score_match[1]) : 50;
    
    // Extract response time
    preg_match('/Response Time:\s*(\d+)\s*minutes?/i', $text, $time_match);
    $response_time = isset($time_match[1]) ? $time_match[1] . ' minutes' : '15 minutes';
    
    // Extract responders
    preg_match('/Responders Needed:\s*(\d+)/i', $text, $responders_match);
    $responders_needed = isset($responders_match[1]) ? intval($responders_match[1]) : 2;
    
    // Extract risk level
    preg_match('/Risk Level:\s*(\d+)/i', $text, $risk_match);
    $risk_level = isset($risk_match[1]) ? intval($risk_match[1]) : 3;
    
    // Extract reasoning
    preg_match('/Reasoning:\s*(.*?)(?:\n|$)/is', $text, $reasoning_match);
    $reasoning = isset($reasoning_match[1]) ? trim($reasoning_match[1]) : 'Analysis complete.';
    
    return [
        'priority' => $priority,
        'score' => min(100, max(0, $score)),
        'response_time' => $response_time,
        'responders_needed' => $responders_needed,
        'risk_level' => min(5, max(1, $risk_level)),
        'reasoning' => $reasoning
    ];
}
?>