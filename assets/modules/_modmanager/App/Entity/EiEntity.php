<?php

namespace App\Entity;

use Core\Components\Database;
use Core\Components\Request;

/*
 * Ei entity class, extend /Core/Components/Database class
 */
class EiEntity extends Database
{

    public function __construct() 
    {
        
    }
    
    /*
     * Generate list of products
     * @return array
     */
    public function getProductToExport() 
    {
        $query = $this->query("SELECT  
                    SQL_CALC_FOUND_ROWS 
                    row.*,  
                    meta.title as meta_title,
                    meta.description as meta_description,
                    meta.keywords as meta_keywords,
                    meta.robots as meta_robots,
                    meta.canonical as meta_canonical,
                    meta.analytics as meta_analytics,
                    (SELECT value_id FROM modx_mm_fv2p_link WHERE product_id = row.id AND value_id IN (82,83) LIMIT 1) as collection_id, 
                    (SELECT group_id FROM modx_mm_p2g_link WHERE product_id = row.id) as group_id, 
                    (SELECT modx_id FROM modx_mm_p2c_link WHERE product_id = row.id LIMIT 1) as modx_id
                FROM modx_mm_products row 
                LEFT JOIN modx_mm_metadata meta ON meta.page_id = row.id 
                ORDER BY row.id DESC");
        $rows = $this->fetchAll($query);
        return $rows;
    }
}

