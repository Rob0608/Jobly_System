<?php
// Usage: php test_smtp_send.php recipient@example.com
require_once __DIR__ . '/../app/helpers/phpmailer_helper.php';

$to = $argv[1] ?? null;
if (! $to) {
    echo "Usage: php test_smtp_send.php recipient@example.com\n";
    exit(1);
}

$subject = 'SMTP Test from Jobly_System';
$body = '<p>This is a test email from Jobly_System at ' . date('c') . '</p>';

$res = phpmailer_send([
    'to' => $to,
    'subject' => $subject,
    'body' => $body,
]);

echo "Result:\n";
print_r($res);

if (!empty($res['success'])) {
    echo "Email sent successfully using method: " . ($res['method'] ?? 'unknown') . PHP_EOL;
} else {
    echo "Failed to send email. Error: " . ($res['error'] ?? 'unknown') . PHP_EOL;
}
