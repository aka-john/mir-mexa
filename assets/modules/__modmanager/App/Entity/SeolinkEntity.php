<?php

namespace App\Entity;

use Core\Components\Database;
use Core\Components\Request;

/*
 * Seolink entity class, extend /Core/Components/Database class
 */
class SeolinkEntity extends Database
{

    public function __construct() 
    {
        
    }
    
    /*
     * Generate list of Seolinks by request params
     * buildGrideFilters - build filters by request params
     * @return array
     */
    public function getGridSeolinks() 
    {
        $request = Request::getRequest();
        
        $grid_sort_dir = isset($request['grid_sort_dir']) && $request['grid_sort_dir'] != '' ? $request['grid_sort_dir'] : 'row.id';
        $grid_sort_by = isset($request['grid_sort_by']) && $request['grid_sort_by'] != '' ? $request['grid_sort_by'] : 'DESC';
        
        $query = $this->query("SELECT  
                    SQL_CALC_FOUND_ROWS 
                    row.* 
                FROM modx_mm_seolink row 
                ".($this->buildGrideFilters() != '' ? $this->buildGrideFilters() : '')."
                ORDER BY ".$grid_sort_dir." ".$grid_sort_by." 
                LIMIT ".$request['grid_limit']."  
                OFFSET ".$request['grid_start']);
        $rows = $this->fetchAll($query);
        return $rows;
    }

    /*
     * Get Seolink fields
     * @param int Seolink id
     * @return single result array
     */
    public function getSeolink($id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_seolink row 
                WHERE id = ".intval($id);
        $query = $this->query($sql);
        $row = $this->fetchOne($query);
        return $row;
    }
    
    /*
     * Add Seolink
     * @param array Seolink fields
     * @return inserted id
     */
    public function addSeolink($fields)
    {
        $sql = "INSERT INTO modx_mm_seolink (
                    url,
                    title,
                    description,
                    keywords,
                    robots,
                    canonical,
                    active
                ) VALUES (
                    '".$this->escape($fields['url'])."',
                    '".$this->escape($fields['title'])."',
                    '".$this->escape($fields['description'])."',
                    '".$this->escape($fields['keywords'])."',
                    '".$this->escape($fields['robots'])."',
                    '".$this->escape($fields['canonical'])."',
                    ".intval($fields['active'])."
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }

    /*
     * Update Seolink
     * @param int Seolink id
     * @param array Seolink fields
     * @return single result array
     */
    public function updateSeolink($id, $fields)
    {
        $sql = "UPDATE modx_mm_seolink SET 
            url         = '".$this->escape($fields['url'])."',
            title       = '".$this->escape($fields['title'])."',
            description = '".$this->escape($fields['description'])."',
            keywords    = '".$this->escape($fields['keywords'])."',
            robots      = '".$this->escape($fields['robots'])."',
            canonical   = '".$this->escape($fields['canonical'])."',
            active      = ".intval($fields['active'])."
        WHERE id = ".intval($id);
		$query = $this->query($sql);
		return $this->getSeolink($id);
    }

    /*
     * Remove Seolink
     * @param int Seolink id
     * @return ''
     */
    public function removeSeolink($id)
    {
        $this->query("DELETE FROM modx_mm_seolink WHERE id = ".intval($id));
    }
   
    /*
     * Get all Seolinks 
     * @return array
     */
    public function getAllSeolinks()
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_seolink row";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
}

