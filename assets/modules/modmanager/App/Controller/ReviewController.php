<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Components\Pagination;
use Core\Components\Request;
use Core\Components\Router;
use Core\Components\Flesh;
use Core\Components\Translator;
use Core\Validation\Validator;
use App\Entity\ReviewEntity;
use App\Entity\ProductEntity;

/*
 * Review controller
 */
class ReviewController extends Controller
{
    public function __construct() 
    {
        parent::__construct();
        $this->review = new ReviewEntity();
    }

    /*
     * View all products
     * @return Review/index template
     */
    public function indexAction() 
    {
        $pagination = new Pagination();
        $pagination->init();
        
        $reviews = $this->review->getGridReviews();
        
        return $this->render->display("Review/index", array(
            'reviews' => $reviews,
            'pagination' => $pagination->build($this->review->getTotalRows())
        ));
    }
    
    /*
     * Create new review
     * @return Review/create template
     */
    public function createAction() 
    {
        $this->product = new ProductEntity();
        
        $users = $this->review->getAllWebUsers();
        $products = $this->product->getAllProducts();

        $request = Request::getRequest();
        
        if (CRM_IS_POST()) {
            $validate = $this->validateReviewForm($request);
            
            if (count($validate) > 0) {
                return $this->render->display("Review/create", array(
                    'users' => $users,
                    'products' => $products,
                    'errors' => $validate
                ));
            }

            $id = $this->review->addReview($request);
            
            Flesh::setFlesh(Translator::getTranslate('review_successful_created'), 'info');
            
            Request::redirect(Router::getInstance()->generate('review', 'index'));
        }
        
        return $this->render->display("Review/create", array(
            'users' => $users,
            'products' => $products
        ));
    }
    
    /*
     * Edit review
     * @param int review id
     * @return Review/edit template
     */
    public function editAction() 
    {
        $this->product = new ProductEntity();
        
        $request = Request::getRequest();
        
        $users = $this->review->getAllWebUsers();
        $review = $this->review->getReview($request['review_id']);
        $products = $this->product->getAllProducts();
        
        if (CRM_IS_POST()) {
            $validate = $this->validateReviewForm($request);
            
            if (count($validate) == 0) {
                $review = $this->review->updateReview($review['id'], $request);
                
                Flesh::setFlesh(Translator::getTranslate('review_successful_updated'), 'info');
            } 
        }
        
        return $this->render->display("Review/edit", array(
            'users' => $users,
            'review' => $review,
            'products' => $products,
            'errors' => $validate
        ));
    }
    
    /*
     * Remove review by ajax call
     * @param int id ajax request
     * @return ''
     */
    public function removeAction() 
    {
        $this->review = new ReviewEntity();
        
        $request = Request::getRequest();
        
        if (CRM_IS_AJAX() && isset($request['review_id'])) {
            $this->review->removeReview($request['review_id']);
        }
        
        return '';
    }
    
    /*
     * Validate review form request
     * @param array fields list
     * @return array
     */
    private function validateReviewForm($fields) 
    {
        $errors = array();
        
        foreach ($fields as $key => $value) {
            switch ($key) {
                case 'name':
                    $validator = Validator::notEmpty()->validate($value);
                    $key_name = 'incorrect_null_field';
                    break;
                case 'email':
                    $validator = Validator::email()->validate($value);
                    $key_name = 'incorrect_'.$key;
                    break;
                case 'message':
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
