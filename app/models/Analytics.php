<?php

class Analytics extends Model
{
    protected $table = 'analytics';
    
    public function logPageView($sessionId, $userId = null, $pageName, $url, $referer = '', $userAgent = '')
    {
        $data = [
            'id' => bin2hex(random_bytes(16)),
            'session_id' => $sessionId,
            'user_id' => $userId,
            'ip_address' => $this->getClientIp(),
            'user_agent' => $userAgent ?: ($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'referer' => $referer ?: ($_SERVER['HTTP_REFERER'] ?? ''),
            'current_url' => $url,
            'page_name' => $pageName,
            'event_type' => 'pageview',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($data);
    }
    
    public function logEvent($sessionId, $userId = null, $eventType, $pageName, $data = [])
    {
        $record = [
            'id' => bin2hex(random_bytes(16)),
            'session_id' => $sessionId,
            'user_id' => $userId,
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'current_url' => $_SERVER['REQUEST_URI'] ?? '',
            'page_name' => $pageName,
            'event_type' => $eventType,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($record);
    }
    
    public function getUniqueVisitors($days = 30)
    {
        $sql = "SELECT COUNT(DISTINCT session_id) as count 
                FROM analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $this->db->fetchOne($sql, [$days])['count'] ?? 0;
    }
    
    public function getPageViews($days = 30)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM analytics 
                WHERE event_type = 'pageview' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $this->db->fetchOne($sql, [$days])['count'] ?? 0;
    }
    
    public function getTopPages($days = 30, $limit = 10)
    {
        $sql = "SELECT page_name, COUNT(*) as views, 
                COUNT(DISTINCT session_id) as unique_views
                FROM analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY page_name 
                ORDER BY views DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$days, $limit]);
    }
    
    public function getTrafficSources($days = 30)
    {
        $sql = "SELECT 
                CASE 
                    WHEN referer = '' OR referer IS NULL THEN 'Közvetlen'
                    WHEN referer LIKE '%google%' THEN 'Google'
                    WHEN referer LIKE '%facebook%' THEN 'Facebook'
                    WHEN referer LIKE '%instagram%' THEN 'Instagram'
                    WHEN referer LIKE '%linkedin%' THEN 'LinkedIn'
                    WHEN referer LIKE '%twitter%' OR referer LIKE '%x.com%' THEN 'Twitter'
                    ELSE 'Egyéb'
                END as source,
                COUNT(*) as visits,
                COUNT(DISTINCT session_id) as unique_visits
                FROM analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY source 
                ORDER BY visits DESC";
        return $this->db->fetchAll($sql, [$days]);
    }
    
    public function getDailyStats($days = 30)
    {
        $sql = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as page_views,
                COUNT(DISTINCT session_id) as unique_visitors,
                COUNT(DISTINCT user_id) as registered_users
                FROM analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at) 
                ORDER BY date DESC";
        return $this->db->fetchAll($sql, [$days]);
    }
    
    public function getUserRegistrations($days = 30)
    {
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as registrations
                FROM users 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC";
        return $this->db->fetchAll($sql, [$days]);
    }
    
    public function getAvgTimeOnPage($days = 30)
    {
        $sql = "SELECT page_name, AVG(time_on_page) as avg_time
                FROM analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND time_on_page > 0
                GROUP BY page_name
                ORDER BY avg_time DESC";
        return $this->db->fetchAll($sql, [$days]);
    }
    
    public function getBounceRate($days = 30)
    {
        $sql = "SELECT 
                COUNT(CASE WHEN page_views = 1 THEN 1 END) as bounces,
                COUNT(*) as total_sessions
                FROM (
                    SELECT session_id, COUNT(*) as page_views
                    FROM analytics 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY session_id
                ) as sessions";
        $result = $this->db->fetchOne($sql, [$days]);
        if ($result['total_sessions'] > 0) {
            return round(($result['bounces'] / $result['total_sessions']) * 100, 1);
        }
        return 0;
    }
    
    private function getClientIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        return explode(',', $ip)[0];
    }
}
