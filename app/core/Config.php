<?php

class Config
{
    private static $config = [];
    
    public static function load($configFile)
    {
        if (file_exists($configFile)) {
            self::$config = require $configFile;
        }
    }
    
    public static function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    public static function all()
    {
        return self::$config;
    }
}
