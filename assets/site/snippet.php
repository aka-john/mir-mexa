<?php
switch ($action) {
    case 'recommend_categories':
        $sql = "SELECT value FROM modx_site_tmplvar_contentvalues WHERE tmplvarid = 15 and contentid = 1";
        $query = $modx->db->query($sql);
        
        if ($query) {
            $recommends = $modx->db->getRow($query);
            if ($recommends['value'] == '') {
                return '';
            }

            $exp = explode('||', $recommends['value']);
            $right_items = '';
            
            foreach ($exp as $key => $value) {
                $sql_doc = "SELECT 
                            c.id,
                            c.pagetitle,
                            (SELECT value FROM modx_site_tmplvar_contentvalues WHERE tmplvarid = 9 and contentid = c.id) as image 
                        FROM modx_site_content c 
                        WHERE c.id = ".intval($value);
                $query_doc = $modx->db->query($sql_doc);
                if (!$query_doc) {
                    return '';
                }
                
                $doc = $modx->db->getRow($query_doc);
                
                switch ($key) {
                    case '0':
                        $result = $modx->parseChunk('recommend_left', $doc,'[+','+]');
                        break;
                    case '1':
                        $right_items .= $modx->parseChunk('recommend_right_mid', $doc,'[+','+]');
                        break;
                    case '2':
                        $right_items .= $modx->parseChunk('recommend_right_left', $doc,'[+','+]');
                        break;
                    case '3':
                        $right_items .= $modx->parseChunk('recommend_right_right', $doc,'[+','+]');
                        break;
                } 
            }
            
            if ($right_items != '') {
                $right = $modx->parseChunk('recommend_right', array('items' => $right_items),'[+','+]');
                $result .= $right;
            }
            
        }
        break;
    case 'wm_categories':
        $sql = "SELECT value FROM modx_site_tmplvar_contentvalues WHERE tmplvarid = 24 and contentid = ".$modx->documentIdentifier;
        $query = $modx->db->query($sql);
        
        if ($query) {
            $categories = $modx->db->getRow($query);
            if ($categories['value'] == '') {
                return '';
            }

            $categorys = str_replace('||', ',', $categories['value']);
            
            $sql = "SELECT 
                        c.id,
                        c.pagetitle,
                        (SELECT value FROM modx_site_tmplvar_contentvalues WHERE tmplvarid = 9 and contentid = c.id) as image,
                        (select value from modx_site_tmplvar_contentvalues where tmplvarid = 25 and contentid = c.id) as image_man 
                    FROM modx_site_content c 
                    WHERE c.id IN (".$categorys.")";
            
            $query = $modx->db->query($sql);
            if (!$query) {
                return '';
            }

            while ($row = $modx->db->getRow($query)) {
                $result .= $modx->parseChunk('tpl_WMCategoryItem', $row,'[+','+]');;
            }
        }
        break;
    case 'city_categoryes':
            $sql = "SELECT value FROM modx_site_tmplvar_contentvalues WHERE tmplvarid = 18 and contentid = ".intval($id)."";
            $query = $modx->db->query($sql);
        
            if ($query) {
                $categorys = $modx->db->getRow($query);
                if ($categorys['value'] == '') {
                    return '';
                }

                $exp = explode('||', $categorys['value']);
                
                foreach ($exp as $key => $value) {
                    $sql_doc = "SELECT 
                                c.id,
                                c.pagetitle,
                                (SELECT value FROM modx_site_tmplvar_contentvalues WHERE tmplvarid = 9 and contentid = c.id) as image 
                            FROM modx_site_content c 
                            WHERE c.id = ".intval($value);
                    $query_doc = $modx->db->query($sql_doc);
                    if (!$query_doc) {
                        return '';
                    }

                    $doc = $modx->db->getRow($query_doc);
                
                    $result .= $modx->parseChunk($tpl, $doc,'[+','+]');
                }
            }
        break;    
    default:
        $result = '';
        break;
}

return $result;