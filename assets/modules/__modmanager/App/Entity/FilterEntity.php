<?php

namespace App\Entity;

use Core\Components\Database;
use Core\Components\Request;

/*
 * Filter entity class, extend /Core/Components/Database class
 */
class FilterEntity extends Database
{

    public function __construct() 
    {
        
    }
    
    /*
     * Generate list of filters by request params
     * buildGrideFilters - build filters by request params
     * @return array
     */
    public function getGridFilters() 
    {
        $request = Request::getRequest();
        
        $grid_sort_dir = isset($request['grid_sort_dir']) && $request['grid_sort_dir'] != '' ? $request['grid_sort_dir'] : 'row.id';
        $grid_sort_by = isset($request['grid_sort_by']) && $request['grid_sort_by'] != '' ? $request['grid_sort_by'] : 'DESC';
        
        $query = $this->query("SELECT  
                    SQL_CALC_FOUND_ROWS 
                    row.* 
                FROM modx_mm_filters row 
                ".($this->buildGrideFilters() != '' ? $this->buildGrideFilters() : '')."
                ORDER BY ".$grid_sort_dir." ".$grid_sort_by." 
                LIMIT ".$request['grid_limit']."  
                OFFSET ".$request['grid_start']);
        $rows = $this->fetchAll($query);
        return $rows;
    }

    /*
     * Get filter fields
     * @param int filter id
     * @return single result array
     */
    public function getFilter($id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_filters row 
                WHERE id = ".intval($id);
        $query = $this->query($sql);
        $row = $this->fetchOne($query);
        return $row;
    }
    
    /*
     * Add filter
     * @param int contract id
     * @param array filter fields
     * @return inserted id
     */
    public function addFilter($fields)
    {
        $sql = "INSERT INTO modx_mm_filters (
                    name,
                    type,
                    position,
                    created_at
                ) VALUES (
                    '".$this->escape($fields['name'])."',
                    '".intval($fields['type'])."',
                    '".intval($fields['position'])."',
                    '".($fields['created_at'] == '' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', strtotime($fields['created_at'])))."'
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }

    /*
     * Update filter
     * @param int filter id
     * @param array filter fields
     * @return single result array
     */
    public function updateFilter($id, $fields)
    {
        $sql = "UPDATE modx_mm_filters SET 
			name = '".$this->escape($fields['name'])."',
			type = '".intval($fields['type'])."',
			position = '".intval($fields['position'])."',
			created_at = '".($fields['created_at'] == '' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', strtotime($fields['created_at'])))."'
        WHERE id = ".intval($id);
		$query = $this->query($sql);
		return $this->getFilter($id);
    }

    /*
     * Remove all filter categorys
     * @param int filter id
     * @return ''
     */
    public function removeAllCategorysInFilter($id)
    {
        $this->query("DELETE FROM modx_mm_f2c_link WHERE filter_id = ".intval($id));
    }
    
    /*
     * Add filter categorys
     * @param int filter id
     * @param array filter fields
     * @return ''
     */
    public function addCategorysToFilter($id, $modx_id)
    {
        $sql = "INSERT INTO modx_mm_f2c_link (
                    filter_id,
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
     * Remove filter
     * @param int filter id
     * @return ''
     */
    public function removeFilter($id)
    {
        $this->query("DELETE FROM modx_mm_filters WHERE id = ".intval($id));
        
        $values = $this->getAllFilterValues($id);
        if (count($values) > 0) {
            foreach ($values as $key => $value) {
                $values_item[] = $value['id'];
            }
        }
        
        if (count($values_item) > 0) {
            $this->query("DELETE FROM modx_mm_fv2p_link WHERE value_id IN (".implode(',', $values_item).")");
        }
        
        $this->query("DELETE FROM modx_mm_filters_value WHERE filter_id = ".intval($id));
    }
    
    /*
     * Remove filter value
     * @param int value id
     * @return ''
     */
    public function removeFilterValue($id)
    {
        $this->query("DELETE FROM modx_mm_filters_value WHERE id = ".intval($id));
        $this->query("DELETE FROM modx_mm_fv2p_link WHERE value_id = ".intval($id)); 
    }
   
    /*
     * Get all filters 
     * @return array
     */
    public function getAllFilters()
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_filters row";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get filter categorys by id
     * @param int filter id
     * @return array
     */
    public function getFilterCategorysById($id)
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_f2c_link row
                WHERE row.filter_id = ".intval($id);
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get filter categorys by id
     * @param int filter id
     * @return array
     */
    public function getFilterCategorysIds($id)
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_f2c_link row
                WHERE row.filter_id = ".intval($id);
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        
        $result = array();
        
        foreach ($rows as $key => $value) {
            $result[] = $value['modx_id'];
        }
        
        return $result;
    }

    /*
     * Get filter by category id
     * @param int category id
     * @return array
     */
    public function getFilterByCategoryId($id)
    {
        $sql = "SELECT 
                    row.*,
                    filter.* 
                FROM modx_mm_f2c_link row 
                LEFT JOIN modx_mm_filters filter ON filter.id = row.filter_id 
                WHERE row.modx_id = ".intval($id);
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get all filter values
     * @return array
     */
    public function getAllFilterValues($id)
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_filters_value row
                WHERE row.filter_id = ".intval($id);
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get filter value
     * @return array
     */
    public function getFilterValue($id)
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_filters_value row
                WHERE row.id = ".intval($id);
        $query = $this->query($sql);
        $rows = $this->fetchOne($query);
        return $rows;
    }
    
    /*
     * Get filter value
     * @return array
     */
    public function isFilterValueExist($filter_id, $value)
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_filters_value row
                WHERE row.filter_id = ".intval($filter_id)." AND value = '".$value."'";
        $query = $this->query($sql);
        $rows = $this->fetchOne($query);
        return $rows ? true : false;
    }
    
    /*
     * Update filter value
     * @param int filter id
     * @param array value fields
     * @return single result array
     */
    public function updateFilterValue($id, $fields)
    {
        $sql = "UPDATE modx_mm_filters_value SET 
			value = '".$this->escape($fields['value'])."',
            param = '".$this->escape($fields['param'])."',
			position = ".intval($fields['position'])."
        WHERE id = ".intval($id);
		$query = $this->query($sql);
		return $this->getFilterValue($id);
    }
    
    /*
     * Add filter value
     * @param int filter id
     * @param array value fields
     * @return inserted id
     */
    public function addFilterValue($filter_id, $fields)
    {
        $sql = "INSERT INTO modx_mm_filters_value (
                    filter_id,
                    value,
                    param,
                    position
                ) VALUES (
                    ".intval($filter_id).",
                    '".$this->escape($fields['value'])."',
                    '".$this->escape($fields['param'])."',
                    ".intval($fields['position'])."
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }

}

