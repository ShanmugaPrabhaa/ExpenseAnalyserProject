<?php
require 'C:\xampp\htdocs\ExpenseAnalyser\phpmailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\ExpenseAnalyser\phpmailer\src\Exception.php';
require 'C:\xampp\htdocs\ExpenseAnalyser\phpmailer\src\SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Create a new PHPMailer instance
$mail = new PHPMailer(true); // Passing true enables exceptions

try {
    // SMTP configuration
    $mail->isSMTP(); // Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'prabhaa.262004@gmail.com'; // SMTP username (your Gmail address)
    $mail->Password = 'gjbkpeacgrwdteog'; // SMTP password (your Gmail password)
    $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587; // TCP port to connect to

    // Sender and recipient settings
    $mail->setFrom('prabhaa.262004@gmail.com', 'prabhaa'); // Sender's email and name
    $mail->addAddress('beprepare.1508@gmail.com', 'shanu'); // Recipient's email and name

    // Email content
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = 'You Achieved Successfully'; // Email subject
    $mail->Body    = 'You complete your goal  amount before the target date'; // Email body in HTML
    $mail->AltBody = 'This is a plain-text message for non-HTML mail clients'; // Plain text alternative

    // Enable debugging output
    $mail->SMTPDebug = SMTP::DEBUG_OFF; // Enable verbose debug output

    // Send email
    $mail->send();
    echo '<div style="display: flex; justify-content: center; align-items: center; height: 100vh; flex-direction: column;">
    <p>Email has been sent successfully!</p>
    <button onclick="goBack()" style="padding: 10px 20px; font-size: 16px; color: white; background-color: #4CAF50; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px;">Back to Push Notifications</button>
  </div>';
    echo '<script>
            function goBack() {
                window.location.href = "push_notification.php";
            }
          </script>';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
