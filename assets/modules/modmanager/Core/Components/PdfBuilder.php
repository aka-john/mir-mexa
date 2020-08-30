<?php

namespace Core\Components;

use Core\Config\Config;
use Core\Render\Render;

/*
 * mPdf builder class
 */
class PdfBuilder {
    private $data;
    private $mpdf;
    private $render;

    /*
     * Include mpdf main class and set default parameters
     */
    public function __construct($data = array()){
        require_once(CRM_GET_ROOT_PATH().'/assets/modules/modmanager/Core/Includes/Mpdf/mPDF.php');
        
        $this->data = $data;
        $this->mpdf = new \mPDF();
        $this->render = new Render();
    }
    
    /*
     * Add new data in variable. This data can be puted in template
     * @param array data
     * @return ''
     */
    public function addData($data = array())
    {
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $this->data[$key] = $value;
            }
        }
    }

}
