<?php

class SimpleMail
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
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
        
        $log = sprintf("[%s] To: %s | Subject: %s\n", 
            date('Y-m-d H:i:s'), $to, $subject
        );
        file_put_contents(__DIR__ . '/../../logs/email_log.txt', $log, FILE_APPEND);
        
        if (empty($settings['smtp_host'])) {
            return false;
        }
        
        $headers = [
            'From: ' . ($settings['smtp_from_name'] ?? 'IdeaForge') . ' <' . ($settings['smtp_from_email'] ?? 'noreply@ideaforge.hu') . '>',
            'Reply-To: ' . ($settings['smtp_from_email'] ?? 'noreply@ideaforge.hu'),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        return mail($to, $subject, $body, implode("\r\n", $headers));
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
}
