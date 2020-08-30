<?php

namespace Core\Components;

class Router
{
    public static $host;
    public static $generator;

    protected static $_instance;
    
    private function __construct(){
        static::$host = CRM_GET_HOST();
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
     * Add host
     * @param string name
     */
    public static function addHost($host)
    {
        static::$host = $host;
    }

    public function generate($controller, $action, $params = array())
    {
        if (count($params) > 0) {
            $item = array();
            foreach ($params as $key => $value) {
               $item[] = $key.'='.$value;
            }
            
            $params = '&'.implode('&', $item);
        } else {
            $params = '';
        }
        
        return CRM_MODULE_PATH().'&controller='.$controller.'&action='.$action.$params;
    }

}