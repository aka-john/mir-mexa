<?php

namespace App\Entity;

use Core\Components\Database;
use Core\Components\Request;

/*
 * Review entity class, extend /Core/Components/Database class
 */
class ReviewEntity extends Database
{

    public function __construct() 
    {
        
    }
    
    /*
     * Generate list of reviews by request params
     * buildGrideFilters - build filters by request params
     * @return array
     */
    public function getGridReviews() 
    {
        $request = Request::getRequest();
        
        $grid_sort_dir = isset($request['grid_sort_dir']) && $request['grid_sort_dir'] != '' ? $request['grid_sort_dir'] : 'row.id';
        $grid_sort_by = isset($request['grid_sort_by']) && $request['grid_sort_by'] != '' ? $request['grid_sort_by'] : 'DESC';
        
        $query = $this->query("SELECT  
                    SQL_CALC_FOUND_ROWS 
                    row.* 
                FROM modx_mm_review row 
                ".($this->buildGrideFilters() != '' ? $this->buildGrideFilters() : '')."
                ORDER BY ".$grid_sort_dir." ".$grid_sort_by." 
                LIMIT ".$request['grid_limit']."  
                OFFSET ".$request['grid_start']);
        $rows = $this->fetchAll($query);
        return $rows;
    }

    /*
     * Get review fields
     * @param int review id
     * @return single result array
     */
    public function getReview($id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_review row 
                WHERE id = ".intval($id);
        $query = $this->query($sql);
        $row = $this->fetchOne($query);
        return $row;
    }
    
    /*
     * Add review
     * @param array review fields
     * @return inserted id
     */
    public function addReview($fields)
    {
        $sql = "INSERT INTO modx_mm_review (
                    name,
                    email,
                    message,
                    created_at,
                    status,
                    user_id,
                    product_id
                ) VALUES (
                    '".$this->escape($fields['name'])."',
                    '".$this->escape($fields['email'])."',
                    '".$this->escape($fields['message'])."',
                    '".($fields['created_at'] == '' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', strtotime($fields['created_at'])))."',
                    ".intval($fields['status']).",
                    ".intval($fields['user_id']).",
                    ".intval($fields['product_id'])."
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }

    /*
     * Update review
     * @param int review id
     * @param array review fields
     * @return single result array
     */
    public function updateReview($id, $fields)
    {
        $sql = "UPDATE modx_mm_review SET 
			name    = '".$this->escape($fields['name'])."',
			email   = '".$this->escape($fields['email'])."',
			message = '".$this->escape($fields['message'])."',
			created_at = '".($fields['created_at'] == '' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', strtotime($fields['created_at'])))."',
			status  = ".intval($fields['status']).",
			user_id = ".intval($fields['user_id']).",
			product_id  = ".intval($fields['product_id'])."
        WHERE id = ".intval($id);
		$query = $this->query($sql);
		return $this->getReview($id);
    }

    /*
     * Remove review
     * @param int review id
     * @return ''
     */
    public function removeReview($id)
    {
        $this->query("DELETE FROM modx_mm_review WHERE id = ".intval($id));
    }
   
    /*
     * Get all reviews 
     * @return array
     */
    public function getAllReviews()
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_review row";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get all Get all web users
     * @return array
     */
    public function getAllWebUsers()
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_web_user_attributes row";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }

}

