<?php
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);

        // Configure SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'simpleave33@gmail.com'; // Your email
        $this->mailer->Password = 'ssbb nnhq otzs cnvi'; // Your app password
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;

        // Set default sender
        $this->mailer->setFrom('simpleave33@gmail.com', 'SimpLeav');
    }

    public function send2FACode($to, $code) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Your SimpLeav Verification Code';
            $this->mailer->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #333;'>Verification Code</h2>
                    <p>Your verification code is:</p>
                    <h1 style='color: #007bff; font-size: 32px; letter-spacing: 5px;'>{$code}</h1>
                    <p style='color: #666;'>This code will expire in 15 minutes.</p>
                    <p style='color: #666;'>If you didn't request this code, please ignore this email.</p>
                </div>
            ";

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Failed to send 2FA code: " . $e->getMessage());
            return false;
        }
    }

    public function sendLoginNotification($to) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'New Login to Your SimpLeav Account';
            $this->mailer->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #333;'>New Login Alert</h2>
                    <p>A new login was detected on your SimpLeav account.</p>
                    <p>Time: " . date('Y-m-d H:i:s') . "</p>
                    <p style='color: #666;'>If this wasn't you, please contact support immediately.</p>
                </div>
            ";

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Failed to send login notification: " . $e->getMessage());
            return false;
        }
    }
}
