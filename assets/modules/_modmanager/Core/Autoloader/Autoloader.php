<?php

namespace Core\Autoloader;

/*
 * Autoload class by namespace
 */
class Autoloader 
{
    public static $core_folders = array('Controller','Model','Config','Routing','Utilite');
    
    /*
     * Register classes
     */
    public static function autoloadRegister() 
    {
        spl_autoload_register(function ($class) {
            if (!file_exists(MBP.str_replace('\\', '/', $class). '.php')) {
                $core_class = 'Core\Autoloader\Autoloader';
                foreach ($core_class::$core_folders as $value) {
                    $file = MBP.'Core/'.$value.'/'.$class.'.php';
                    if (file_exists($file)) {
                        require_once $file; 
                    }
                }
            } else {
                //var_dump($class);
                require_once MBP.str_replace('\\', '/', $class).'.php'; 
            }
        });
    }
}
