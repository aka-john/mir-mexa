<?php

namespace App\Entity;

use Core\Components\Database;
use Core\Components\Request;

/*
 * Product entity class, extend /Core/Components/Database class
 */
class ProductEntity extends Database
{

    public function __construct() 
    {
        
    }
    
    /*
     * Generate list of products by request params
     * buildGrideFilters - build products by request params
     * @return array
     */
    public function getGridProducts() 
    {
        $request = Request::getRequest();
        
        $grid_sort_dir = isset($request['grid_sort_dir']) && $request['grid_sort_dir'] != '' ? $request['grid_sort_dir'] : 'row.id';
        $grid_sort_by = isset($request['grid_sort_by']) && $request['grid_sort_by'] != '' ? $request['grid_sort_by'] : 'DESC';
        
        $query = $this->query("SELECT  
                    SQL_CALC_FOUND_ROWS 
                    row.*,  
                    (SELECT group_id FROM modx_mm_p2g_link WHERE product_id = row.id) as group_id, 
                    (SELECT modx_id FROM modx_mm_p2c_link WHERE product_id = row.id LIMIT 1) as modx_id
                FROM modx_mm_products row 
                ".($this->buildGrideFilters() != '' ? $this->buildGrideFilters() : '')."
                ORDER BY ".$grid_sort_dir." ".$grid_sort_by." 
                LIMIT ".$request['grid_limit']."  
                OFFSET ".$request['grid_start']);
        $rows = $this->fetchAll($query);
        return $rows;
    }

    /*
     * Get product fields
     * @param int product id
     * @return single result array
     */
    public function getProduct($id) 
    {
        $sql = "SELECT 
                    row.*, 
                    (SELECT group_id FROM modx_mm_p2g_link WHERE product_id = row.id) as group_id,
                    (SELECT modx_id FROM modx_mm_p2c_link WHERE product_id = row.id LIMIT 1) as modx_id
                FROM modx_mm_products row 
                WHERE id = ".intval($id);
        $query = $this->query($sql);
        $row = $this->fetchOne($query);
        return $row;
    }
    
    /*
     * Add product
     * @param array product fields
     * @return inserted id
     */
    public function addProduct($fields)
    {
        $sql = "INSERT INTO modx_mm_products (
                    name,
                    position,
                    description,
                    introtext,
                    content,
                    alias,
                    published,
                    to_slider,
                    created_at,
                    status,
                    sku,
                    amount,
                    searchable,
                    additional,
                    video,
                    presence,
                    material
                ) VALUES (
                    '".trim($this->escape($fields['name']))."',
                    ".intval($fields['position']).",
                    '".$this->escape($fields['description'])."',
                    '".$this->escape($fields['introtext'])."',
                    '".$this->escape($fields['content'])."',
                    '".trim($this->escape($fields['alias']))."',
                    ".intval($fields['published']).",
                    ".intval($fields['to_slider']).",
                    '".($fields['created_at'] == '' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', strtotime($fields['created_at'])))."',
                    ".intval($fields['status']).",
                    '".$this->escape($fields['sku'])."',
                    ".intval($fields['amount']).",
                    ".intval($fields['searchable']).",
                    '".$this->escape($fields['additional'])."',
                    '".$this->escape($fields['video'])."',
                    ".intval($fields['presence']).",
                    '".$this->escape($fields['material'])."'
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }

    /*
     * Update product
     * @param int product id
     * @param array product fields
     * @return single result array
     */
    public function updateProduct($id, $fields)
    {
        $sql = "UPDATE modx_mm_products SET 
                    name = '".trim($this->escape($fields['name']))."',
                    position = ".intval($fields['position']).",
                    description = '".$this->escape($fields['description'])."',
                    introtext = '".$this->escape($fields['introtext'])."',
                    content = '".$this->escape($fields['content'])."',
                    alias = '".trim($this->escape($fields['alias']))."',
                    published = ".intval($fields['published']).",
                    to_slider = ".intval($fields['to_slider']).",
                    created_at = '".($fields['created_at'] == '' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', strtotime($fields['created_at'])))."',
                    status = ".intval($fields['status']).",
                    sku = '".$this->escape($fields['sku'])."',
                    amount = ".intval($fields['amount']).",
                    searchable = ".intval($fields['searchable']).",
                    additional = '".$this->escape($fields['additional'])."',
                    video = '".$this->escape($fields['video'])."',
                    presence = ".intval($fields['presence']).",
                    material = '".$this->escape($fields['material'])."' 
                WHERE id = ".intval($id);
		$query = $this->query($sql);
		return $this->getProduct($id);
    }

    /*
     * Remove product
     * @param int product id
     * @return ''
     */
    public function removeProduct($id)
    {
        $this->query("DELETE FROM modx_mm_products WHERE id = ".intval($id));
        $this->query("DELETE FROM modx_mm_p2c_link WHERE product_id = ".intval($id));
        $this->query("DELETE FROM modx_mm_p2g_link WHERE product_id = ".intval($id));
        $this->query("DELETE FROM modx_mm_fv2p_link WHERE product_id = ".intval($id));
        $this->query("DELETE FROM modx_mm_metadata WHERE page_id = ".intval($id));

        $ids = array();
        $sizes = $this->getProductSizeByProduct($id);
        if (count($sizes) > 0) {
            foreach ($sizes as $key => $value) {
                $ids[] = $value['id'];
            }

            $this->query("DELETE FROM modx_mm_size2city WHERE size_id IN (".implode(',' , $ids).")");
        }

        $this->query("DELETE FROM modx_mm_product_size WHERE product_id = ".intval($id));
        $this->removeProductPrices($id);
        return '';
    }
   
    /*
     * Get all products 
     * @return array
     */
    public function getAllProducts()
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_products row ORDER BY row.name ASC";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Remove products group
     * @param array products ids
     * @return ''
     */
    public function removeProductsGroups($products = array())
    {
        $this->query("DELETE FROM modx_mm_p2g_link WHERE product_id IN (".implode(',', $products).")");
        return '';
    }

    /*
     * Get product size fields
     * @param int size id
     * @return single result array
     */
    public function getProductSize($id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_product_size row 
                WHERE id = ".intval($id);
        $query = $this->query($sql);
        $row = $this->fetchOne($query);
        return $row;
    }
    
    /*
     * Get availability fields
     * @param int product id
     * @return array
     */
    public function getProductAvailabilitySubscribe($id, $size_id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_availability row 
                WHERE status = 0 AND size_id = ".intval($size_id)." AND product_id = ".intval($id);
        $query = $this->query($sql);
        $row = $this->fetchAll($query);
        return $row;
    }
    
    /*
     * Update availability status
     * @param int availability id
     * @return ''
     */
    public function removeAvailabilitySubscribe($id)
    {
        $sql = "UPDATE modx_mm_availability SET 
                    status = 1 
                WHERE id = ".intval($id);
		$query = $this->query($sql);
		return '';
    }
    
    /*
     * Get product in groups
     * @param int group id
     * @return single result array
     */
    public function getProductByGroupId($group_id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_p2g_link row 
                WHERE group_id = ".intval($group_id);
        $query = $this->query($sql);
        $row = $this->fetchAll($query);
        return $row;
    }
    
    /*
     * Get product size fields
     * @param int product id
     * @return single result array
     */
    public function getProductSizeByProduct($product_id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_product_size row 
                WHERE product_id = ".intval($product_id);
        $query = $this->query($sql);
        $row = $this->fetchAll($query);
        return $row;
    }
    
    /*
     * Add product size
     * @param int product id
     * @param array product fields
     * @return inserted id
     */
    public function addProductSize($product_id, $fields)
    {
        $sql = "INSERT INTO modx_mm_product_size (
                    size_id,
                    size,
                    amount,
                    price,
                    sale_price,
                    created_at,
                    product_id
                ) VALUES (
                    ".$this->escape($fields['size_id']).",
                    '".$this->escape($fields['size'])."',
                    ".intval($fields['amount']).",
                    ".str_replace(",",".",floatval($fields['price'])).",
                    ".str_replace(",",".",floatval($fields['sale_price'])).",
                    '".($fields['created_at'] == '' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', strtotime($fields['created_at'])))."',
                    ".intval($product_id)."
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }

    /*
     * Update product size
     * @param int size id
     * @param array product size fields
     * @return single result array
     */
    public function updateProductSize($id, $fields)
    {
        $sql = "UPDATE modx_mm_product_size SET 
                    size_id = ".$this->escape($fields['size_id']).",
                    size = '".$this->escape($fields['size'])."',
                    amount = '".intval($fields['amount'])."',
                    price = '".str_replace(",",".",floatval($fields['price']))."',
                    sale_price = '".str_replace(",",".",floatval($fields['sale_price']))."'
                WHERE id = ".intval($id);
		$query = $this->query($sql);
		return $this->getProductSize($id);
    }

    /*
     * Remove product
     * @param int product id
     * @return ''
     */
    public function removeProductSize($id)
    {
        $this->query("DELETE FROM modx_mm_product_size WHERE id = ".intval($id));
        $this->query("DELETE FROM modx_mm_price WHERE size_id = ".intval($id));
    }
   
    /*
     * Get all product sizes
     * @return array
     */
    public function getAllProductSizes($product_id)
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_product_size row
                WHERE row.product_id = ".intval($product_id);
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }

    /*
     * Check alias duplicate
     * @param string alias
     * @return boolean
     */
    public function isAliasExist($product_id, $alias) 
    {
        if ($alias == '') {
           return false;
        }
        
        $sql = "SELECT count(*) as cnt, id as product_id  
                FROM modx_mm_products row 
                WHERE alias = '".$this->escape($alias)."'";
        $query = $this->query($sql);
        $row = $this->fetchOne($query);
 
        return $row['cnt'] > 0 && $row['product_id'] != $product_id ? false : true;
    }
    
    /*
     * Get product categorys by id
     * @param int product id
     * @return array
     */
    public function getProductCategorysById($id)
    {
        $sql = "SELECT 
                    row.*,
                    category.*
                FROM modx_mm_p2c_link row
                LEFT JOIN modx_site_content category ON category.id = row.modx_id 
                WHERE row.product_id = ".intval($id);
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get product categorys by id
     * @param int product id
     * @return array
     */
    public function getProductCategorysIds($id)
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_p2c_link row
                WHERE row.product_id = ".intval($id);
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        
        $result = array();
        
        foreach ($rows as $key => $value) {
            $result[] = $value['modx_id'];
        }
        
        return $result;
    }
    
    /*
     * Get filter values by autocomplete word
     * @param string word
     * @return array
     */
    public function getAutocompleteFilter($word)
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_filters_value row
                WHERE row.value LIKE '%".$word."%'";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get product by autocomplete word
     * @param string word
     * @return array
     */
    public function getAutocompleteProducts($word)
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_products row
                WHERE row.name LIKE '%".$word."%'";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get all filter values
     * @return array
     */
    public function getProductFilterValues($product_id, $filter_id, $category_id)
    {
        $sql = "SELECT 
                    row.*, 
                    value.*
                FROM modx_mm_fv2p_link row
                LEFT JOIN modx_mm_filters_value value ON value.id = row.value_id 
                WHERE row.product_id = ".intval($product_id)." AND row.modx_id = ".intval($category_id)." AND value.filter_id = ".intval($filter_id);
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get all product filters and values
     * @return array
     */
    public function getProductFiltersAndValues($product_id, $category_id)
    {
        $sql = "SELECT 
                    row.*, 
                    value.*
                FROM modx_mm_fv2p_link row
                JOIN modx_mm_filters_value value ON value.id = row.value_id 
                WHERE row.product_id = ".intval($product_id)." AND row.modx_id = ".intval($category_id);
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Remove all filter values
     * @return ''
     */
    public function removeAllProductFilterValues($product_id)
    {
        $this->query("DELETE FROM modx_mm_fv2p_link WHERE product_id = ".intval($product_id));
    }
    
    /*
     * Remove all filter categorys
     * @param int filter id
     * @return ''
     */
    public function removeAllCategorysInProduct($id)
    {
        $this->query("DELETE FROM modx_mm_p2c_link WHERE product_id = ".intval($id));
    }
    
    /*
     * Add product to group
     * @param int group id
     * @param int product id
     * @return ''
     */
    public function addProductToGroup($group_id, $product_id)
    {
        $sql = "INSERT INTO modx_mm_p2g_link (
                    product_id,
                    group_id
                ) VALUES (
                    ".intval($product_id).",
                    ".intval($group_id)."
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }
    
    /*
     * Add filter categorys
     * @param int product id
     * @param int modx id
     * @return ''
     */
    public function addCategorysToProduct($id, $modx_id)
    {
        $sql = "INSERT INTO modx_mm_p2c_link (
                    product_id,
                    modx_id
                ) VALUES (
                    ".intval($id).",
                    ".intval($modx_id)."
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }
    
    /*
     * Add product filter value
     * @param int filter id
     * @param array filter fields
     * @return ''
     */
    public function addFilterValueToProduct($product_id, $value_id, $category_id)
    {
        $sql = "INSERT INTO modx_mm_fv2p_link (
                    product_id,
                    value_id,
                    modx_id
                ) VALUES (
                    ".intval($product_id).",
                    ".intval($value_id).",
                    ".intval($category_id)."
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }
    
    /*
     * Get product metadata fields
     * @param int product id
     * @return single result array
     */
    public function getProductSeo($product_id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_metadata row 
                WHERE page_id = ".intval($product_id);
        $query = $this->query($sql);
        $row = $this->fetchOne($query);
        return $row;
    }
    
    /*
     * Add product metadata
     * @param int product id
     * @param array metadata fields
     * @return inserted id
     */
    public function addProductSeo($product_id, $fields)
    {
        $sql = "INSERT INTO modx_mm_metadata (
                    title,
                    description,
                    keywords,
                    robots,
                    canonical,
                    analytics,
                    page_id
                ) VALUES (
                    '".$this->escape($fields['title'])."',
                    '".$this->escape($fields['description'])."',
                    '".$this->escape($fields['keywords'])."',
                    '".$this->escape($fields['robots'])."',
                    '".$this->escape($fields['canonical'])."',
                    '".$this->escape($fields['analytics'])."',
                    ".$product_id."
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }
    
    /*
     * Remove product image
     * @param int product id
     * @return ''
     */
    public function removeProductImage($product_id)
    {
        $sql = "UPDATE modx_mm_products SET 
                    image = '' 
                WHERE id = ".intval($product_id);
		$query = $this->query($sql);
		return '';
    }
    
    /*
     * Remove product gallery image
     * @param int image id
     * @return ''
     */
    public function removeProductGalleryImage($id)
    {
        $this->query("DELETE FROM modx_mm_images WHERE id = ".intval($id));
		return '';
    }
    
    /*
     * Update product image
     * @param int product id
     * @param string image
     * @return product
     */
    public function uploadProductImage($product_id, $image)
    {
        $sql = "UPDATE modx_mm_products SET 
                    image = '".$image."' 
                WHERE id = ".intval($product_id);
		$query = $this->query($sql);
		return $this->getProduct($product_id);
    }

    /*
     * Update product metadata
     * @param int product id
     * @param array metadata fields
     * @return single result array
     */
    public function updateProductSeo($product_id, $fields)
    {
        $sql = "UPDATE modx_mm_metadata SET 
                    title = '".$this->escape($fields['title'])."',
                    description = '".$this->escape($fields['description'])."',
                    keywords = '".$this->escape($fields['keywords'])."',
                    robots = '".$this->escape($fields['robots'])."',
                    canonical = '".$this->escape($fields['canonical'])."',
                    analytics = '".$this->escape($fields['analytics'])."'
                WHERE page_id = ".intval($product_id);
		$query = $this->query($sql);
		return $this->getProductSeo($product_id);
    }
    
    /*
     * Get product gallery image fields
     * @param int gallery image id
     * @return single result array
     */
    public function getProductGalleryImage($id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_images row 
                WHERE id = ".intval($id);
        $query = $this->query($sql);
        $row = $this->fetchOne($query);
        return $row;
    }
    
    /*
     * Get product gallery images
     * @param int product id
     * @return single result array
     */
    public function getAllProductGalleryImages($product_id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_images row 
                WHERE product_id = ".intval($product_id)." AND flag = 0";
        $query = $this->query($sql);
        $row = $this->fetchAll($query);
        return $row;
    }
    
    /*
     * Add product gallery image
     * @param int product id
     * @param array gallery image fields
     * @return inserted id
     */
    public function addProductGalleryImage($product_id, $fields)
    {
        $sql = "INSERT INTO modx_mm_images (
                    image,
                    alt,
                    title,
                    position,
                    flag,
                    product_id
                ) VALUES (
                    '".$this->escape($fields['image'])."',
                    '".$this->escape($fields['alt'])."',
                    '".$this->escape($fields['title'])."',
                    '".intval($fields['position'])."',
                    0,
                    ".$product_id."
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }

    /*
     * Update product gallery image
     * @param int gallery image id
     * @param array gallery image fields
     * @return single result array
     */
    public function updateProductGalleryImage($id, $fields)
    {
        $sql = "UPDATE modx_mm_images SET 
                    image = '".$this->escape($fields['image'])."',
                    alt = '".$this->escape($fields['alt'])."',
                    title = '".$this->escape($fields['title'])."',
                    position = ".intval($fields['position'])."
                WHERE id = ".intval($id);
		$query = $this->query($sql);
		return $this->getProductGalleryImage($id);
    }

    public function getAllCitys()
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_city row
                ORDER BY name ASC";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }

    /*
     * Get city fields
     * @param int city id
     * @return single result array
     */
    public function getCity($id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_city row 
                WHERE id = ".intval($id);
        $query = $this->query($sql);
        $row = $this->fetchOne($query);
        return $row;
    }
    
    /*
     * Add city
     * @param array city fields
     * @return inserted id
     */
    public function addCity($fields)
    {
        $sql = "INSERT INTO modx_mm_city (
                    name,
                    alias,
                    default_city
                ) VALUES (
                    '".$this->escape($fields['name'])."',
                    '".$this->escape($fields['alias'])."',
                    '".$this->escape($fields['default_city'])."'
                )";
        $this->query($sql);
        return $this->getInsertId();
    }

    /*
     * Update city
     * @param int city id
     * @param array city fields
     * @return single result array
     */
    public function updateCity($id, $fields)
    {
        $sql = "UPDATE modx_mm_city SET 
                    name = '".$this->escape($fields['name'])."',
                    alias = '".$this->escape($fields['alias'])."',
                    default_city = ".intval($fields['default_city'])."
                WHERE id = ".intval($id);
        $query = $this->query($sql);
        return $this->getProduct($id);
    }

    /*
     * Remove city
     * @param int city id
     * @return ''
     */
    public function removeCity($id)
    {
        $this->query("DELETE FROM modx_mm_city WHERE id = ".intval($id));
        $this->query("DELETE FROM modx_mm_price WHERE city_id = ".intval($id));
        return '';
    }

    /*
     * Get city fields
     * @param int city id
     * @return single result array
     */
    public function getCityBySizeId($size_id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_size2city row 
                WHERE size_id = ".intval($size_id);
        $query = $this->query($sql);
        $row = $this->fetchAll($query);
        return $row;
    }

    /*
     * Remove size citys
     * @param int size id
     * @return ''
     */
    public function removeSizeCitys($id)
    {
        $this->query("DELETE FROM modx_mm_size2city WHERE id = ".intval($id));
        $this->query("DELETE FROM modx_mm_price WHERE city_id = ".intval($id));
        return '';
    }

    /*
     * Add size city
     * @param int size id
     * @param array city fields
     * @return inserted id
     */
    public function addProductSizeCity($size_id, $fields)
    {
        $sql = "INSERT INTO modx_mm_size2city (
                    size_id,
                    city_id,
                    price,
                    sale_price
                ) VALUES (
                    ".intval($size_id).",
                    '".intval($fields['city_id'])."',
                    ".str_replace(",",".",floatval($fields['price'])).",
                    ".str_replace(",",".",floatval($fields['sale_price']))."
                )
        ";
        $this->query($sql);
        return $this->getInsertId();
    }

    /*
     * Update size city
     * @param int city id
     * @param array city fields
     * @return single result array
     */
    public function updateProductSizeCity($id, $size_id, $fields)
    {
        $sql = "UPDATE modx_mm_size2city SET 
                    size_id = ".intval($size_id).",
                    city_id = ".intval($fields['city_id']).",
                    price = '".str_replace(",",".",floatval($fields['price']))."',
                    sale_price = '".str_replace(",",".",floatval($fields['sale_price']))."'
                WHERE id = ".intval($id);
        $query = $this->query($sql);
        return $this->getProductSize($id);
    }

    /*
     * Insert product prices
     * @param int product id
     * @param array product fields
     * @return result
     */
    public function insertPrices($id, $fields) {
        $this->removeProductPrices($id);
        $sql = "INSERT INTO modx_mm_price (
                    product_id,
                    size_id,
                    city_id,
                    price,
                    sale_price
                ) VALUES ".implode(',',$fields);
        $query = $this->query($sql);
        return true;
    }

    /*
     * Remove product prices
     * @param int product id
     * @return result
     */
    public function removeProductPrices($id) 
    {
        $sql = "DELETE FROM modx_mm_price WHERE product_id = ".intval($id);
        $query = $this->query($sql);
        return true;
    }

    public function refreshProductPrice($id) 
    {
        $sizes = $this->getProductSizeByProduct($id);
        $insert = array();

        if (count($sizes) > 0) {
            foreach ($sizes as $key => $size) {
                $insert[] = '('.(int)$id.','.(int)$size['size_id'].',0,'.(float)$size['price'].','.(float)$size['sale_price'].')';//базовая цена размера
                $citys = $this->getCityBySizeId($size['id']);
                
                if (count($citys) == 0) {
                    continue;
                }

                foreach ($citys as $city) {
                    $insert[] = '('.(int)$id.','.(int)$city['size_id'].','.(int)$city['city_id'].','.(float)$city['price'].','.(float)$city['sale_price'].')';//цены по городам
                }
            }
        }

        $this->insertPrices($id, $insert);
    }

    public function refreshAllProductPrice() 
    {
        $products = $this->getAllProducts();

        if (count($products) == 0) {
            continue;
        }

        foreach ($products as $product) {
            $sizes = $this->getProductSizeByProduct($product['id']);
            $insert = array();

            if (count($sizes) > 0) {
                foreach ($sizes as $key => $size) {
                    $insert[] = '('.(int)$product['id'].','.(int)$size['size_id'].',0,'.(float)$size['price'].','.(float)$size['sale_price'].')';//базовая цена размера
                    $citys = $this->getCityBySizeId($size['id']);
                    
                    if (count($citys) == 0) {
                        continue;
                    }

                    foreach ($citys as $city) {
                        $insert[] = '('.(int)$product['id'].','.(int)$city['size_id'].','.(int)$city['city_id'].','.(float)$city['price'].','.(float)$city['sale_price'].')';//цены по городам
                    }
                }
            }

            $this->insertPrices($product['id'], $insert);
        }
    }

}

