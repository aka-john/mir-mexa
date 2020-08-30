<?php

namespace Core\Controller;

use Core\Render\Render;

class Controller {
    
    public $modx = '';
            
    function __construct() {
        global $modx;
        $this->modx = $modx;
        $this->render = new Render();
    }
    
}
