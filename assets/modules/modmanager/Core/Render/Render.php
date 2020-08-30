<?php

namespace Core\Render;

/*
 * Render class. Display templates
 * All date in variable $data will be accesible in childrens templates
 */
class Render 
{
    private $data = array();
    private $parent = '';
    private $modx = '';
    
    function __construct() {
        global $modx;
        $this->modx = $modx;
    }

    /*
     * Set value. Will be accesible in template
     * @param string key name
     * @param string value
     */
    function set($name, $value) 
    {
        $this->data[$name] = $value; 
    }

    /*
     * Delete key in $data
     * @param string key name
     */
    public function delete($name) 
    {
        unset($this->data[$name]);
    }

    /*
     * Factory load singltone classes
     * @param string class name
     * @return object
     */
    public function factory($name) 
    {
        
        $name = ucfirst($name);
        switch ($name) {
            case 'Request':
            case 'Router':
            case 'Flesh':
            case 'Convert':
            case 'Translator':
                $namespace = 'Core\Components\\';
                break;
            case 'Config':
                $namespace = 'Core\Config\\';
                break;
        }

        return call_user_func(array($namespace.$name, 'getInstance'));
    }
    
    /*
     * Set name of parent template
     * @param string template name
     * @return ''
     */
    public function setParent($template) 
    {
       if ($template != '') {
            $explode = explode('/', $template);
            unset($explode[count($explode) - 1]);
            
            $this->parent = implode('/', $explode);
        }
    }
    
    /*
     * Get parent template
     * @param string template name
     * @return string
     */
    public function getParent($template) 
    {
        return $this->parent;
    }
    
    /*
     * Get extension. Load extension in Extension folder
     * @param string extension name
     * @param string extension params
     * @return html
     */
    public function getExtension($name = null, $params = array()) 
    {
        if ($name == null) {
            return '';
        }
        
        if (isset($this->data['errors'])) {
            $params['errors'] = $this->data['errors'];
        }
        
        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                $this->set($key, $value);
            }
        }
        
        $template = MBP.'/Core/Render/Extension/'.$name.".phtml";
        ob_start();
        include($template);
        return ob_get_clean();
    }

    public function __GET($name) 
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return "";
    }

    /*
     * Display template with parametrs
     * @param string template name
     * @param string params
     * @return html
     */
    public function display($template, $params = array()) 
    {
        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                $this->set($key, $value);
            }
        }

        $this->setParent($template);

        $view = MBP.'/App/View/'.$template.".phtml";
        
        if (!file_exists($view)) {
            $view = MBP.'/App/View/'.$template.".phtml";
        }
        
        ob_start();
        include($view);
        return ob_get_clean();
    }

}

