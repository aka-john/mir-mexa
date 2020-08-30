<?php

namespace Core\Components;

use Core\Parser\Parser;

/*
 * Translator class. Get messagers or text in translate.xml.Singleton class
 */
class Translator {
    
    protected static $_translate = array();
    protected static $_instance;
    
    private function __construct(){
        static::$_translate = Parser::factory('Xml');
        static::$_translate->loadXml('translate');
    }
    
    private function __clone(){}
    
    public static function getInstance() 
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /*
     * Load config file
     * @param string name of config. By default is translate
     * @return object
     */
    public static function loadConfigFile($name = null) 
    {
        static::$_instance = Parser::factory('Xml');
        static::$_instance->loadXml($name == null ? 'translate' : $name);
        return static::$_instance;
	}
    
    /*
     * Get translate
     * @param string ke
     * @param string config. By default is translate
     * @param string language. By default is ru
     * @return object
     */
    public static function getTranslate($key, $config = 'translate', $language = null)
    {
        if (count(static::$_translate) < 1) {
            self::getInstance();
        }
        
        if (static::$_translate->config != 'translate') {
            static::$_translate->loadXml('translate');
        }

        static::$_translate->getNode($key);
        return static::$_translate->getVal($language == null ? 'ru' : $language);
    }
}
