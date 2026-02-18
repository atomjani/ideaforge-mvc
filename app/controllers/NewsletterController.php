<?php

class NewsletterController extends Controller
{
    private $newsletter;
    private $mail;
    
    public function __construct()
    {
        parent::__construct();
        $this->newsletter = new Newsletter();
        $this->mail = new SimpleMail();
    }
    
    public function subscribe()
    {
        $input = $this->getInput();
        $email = $input['email'] ?? '';
        $name = $input['name'] ?? '';
        
        if (!$email) {
            $this->jsonResponse(['error' => 'Hiányzó email'], 400);
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['error' => 'Érvénytelen email cím'], 400);
        }
        
        $this->newsletter->subscribe($email, $name);
        
        $this->jsonResponse(['status' => 'ok']);
    }
    
    public function unsubscribe()
    {
        $input = $this->getInput();
        $email = $input['email'] ?? '';
        
        if (!$email) {
            $this->jsonResponse(['error' => 'Hiányzó email'], 400);
        }
        
        $this->newsletter->unsubscribe($email);
        
        $this->jsonResponse(['status' => 'ok']);
    }
    
    public function check_subscription()
    {
        $input = $this->getInput();
        $email = $input['email'] ?? '';
        
        if (!$email) {
            $this->jsonResponse(['error' => 'Hiányzó email'], 400);
        }
        
        $subscribed = $this->newsletter->isSubscribed($email);
        
        $this->jsonResponse(['subscribed' => $subscribed]);
    }
    
    public function send_newsletter()
    {
        $this->requireAdmin();
        
        $input = $this->getInput();
        $subject = $input['subject'] ?? '';
        $body = $input['body'] ?? '';
        $recipientsType = $input['recipients_type'] ?? 'all';
        
        if (!$subject || !$body) {
            $this->jsonResponse(['error' => 'Hiányzó adatok'], 400);
        }
        
        $emails = [];
        
        if ($recipientsType === 'all' || $recipientsType === 'subscribers') {
            $subscribers = $this->newsletter->getActiveSubscribers();
            foreach ($subscribers as $s) {
                $emails[] = $s['email'];
            }
        }
        
        if ($recipientsType === 'all' || $recipientsType === 'users') {
            $users = $this->newsletter->getRegisteredUserEmails();
            foreach ($users as $u) {
                if (!in_array($u['email'], $emails)) {
                    $emails[] = $u['email'];
                }
            }
        }
        
        if (empty($emails)) {
            $this->jsonResponse(['error' => 'Nincs címzett'], 400);
        }
        
        $results = $this->mail->sendBulk($emails, $subject, $body);
        
        $sentCount = count(array_filter($results));
        
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO newsletter_campaigns (id, subject, body, recipients_type, recipient_count, sent_count, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );
        
        $id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        
        $stmt->execute([$id, $subject, $body, $recipientsType, count($emails), $sentCount]);
        
        $this->jsonResponse([
            'status' => 'ok',
            'sent_count' => $sentCount,
            'total_recipients' => count($emails)
        ]);
    }
    
    public function get_campaigns()
    {
        $this->requireAdmin();
        
        $campaigns = $this->db->fetchAll(
            "SELECT * FROM newsletter_campaigns ORDER BY created_at DESC LIMIT 20"
        );
        
        $this->jsonResponse($campaigns);
    }
}
