<?php
// Lightweight PHPMailer wrapper that reads SMTP config from environment
// Usage: phpmailer_send(['to' => 'user@domain', 'to_name' => 'User', 'subject'=>'...', 'body'=>'...', 'attachments'=>[['path'=>'/tmp/x.pdf','name'=>'x.pdf']]]);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../app/third_party/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../app/third_party/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../app/third_party/PHPMailer/src/SMTP.php';

function phpmailer_send(array $opts)
{
    $to = $opts['to'] ?? '';
    $to_name = $opts['to_name'] ?? '';
    $subject = $opts['subject'] ?? '';
    $body = $opts['body'] ?? '';
    $is_html = $opts['is_html'] ?? true;
    $from = $opts['from'] ?? getenv('SMTP_FROM') ?: (getenv('SMTP_USERNAME') ?: 'noreply@localhost');
    $from_name = $opts['from_name'] ?? getenv('SMTP_FROM_NAME') ?: 'Job Portal';
    $attachments = $opts['attachments'] ?? [];

    $smtpHost = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $smtpPort = getenv('SMTP_PORT') ?: '587';
    $smtpUser = getenv('SMTP_USERNAME') ?: 'robabarintos@gmail.com';
    $smtpPass = getenv('SMTP_PASSWORD') ?: 'osme task gmti itav';
    $smtpSecure = getenv('SMTP_SECURE') ?: 'tls';
    $smtpAuth = getenv('SMTP_AUTH') !== false ? getenv('SMTP_AUTH') : '1';

    try {
        $mail = new PHPMailer(true);

        if (!empty($smtpHost)) {
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = ($smtpAuth === '1' || $smtpAuth === 'true');
            if (!empty($smtpUser)) {
                $mail->Username = $smtpUser;
                $mail->Password = $smtpPass;
            }
            if (!empty($smtpSecure)) {
                if (strtolower($smtpSecure) === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } else {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }
            }
            if (!empty($smtpPort)) {
                $mail->Port = (int)$smtpPort;
            }
        } else {
            // fallback to mail() if SMTP not configured
            $mail->isMail();
        }

        // Honor SMTP debug level from environment for troubleshooting
        $smtpDebug = getenv('SMTP_DEBUG');
        if ($smtpDebug !== false) {
            $mail->SMTPDebug = (int)$smtpDebug;
        }

        $mail->setFrom($from, $from_name);
        if (!empty($to_name)) {
            $mail->addAddress($to, $to_name);
        } else {
            $mail->addAddress($to);
        }

        foreach ($attachments as $att) {
            if (is_array($att) && !empty($att['path'])) {
                $mail->addAttachment($att['path'], $att['name'] ?? null);
            } elseif (is_string($att) && file_exists($att)) {
                $mail->addAttachment($att);
            }
        }

        $mail->isHTML($is_html);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return ['success' => true, 'error' => null];
    } catch (Exception $e) {
        error_log('phpmailer_send error: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function mailer_helper(array $opts)
{
    return phpmailer_send($opts);
}
