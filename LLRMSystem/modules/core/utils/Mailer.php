<?php
/**
 * Mailer Utility
 * This class handles sending emails (OTP, Notifications, etc.)
 * Uses PHPMailer for reliable SMTP delivery via Gmail.
 */

// Include PHPMailer files
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Mailer {
    private $host;
    private $port;
    private $user;
    private $pass;
    private $from;
    private $fromName;

    public function __construct() {
        $this->host = defined('SMTP_HOST') ? SMTP_HOST : '';
        $this->port = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $this->user = defined('SMTP_USER') ? SMTP_USER : '';
        $this->pass = defined('SMTP_PASS') ? SMTP_PASS : '';
        $this->from = defined('SMTP_FROM') ? SMTP_FROM : '';
        $this->fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'LLRM System Security';
    }

    /**
     * Send an OTP code to a user
     */
    public function sendOTP($toEmail, $otpCode) {
        $subject = "Your LLRM System Login Code";
        $body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 10px; max-width: 500px; margin: auto;'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <h1 style='color: #b91c1c; margin: 0;'>LLRM System</h1>
                    <p style='color: #666; font-size: 14px;'>Legislative Records Management</p>
                </div>
                <div style='padding: 20px; background-color: #fff; border: 1px solid #ddd; border-radius: 8px;'>
                    <h2 style='color: #333; font-size: 18px; margin-top: 0;'>Security Verification</h2>
                    <p>Hello,</p>
                    <p>You are attempting to log in. Please use the following code to complete your verification:</p>
                    <div style='background-color: #fcebeb; padding: 20px; text-align: center; font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #b91c1c; border-radius: 8px; margin: 25px 0;'>
                        {$otpCode}
                    </div>
                    <p style='color: #666; font-size: 13px; font-style: italic;'>This code is valid for 10 minutes. If you did not request this, please ignore this email.</p>
                </div>
                <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='font-size: 11px; color: #999; text-align: center;'>This is an automated system message. Please do not reply.</p>
            </div>
        ";

        return $this->send($toEmail, $subject, $body);
    }

    /**
     * Core send function using PHPMailer
     */
    private function send($to, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->user;
            $mail->Password   = $this->pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->port;
            
            // SMTP Debugging (logs to error_log)
            $mail->SMTPDebug = SMTP::DEBUG_OFF; 

            // Recipients
            $mail->setFrom($this->from, $this->fromName);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags(str_replace(['<br>', '</div>', '</p>'], ["\n", "\n", "\n"], $body));

            $mail->send();
            error_log("Email successfully sent to $to via SMTP");
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}
