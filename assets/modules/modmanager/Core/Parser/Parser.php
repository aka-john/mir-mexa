<?php

namespace Core\Parser;

/*
 * Parser class. Load parsers in Extension folder
 */
class Parser {
    
    /*
     * Factory load extension
     * @param string extension class name. By default is xml
     * @return object
     */
    public static function factory($extension = 'Xml') 
    {
		$extension_namespace = 'Core\Parser\Extensions\\';
		$class = $extension_namespace.$extension.'Extension';
		if(@class_exists($class)) {
			return $class::getInstance();
		}
	}
    
}
