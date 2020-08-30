<?php

namespace App\Controller;

use Core\Components\Pagination;
use Core\Components\Request;
use Core\Components\Router;
use Core\Components\Flesh;
use Core\Components\Translator;
use Core\Validation\Validator;
use App\Entity\ProductEntity;
use App\Entity\FilterEntity;
use Core\Components\SendMail;
use Core\Controller\Controller;

/*
 * Product controller
 */
class ProductController extends Controller
{
    public function __construct() 
    {
        parent::__construct();
        $this->product = new ProductEntity();
    }

    /*
     * View all products
     * @return Product/index template
     */
    public function indexAction() 
    {
        $pagination = new Pagination();
        $pagination->init();

        //$this->product->refreshAllProductPrice();
        
        $products = $this->product->getGridProducts();
        
        return $this->render->display("Product/index", array(
            'products' => $products,
            'pagination' => $pagination->build($this->product->getTotalRows())
        ));
    }
    
    /*
     * Create new product
     * @return Product/create template
     */
    public function createAction() 
    {
        $request = Request::getRequest();
        
        $categorys = $this->product->getAllCategorys();
        $citys = $this->product->getAllCitys();

        if (CRM_IS_POST()) {
            $validate = $this->validateProductForm($request);
            
            if (count($validate) > 0) {
                return $this->render->display("Product/create", array(
                    'categorys' => $categorys,
                    'errors' => $validate
                ));
            }

            $id = $this->product->addProduct($request);
            $this->product->addProductSeo($id, $request['seo']);
            
            //сохранение категорий
            if (count($request['categorys']) > 0) {
                foreach ($request['categorys'] as $key => $value) {
                    $this->product->addCategorysToProduct($id, $value);
                }
            }
            
            //сохранение размеров и цен по размерам
            if (count($request['size']) > 0) {
                foreach ($request['size'] as $key => $value) {
                    $this->product->addProductSize($id, $value);

                    if (count($value['citys']) > 0) {
                        foreach ($value['citys'] as $city_key => $city_value) {
                            $this->product->addProductSizeCity($key, $city_value);
                        }
                    }
                }
            }
            
            //изображение
            $image = $this->uploadProductImageAction($id, '');
            $this->product->uploadProductImage($id, $image);
            
            //галерея
            if ($request['gallery']) {
                foreach ($request['gallery'] as $key => $value) {
                    $value['image'] = $this->uploadProductGalleryAction($id, $key, $value);

                    $this->product->addProductGalleryImage($id, $value);
                }
            }

            $this->product->refreshProductPrice($id);
            
            Flesh::setFlesh(Translator::getTranslate('product_successful_created'), 'info');
			
            
            Request::redirect(Router::getInstance()->generate('product', 'index'));
        }
        
        return $this->render->display("Product/create", array(
            'categorys' => $categorys,
            'citys' => $citys
        ));
    }
    
    /*
     * Edit product
     * @param int product id
     * @return Product/edit template
     */
    public function editAction() 
    {
        $this->product = new ProductEntity();
        $this->filter = new FilterEntity();
        
        $request = Request::getRequest();
        
        $product = $this->product->getProduct($request['product_id']);
        $seo = $this->product->getProductSeo($request['product_id']);
        $categorys = $this->product->getAllCategorys();
        
        $citys = $this->product->getAllCitys();
        
        $old_presence = $product['presence'];

        if (CRM_IS_POST()) {
            $validate = $this->validateProductForm($request);

            if (count($validate) == 0) {
                $product = $this->product->updateProduct($product['id'], $request);
                $seo = $this->product->updateProductSeo($product['id'], $request['seo']);
                
                //сохранение категорий товара
                if (count($request['categorys']) > 0) {
                    $this->product->removeAllCategorysInProduct($request['product_id']);
                    
                    foreach ($request['categorys'] as $key => $value) {
                        $this->product->addCategorysToProduct($request['product_id'], $value);
                    }
                }
                
                //сохранение размеров и цен по размерам
                if (count($request['size']) > 0) {
                    foreach ($request['size'] as $key => $value) {
                        if (strpos($key, 'new') !== false) {
                            $this->product->addProductSize($request['product_id'], $value);
                        } else {
                            $size = $this->product->getProductSize($key);
                            $new_size = $this->product->updateProductSize($key, $value);

                            //отправка сообщения о наличии
                            if ((int)$size['amount'] == 0 && (int)$new_size['amount'] > 0) {
                                $availability = $this->product->getProductAvailabilitySubscribe($request['product_id'], $key);
                                $mailer = new SendMail();
                                if ($availability) {
                                    $mail = $this->modx->rewriteUrls($this->modx->parseDocumentSource($this->modx->parseChunk('mail_report', $product,'[+','+]')));
                                    foreach ($availability as $key => $value) {
                                        $mailer->send($value['email'], 'Товар в наличии', $mail);
                                        $this->product->removeAvailabilitySubscribe($value['id']);
                                    }
                                }
                            }
                        }

                        if (count($value['citys']) > 0) {
                            foreach ($value['citys'] as $city_key => $city_value) {
                                if (strpos($city_key, 'new') !== false) {
                                    $this->product->addProductSizeCity($key, $city_value);
                                } else {
                                    $this->product->updateProductSizeCity($city_key, $key, $city_value);
                                }
                            }
                        }
                    }
                }
                
                //сохранение линковки товаров
                if ($request['product_group'] != '') {
                    $products = explode(',', $request['product_group']);
                    $products = array_unique(array_merge($products, array('0' => $request['product_id'])));

                    $this->product->removeProductsGroups($products);
                    
                    $product['group_id'] = uniqid();
                    foreach ($products as $key => $value) {
                        $this->product->addProductToGroup($product['group_id'], $value);
                    }
                }

                //сохранение фильтров товароа
                if (count($request['filter_values']) > 0) {
                    $this->product->removeAllProductFilterValues($request['product_id']);
                    
                    foreach ($request['filter_values'] as $category_key => $category_value) {//по категориям в фильтрах

                        foreach ($category_value as $filter_key => $filter_value) {//по фильтрам
                            $exp = explode(',', $filter_value);
                            
                            for ($i = 0; $i < count($exp); $i++) {
                                $exp_val = explode('_', $exp[$i]);
                                
                                if ($exp_val[0] == 'new') {
                                    $fields = array('value' => end($exp_val), 'position' => $i);
                                    $filter_value_exist = $this->filter->isFilterValueExist($filter_key, end($exp_val));
                                    
                                    if (!$filter_value_exist) {
                                        $filter_value_id = $this->filter->addFilterValue($filter_key, $fields);
                                        $this->product->addFilterValueToProduct($request['product_id'], $filter_value_id, $category_key);
                                    }
                                } elseif (!empty($exp[$i])) {
                                    $this->product->addFilterValueToProduct($request['product_id'], $exp[$i], $category_key);
                                }
                            }
                        }
                    }
                }
                
                //изображение
                $image = $this->uploadProductImageAction($request['product_id'], $request['image']);
                $this->product->uploadProductImage($request['product_id'], $image);
                $product['image'] = $image;
                
                //галерея
                if ($request['gallery']) {
                    foreach ($request['gallery'] as $key => $value) {
                        $value['image'] = $this->uploadProductGalleryAction($request['product_id'], $key, $value['image']);
                        
                        if (strpos($key, 'new') !== false) {
                            $this->product->addProductGalleryImage($request['product_id'], $value);
                        } else {
                            
                            $this->product->updateProductGalleryImage($key, $value);
                        }
                    }
                }

                $this->product->refreshProductPrice($request['product_id']);

                Flesh::setFlesh(Translator::getTranslate('product_successful_updated'), 'info');
            } 
        }
        
        $selected_categorys = $this->product->getProductCategorysIds($request['product_id']);
        $product_categorys = $this->product->getProductCategorysById($request['product_id']);
        $gallerys = $this->product->getAllProductGalleryImages($request['product_id']);
        $sizes = $this->product->getProductSizeByProduct($request['product_id']);

        $product_ingroup = array();
        if ($product['group_id']) {
            $product_ingroup = $this->product->getProductByGroupId($product['group_id']);
        }
        
        //парсинг добавочных товаров
        if ($product['additional'] != '') {
            $count = 0;
            $items = array();
            $exp = explode(',', $product['additional']);
            foreach ($exp as $key => $value) {
                $product_additional = $this->product->getProduct($value);
                $items[$count]['key']  = $product_additional['id'];
                $items[$count]['name'] = $product_additional['name'];
                $count++;
            }
            
            $product['additional_values'] = json_encode($items);
        }
        
        //парсинг групп
        if ($product['group_id']) {
            $count = 0;
            $items = array();
            foreach ($product_ingroup as $key => $value) {
                $product_groups = $this->product->getProduct($value['product_id']);
                $items[$count]['key']  = $product_groups['id'];
                $items[$count]['name'] = $product_groups['name'];
                $count++;
            }
            
            $product['product_group_values'] = json_encode($items);
        }
        
        return $this->render->display("Product/edit", array(
            'product' => $product,
            'categorys' => $categorys,
            'gallerys' => $gallerys,
            'selected_categorys' => $selected_categorys,
            'product_categorys' => $product_categorys,
            'seo' => $seo,
            'sizes' => $sizes,
            'citys' => $citys,
            'filters' => $this->buildFilterBlock($product_categorys),
            'errors' => $validate
        ));
    }
    
    /*
     * Remove product by ajax call
     * @param string word ajax request
     * @return json
     */
    public function autocompleteProductFilterAction() 
    {
        $items = array();

        $request = Request::getRequest();

        if (CRM_IS_AJAX() && isset($request['word'])) {
            $result = $this->product->getAutocompleteFilter($request['word']);
            
            if ($result) {
				$count = 0;
				foreach ($result as $key => $value) {
					$items[$count]['key']  = $value['id'];
					$items[$count]['name'] = $value['value'];
					$count++;
				}
			}
        }
        
        die(json_encode($items));
    }
    
    /*
     * Remove product by ajax call
     * @param string word ajax request
     * @return json
     */
    public function autocompleteProductAction() 
    {
        $items = array();

        $request = Request::getRequest();

        if (CRM_IS_AJAX() && isset($request['word'])) {
            $result = $this->product->getAutocompleteProducts($request['word']);
            
            if ($result) {
				$count = 0;
				foreach ($result as $key => $value) {
					$items[$count]['key']  = $value['id'];
					$items[$count]['name'] = $value['name'];
					$count++;
				}
			}
        }
        
        die(json_encode($items));
    }
    
    /*
     * Remove product by ajax call
     * @param string word ajax request
     * @return json
     */
    public function autocompleteProductGroupAction() 
    {
        $items = array();

        $request = Request::getRequest();

        if (CRM_IS_AJAX() && isset($request['word'])) {
            $result = $this->product->getAutocompleteProducts($request['word']);
            
            if ($result) {
				$count = 0;
				foreach ($result as $key => $value) {
					$items[$count]['key']  = $value['id'];
					$items[$count]['name'] = $value['name'];
					$count++;
				}
			}
        }
        
        die(json_encode($items));
    }
    
    /*
     * Remove product by ajax call
     * @param int id ajax request
     * @return ''
     */
    public function removeAction() 
    {
        $request = Request::getRequest();
        
        if (CRM_IS_AJAX() && isset($request['product_id'])) {
            $this->product->removeProduct($request['product_id']);
        }
        
        die;
    }
    
    /*
     * Remove product size by ajax call
     * @param int id ajax request
     * @return ''
     */
    public function removeSizeAction() 
    {
        $request = Request::getRequest();
        
        if (CRM_IS_AJAX() && isset($request['id'])) {
            $this->product->removeProductSize($request['id']);
        }
        
        die;
    }
    
    /*
     * Remove product image by ajax call
     * @param int id ajax request
     * @return ''
     */
    public function removeProductImageAction() 
    {
        $request = Request::getRequest();
        
        if (CRM_IS_AJAX() && isset($request['id'])) {
            $product = $this->product->getProduct($request['id']);
            $file = CRM_GET_ROOT_PATH().'/assets/files/product/'.$product['id'].'/image/'.$product['image'];

            if (file_exists($file)) {
                unlink($file);
            }
            
            $this->product->removeProductImage($request['id']);
        }
        
        die;
    }
    
    /*
     * Remove product gallery image by ajax call
     * @param int id ajax request
     * @return ''
     */
    public function removeGalleryImageAction() 
    {
        $request = Request::getRequest();
        
        if (CRM_IS_AJAX() && isset($request['id'])) {
            $image = $this->product->getProductGalleryImage($request['id']);
            $file = CRM_GET_ROOT_PATH().'/assets/files/product/'.$image['product_id'].'/gallery/'.$image['image'];

            if (file_exists($file)) {
                unlink($file);
            }
            
            $this->product->removeProductGalleryImage($request['id']);
        }
        
        die;
    }


    /*
    * View size citys price
    * @param int size id
    * @return Product/Size/city template
    */
    public function viewCitysAction()
    {
        $request = Request::getRequest();

        if (CRM_IS_AJAX() && isset($request['size_id'])) {
            $citys = $this->product->getCityBySizeId($request['size_id']);
            $size = $this->product->getProductSize($request['size_id']);
            $all_citys = $this->product->getAllCitys();

            $result = '';
            if (count($citys) > 0) {
                foreach ($citys as $key => $value) {
                    $result .= $this->render->display("Product/Size/city", array(
                        'city' => $value,
                        'size' => $size,
                        'citys' => $all_citys
                    )); 
                }
            }
        }
        
        die($result);
    }

    /*
     * Remove size city by ajax call
     * @param int id ajax request
     * @return ''
     */
    public function removeSizeCityAction() 
    {
        $request = Request::getRequest();
        
        if (CRM_IS_AJAX() && isset($request['id'])) {
            $this->product->removeSizeCitys($request['id']);
        }
        
        die;
    }

    /*
     * Validate product form request
     * @param array fields list
     * @return array
     */
    private function validateProductForm($fields) 
    {
        $errors = array();
        
        foreach ($fields as $key => $value) {
            switch ($key) {
                case 'name':
                    $validator = Validator::notEmpty()->validate($value);
                    $key_name = 'incorrect_null_field';
                    break;
                case 'sku':
                    $validator = Validator::notEmpty()->validate($value);
                    $key_name = 'incorrect_null_field';
                    break;
                case 'alias':
                    $validator = $this->product->isAliasExist($fields['product_id'], $value);
                    $key_name = 'incorrect_alias_exist';
                    break;
                case 'price':
                    $validator = Validator::numeric()->validate($value);
                    $key_name = 'incorrect_'.$key;
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

    public function uploadProductGalleryAction($id, $key, $image) 
    {
        if (!is_dir(CRM_GET_ROOT_PATH().'/assets/files/product/'.$id)) {
            mkdir(CRM_GET_ROOT_PATH().'/assets/files/product/'.$id, 0777);
        }
        
        if (!is_dir(CRM_GET_ROOT_PATH().'/assets/files/product/'.$id.'/gallery')) {
            mkdir(CRM_GET_ROOT_PATH().'/assets/files/product/'.$id.'/gallery', 0777);
        }
               
        $dir = CRM_GET_ROOT_PATH().'/assets/files/product/'.$id.'/gallery';
        $files = CRM_GET_FILES();
        $new_file_name = uniqid();
        $ext = strtolower(substr($files["gallery"]['name'][$key]["image"], 1 + strrpos($files["gallery"]['name'][$key]["image"], ".")));

        if (is_uploaded_file($files['gallery']['tmp_name'][$key]["image"])) {
            if (file_exists($dir.'/'.$image) && $image != '') {
                unlink($dir.'/'.$image);
            }

            $file = $new_file_name.'.'.$ext;

            if (move_uploaded_file($files['gallery']['tmp_name'][$key]["image"], $dir.'/'.$file)) {
                return $file;
            } else {
                return null;
            }
        } else {
            return $image;
        }
    }
    
    public function uploadProductImageAction($id, $image) 
    {
        if (!is_dir(CRM_GET_ROOT_PATH().'/assets/files/product/'.$id)) {
            mkdir(CRM_GET_ROOT_PATH().'/assets/files/product/'.$id, 0777);
        }
        
        if (!is_dir(CRM_GET_ROOT_PATH().'/assets/files/product/'.$id.'/image')) {
            mkdir(CRM_GET_ROOT_PATH().'/assets/files/product/'.$id.'/image', 0777);
        }
 
        $dir = CRM_GET_ROOT_PATH().'/assets/files/product/'.$id.'/image';
        $files = CRM_GET_FILES();
        $new_file_name = uniqid();
        $ext = strtolower(substr($files['image']['name'], 1 + strrpos($files['image']['name'], ".")));
        
        if (is_uploaded_file($files['image']['tmp_name'])) {
            
            if (file_exists($dir.'/'.$image) && $image != '') {
                unlink($dir.'/'.$image);
            }

            $file = $new_file_name.'.'.$ext;

            if (move_uploaded_file($files['image']['tmp_name'], $dir.'/'.$file)) {
                return $file;
            } else {
                return null;
            }
        } else {
            return $image;
        }
    }
    
    public function buildFilterBlock($categorys) 
    {
        $this->filter = new FilterEntity();
        
        $request = Request::getRequest();
        
        $categorys_block = '';
        if (!empty($categorys) && count($categorys) > 0) {
            
            foreach ($categorys as $category_key =>  $category_value) {//по категориям товара
                $category_filters = $this->filter->getFilterByCategoryId($category_value['id']);
                
                $filters_block = '';
                if (!empty($category_filters) && count($category_filters) > 0) {
                    $filter_values = array();

                    foreach ($category_filters as $filter_key => $filter_value) {//по фильтрам в категории
                        $filter_values = $this->product->getProductFilterValues($request['product_id'], $filter_value['id'], $category_value['id']);
                        
                        $filter_values_item = array();
                        if (!empty($filter_values) && count($filter_values) > 0) {
                            foreach ($filter_values as $filter_item_key => $filter_item_value) {
                                $filter_values_item[$filter_item_key]['key']  = $filter_item_value['id'];
                                $filter_values_item[$filter_item_key]['name'] = $filter_item_value['value'];
                            }
                        }

                        $filters_block .= $this->render->display("Product/Filter/filters_block", array(
                            'filter_values' => json_encode($filter_values_item),
                            'filter' => $filter_value
                        ));
                    }
                }

                $categorys_block .= $this->render->display("Product/Filter/cetegorys_block", array(
                    'filters_block' => $filters_block,
                    'category' => $category_value
                ));
            } 
        }

        return $categorys_block;
    }
}
