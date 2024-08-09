<?php

namespace App\Controllers;

use App\Helpers\URLEncode;
use App\Middleware\JwtMiddleware;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class EmailSender {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }

    private function configureMailer() {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['SMTP_HOST'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['SENDER_EMAIL'];
            $this->mailer->Password = $_ENV['SENDER_PASSWORD'];
            $this->mailer->SMTPSecure = 'ssl';
            $this->mailer->Port = 465; 
            $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER; // Enable debugging
            $this->mailer->Debugoutput = function($str, $level) {
                file_put_contents('../logs/phpmailer_debug.log', date('Y-m-d H:i:s')." - $level: $str\n", FILE_APPEND);
            };
            // Set a valid hostname for the EHLO command
            $this->mailer->Helo = $_ENV['SMTP_HOST'] ?? 'domain.com';
        } catch (Exception $e) {
            throw new Exception("Mailer configuration error: " . $e->getMessage());
        }
    }

    public function sendPlainTextEmail($to, $subject, $body, $from = null, $fromName = null) {
        $from = $from ?? $_ENV['SENDER_EMAIL'];
        $fromName = $fromName ?? 'NoReply';

        try {
            $this->mailer->setFrom($from, $fromName);
            $this->mailer->addAddress($to);
            if (filter_var($from, FILTER_VALIDATE_EMAIL) === false) {
                throw new Exception("Invalid 'from' email address");
            }
            if (filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
                throw new Exception("Invalid 'to' email address");
            }
            $this->mailer->isHTML(false);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            return [
                'code' => 500,
                'message' => 'Message could not be sent. Mailer Error: ' . $this->mailer->ErrorInfo,
                'data' => null
            ];
        }
    }

    public function sendHTMLEmail($to, $subject, $htmlBody, $from = null, $fromName = null) {
        $from = $from ?? $_ENV['SENDER_EMAIL'];
        $fromName = $fromName ?? 'NoReply';
    
        try {
    
            $this->mailer->setFrom($from, $fromName);
            $this->mailer->addAddress($to);
            if (filter_var($from, FILTER_VALIDATE_EMAIL) === false) {
                throw new Exception("Invalid 'from' email address");
            }
            if (filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
                throw new Exception("Invalid 'to' email address");
            }
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = strip_tags($htmlBody);
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            return [
                'code' => 500,
                'message' => 'Message could not be sent. Mailer Error: ' . $this->mailer->ErrorInfo,
                'data' => null
            ];
        }
    }
    
    
}
