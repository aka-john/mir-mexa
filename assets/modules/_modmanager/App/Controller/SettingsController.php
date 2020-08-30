<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Components\Request;
use Core\Components\PhpExcel;
use Core\Config\Config;
use Core\Parser\Parser;
use App\Entity\ProductEntity;

/*
 * Settings controller
 */
class SettingsController extends Controller
{
    public function __construct() 
    {
        parent::__construct();
        $this->parser = new Parser();
        $this->product = new ProductEntity();
    }

    /*
     * View all settings size
     * @return Settings/size template
     */
    public function sizeAction() 
    {
        return $this->render->display("Settings/Size/size_tab", array(
            'sizes' => $this->parser->factory('Xml')->loadXml('filter')->getNode('size/item')->nodeToArray()
        ));
    }

    /*
     * Update sizes
     * @return Settings/size template
     */
    public function sizeUpdateAction() 
    {
        $request = Request::getRequest();
        $parser = $this->parser->factory('Xml')->loadXml('filter');
        if (isset($request['size']) && count($request['size']) > 0) {
            foreach ($request['size'] as $key => $value) {
                if (strpos($key, 'new') !== false) {
                    $node = $parser->getXml()->xpath('size');
                    //var_dump($node);die;
                    $item = $node[0]->addChild('item');
                    $item->addChild('label', $value['size']);
                    $item->addChild('value', count($request['size'])+1);
                } else {
                    $node = $parser->getXml()->xpath('size/item/value[.="'.$key.'"]/parent::*');
                    $node[0]->label[0] = $value['size'];
                }
            }
        }
        
        $parser->saveXml();

        return $this->render->display("Settings/Size/size_tab", array(
            'sizes' => $parser->loadXml('filter')->getNode('size/item')->nodeToArray()
        ));
    }

    /*
     * Remove size
     * @param int id ajax request
     * @return ''
     */
    public function removeSizeAction() 
    {
        $request = Request::getRequest();
        
        if (CRM_IS_AJAX() && isset($request['id'])) {
            $parser = $this->parser->factory('Xml')->loadXml('filter');
            $node = $parser->getXml()->xpath('size/item/value[.="'.$request['id'].'"]/parent::*');
            $dom = dom_import_simplexml($node[0]);
            $dom->parentNode->removeChild($dom);
            $parser->saveXml();
        }
        
        die();
    }

    /*
     * View all settings city
     * @return Settings/city template
     */
    public function cityAction() 
    {
        $request = Request::getRequest();

        $citys = $this->product->getAllCitys();

        return $this->render->display("Settings/City/city_tab", array(
            'citys' => $citys
        ));
    }

    /*
     * Update citys
     * @return Settings/city template
     */
    public function cityUpdateAction() 
    {
        $request = Request::getRequest();

        if (isset($request['city']) && count($request['city']) > 0) {
            foreach ($request['city'] as $key => $value) {
                if (strpos($key, 'new') !== false) {
                    $this->product->addCity($value);
                } else {
                    $this->product->updateCity($value['id'], $value);
                }
            }
        }

        $citys = $this->product->getAllCitys();

        return $this->render->display("Settings/City/city_tab", array(
            'citys' => $citys
        ));
    }

    /*
     * Remove city
     * @param int id ajax request
     * @return ''
     */
    public function removeCityAction() 
    {
        $request = Request::getRequest();
        
        if (CRM_IS_AJAX() && isset($request['id'])) {
            $this->product->removeCity($request['id']);
        }
        
        die();
    }
}
