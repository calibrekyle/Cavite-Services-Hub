<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure PHPMailer is installed

$mail = new PHPMailer(true);

try {
    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'patrickbautista508@gmail.com'; // Your Gmail address
    $mail->Password = 'patgel09'; // Generate an App Password (explained below)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Email Content
    $mail->setFrom('patrickbautista508@gmail.com', 'Cavite Services Hub');
    $mail->addAddress('patrickbautista508@gmail.com');
    $mail->Subject = 'Verify Your Email';
    $mail->Body = 'Click the link to verify your account: http://localhost/CaviteServicesHub/verify.php?token=YOUR_TOKEN';

    $mail->send();
    echo 'Email sent successfully!';
} catch (Exception $e) {
    echo "Email sending failed: {$mail->ErrorInfo}";
}
?>
