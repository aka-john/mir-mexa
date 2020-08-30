<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Components\Request;
use Core\Components\Router;
use Core\Components\Pagination;
use Core\Components\Flesh;
use Core\Components\Translator;
use Core\Validation\Validator;
use App\Entity\SeolinkEntity;
/*
 * Ei controller
 */
class SeolinkController extends Controller
{
    public function __construct() 
    {
        parent::__construct();
        $this->seolink = new SeolinkEntity();
    }

    /*
     * View all Seolink size
     * @return Seolink/size template
     */
    public function indexAction() 
    {
        $pagination = new Pagination();
        $pagination->init();
        
        $links = $this->seolink->getGridSeolinks();

        return $this->render->display("Seolink/index", array(
            'links' => $links,
            'pagination' => $pagination->build($this->seolink->getTotalRows())
        ));
    }

    /*
     * Create new seolink
     * @return seolink/create template
     */
    public function createAction() 
    {
        $request = Request::getRequest();
        
        if (CRM_IS_POST()) {
            $validate = $this->validateSeolinkForm($request);
            
            if (count($validate) > 0) {
                return $this->render->display("Seolink/create", array(
                    'errors' => $validate
                ));
            }

            $id = $this->seolink->addseolink($request);
            
            Flesh::setFlesh(Translator::getTranslate('seolink_successful_created'), 'info');
            
            Request::redirect(Router::getInstance()->generate('Seolink', 'index'));
        }
        
        return $this->render->display("Seolink/create");
    }
    
    /*
     * Edit seolink
     * @param int seolink id
     * @return seolink/edit template
     */
    public function editAction() 
    {
        $request = Request::getRequest();

        $seolink = $this->seolink->getSeolink($request['link_id']);
        
        if (CRM_IS_POST()) {
            $validate = $this->validateSeolinkForm($request);
            
            if (count($validate) == 0) {
                $seolink = $this->seolink->updateSeolink($seolink['id'], $request);
                
                Flesh::setFlesh(Translator::getTranslate('seolink_successful_updated'), 'info');
            } 
        }
        
        return $this->render->display("Seolink/edit", array(
            'link' => $seolink,
            'errors' => $validate
        ));
    }
    
    /*
     * Remove seolink by ajax call
     * @param int id ajax request
     * @return ''
     */
    public function removeAction() 
    {
        $this->seolink = new seolinkEntity();
        
        $request = Request::getRequest();
        
        if (CRM_IS_AJAX() && isset($request['link_id'])) {
            $this->seolink->removeSeolink($request['link_id']);
        }
        
        return '';
    }
    
    /*
     * Validate seolink form request
     * @param array fields list
     * @return array
     */
    private function validateSeolinkForm($fields) 
    {
        $errors = array();
        
        foreach ($fields as $key => $value) {
            switch ($key) {
                 case 'url':
                    $validator = Validator::notEmpty()->validate($value);
                    $key_name = 'incorrect_null_field';
                    break;
                default :
                    $validator = true;
                    break;
            }
            
            if (!$validator) {
                $errors[$key] = Translator::getTranslate($key_name);
            }
        }
        
        if (count($errors) > 0) {
            Flesh::setFlesh(Translator::getTranslate('form_error_detect'), 'danger');
        }
        
        return $errors;
    }
}
