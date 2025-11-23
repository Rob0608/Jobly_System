<?php
// CLI script to send a quick SMTP test using the phpmailer helper.
// Usage: php scripts/test_smtp_send.php recipient@example.com

require_once __DIR__ . '/../app/helpers/phpmailer_helper.php';

if (PHP_SAPI !== 'cli') {
    echo "This script is CLI only.\n";
    exit(2);
}

$recipient = $argv[1] ?? '';
if (empty($recipient) || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
    echo "Usage: php scripts/test_smtp_send.php recipient@example.com\n";
    exit(2);
}

// Basic environment checks
$smtpHost = getenv('SMTP_HOST') ?: '';
if (empty($smtpHost)) {
    echo "SMTP_HOST is not set. Configure SMTP environment variables first.\n";
    exit(3);
}

$subject = 'Jobly System SMTP Test (' . date('Y-m-d H:i:s') . ')';
$body = '<p>This is a test email from Jobly System. If you received this, SMTP works.</p>';

$res = phpmailer_send([
    'to' => $recipient,
    'subject' => $subject,
    'body' => $body,
    'is_html' => true,
]);

if ($res['success']) {
    echo "SMTP test succeeded: email sent to {$recipient}\n";
    exit(0);
} else {
    echo "SMTP test failed: " . ($res['error'] ?? 'unknown error') . "\n";
    // If SMTP_DEBUG environment is present, PHPMailer debug will be printed to logs.
    exit(1);
}
