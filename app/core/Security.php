<?php

class Security
{
    const CSRF_TOKEN_NAME = 'csrf_token';
    const CSRF_TOKEN_LENGTH = 32;
    
    public static function generateCsrfToken(): string
    {
        if (!isset($_SESSION[self::CSRF_TOKEN_NAME])) {
            $_SESSION[self::CSRF_TOKEN_NAME] = bin2hex(random_bytes(self::CSRF_TOKEN_LENGTH));
        }
        return $_SESSION[self::CSRF_TOKEN_NAME];
    }
    
    public static function getCsrfToken(): string
    {
        return self::generateCsrfToken();
    }
    
    public static function validateCsrfToken(string $token): bool
    {
        if (!isset($_SESSION[self::CSRF_TOKEN_NAME])) {
            return false;
        }
        return hash_equals($_SESSION[self::CSRF_TOKEN_NAME], $token);
    }
    
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    public static function checkCsrfToken(): bool
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return self::validateCsrfToken($token);
    }
    
    public static function sanitizeHtml(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    
    public static function sanitizeXss(string $input): string
    {
        $input = stripslashes($input);
        $input = str_replace(['<script>', '</script>', '<iframe>', '</iframe>'], '', $input);
        $input = preg_replace('/javascript:/i', '', $input);
        $input = preg_replace('/on\w+=/i', '', $input);
        return $input;
    }
    
    public static function sanitizeForHtmlAttribute(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    
    public static function sanitizeForJavaScript(string $input): string
    {
        $input = json_encode($input, JSON_UNESCAPED_UNICODE);
        return $input;
    }
    
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    public static function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
    
    public static function generateUuid(): string
    {
        return bin2hex(random_bytes(16));
    }
    
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function validatePasswordStrength(string $password, int $minLength = 8): array
    {
        $errors = [];
        
        if (strlen($password) < $minLength) {
            $errors[] = "A jelszónak minimum {$minLength} karakter hosszúnak kell lennie";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "A jelszónak tartalmaznia kell legalább egy nagybetűt";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "A jelszónak tartalmaznia kell legalább egy kisbetűt";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "A jelszónak tartalmaznia kell legalább egy számot";
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "A jelszónak tartalmaznia kell legalább egy speciális karaktert";
        }
        
        return $errors;
    }
    
    public static function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        
        return explode(',', $ip)[0];
    }
    
    public static function isRateLimited(string $key, int $maxAttempts = 5, int $timeWindow = 300): bool
    {
        $rateKey = 'rate_limit_' . $key;
        
        if (!isset($_SESSION[$rateKey])) {
            $_SESSION[$rateKey] = ['attempts' => 0, 'first_attempt' => time()];
        }
        
        $rateData = $_SESSION[$rateKey];
        $timePassed = time() - $rateData['first_attempt'];
        
        if ($timePassed > $timeWindow) {
            $_SESSION[$rateKey] = ['attempts' => 0, 'first_attempt' => time()];
            return false;
        }
        
        if ($rateData['attempts'] >= $maxAttempts) {
            return true;
        }
        
        $_SESSION[$rateKey]['attempts']++;
        return false;
    }
    
    public static function resetRateLimit(string $key): void
    {
        $rateKey = 'rate_limit_' . $key;
        unset($_SESSION[$rateKey]);
    }
    
    public static function setSecurityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }
    
    public static function getAllowedOrigins(): array
    {
        return [
            'https://ideaforge.uzletinovekedes.hu',
            'https://www.ideaforge.uzletinovekedes.hu',
            'http://ideaforge.uzletinovekedes.hu',
            'http://www.ideaforge.uzletinovekedes.hu'
        ];
    }
    
    public static function validateOrigin(string $origin): bool
    {
        return in_array($origin, self::getAllowedOrigins(), true);
    }
}
