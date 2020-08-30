<?php

namespace Core\Components;

use Core\Components\Request;
use Core\Config\Config;

/*
 * Database class
 */
class Database {

    /*
     * Get total row of last query
     */
    public function getTotalRows() 
    {
        $query = $this->query('SELECT FOUND_ROWS()'); 
		return (int)current(mysql_fetch_assoc($query)); //change reset to current by eyrad4
    }
    
    public function getAllCategorys() 
    {
        $sql = "SELECT 
                    row.*,
                    catalog.pagetitle as catalog_name 
                FROM modx_site_content row 
                LEFT JOIN modx_site_content catalog ON catalog.id = row.parent 
                WHERE row.template = ".intval(Config::getVal('app', 'category_template', 'application'));
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    public function getAllCatalogs() 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_site_content row
                WHERE row.template = ".intval(Config::getVal('app', 'catalog_template', 'application'));
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get all revweb usersiews 
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
    
    /*
     * Get all revweb usersiews 
     * @return array
     */
    public function getWebUser($id)
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_web_user_attributes row
                WHERE internalKey = ".intval($id);
        $query = $this->query($sql);
        $row = $this->fetchOne($query);
        return $row;
    }
    
    public function query($query) {
        return mysql_query($query);
    }
    
    public function getInsertId() {
        return mysql_insert_id();
    }
    
    public function escape($query) {
        return mysql_real_escape_string($query);
    }

    public function fetchAll($query) {
        if ($query != null) {
	        $a = array();
	        $c = mysql_num_rows($query);
	        for ($i = 0; $i < $c; $i++)
	            $a[] = mysql_fetch_assoc($query);
	        return $a;
    	}
        
        return '';
    }
    
    public function fetchOne($query) {
        return mysql_fetch_assoc($query);
    }
    
    /*
     * Build filters query by request array
     * @return query line
     */
    public function buildGrideFilters() 
    {
        $request = Request::getRequest();
        
        $where = array();
        if (isset($request['filter']) && count($request['filter']) > 0) {
            foreach ($request['filter'] as $key => $value) {
                if ($value == '' || $value == 'all') {
                    continue;
                }

                if (is_numeric($value)) {
                    switch ($key) {
                        case 'category':
                            $where[] = 'row.id IN (SELECT product_id FROM modx_mm_p2c_link WHERE modx_id IN ('.intval($value).'))';
                            break;
                        default:
                            $where[] = 'row.'.$key.' = '.$value;
                            break;
                    }
                } else {
                    switch ($key) {
                        case 'category':
                            $exp = array_filter(explode(',', $value));
                            $exp = count($exp) > 0 ? implode(',', $exp) : '';
                            $where[] = 'row.id IN (SELECT product_id FROM modx_mm_p2c_link WHERE modx_id IN ('.$exp.'))';
                            break;
                        case 'create_at':
                            $where[] = 'row.'.$key.' = "'.$value.'"';
                            break;
                        case 'closed_at':
                            $where[] = 'row.'.$key.' = "'.$value.'"';
                            break;
                        case 'payed_at':
                            $where[] = 'row.'.$key.' = "'.$value.'"';
                            break;
                        default:
                            $where[] = 'row.'.$key.' Like "%'.$value.'%"';
                            break;
                    }
                    
                }
            }
        }
        
        return count($where) > 0 ? ' WHERE '.implode(' AND ', $where) : '';
    }
    
}
    