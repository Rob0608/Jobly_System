<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../third_party/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../third_party/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../third_party/PHPMailer/src/SMTP.php';

/**
 * Robust PHPMailer helper. Tries STARTTLS (587) then SMTPS (465) and falls back to sendmail() or PHP mail().
 * Options (array):
 *  - to: string email OR array (email => name) OR array of emails
 *  - subject: string
 *  - body: html string
 *  - alt: alt body plain text
 *  - attachments: array of file paths or [path, name]
 *  - from: optional from email (defaults to SMTP_USERNAME)
 *  - from_name: optional
 *
 * Returns array with keys: success (bool), error (string|null), method (smtp|sendmail|mail)
 */
function phpmailer_send(array $opts)
{
    $to = $opts['to'] ?? '';
    $subject = $opts['subject'] ?? '(no subject)';
    $body = $opts['body'] ?? '';
    $alt = $opts['alt'] ?? strip_tags($body);
    $attachments = $opts['attachments'] ?? [];
    $from = $opts['from'] ?? getenv('SMTP_USERNAME') ?: 'no-reply@localhost';
    $from_name = $opts['from_name'] ?? 'Job Portal';

    // Read SMTP configuration from environment. No hardcoded secrets here.
    $smtpHost = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $smtpUser = getenv('SMTP_USERNAME') ?: '';
    $smtpPass = getenv('SMTP_PASSWORD') ?: '';
    $smtpPortEnv = getenv('SMTP_PORT');
    $smtpPort = $smtpPortEnv !== false && $smtpPortEnv !== null && $smtpPortEnv !== '' ? intval($smtpPortEnv) : null;
    $smtpSecure = getenv('SMTP_SECURE') ?: ''; // expected 'ssl' or 'tls'
    $smtpAuth = (getenv('SMTP_AUTH') === 'false') ? false : true;
    $smtpDebug = intval(getenv('SMTP_DEBUG') ?: 0);

    $log = function ($msg) { error_log($msg); };

    // If SMTP username/password missing, we'll attempt fallback to sendmail()/mail()
    $useSmtp = !empty($smtpUser) && !empty($smtpPass);

    $mail = new PHPMailer(true);
    try {
        // Decide send method: prefer SMTP if credentials present, otherwise try sendmail/mail
        if ($useSmtp) {
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = $smtpAuth;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;

            // Try a preferred order: STARTTLS on 587, then SMTPS on 465, then provided port
            $attempts = [];
            if ($smtpPort === 587 || strtolower($smtpSecure) === 'tls') {
                $attempts[] = ['port' => 587, 'secure' => 'tls'];
            }
            if ($smtpPort === 465 || strtolower($smtpSecure) === 'ssl') {
                $attempts[] = ['port' => 465, 'secure' => 'ssl'];
            }
            // If user provided a custom port not already in attempts, add it last
            if ($smtpPort !== null && !in_array($smtpPort, array_column($attempts, 'port'))) {
                $attempts[] = ['port' => $smtpPort, 'secure' => $smtpSecure];
            }
            // Always ensure a sensible fallback order if nothing specified
            if (empty($attempts)) {
                $attempts[] = ['port' => 587, 'secure' => 'tls'];
                $attempts[] = ['port' => 465, 'secure' => 'ssl'];
            }

            $sendException = null;
            foreach ($attempts as $a) {
                try {
                    // Configure encryption based on attempt
                    if (strtolower($a['secure']) === 'ssl' || $a['port'] === 465) {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    } else {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    }
                    $mail->Port = $a['port'];

                    if ($smtpDebug > 0) {
                        $mail->SMTPDebug = $smtpDebug;
                        $mail->Debugoutput = function ($str, $level) use ($log) { $log('PHPMailer: ' . trim($str)); };
                    }

                    $mail->setFrom($from, $from_name);

                    // Add recipients
                    if (is_array($to)) {
                        foreach ($to as $k => $v) {
                            if (filter_var($k, FILTER_VALIDATE_EMAIL)) {
                                $mail->addAddress($k, is_string($v) ? $v : '');
                            } else {
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
                    return ['success' => true, 'method' => 'smtp', 'port' => $a['port']];
                } catch (Exception $e) {
                    $sendException = $e;
                    $log('PHPMailer attempt failed (port ' . $a['port'] . '): ' . $e->getMessage());
                    // reset mailer for next loop
                    $mail = new PHPMailer(true);
                }
            }

            // All SMTP attempts failed; capture last exception
            if ($sendException !== null) {
                $log('All SMTP attempts failed. Last error: ' . $sendException->getMessage());
            }

        }

        // If SMTP wasn't used or SMTP attempts failed, try sendmail then PHP mail()
        try {
            $mail->isSendmail();
            $mail->setFrom($from, $from_name);
            if (is_array($to)) {
                foreach ($to as $k => $v) {
                    if (filter_var($k, FILTER_VALIDATE_EMAIL)) {
                        $mail->addAddress($k, is_string($v) ? $v : '');
                    } else {
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
            foreach ($attachments as $att) {
                if (is_array($att)) {
                    $mail->addAttachment($att[0], $att[1] ?? '');
                } else {
                    $mail->addAttachment($att);
                }
            }
            $mail->send();
            return ['success' => true, 'method' => 'sendmail'];
        } catch (Exception $e) {
            $log('Sendmail attempt failed: ' . $e->getMessage());
        }

        // Last resort: PHP mail()
        $headers = "From: {$from_name} <{$from}>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $recipient = is_array($to) ? (is_string(reset($to)) ? reset($to) : key($to)) : $to;
        $ok = @mail($recipient, $subject, $body, $headers);
        if ($ok) {
            return ['success' => true, 'method' => 'mail'];
        }
        return ['success' => false, 'error' => 'All SMTP and local delivery methods failed. Check SMTP credentials, ports, and server mail configuration.'];
    } catch (Exception $e) {
        $log('PHPMailer unrecoverable error: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
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
