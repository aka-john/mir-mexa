<?php

namespace App\Entity;

use Core\Components\Database;
use Core\Components\Request;

/*
 * Shop entity class, extend /Core/Components/Database class
 */
class ShopEntity extends Database
{

    public function __construct() 
    {
        
    }
    
    /*
     * Generate list of shops by request params
     * buildGrideShops - build shops by request params
     * @return array
     */
    public function getGridShops() 
    {
        $request = Request::getRequest();
        
        $grid_sort_dir = isset($request['grid_sort_dir']) && $request['grid_sort_dir'] != '' ? $request['grid_sort_dir'] : 'row.id';
        $grid_sort_by = isset($request['grid_sort_by']) && $request['grid_sort_by'] != '' ? $request['grid_sort_by'] : 'DESC';
        
        $query = $this->query("SELECT  
                    SQL_CALC_FOUND_ROWS 
                    row.* 
                FROM modx_mm_shop row 
                ".($this->buildGrideFilters() != '' ? $this->buildGrideFilters() : '')."
                ORDER BY ".$grid_sort_dir." ".$grid_sort_by." 
                LIMIT ".$request['grid_limit']."  
                OFFSET ".$request['grid_start']);
        $rows = $this->fetchAll($query);
        return $rows;
    }

    /*
     * Get shop fields
     * @param int shop id
     * @return single result array
     */
    public function getShop($id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_shop row 
                WHERE id = ".intval($id);
        $query = $this->query($sql);
        $row = $this->fetchOne($query);
        return $row;
    }
    
    /*
     * Add shop
     * @param int contract id
     * @param array shop fields
     * @return inserted id
     */
    public function addShop($fields)
    {
        $sql = "INSERT INTO modx_mm_shop (
                    `number`,
                    created_at,
                    order_price,
                    delivery_method,
                    pay_method,
                    delivery_price,
                    payment_price,
                    orders,
                    status,
                    user_id,
                    transaction,
                    comment,
                    user_info
                ) VALUES (
                    '".$this->escape($fields['number'])."',
                    '".($fields['created_at'] == '' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', strtotime($fields['created_at'])))."',
                    '".str_replace(",",".",floatval($fields['order_price']))."',
                    '".intval($fields['delivery_method'])."',
                    '".intval($fields['pay_method'])."',
                    '".str_replace(",",".",floatval($fields['delivery_price']))."',
                    '".str_replace(",",".",floatval($fields['payment_price']))."',
                    '".$this->escape($fields['orders'])."',
                    '".intval($fields['status'])."',
                    '".intval($fields['user_id'])."',
                    '".$this->escape($fields['transaction'])."',
                    '".$this->escape($fields['comment'])."',
                    '".$this->escape($fields['user_info'])."'
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }

    /*
     * Update shop
     * @param int shop id
     * @param array shop fields
     * @return single result array
     */
    public function updateShop($id, $fields)
    {
        $sql = "UPDATE modx_mm_shop SET 
                    `number` = '".$this->escape($fields['number'])."',
                    created_at = '".($fields['created_at'] == '' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', strtotime($fields['created_at'])))."',
                    order_price = '".str_replace(",",".",floatval($fields['order_price']))."',
                    delivery_method = '".intval($fields['delivery_method'])."',
                    pay_method = '".intval($fields['pay_method'])."',
                    delivery_price = '".str_replace(",",".",floatval($fields['delivery_price']))."',
                    payment_price = '".str_replace(",",".",floatval($fields['payment_price']))."',
                    orders = '".$this->escape($fields['orders'])."',
                    status = '".intval($fields['status'])."',
                    user_id = '".intval($fields['user_id'])."',
                    transaction = '".$this->escape($fields['transaction'])."',
                    comment = '".$this->escape($fields['comment'])."',
                    user_info = '".$this->escape($fields['user_info'])."'
            WHERE id = ".intval($id);
		$query = $this->query($sql);
		return $this->getShop($id);
    }

    /*
     * Remove all shop categorys
     * @param int shop id
     * @return ''
     */
    public function removeAllCategorysInShop($id)
    {
        $this->query("DELETE FROM modx_mm_f2c_link WHERE shop_id = ".intval($id));
    }
    
    /*
     * Remove shop
     * @param int shop id
     * @return ''
     */
    public function removeShop($id)
    {
        $this->query("DELETE FROM modx_mm_shop WHERE id = ".intval($id));
    }
    
    /*
     * Remove shop
     * @param int shop id
     * @return ''
     */
    public function removeShopOrder($id, $fields)
    {
        $sql = "UPDATE modx_mm_shop SET 
                    order_price = '".str_replace(",",".",floatval($fields['order_price']))."',
                    orders = '".$this->escape($fields['orders'])."'
            WHERE id = ".intval($id);
		$query = $this->query($sql);
		return $this->getShop($id);
    }

    /*
     * Get all shops 
     * @return array
     */
    public function getAllShops()
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_shop row";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get all fitting citys 
     * @return array
     */
    public function getFittingCitys() 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_site_content row 
                WHERE parent = 10 AND deleted = 0 AND published = 1 
                ORDER BY pagetitle ASC";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get all fitting city shops
     * @return array
     */
    public function getFittingCityShops($city_id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_site_tmplvar_contentvalues row 
                WHERE contentid = ".intval($city_id)." AND tmplvarid = 21 
                ORDER BY id DESC";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }

}

