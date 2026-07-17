<?php
require_once 'config/db_connect.php';

echo "Setting up Barangay 178 Emergency System...\n";

// Check if admin exists
$check = $conn->query("SELECT * FROM users WHERE email = 'admin@barangay178.gov.ph'");
if ($check->num_rows === 0) {
    // Insert admin
    $password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // Admin@123
    $sql = "INSERT INTO users (email, password, full_name, barangay_id, role, contact_number, is_active) 
            VALUES ('admin@barangay178.gov.ph', '$password_hash', 'Barangay Administrator', '178', 'admin', '09123456788', 1)";
    
    if ($conn->query($sql)) {
        echo "✅ Admin user created successfully!\n";
    } else {
        echo "❌ Error creating admin: " . $conn->error . "\n";
    }
} else {
    echo "ℹ️ Admin user already exists.\n";
}

// Insert responders
$check_responders = $conn->query("SELECT * FROM responders WHERE team_name = 'Alpha Team'");
if ($check_responders->num_rows === 0) {
    $sql = "INSERT INTO responders (user_id, team_name, specialization, availability) VALUES 
            (1, 'Alpha Team', 'Fire Rescue', 'Available'),
            (1, 'Bravo Team', 'Medical Response', 'Available'),
            (1, 'Charlie Team', 'Police Response', 'Available')";
    
    if ($conn->query($sql)) {
        echo "✅ Responders created successfully!\n";
    } else {
        echo "❌ Error creating responders: " . $conn->error . "\n";
    }
} else {
    echo "ℹ️ Responders already exist.\n";
}

// Insert barangay contacts
$check_contacts = $conn->query("SELECT * FROM barangay_contacts WHERE name = 'Barangay Captain'");
if ($check_contacts->num_rows === 0) {
    $sql = "INSERT INTO barangay_contacts (name, position, contact_number, email, department) VALUES 
            ('Barangay Captain', 'Barangay Captain', '09123456789', 'captain@barangay178.gov.ph', 'Barangay Hall'),
            ('Barangay Secretary', 'Secretary', '09123456780', 'secretary@barangay178.gov.ph', 'Barangay Hall'),
            ('Barangay Health Center', 'Health Officer', '09123456782', 'health@barangay178.gov.ph', 'Health Services'),
            ('Barangay Police', 'Police Officer', '09123456783', 'police@barangay178.gov.ph', 'Peace and Order')";
    
    if ($conn->query($sql)) {
        echo "✅ Barangay contacts created successfully!\n";
    } else {
        echo "❌ Error creating contacts: " . $conn->error . "\n";
    }
} else {
    echo "ℹ️ Barangay contacts already exist.\n";
}

echo "\n=== Setup Complete ===";
echo "\nLogin: admin@barangay178.gov.ph";
echo "\nPassword: Admin@123";
echo "\n";
?>