<?php

namespace Core\Config;
use Core\Parser\Parser;

class Config {
    protected static $_instance;
    protected static $_config = array();
    
    private function __construct(){}
    
    private function __clone(){}
    
    public static function getInstance() 
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /*
     * Load config xml file from Setting folder
     * @param string $name name of loaded config file, be default is application.xml
     */
    public static function loadConfigFile($name = null) 
    {
        static::$_config = Parser::factory('Xml');
        static::$_config->loadXml($name == null ? 'application' : $name);
        return static::$_config;
	}
    
    /*
     * Get values in node
     * @param string $key node name
     * @param string $sub param name
     * @param string $config load config file
     */
    public static function getVal($key, $sub, $config = null) 
    {
        if ($config || static::$_config->config != 'application') {
            static::loadConfigFile($config);
        }

        static::$_config->getNode($key);
        return static::$_config->getVal($sub);
	}

}
