<?php
/**
 * Mailer Utility - VDM System
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
        // These constants should be defined in config.php
        $this->host = defined('SMTP_HOST') ? SMTP_HOST : '';
        $this->port = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $this->user = defined('SMTP_USER') ? SMTP_USER : '';
        $this->pass = defined('SMTP_PASS') ? SMTP_PASS : '';
        $this->from = defined('SMTP_FROM') ? SMTP_FROM : '';
        $this->fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'VDM System Security';
    }

    /**
     * Send an OTP code to a user
     */
    public function sendOTP($toEmail, $otpCode) {
        $appName = defined('APP_NAME') ? APP_NAME : 'VDM System';
        $subject = "Your {$appName} Login Verification Code";
        $body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 12px; max-width: 500px; margin: auto; background-color: #ffffff;'>
                <div style='text-align: center; margin-bottom: 25px;'>
                    <div style='display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 10px;'>
                        <h1 style='color: #dc2626; margin: 0; font-size: 28px; font-weight: 800;'>VDM System</h1>
                    </div>
                    <p style='color: #64748b; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;'>Voting & Decisions</p>
                </div>
                <div style='padding: 30px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px;'>
                    <h2 style='color: #1e293b; font-size: 20px; margin-top: 0; font-weight: 700; text-align: center;'>Security Verification</h2>
                    <p style='color: #475569; font-size: 15px; text-align: center; margin-bottom: 25px;'>You are attempting to log in to your account. Please use the verification code below to complete your sign-in.</p>
                    <div style='background-color: #ffffff; padding: 25px; text-align: center; font-size: 42px; font-weight: 800; letter-spacing: 10px; color: #dc2626; border-radius: 12px; border: 2px solid #fee2e2; margin: 25px 0; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);'>
                        {$otpCode}
                    </div>
                    <p style='color: #dc2626; font-size: 13px; font-weight: 700; text-align: center;'>THIS CODE EXPIRES IN 10 MINUTES</p>
                </div>
                <div style='margin-top: 25px; text-align: center;'>
                    <p style='color: #94a3b8; font-size: 12px;'>If you did not request this login, please change your password immediately or contact your system administrator.</p>
                </div>
                <hr style='border: 0; border-top: 1px solid #f1f5f9; margin: 25px 0;'>
                <p style='font-size: 11px; color: #cbd5e1; text-align: center; font-weight: 600;'>&copy; " . date('Y') . " City Government of Valenzuela. All Rights Reserved.</p>
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
            
            // SMTP Debugging
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
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}
