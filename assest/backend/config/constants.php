<?php
// Barangay Information
define('BARANGAY_NAME', 'Barangay 178 Camarin North');
define('BARANGAY_CITY', 'Caloocan City');
define('BARANGAY_CONTACT', '02-8123-4567');
define('BARANGAY_EMAIL', 'barangay178@caloocan.gov.ph');

// Emergency Contact Numbers
define('EMERGENCY_HOTLINE', '911');
define('BARANGAY_HOTLINE', '02-8123-4567');
define('POLICE_HOTLINE', '117');
define('FIRE_HOTLINE', '160');
define('MEDICAL_HOTLINE', '112');

// Session Timeout (15 minutes)
define('SESSION_TIMEOUT', 900);

// Login Attempts
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 300); // 5 minutes

// OTP Settings
define('OTP_EXPIRY', 5); // 5 minutes
define('OTP_LENGTH', 6);

// JWT Secret (change this!)
define('JWT_SECRET', 'your-secret-key-change-this-in-production');

// Gemini API (get from https://makersuite.google.com/app/apikey)
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent');
?>