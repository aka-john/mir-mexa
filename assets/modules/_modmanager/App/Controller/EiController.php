<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Components\Request;
use Core\Components\PhpExcel;
use Core\Components\Router;
use App\Entity\EiEntity;
use App\Entity\FilterEntity;

/*
 * Ei controller
 */
class EiController extends Controller
{
    public function __construct() 
    {
        parent::__construct();
        $this->excel = new PhpExcel();
        $this->ei = new EiEntity();
    }

    /*
     * Export
     * @return Ei/index template
     */
    public function indexAction() 
    { 
        return $this->render->display("Ei/export");
    }
    
    /*
     * Export
     * @return Ei/index template
     */
    public function exportAction() 
    {
        $request = Request::getRequest();
        
        if (CRM_IS_POST()) {
            $request['id'] = $request['product_id'];
            unset($request['product_id']);
            unset($request['a']);
            unset($request['controller']);
            unset($request['action']);
            
            $products = $this->ei->getProductToExport();

            $this->excel->export($products, $request);
        }
        
        return $this->render->display("Ei/export");
    }
    
    /*
     * Import
     * @return Ei/index template
     */
    public function importAction() 
    {
        $request = Request::getRequest();
        $filter = new FilterEntity();
        $filters = $filter->getAllFilters();
        
        if (CRM_IS_POST() || $request['filename'] != '') {
            $this->excel->import('file', $request['filename']);
        }

        return $this->render->display("Ei/import", array(
            'filters' => $filters
        ));
    }

    /*
     * Stages import
     * @return Ei/index template
     */
    public function stagesImportAction() 
    {
        $request = Request::getRequest();

        if (CRM_IS_POST() || $request['filename'] != '') {
            $this->excel->import('file', $request['filename']);
        }

         Request::redirect(Router::getInstance()->generate('ei', 'stages'));
    }

    /*
     * Stages
     * @return Ei/index template
     */
    public function stagesAction() 
    {
        $request = Request::getRequest();

        return $this->render->display("Ei/stages", array(
            'in_import' => $this->getFilesList()
        ));
    }

    /*
     * Split
     * @return Ei/index template
     */
    public function splitExcelAction() 
    { 
        $request = Request::getRequest();
        $this->excel->splitExcel();
        Request::redirect(Router::getInstance()->generate('ei', 'stages'));
    }

    /*
     * Refresh
     * @return Ei/index template
     */
    public function refreshStagesAction() 
    { 
        die($this->getFilesList());
    }

    public function getFilesList() 
    { 
        $export_path = CRM_GET_ROOT_PATH().'/assets/export/export_files/';
        foreach(scandir($export_path) as $key => $file) {
            if ($file != "." && $file != "..") {                                            
                $in_import .= '<li><a href="'.Router::getInstance()->generate('ei', 'stagesImport', array('filename' => $file)).'" class="btn btn-primary"><span class="glyphicon glyphicon-file"></span> '.$file.'</a></li>';
            }
        }

        return $in_import;
    }

}
