<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Components\Pagination;
use Core\Components\Request;
use Core\Components\Router;
use Core\Components\Flesh;
use Core\Components\Translator;
use Core\Validation\Validator;
use App\Entity\MailerEntity;
use App\Entity\ProductEntity;

/*
 * Mailer controller
 */
class MailerController extends Controller
{
    public function __construct() 
    {
        parent::__construct();
        $this->mailer = new MailerEntity();
    }

    /*
     * View all subscribers
     * @return Mailer/index template
     */
    public function indexSubscriberAction() 
    {
        $pagination = new Pagination();
        $pagination->init();
        
        $subscribers = $this->mailer->getGridSubscribers();
        
        return $this->render->display("Mailer/index", array(
            'subscribers' => $subscribers,
            'pagination' => $pagination->build($this->mailer->getTotalRows())
        ));
    }
    
    /*
     * View all letters
     * @return Mailer/Letter/index template
     */
    public function indexLetterAction() 
    {
        $pagination = new Pagination();
        $pagination->init();
        
        $letters = $this->mailer->getGridLetters();
        
        return $this->render->display("Mailer/Letter/index", array(
            'letters' => $letters,
            'pagination' => $pagination->build($this->mailer->getTotalRows())
        ));
    }
    
    /*
     * Create new subscriber
     * @return Mailer/create template
     */
    public function createSubscriberAction() 
    {
        $users = $this->mailer->getAllWebUsers();

        $request = Request::getRequest();
        
        $letters = $this->mailer->getAllLetters();
        
        if (CRM_IS_POST()) {
            $validate = $this->validateSubscriberForm($request);
            
            if (count($validate) > 0) {
                return $this->render->display("Mailer/create", array(
                    'users' => $users,
                    'letters' => $letters,
                    'errors' => $validate
                ));
            }

            $id = $this->mailer->addSubscriber($request);
            
            Flesh::setFlesh(Translator::getTranslate('subscriber_successful_created'), 'info');
            
            Request::redirect(Router::getInstance()->generate('mailer', 'indexSubscriber'));
        }
        
        return $this->render->display("Mailer/create", array(
            'users' => $users,
            'letters' => $letters
        ));
    }
    
    /*
     * Create new letter
     * @return Mailer/Letter/create template
     */
    public function createLetterAction() 
    {
        $request = Request::getRequest();
        
        if (CRM_IS_POST()) {
            $validate = $this->validateLetterForm($request);
            
            if (count($validate) > 0) {
                return $this->render->display("Mailer/Letter/create", array(
                    'errors' => $validate
                ));
            }

            $id = $this->mailer->addLetter($request);
            
            Flesh::setFlesh(Translator::getTranslate('letter_successful_created'), 'info');
            
            Request::redirect(Router::getInstance()->generate('mailer', 'indexLetter'));
        }
        
        return $this->render->display("Mailer/Letter/create");
    }
    
    /*
     * Edit subscriber
     * @param int subscriber id
     * @return Mailer/edit template
     */
    public function editSubscriberAction() 
    {
        $request = Request::getRequest();
        
        $users = $this->mailer->getAllWebUsers();
        $subscriber = $this->mailer->getSubscriber($request['subscriber_id']);
        $letters = $this->mailer->getAllLettersAndMailStatus($request['subscriber_id']);
        
        if (CRM_IS_POST()) {
            $validate = $this->validateSubscriberForm($request);
            
            if (count($validate) == 0) {
                $subscriber = $this->mailer->updateSubscriber($subscriber['id'], $request);
                
                if (count($request['letters']) > 0) {
                    $ids = array();
                    foreach ($request['letters'] as $key => $value) {
                        $exist = $this->mailer->existLetter($subscriber['id'], $value);
                        if ($exist['id'] == false) {
                            $ids[] = $this->mailer->addLettersToSubscriber($subscriber['id'], $value);
                        } else {
                            $ids[] = $exist['id'];
                        }
                    }
                    
                    $this->mailer->removeSubscriberLettersByIds($ids);
                }
                
                Flesh::setFlesh(Translator::getTranslate('subscriber_successful_updated'), 'info');
            } 
        }
        
        $selected_letters = $this->mailer->getSubscriberLettersIds($request['subscriber_id']);
        
        return $this->render->display("Mailer/edit", array(
            'users' => $users,
            'subscriber' => $subscriber,
            'letters' => $letters,
            'selected_letters' => $selected_letters,
            'errors' => $validate
        ));
    }
    
    /*
     * Edit letter
     * @param int letter id
     * @return Mailer/Letter/edit template
     */
    public function editLetterAction() 
    {
        $request = Request::getRequest();

        $letter = $this->mailer->getLetter($request['letter_id']);
        
        if (CRM_IS_POST()) {
            $validate = $this->validateLetterForm($request);
            
            if (count($validate) == 0) {
                $letter = $this->mailer->updateLetter($letter['id'], $request);
                
                Flesh::setFlesh(Translator::getTranslate('letter_successful_updated'), 'info');
            } 
        }
        
        return $this->render->display("Mailer/Letter/edit", array(
            'letter' => $letter,
            'errors' => $validate
        ));
    }
    
    /*
     * Remove subscribe by ajax call
     * @param int id ajax request
     * @return ''
     */
    public function removeSubscriberAction() 
    {
        $this->mailer = new MailerEntity();
        
        $request = Request::getRequest();
        
        if (CRM_IS_AJAX() && isset($request['subscriber_id'])) {
            $this->mailer->removeSubscriber($request['subscriber_id']);
        }
        
        return '';
    }
    
    /*
     * Remove letter by ajax call
     * @param int id ajax request
     * @return ''
     */
    public function removeLetterAction() 
    {
        $this->mailer = new MailerEntity();
        
        $request = Request::getRequest();
        
        if (CRM_IS_AJAX() && isset($request['letter_id'])) {
            $this->mailer->removeLetter($request['letter_id']);
        }
        
        return '';
    }
    
    /*
     * Validate subscriber form request
     * @param array fields list
     * @return array
     */
    private function validateSubscriberForm($fields) 
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
    
    /*
     * Validate letter form request
     * @param array fields list
     * @return array
     */
    private function validateLetterForm($fields) 
    {
        $errors = array();
        
        foreach ($fields as $key => $value) {
            switch ($key) {
                case 'name':
                    $validator = Validator::notEmpty()->validate($value);
                    $key_name = 'incorrect_null_field';
                    break;
                case 'subject':
                    $validator = Validator::notEmpty()->validate($value);
                    $key_name = 'incorrect_null_field';
                    break;
                case 'text':
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
