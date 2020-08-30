<?php

namespace Core\Parser\Extensions;

/*
 * Xml extension class. Singleton class
 */
class XmlExtension {
    protected static $_instance;
    protected $xml;
    protected $node;
    public $config;
    protected $path;
    
    private function __construct(){
        $this->path = MBP.'Core/Settings/';
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
     * Set path to folder
     * @param string path to folder. By default id Core/Settings/
     * @return ''
     */
    public function setPath($path = 'Core/Settings/')
    {
        $this->path = MBP.$path;
        return '';
    }
    
    /*
     * Get folder path
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /*
     * Load xml file
     * @param string file name
     * @return object 
     */
    public function loadXml($xml)
    {
        $file = $this->path.$xml.'.xml';
        if ($xml == '' || !file_exists($file)) {
            return '';
        }
        
        $this->config = $xml;
        
        $this->xml = simplexml_load_string(file_get_contents($file), 'SimpleXMLElement', LIBXML_NOCDATA);
        return $this;
    }

    /*
     * Save xml file
     * @param string file name
     * @return object 
     */
    public function saveXml()
    {
        $file = $this->path.$this->config.'.xml';
        $this->xml->asXML($file);
    }
    
    /*
     * Get xml structure
     * @return object 
     */
    public function getXml()
    {
        return $this->xml;
    }
    
    /*
     * Get node by xpath method
     * @param string node path
     * @return object 
     */
    public function getNode($name)
    {
        $node = $this->xml->xpath($name);
        $this->node = count($node) > 1 ? $node : $node[0];
        return $this;
    }
    
    /*
     * Get node value
     * @param string value name
     * @return object 
     */
    public function getVal($name)
    {
        $name = (string)$name;
        return (string)$this->node->$name;
    }
    
    /*
     * Get node element
     * @param string element name
     * @return object 
     */
    public function getElement($name)
    {
        return $this->node->$name;
    }
    
    public function setVal($name, $value)
    {
        $name = (string)$name;
        $this->node->$name = $value;
        return (string)$this->node->$name;
    }
    
    /*
     * Conver xml structure to array
     * @param object structure
     * @return array 
     */
    public function toArray($xml = null) {
        $xml = $xml != null ? $xml : $this->xml;
        $arr = json_decode(json_encode($xml), 1);
        return $arr;
    }
    
    /*
     * Conver node to array
     * @param object node
     * @return array 
     */
    public function nodeToArray($node = null) {
        $node = $node != null ? $node : $this->node;
        $arr = json_decode(json_encode($node), 1);
        return $arr;
    }
}
