<?php
define('PREVENT_DIRECT_ACCESS', false); // Override protection for diagnostic
// Diagnostic script to check password storage
require_once 'app/config/config.php';
require_once 'app/config/database.php';
require_once 'vendor/autoload.php';

// Get database connection
$db_config = config_item('database');
try {
    $pdo = new PDO(
        'mysql:host=' . $db_config['hostname'] . ';dbname=' . $db_config['database'],
        $db_config['username'],
        $db_config['password']
    );
    
    // Check applicant password
    $email = 'aizablnco@gmail.com'; // Change this to your email
    $stmt = $pdo->prepare('SELECT email, password FROM applicants WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $applicant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($applicant) {
        echo "=== APPLICANT PASSWORD DEBUG ===\n";
        echo "Email: " . $applicant['email'] . "\n";
        echo "Password hash: " . $applicant['password'] . "\n";
        echo "Hash length: " . strlen($applicant['password']) . "\n";
        echo "Is bcrypt hash: " . (preg_match('/^\$2[aby]\$/', $applicant['password']) ? 'YES' : 'NO') . "\n\n";
        
        // Test password_verify with different passwords
        $testPasswords = ['robrob0608', 'robrob12', 'test123', 'wrongpassword'];
        foreach ($testPasswords as $pwd) {
            $result = password_verify($pwd, $applicant['password']);
            echo "password_verify('$pwd', hash): " . ($result ? 'MATCH' : 'NO MATCH') . "\n";
        }
        
        // Test if it's plain text
        echo "\n=== Plain text comparison ===\n";
        echo "Stored == 'robrob0608': " . ($applicant['password'] === 'robrob0608' ? 'YES' : 'NO') . "\n";
        echo "Stored == 'robrob12': " . ($applicant['password'] === 'robrob12' ? 'YES' : 'NO') . "\n";
    } else {
        echo "Applicant not found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
