<?php

namespace Core\Components;

/*
 * Flash/Warning message class. Store in session flash and warning. Singleton class
 * flesh message unsets after view
 */
class Flesh
{
    protected static $_instance;
    
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
     * Set flash message
     * @param string message
     * @param string color
     * @return ''
     */
    public static function setFlesh($message, $color = 'danger') 
    {
        $flesh = isset($_SESSION['flesh']) ? $_SESSION['flesh'] : array();
        $count = count($flesh);
        $flesh[$count]['alert'] = $message;
        $flesh[$count]['color'] = $color;
        $_SESSION['flesh'] = $flesh;
    }
    
    /*
     * Get flesh message
     */
    public static function getFlesh() 
    {
        if (isset($_SESSION['flesh'])) {
            $message = (array)$_SESSION['flesh'];
            self::unsetFlesh();
            return $message;
        }
    }
    
    /*
     * Remove flesh message
     */
    public static function unsetFlesh() 
    {
        if (isset($_SESSION['flesh'])) {
            unset($_SESSION['flesh']);
        }
    }
    
    /*
     * Check on flesh exist
     */
    public static function isFlesh() 
    {
        if (isset($_SESSION['flesh'])) {
            return true;
        }
        
        return false;
    }
    
    /*
     * Set warning message
     * @param string message
     * @param string color
     * @return ''
     */
    public static function setWarning($message, $color = 'danger') 
    {
        $flesh = isset($_SESSION['warning']) ? $_SESSION['warning'] : array();
        $count = count($flesh);
        $flesh[$count]['alert'] = $message;
        $flesh[$count]['color'] = $color;
        $_SESSION['warning'] = $flesh;
    }
    
    /*
     * Get warning message
     */
    public static function getWarning() 
    {
        if (isset($_SESSION['warning'])) {
            $message = (array)$_SESSION['warning'];
            self::unsetWarning();
            return $message;
        }
    }
    
    /*
     * Remove warning message
     */
    public static function unsetWarning() 
    {
        if (isset($_SESSION['warning'])) {
            unset($_SESSION['warning']);
        }
    }
    
    /*
     * Check on warning exist
     */
    public static function isWarning() 
    {
        if (isset($_SESSION['warning'])) {
            return true;
        }
        
        return false;
    }

}
