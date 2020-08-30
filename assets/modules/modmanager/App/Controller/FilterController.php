<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Components\Pagination;
use Core\Components\Request;
use Core\Components\Router;
use Core\Components\Flesh;
use Core\Components\Translator;
use Core\Validation\Validator;
use App\Entity\FilterEntity;

/*
 * Filter controller
 */
class FilterController extends Controller
{
    public function __construct() 
    {
        parent::__construct();
        $this->filter = new FilterEntity();
    }

    /*
     * View all products
     * @return Filter/index template
     */
    public function indexAction() 
    {
        $pagination = new Pagination();
        $pagination->init();
        
        $filters = $this->filter->getGridFilters();
        
        return $this->render->display("Filter/index", array(
            'filters' => $filters,
            'pagination' => $pagination->build($this->filter->getTotalRows())
        ));
    }
    
    /*
     * Create new filter
     * @return Filter/create template
     */
    public function createAction() 
    {
        $request = Request::getRequest();
        
        $categorys = $this->filter->getAllCategorys($request);
        
        if (CRM_IS_POST()) {
            
            $validate = $this->validateFilterForm($request);
            
            if (count($validate) > 0) {
                return $this->render->display("Filter/create", array(
                    'categorys' => $categorys,
                    'errors' => $validate
                ));
            }

            $id = $this->filter->addFilter($request);
            
            if (count($request['categorys']) > 0) {
                foreach ($request['categorys'] as $key => $value) {
                    $this->filter->addCategorysToFilter($id, $value);
                }
            }
            
            if (count($request['filter_values']) > 0) {
                foreach ($request['filter_values'] as $key => $value) {
                    if (strpos($key, 'new') !== false) {
                        $this->filter->addFilterValue($id, $value);
                    }
                }
            }

            Flesh::setFlesh(Translator::getTranslate('filter_successful_created'), 'info');
            
            Request::redirect(Router::getInstance()->generate('filter', 'index'));
        }
        
        return $this->render->display("Filter/create", array(
            'categorys' => $categorys
        ));
    }
    
    /*
     * Edit filter
     * @param int filter id
     * @return Filter/edit template
     */
    public function editAction() 
    {
        $request = Request::getRequest();

        $filter = $this->filter->getFilter($request['filter_id']);
        $categorys = $this->filter->getAllCategorys();

        if (CRM_IS_POST()) {
            $validate = $this->validateFilterForm($request);
            
            if (count($validate) == 0) {
                $filter = $this->filter->updateFilter($filter['id'], $request);
                
                if (count($request['categorys']) > 0) {
                    $this->filter->removeAllCategorysInFilter($filter['id']);
                    
                    foreach ($request['categorys'] as $key => $value) {
                        $this->filter->addCategorysToFilter($filter['id'], $value);
                    }
                    
                    if (count($request['filter_values']) > 0) {
                        foreach ($request['filter_values'] as $key => $value) {
                            if (strpos($key, 'new') !== false) {
                                $this->filter->addFilterValue($filter['id'], $value);
                            } else {
                                $this->filter->updateFilterValue($key, $value);
                            }
                        }
                    }
                } else {
                    $this->filter->removeAllCategorysInFilter($filter['id']);
                }
                
                Flesh::setFlesh(Translator::getTranslate('filter_successful_updated'), 'info');
            } 
        }
        
        $selected_categorys = $this->filter->getFilterCategorysIds($request['filter_id']);
        $filter_values = $this->filter->getAllFilterValues($request['filter_id']);
        
        return $this->render->display("Filter/edit", array(
            'filter' => $filter,
            'categorys' => $categorys,
            'selected_categorys' => $selected_categorys,
            'filter_values' => $filter_values,
            'errors' => $validate
        ));
    }
    
    /*
     * Remove filter by ajax call
     * @param int id ajax request
     * @return ''
     */
    public function removeAction() 
    {
        $this->filter = new FilterEntity();
        
        $request = Request::getRequest();
        
        if (CRM_IS_AJAX() && isset($request['filter_id'])) {
            $this->filter->removeFilter($request['filter_id']);
        }
        
        return '';
    }
    
    /*
     * Remove filter by ajax call
     * @param int id ajax request
     * @return ''
     */
    public function removeValueAction() 
    {
        $this->filter = new FilterEntity();
        
        $request = Request::getRequest();
        
        if (CRM_IS_AJAX() && isset($request['value_id'])) {
            $this->filter->removeFilterValue($request['value_id']);
        }
        
        return '';
    }
    
    /*
     * Validate filter form request
     * @param array fields list
     * @return array
     */
    private function validateFilterForm($fields) 
    {
        $errors = array();
        
        foreach ($fields as $key => $value) {
            switch ($key) {
                case 'name':
                    $validator = Validator::notEmpty()->validate($value);
                    $key_name = 'incorrect_null_field';
                    break;
                case 'position':
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
