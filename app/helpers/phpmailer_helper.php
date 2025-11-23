<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../third_party/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../third_party/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../third_party/PHPMailer/src/SMTP.php';

/**
 * Simple PHPMailer helper wrapper.
 * Options (array):
 *  - to: string email OR array (email => name) OR array of emails
 *  - subject: string
 *  - body: html string
 *  - alt: alt body plain text
 *  - attachments: array of file paths or [path, name]
 *  - from: optional from email (defaults to SMTP_USERNAME)
 *  - from_name: optional
 *
 * Returns array with keys: success (bool), error (string|null)
 */
function phpmailer_send(array $opts)
{
    $to = $opts['to'] ?? '';
    $subject = $opts['subject'] ?? '(no subject)';
    $body = $opts['body'] ?? '';
    $alt = $opts['alt'] ?? strip_tags($body);
    $attachments = $opts['attachments'] ?? [];
    $from = $opts['from'] ?? getenv('SMTP_USERNAME');
    $from_name = $opts['from_name'] ?? 'Job Portal';

    // Restore previous (inline) SMTP credentials as fallback
    // NOTE: keeping hardcoded credentials in code is insecure — consider using environment variables.
    $smtpHost = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $smtpUser = getenv('SMTP_USERNAME') ?: 'abarintoscristan@gmail.com';
    $smtpPass = getenv('SMTP_PASSWORD') ?: 'aufb puoo iwka pmeg';
    // Default to SMTPS on port 465 like the original implementation
    $smtpPort = intval(getenv('SMTP_PORT') ?: 465);
    $smtpSecure = getenv('SMTP_SECURE') ?: 'ssl';
    $smtpAuth = getenv('SMTP_AUTH') !== 'false';
    $smtpDebug = intval(getenv('SMTP_DEBUG') ?: 0);

    $log = function ($msg) { error_log($msg); };

    if (empty($smtpUser) || empty($smtpPass)) {
        $log('SMTP credentials missing: set SMTP_USERNAME and SMTP_PASSWORD in environment');
        return ['success' => false, 'error' => 'Missing SMTP credentials'];
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = $smtpAuth;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPass;
        if (strtolower($smtpSecure) === 'ssl' || $smtpPort === 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->Port = $smtpPort;

        if ($smtpDebug > 0) {
            $mail->SMTPDebug = $smtpDebug;
            $mail->Debugoutput = function ($str, $level) use ($log) { $log('PHPMailer: ' . trim($str)); };
        }

        $mail->setFrom($from, $from_name);

        // Add recipients
        if (is_array($to)) {
            // associative or numeric
            foreach ($to as $k => $v) {
                if (filter_var($k, FILTER_VALIDATE_EMAIL)) {
                    $mail->addAddress($k, is_string($v) ? $v : '');
                } else {
                    // numeric index: value is email string
                    $mail->addAddress($v);
                }
            }
        } else {
            $mail->addAddress($to);
        }

        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $body;
        $mail->AltBody = $alt;

        // Attachments
        foreach ($attachments as $att) {
            if (is_array($att)) {
                $mail->addAttachment($att[0], $att[1] ?? '');
            } else {
                $mail->addAttachment($att);
            }
        }

        $mail->send();
        return ['success' => true];
    } catch (Exception $e) {
        $log('PHPMailer send failed: ' . $e->getMessage() . ' | ' . ($mail->ErrorInfo ?? ''));
        return ['success' => false, 'error' => $e->getMessage(), 'info' => $mail->ErrorInfo ?? ''];
    }

}

/**
 * Backwards-compatible wrapper matching the previous `mailer_helper` signature.
 * Returns true on success or an error string on failure.
 */
function mailer_helper($recipient, $subject, $message, $attachment_path = null)
{
    $opts = [
        'to' => $recipient,
        'subject' => $subject,
        'body' => $message,
        'attachments' => []
    ];
    if ($attachment_path) {
        $opts['attachments'] = [$attachment_path];
    }
    $res = phpmailer_send($opts);
    if (!empty($res['success'])) return true;
    return $res['error'] ?? 'Unknown error';
}
