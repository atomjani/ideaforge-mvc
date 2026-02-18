<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mail
{
    private $mail;
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->mail = new PHPMailer(true);
        $this->configure();
    }
    
    private function configure()
    {
        $settings = $this->getSmtpSettings();
        
        if (empty($settings['smtp_host'])) {
            return;
        }
        
        $this->mail->isSMTP();
        $this->mail->Host = $settings['smtp_host'];
        $this->mail->Port = $settings['smtp_port'] ?? 587;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $settings['smtp_user'] ?? '';
        $this->mail->Password = $settings['smtp_pass'] ?? '';
        $this->mail->CharSet = 'UTF-8';
    }
    
    private function getSmtpSettings()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM settings LIMIT 1");
            return $stmt->fetch() ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function send($to, $subject, $body)
    {
        $settings = $this->getSmtpSettings();
        
        if (empty($settings['smtp_host'])) {
            $this->logEmail($to, $subject, 'SMTP not configured');
            return false;
        }
        
        try {
            $this->mail->setFrom(
                $settings['smtp_from_email'] ?? 'noreply@ideaforge.hu',
                $settings['smtp_from_name'] ?? 'IdeaForge'
            );
            
            $this->mail->addAddress($to);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->AltBody = strip_tags($body);
            
            $this->mail->send();
            
            $this->logEmail($to, $subject, 'sent');
            return true;
            
        } catch (Exception $e) {
            $this->logEmail($to, $subject, 'error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function sendBulk($recipients, $subject, $body)
    {
        $results = [];
        
        foreach ($recipients as $email) {
            $results[$email] = $this->send($email, $subject, $body);
            
            usleep(100000);
        }
        
        return $results;
    }
    
    private function logEmail($to, $subject, $status)
    {
        $log = sprintf("[%s] To: %s | Subject: %s | Status: %s\n", 
            date('Y-m-d H:i:s'), 
            $to, 
            $subject, 
            $status
        );
        
        file_put_contents(__DIR__ . '/../../logs/email_log.txt', $log, FILE_APPEND);
    }
    
    public static function sendQuick($to, $subject, $body)
    {
        $mail = new self();
        return $mail->send($to, $subject, $body);
    }
}
