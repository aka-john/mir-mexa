<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Components\Pagination;
use Core\Components\Request;
use Core\Components\Router;
use Core\Components\Flesh;
use Core\Components\Translator;
use Core\Validation\Validator;
use App\Entity\ShopEntity;
use App\Entity\ProductEntity;

/*
 * Shop controller
 */
class ShopController extends Controller
{
    public function __construct() 
    {
        parent::__construct();
        $this->shop = new ShopEntity();
    }

    /*
     * View all products
     * @return Shop/index template
     */
    public function indexAction() 
    {
        $pagination = new Pagination();
        $pagination->init();
        
        $shops = $this->shop->getGridShops();
        
        return $this->render->display("Shop/index", array(
            'shops' => $shops,
            'pagination' => $pagination->build($this->shop->getTotalRows())
        ));
    }
    
    /*
     * Create new shop
     * @return Shop/create template
     */
    public function createAction() 
    {
        $this->product = new ProductEntity();
        
        $request = Request::getRequest();
        
        $users = $this->shop->getAllWebUsers();
        $products = $this->product->getAllProducts($request);
        $citys = $this->product->getAllCitys();

        if (CRM_IS_POST()) {
            
            $validate = $this->validateShopForm($request);
            
            if (count($validate) > 0) {
                return $this->render->display("Shop/create", array(
                    'products' => $products,
                    'users' => $users,
                    'errors' => $validate
                ));
            }
            
            $orders = array();
            if (count($request['orders']) > 0) {
                $order_price = 0;
                foreach ($request['orders'] as $key => $value) {
                    $product = $this->product->getProduct($value['product']);
                    $value['price'] = $value['price'] == '' ? $product['price'] : $value['price'];
                    
                    $orders[] = array(
                        'id' => $value['product'],
                        'amount' => $value['amount'],
                        'price' => $value['price'],
                        'user_id' => $request['user_id']
                    );
                    
                    $order_price += $value['price'] * $value['amount'];
                }
            }
            
            $user = $this->shop->getWebUser($request['user_id']);
            
            $request['orders'] = json_encode($orders);
            $request['user_info'] = json_encode($request['user_info']);
            $request['order_price'] = $order_price;

            $id = $this->shop->addShop($request);

            Flesh::setFlesh(Translator::getTranslate('shop_successful_created'), 'info');
            
            Request::redirect(Router::getInstance()->generate('shop', 'index'));
        }
        
        $fitting_citys = $this->shop->getFittingCitys();
        
        return $this->render->display("Shop/create", array(
            'products' => $products,
            'fitting_citys' => $fitting_citys,
            'citys' => $citys,
            'users' => $users
        ));
    }
    
    /*
     * Edit shop
     * @param int shop id
     * @return Shop/edit template
     */
    public function editAction() 
    {
        $this->product = new ProductEntity();
        
        $request = Request::getRequest();

        $shop = $this->shop->getShop($request['shop_id']);
        $users = $this->shop->getAllWebUsers();
        $products = $this->product->getAllProducts($request);
        $citys = $this->product->getAllCitys();

        if (CRM_IS_POST()) {
            $validate = $this->validateShopForm($request);

            if (count($validate) == 0) {
                $orders = array();
                $order_price = 0;
                if (count($request['orders']) > 0) {
                    foreach ($request['orders'] as $key => $value) {
                        $product = $this->product->getProduct($value['product']);
                        $value['price'] = $value['price'] == '' ? $product['price'] : $value['price'];
                    
                        $orders[] = array(
                            'id' => $value['product'],
                            'amount' => $value['amount'],
                            'price' => $value['price'],
                            'user_id' => $request['user_id']
                        );
                        
                        $order_price += $value['price'] * $value['amount'];
                    }
                }
                
                $request['orders'] = json_encode($orders);
                $request['user_info'] = json_encode($request['user_info']);
                $request['order_price'] = $order_price;
                
                $shop = $this->shop->updateShop($shop['id'], $request);
                
                Flesh::setFlesh(Translator::getTranslate('shop_successful_updated'), 'info');
            } 
        }
        
        $fitting_citys = $this->shop->getFittingCitys();
        
        $fitting_shops = $this->shop->getFittingCityShops($shop['fitting_city']);
        $fitting_shops_decode = json_decode($fitting_shops[0]["value"], true);
        $shop['fitting_shop'] = $fitting_shops_decode["fieldValue"][$shop['fitting_shop']]['name'];
        
        return $this->render->display("Shop/edit", array(
            'shop' => $shop,
            'fitting_citys' => $fitting_citys,
            'citys' => $citys,
            'user_info' => json_decode($shop['user_info'], true),
            'orders' => json_decode($shop['orders'], true),
            'products' => $products,
            'users' => $users,
            'errors' => $validate
        ));
    }
    
    /*
     * Remove shop by ajax call
     * @param int id ajax request
     * @return ''
     */
    public function removeAction() 
    {
        $this->shop = new ShopEntity();
        
        $request = Request::getRequest();
        
        if (CRM_IS_AJAX() && isset($request['shop_id'])) {
            $this->shop->removeShop($request['shop_id']);
        }
        
        return '';
    }
    
    /*
     * Remove shop by ajax call
     * @param int id ajax request
     * @return ''
     */
    public function removeOrderAction() 
    {
        $this->shop = new ShopEntity();
        $this->product = new ProductEntity();
        
        $request = Request::getRequest();

        return '';
    }
    
    /*
     * Validate shop form request
     * @param array fields list
     * @return array
     */
    private function validateShopForm($fields) 
    {
        $errors = array();
        
        foreach ($fields as $key => $value) {
            switch ($key) {
                case 'number':
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
