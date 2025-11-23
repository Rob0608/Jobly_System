<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'app/third_party/PHPMailer/src/Exception.php';
require_once 'app/third_party/PHPMailer/src/PHPMailer.php';
require_once 'app/third_party/PHPMailer/src/SMTP.php';


$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'robabarintos@gmail.com'; // palitan ng Gmail mo
    $mail->Password   = 'zfmeirqxgycztvlw';   // iyong App Password (no spaces)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('robabarintos@gmail.com', 'Test Mailer');
    $mail->addAddress('robabarintos@gmail.com', 'Test Receiver'); // pwedeng ikaw rin muna

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'PHPMailer Test';
    $mail->Body    = 'This is a test email sent using your Gmail account and PHPMailer. 🎉';

    $mail->send();
    echo '✅ Message has been sent successfully';
} catch (Exception $e) {
    echo "❌ Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
