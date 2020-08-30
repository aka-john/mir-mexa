<?php

namespace Core\Components;

use Core\Components\Flesh;
use App\Entity\ProductEntity;

/*
 * PHPExcel builder class
 */
class PhpExcel 
{
    private $excel;
    private $cols;
    private $product;

    /*
     * Include phpexcel main class and set default parameters
     */
    public function __construct($data = array())
    {
        global $modx;
        require_once(CRM_GET_ROOT_PATH().'/assets/modules/modmanager/Core/Includes/Excel/PHPExcel.php');
        $this->excel = new \PHPExcel();
        $this->product = new ProductEntity();
        $this->modx = $modx;
    }

    public function importPrice() 
    {
        $dir  = CRM_GET_ROOT_PATH().'/1c/citys/';
        $files = array();
        if (is_dir($dir)) {
            $d = opendir($dir);
            while ($data = readdir($d)) { 
                if ($data != '.' && $data != '..') { 
                    $files[] = $data;
                }
            }
        }

        if (count($files) == 0) {
            return 'Файлы импорта не найдены!';
        }

        foreach ($files as $key => $value) {
            $path = $dir.$value;

            if (!file_exists($path)) {
                continue;
            }

            $sql = mysql_query("SELECT count(*) as cnt FROM modx_mm_city WHERE LOWER(alias) = '".strtolower($city_alias)."'");
            $chack = mysql_fetch_assoc($sql);
            
            if ($chack['cnt'] == 0) {
                continue;
            }

            $city_alias = explode('.', $value);
            $city_alias = $city_alias[0];

            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
            $cacheSettings = array('memoryCacheSize' => '32MB');
            \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            $filetype = \PHPExcel_IOFactory::identify($path);
            $objPHPExcel = \PHPExcel_IOFactory::createReader($filetype);
            $objPHPExcel->setReadDataOnly(true);
            //подгрузка файла
            $objPHPExcel = \PHPExcel_IOFactory::load($path);
            $objPHPExcel->setActiveSheetIndex(0);
            $excel_array = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

            if (count($excel_array) < 1) {
                return;
            }
            
            $this->cols = array();

            foreach (array_filter($excel_array[1]) as $key => $value) {
                $this->cols[] = $value;
            }

            unset($excel_array[1]);

            foreach ($excel_array as $k => $v) {
                if ($v[0] == null) {
                    continue;
                }
                
                $item = array();
                $size_id = 0;
                
                for ($i = 0; $i < count($this->cols); $i++) {
                    $coll_name = $this->cols[$i];
                    switch ($this->cols[$i]) {
                        case 'size_id':
                            $size_id = $v[$i];
                            break;
                        default:
                            $item[] = '`'.$coll_name.'`="'.mysql_escape_string($v[$i]).'"';
                            break;
                    }
                }
            }
            
            if (count($item) > 0) {
                $sql = mysql_query("SELECT count(*) as cnt, id 
                    FROM modx_mm_size2city
                    WHERE size_id = ".intval($size_id)." 
                          AND city_id = (SELECT id FROM modx_mm_city WHERE LOWER(alias) = '".strtolower($city_alias)."' LIMIT 1)");
                $chack = mysql_fetch_assoc($sql);
                
                if ($chack['cnt'] > 0) {
                    $sql = "UPDATE modx_mm_size2city SET ".implode(',', $item)." WHERE id = ".intval($chack['id']);
                    mysql_query($sql);
                } else {
                    $item[] = '`size_id`="'.intval($size_id).'"';
                    $item[] = "`city_id`=(SELECT id FROM modx_mm_city WHERE LOWER(alias) = '".strtolower($city_alias)."' LIMIT 1)";
                    $sql = "INSERT INTO modx_mm_size2city SET ".implode(',', $item);
                    mysql_query($sql);
                }

                unset($path);    
            }
        }

        return true;
    }
    
    public function import($type = 'file', $filename = null) 
    {
        switch ($type) {
            case '1c':
                $files = array();
                $dir  = CRM_GET_ROOT_PATH().'/1c/product/';

                if (is_dir($dir)) {
                    $d = opendir($dir);
                    while ($data = readdir($d)) { 
                        if ($data != '.' && $data != '..') { 
                            $files[] = $data;
                        }
                    }
                }

                $path = CRM_GET_ROOT_PATH().'/1c/product/'.$files[0];

                if (is_file($path) && !file_exists($path)) {
                    return 'Файл импорта не найден! Подгрузите новый файл или проверьте его наличие по пути: '.CRM_GET_ROOT_PATH().'/1c/product/'.$files[0];
                }
                break;
            default:
                $files = CRM_GET_FILES();
                $path = CRM_GET_ROOT_PATH().'/assets/export/import.xls';
                
                if (is_uploaded_file($files['import']['tmp_name']) && $filename == null) {
                    if (file_exists($path)) {
                        unlink($path);
                    }
                    
                    move_uploaded_file($files['import']['tmp_name'], $path);
                } 

                if (!is_uploaded_file($files['import']['tmp_name']) && $filename != null) {
                    $path = CRM_GET_ROOT_PATH().'/assets/export/export_files/'.$filename;
                }

                if (!file_exists($path)) {
                    Flesh::setFlesh('Файл импорта не найден! Подгрузите новый файл или проверьте его наличие по пути: '.CRM_GET_ROOT_PATH().'/assets/export/import.xls', 'danger');
                    return '';
                }
                break;
        }

        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array('memoryCacheSize' => '32MB');
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        $filetype = \PHPExcel_IOFactory::identify($path);
        $objPHPExcel = \PHPExcel_IOFactory::createReader($filetype);
        $objPHPExcel->setReadDataOnly(true);
        //подгрузка файла
        $objPHPExcel = \PHPExcel_IOFactory::load($path);
        $objPHPExcel->setActiveSheetIndex(0);
        $excel_array = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

        if (count($excel_array) < 1) {
            return;
        }
        
        $this->cols = array();

        foreach (array_filter($excel_array[1]) as $key => $value) {
            $this->cols[] = $value;
        }

        unset($excel_array[1]);
        $products_exists = array();

        foreach ($excel_array as $key => $value) {
            $item = array();
            $item_meta = array();
            
            for ($i = 0; $i < count($this->cols); $i++) {
                $coll_name = $this->cols[$i];
                $alias = '';
                switch ($this->cols[$i]) {
                    case 'name':
                        $alias = $this->modx->stripAlias($value[$i]);
                        $item[] = '`alias`="'.mysql_real_escape_string($alias).'"';
                        $item[] = '`name`="'.mysql_real_escape_string($value[$i]).'"';
                        break;
                    // case 'image':
                    //     $image = $value[$i];
                    //     $ext = strtolower(substr($image, 1 + strrpos($image, ".")));
                    //     $image_name = uniqid().'.'.$ext;
                        
                    //     $item[] = $coll_name.'="'.$image_name.'"';
                    //     break;
                    case 'gallery':
                        $gallery = explode('||', $value[$i]);
                        break;
                    case 'modx_id':
                    case 'category':
                        $modx_id = $value[$i];
                        $coll_name = 'modx_id';
                        break;
                    case 'group':
                    case 'group_id':
                        $group_id = $value[$i];
                        $coll_name = 'group_id';
                        break;
                    case 'filters':
                        $filters = explode('||', $value[$i]);
                        break;
                    case 'sizes':
                        $sizes = explode('||', $value[$i]);
                        break;
                    case 'collection':
                        $collection = strtolower($value[$i]);
                        break;
                    case 'searchable':
                        $item[] = '`searchable` = "'.($value[$i] == '' ? 1 : $value[$i]).'"';
                        break;
                    default:
                        $explode_product = explode('_', $coll_name);                        
                        if (reset($explode_product) == 'meta') {                                            
                            $item_meta[] = '`'.$coll_name."`='".mysql_real_escape_string($value[$i])."'";
                        } else {
                            $item[] = '`'.$coll_name.'`="'.mysql_real_escape_string($value[$i]).'"';
                        }
                        break;
                }
            }

            //товар
            if (count($item) > 0) {
                $item[] = '`created_at` = "'. date('Y-m-d H:i:s', time()).'"';

                $sql = "INSERT INTO modx_mm_products SET ".implode(',', $item)." ON DUPLICATE KEY UPDATE ".implode(',', $item);

                $query = mysql_query($sql);
                
                if(mysql_insert_id() != 0)  {
                    $product_id = mysql_insert_id();
                } else {
                    $product_id = $value[0];
                }               
            }

            $products_exists[] = $product_id;

            //мета
            if (count($item_meta) > 0) {
                $sql_meta = "UPDATE modx_mm_metadata SET ".implode(',', $item_meta)." WHERE page_id = ".intval($product_id);
                mysql_query($sql_meta);
            }
            
            //категория
            if ($modx_id != '') {
                $sql = mysql_query("SELECT count(*) as cnt FROM modx_mm_p2c_link WHERE product_id = ".$product_id." AND modx_id = '".intval($modx_id)."'");
                $chack = mysql_fetch_assoc($sql);
                if ($chack['cnt'] > 0) {
                    $sql_modx = "UPDATE modx_mm_p2c_link SET modx_id = ".intval($modx_id)." WHERE product_id = ".$product_id;
                    mysql_query($sql_modx);
                } else {
                    $sql_modx = "INSERT INTO modx_mm_p2c_link (product_id, modx_id) VALUES (".$product_id.",".$modx_id.")";
                    mysql_query($sql_modx);
                }
            }
            
            //группа
            if ($group_id != '') {
                mysql_query("DELETE FROM modx_mm_p2g_link WHERE product_id = '".intval($product_id)."'");
                $this->product->addProductToGroup($group_id, $product_id);
            }

            // размеры
            // && - разделитель между данными о размере и городами
            // | - разделитель между городами
            // || - разделитель между размерами
            if (count($sizes) > 0) {
                //$size_exists = array();
                foreach ($sizes as $key => $value) {
                    $sections = array_filter(explode('&&', $value));//разделение блока размеров и цен по городам
                    $size_section = array_filter(explode(';', $sections[0]));//секция размера

                    $size_item = array();
                    $size_update = array();
                    
                    for ($i = 0; $i < count($size_section); $i++) {
                        $v = explode('=', $size_section[$i]);
                        switch ($v[0]) {
                            case 'size_id':
                                $size_val = trim($v[1]);
                                break;
                            case 'size':
                                $size_name = trim($v[1]);
                                break;
                        }
                        $size_item[] = '"'.trim($v[1]).'"';
                        $size_update[] = $v[0].'="'.trim($v[1]).'"';
                    }

                    $sql_size = mysql_query("SELECT count(*) as cnt, id 
                        FROM modx_mm_product_size
                        WHERE size_id = ".intval($size_val)." 
                              AND size = '".$size_name."' 
                              AND product_id = '".intval($product_id)."'");
                    $chack = mysql_fetch_assoc($sql_size);

                    if ($chack['cnt'] > 0) {//обновление размера
                        $sql_update = "UPDATE modx_mm_product_size SET ".implode(',', $size_update)." WHERE size_id = ".intval($size_val)." AND size = '".$size_name."' AND product_id = '".intval($product_id)."'";
                        mysql_query($sql_update);
                        $size_id = $chack['id'];
                    } else {//запись нового размера
                        $size_insert = "(".implode(',', $size_item).", ".intval($product_id).", '".date('Y-m-d H:i:s', time())."')";
                        $sql_insert = "INSERT INTO modx_mm_product_size (size_id, size, amount, price, sale_price, product_id, created_at) VALUES ".$size_insert;
                        mysql_query($sql_insert);
                        $size_id = mysql_insert_id();
                    }

                    $size_exists[] = $size_id;

                    //запись городов если они есть
                    if (isset($sections[1]) && isset($size_id)) {
                        $size_section = array_filter(explode('|', $sections[1]));//секция города
                        
                        if (count($size_section) > 0) {
                            foreach ($size_section as $key_city => $value_city) {
                                $city_section = array_filter(explode(';', $value_city));
                                $city_update = array();
                                $city_item = array();

                                for ($i = 0; $i < count($city_section); $i++) {
                                    $v = explode('=', $city_section[$i]);
                                    switch ($v[0]) {
                                        case 'city_id':
                                            $city_id = trim($v[1]);
                                            break;
                                    }
                                    $city_item[] = '"'.trim($v[1]).'"';
                                    $city_update[] = $v[0].'="'.trim($v[1]).'"';
                                }

                                $sql_city = mysql_query("SELECT count(*) as cnt  
                                    FROM modx_mm_size2city
                                    WHERE size_id = ".intval($size_id)." 
                                          AND city_id = '".intval($city_id)."'");
                                $chack = mysql_fetch_assoc($sql_city);

                                if ($chack['cnt'] > 0) {//обновление цен по городу у данного размера
                                    $sql_update = "UPDATE modx_mm_size2city SET ".implode(',', $city_update)." WHERE size_id = ".intval($size_id)." AND city_id = '".intval($city_id)."'";
                                    mysql_query($sql_update);
                                } else {//запись города к размеру
                                    $city_insert = "(".intval($size_id).",".implode(',', $city_item).")";
                                    $sql_insert = "INSERT INTO modx_mm_size2city (size_id, city_id, price, sale_price) VALUES ".$city_insert;
                                    mysql_query($sql_insert);
                                }
                            }
                        }
                    }
                }
            }
            
            //фильтры
            if (count($filters) > 0) {
                //очистка линковки
                mysql_query("DELETE FROM modx_mm_fv2p_link WHERE product_id = '".intval($product_id)."'");

                foreach ($filters as $key => $value) {
                    $field = array_filter(explode(';', $value));
                    $filter_insert_item = array();
                    $filter_update = array();
                    
                    for ($i = 0; $i < count($field); $i++) {
                        $v = explode('=', $field[$i]);
                        switch ($v[0]) {
                            case 'filter_id':
                                $filter_id = trim($v[1]);
                                break;
                            case 'value':
                                $filter_value = trim($v[1]);
                                break;
                        }
                        $filter_insert_item[] = '"'.trim($v[1]).'"';
                        $filter_update[] = $v[0].'="'.trim($v[1]).'"';
                    }

                    $sql_f_value = mysql_query("SELECT count(*) as cnt, id 
                        FROM modx_mm_filters_value
                        WHERE filter_id = ".intval($filter_id)." 
                              AND value = '".$filter_value."'");
                    $chack = mysql_fetch_assoc($sql_f_value);

                    if ($chack['cnt'] > 0) {//обновление фильтра
                        $sql_update = "UPDATE modx_mm_filters_value SET ".implode(',', $filter_update)." WHERE filter_id = ".intval($filter_id)." AND value = '".$filter_value."'";
                        mysql_query($sql_update);
                        $value_id = $chack['id'];
                    } else {//запись нового значения фильтра
                        $sql_insert = "INSERT INTO modx_mm_filters_value (filter_id, value, position, param) VALUES (".implode(',', $filter_insert_item).")";
                        mysql_query($sql_insert);
                        $value_id = mysql_insert_id();
                    }

                    $sql_filter_link = "INSERT INTO modx_mm_fv2p_link (value_id, product_id, modx_id) VALUES (".intval($value_id).", ".intval($product_id).", (SELECT modx_id FROM modx_mm_p2c_link WHERE product_id = ".intval($product_id)." LIMIT 1))";
                    mysql_query($sql_filter_link);
                }
            }

            //коллекция(м/ж)
            if ($collection != '') {
                switch(strtolower(trim($collection))) {
                    case 'м'://82
                        //удаляем ж если есть
                        mysql_query("DELETE FROM modx_mm_fv2p_link WHERE value_id = 83 AND product_id = ".$product_id." AND modx_id = '".intval($modx_id)."'");
                        $sql = mysql_query("SELECT count(*) as cnt FROM modx_mm_fv2p_link WHERE value_id = 82 AND product_id = ".$product_id." AND modx_id = '".intval($modx_id)."'");
                        $chack = mysql_fetch_assoc($sql);
                        if ($chack['cnt'] == 0) {
                            $sql_modx = "INSERT INTO modx_mm_fv2p_link (value_id,product_id,modx_id) VALUES (82,".$product_id.",".$modx_id.")";
                            mysql_query($sql_modx);
                        }
                        break;
                    case 'ж'://83
                        //удаляем м если есть
                        mysql_query("DELETE FROM modx_mm_fv2p_link WHERE value_id = 82 AND product_id = ".$product_id." AND modx_id = '".intval($modx_id)."'");
                        $sql = mysql_query("SELECT count(*) as cnt FROM modx_mm_fv2p_link WHERE value_id = 83 AND product_id = ".$product_id." AND modx_id = '".intval($modx_id)."'");
                        $chack = mysql_fetch_assoc($sql);
                        if ($chack['cnt'] == 0) {
                            $sql_modx = "INSERT INTO modx_mm_fv2p_link (value_id,product_id,modx_id) VALUES (83,".$product_id.",".$modx_id.")";
                            mysql_query($sql_modx);
                        }
                        break;
                }
            }

            $images_path = CRM_GET_ROOT_PATH().'/assets/export/images/';
            $product_path = CRM_GET_ROOT_PATH().'/assets/files/product/'.$product_id;
            $image_path = CRM_GET_ROOT_PATH().'/assets/files/product/'.$product_id.'/image/';
            $gallery_path = CRM_GET_ROOT_PATH().'/assets/files/product/'.$product_id.'/gallery/';
            
            if (!is_dir($product_path)) {
                mkdir($product_path, 0777);
            }

            if (!is_dir($image_path)) {
                mkdir($image_path, 0777);
            }
            
            if (!is_dir($gallery_path)) {
                mkdir($gallery_path, 0777);
            }
            
            //изображение
            // if ($image != '' && $image_name != '') {
            //     foreach(scandir($image_path) as $file) {
            //         if ($file != "." && $file != "..") {                                         
            //             unlink($image_path.$file);
            //         }
            //     }
            //     copy($images_path.$image, $image_path.$image_name);
            // }
            
            //галерея
            if (count($gallery) > 0) {
                foreach(scandir($gallery_path) as $file) {
                    if ($file != "." && $file != "..") {                                            
                        unlink($gallery_path.$file);
                    }
                }
                
                $gallery_insert = array();
                
                mysql_query("DELETE FROM modx_mm_images WHERE product_id = '".intval($product_id)."' AND flag = 0");
                
                foreach ($gallery as $key => $value) {
                    $image = $value;
                    if(trim($image) == '' || !file_exists($images_path.$image)) {
                        continue;
                    }
                    
                    //$ext = strtolower(substr($image, 1 + strrpos($image, ".")));
                    //$image_name = uniqid().'.'.$ext;

                    if ($key == 0) {
                        foreach(scandir($image_path) as $file) {
                            if ($file != "." && $file != "..") {                                            
                                unlink($image_path.$file);
                            }
                        }
                        if (copy($images_path.$value, $image_path.$image)) {
                            $sql = "UPDATE modx_mm_products SET image = '".$image."' WHERE id = ".intval($product_id);
                            $query = mysql_query($sql);
                        }

                        continue;
                    }

                    /*$sql_gallery_cnt = mysql_query("SELECT count(*) as cnt, id 
                        FROM modx_mm_images
                        WHERE size_id = ".intval($size_val)." 
                              AND original_name = '".$image."',
                              AND product_id = '".intval($product_id)."'");
                    $chack = mysql_fetch_assoc($sql_gallery_cnt);
                    if ($chack['cnt'] == 0) {
                        if (copy($images_path.$image, $gallery_path.$image)) {
                            $gallery_insert[] = "(".intval($product_id).", '".$image."', '".$image."', ".$key.", '".$image."', '".$image."', 0)";
                        }
                    }*/
                    if (copy($images_path.$image, $gallery_path.$image)) {
                        $gallery_insert[] = "(".intval($product_id).", '".$image."', '".$image."', ".$key.", '".$image."', '".$image."', 0)";
                    }
                }
                
                if (count($gallery_insert) > 0) {
                    $sql_gallery = "INSERT INTO modx_mm_images (product_id, image, original_name, position, alt, title, flag) VALUES ".implode(',', $gallery_insert);
                    $query = mysql_query($sql_gallery);
                }
            }

            $this->product->refreshProductPrice($product_id);//обновление цен

        }

        if (count($size_exists) > 0 && count($products_exists) > 0) {
            $size_exists = array_unique($size_exists);
            $sizes_list = implode(',', $size_exists);

            $products_exists = array_unique($products_exists);
            $products_list = implode(',', $products_exists);

            $sql_size = mysql_query("SELECT * 
                FROM modx_mm_product_size
                WHERE id NOT IN (".$sizes_list.")  
                      AND product_id IN (".$products_list.")");
            $delete_sizes = array();
            if ($sql_size) {
                while ($row_size = mysql_fetch_assoc($sql_size)) {
                    $delete_sizes[] = $row_size['id'];
                }
            }

            if (count($delete_sizes) > 0) {
                $delete_sizes = implode(',', $delete_sizes);
                //очистка размеров
                $sql = "DELETE FROM modx_mm_product_size WHERE id IN (".$delete_sizes.") AND product_id IN (".$products_list.")";

                mysql_query($sql);
                //очистка цен по городам к размерам
                $sql = "DELETE FROM modx_mm_size2city s WHERE s.size_id IN (".$delete_sizes.")";
                mysql_query($sql);
            }
        }
        
        Flesh::setFlesh('Импорт завершен!', 'info');

        if (file_exists($path)) {
            unlink($path);
        }
        
        return true;
    }
    
    public function export($products, $param) 
    {
        if (count($param['ei']) < 1 || count($products) < 1) {
            return '';
        }

        $filename = 'export.xls';
        $path = CRM_GET_ROOT_PATH().'/assets/export/'.$filename;
        $images_path = CRM_GET_ROOT_PATH().'/assets/export/images/';
        if(!is_dir($images_path)){
            mkdir($images_path, 0777);
        }

        $this->excel->getProperties()
            ->setCreator("Meh")
            ->setLastModifiedBy("Meh")
            ->setTitle('Meh export')
            ->setSubject('Meh export');
    
        $row = 1;
        $col = 0;
        foreach ($param['ei'] as $key => $value) {
            $this->cols[] = $key;
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $key);
            $col++;
        }
        
        $row = 2;

        foreach ($products as $key => $value) {
            $col = 0;
            foreach ($value as $key_data => $value_data) {
                if (in_array($key_data, $this->cols) && $key_data != 'image') {
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value_data);
                    $col++;
                }
            }

            $col--;

            //коллекция(м/ж)
            if (in_array('collection', $this->cols)) {
                $col++;
                switch (strtolower($value['collection_id'])) {
                    case '82':
                        $collection_id = 'м';
                        break;
                    case '83':
                        $collection_id = 'ж';
                        break;
                    default:
                        $collection_id = '';
                        break;
                }
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $collection_id);
            }
            
            //категория
            if (in_array('category', $this->cols)) {
                $col++;
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, (int)$value['modx_id']);
            }
            
            //группы
            if (in_array('group', $this->cols)) {
                $col++;
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value['group_id']);
            }
            
            //изображение
            /*if (in_array('image', $this->cols)) {
                $export_image_path = CRM_GET_ROOT_PATH().'/assets/files/product/'.$value['id'].'/image/';
                
                $image = '';
                if(is_dir($export_image_path)){
                    foreach(scandir($export_image_path) as $file) {
                        if($file != "." && $file != "..") {
                            if(copy($export_image_path.$file, $images_path.$file)) {
                                $image = $file;
                            } 
                        }
                    }
                }

                $col++;
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $image);
            }*/
            
            //галлерея
            if (in_array('gallery', $this->cols)) {
                $export_gallery_path = CRM_GET_ROOT_PATH().'/assets/files/product/'.$value['id'].'/gallery/';
                
                $gallery = array();
                if(is_dir($export_gallery_path)){
                    foreach(scandir($export_gallery_path) as $file) {
                        if($file != "." && $file != "..") {
                            if(copy($export_gallery_path.$file, $images_path.$file)){					        		
                                $gallery[] = $file;					        	
                            }
                        }
                    }
                }
                
                if (count($gallery) > 0) {
                    $col++;
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, implode('||', $gallery));
                }
            }
            
            // размеры
            // && - разделитель между данными о размере и городами
            // | - разделитель между городами
            // || - разделитель между размерами
            if (in_array('sizes', $this->cols)) {
                $sizes = $this->product->getAllProductSizes($value['id']);
                $size_arr = array();
                
                if (count($sizes) > 0) {
                    for ($i = 0; $i < count($sizes); $i++) {
                        $size_arr[$i]  = 'size_id='.$sizes[$i]['size_id'].';';
                        $size_arr[$i] .= 'size='.$sizes[$i]['size'].';';
                        $size_arr[$i] .= 'amount='.$sizes[$i]['amount'].';';
                        $size_arr[$i] .= 'price='.$sizes[$i]['price'].';';
                        $size_arr[$i] .= 'sale_price='.$sizes[$i]['sale_price'];

                        $citys = $this->product->getCityBySizeId($sizes[$i]['id']);

                        if (count($citys) > 0) {
                            $city_arr = array();

                            for ($c = 0; $c < count($citys); $c++) {
                                $city_arr[$c]  = 'city_id='.$citys[$c]['city_id'].';';
                                $city_arr[$c] .= 'price='.$citys[$c]['price'].';';
                                $city_arr[$c] .= 'sale_price='.$citys[$c]['sale_price'];
                            }

                            $size_arr[$i] .= '&&'.(count($city_arr) > 0 ? implode('|', $city_arr) : '');
                        }
                    }
                    
                    $col++;
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, implode('||', $size_arr));
                } 
            }
            
            //фильтры
            if (in_array('filters', $this->cols)) {
                $filters = $this->product->getProductFiltersAndValues($value['id'], $value['modx_id']);

                $filter_arr = array();
                if (count($filters) > 0) {
                    for ($i = 0; $i < count($filters); $i++) {
                        $filter_arr[$i]  = 'filter_id='.$filters[$i]['filter_id'].';';
                        $filter_arr[$i] .= 'value='.$filters[$i]['value'].';';
                        $filter_arr[$i] .= 'position='.$filters[$i]['position'].';';
                        $filter_arr[$i] .= 'param='.$filters[$i]['param'];
                    }
                    
                    $col++;
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, implode('||', $filter_arr));
                } 
            }

            $row++;
        }
        
        $this->excel->setActiveSheetIndex(0);
        $objWriter = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        $objWriter->save($path);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($path));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        // читаем файл и отправляем его пользователю
        readfile($path);
        
        //Flesh::setFlesh('Экспорт завершен! Вы можете найти файлы экспорта по пути '.CRM_GET_ROOT_PATH().'/assets/export/', 'info');
    }

    public function splitExcel() 
    {
        $files = CRM_GET_FILES();
        $path = CRM_GET_ROOT_PATH().'/assets/export/import.xls';
        $export_path = CRM_GET_ROOT_PATH().'/assets/export/export_files/';
        
        if (is_uploaded_file($files['import']['tmp_name'])) {
            if (file_exists($path)) {
                unlink($path);
            }
            
            move_uploaded_file($files['import']['tmp_name'], $path);
        }

        if (!file_exists($path)) {
            Flesh::setFlesh('Файл импорта не найден! Подгрузите новый файл или проверьте его наличие по пути: '.CRM_GET_ROOT_PATH().'/assets/export/import.xls', 'danger');
            return '';
        }

        /*foreach(scandir($export_path) as $file) {
            if ($file != "." && $file != "..") {                                            
                unlink($export_path.$file);
            }
        }*/

        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array('memoryCacheSize' => '32MB');
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        $filetype = \PHPExcel_IOFactory::identify($path);
        $objPHPExcel = \PHPExcel_IOFactory::createReader($filetype);
        $objPHPExcel->setReadDataOnly(true);
        //подгрузка файла
        $objPHPExcel = \PHPExcel_IOFactory::load($path);
        $objPHPExcel->setActiveSheetIndex(0);
        $excel_array = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

        $files_count = ceil(count($excel_array)/50);
        $counter = 2;

        for ($i=0; $i < $files_count; $i++) { 
            $filename = 'file_'.$i.'_'.date('d-m-Y-H-i', time()).'.xls';
            $export_file = CRM_GET_ROOT_PATH().'/assets/export/export_files/'.$filename;

            $this->excel = new \PHPExcel();
            $this->excel->getProperties()
                ->setCreator("Meh")
                ->setLastModifiedBy("Meh")
                ->setTitle($filename)
                ->setSubject('Meh export');

            $col = 0;
            $row = 2;
            $this->cols = array();

            foreach (array_filter($excel_array[1]) as $key => $value) {//заполняем первую строку файла
                $this->cols[] = $value;
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $value);
                $col++;
            }


            for ($j=0; $j < 50; $j++) {//в файле 50 товаров
                $col = 0;
                foreach ($excel_array[$counter] as $key => $value) {//заполняем каждую ячейку
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value);
                    $col++;
                }

                $row++;
                $counter++;
            }

            $this->excel->setActiveSheetIndex(0);
            $objWriter = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
            $objWriter->save($export_file);
        }

        return true;
    }
}