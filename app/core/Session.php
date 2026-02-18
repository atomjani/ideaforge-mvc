<?php

class Session
{
    private static $started = false;
    
    public static function start()
    {
        if (self::$started) {
            return;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            session_start();
            self::$started = true;
            
            self::regenerate();
        }
    }
    
    public static function regenerate()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
    
    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    public static function remove($key)
    {
        self::start();
        unset($_SESSION[$key]);
    }
    
    public static function destroy()
    {
        self::start();
        $_SESSION = [];
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        session_destroy();
    }
    
    public static function flash($key, $value = null)
    {
        self::start();
        
        if ($value === null) {
            $value = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $value;
        }
        
        $_SESSION['flash'][$key] = $value;
    }
}
