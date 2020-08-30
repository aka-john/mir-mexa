<?php
//создаем модуль и вставляем строку: 
//include_once($modx->config['base_path'].'assets/modules/easyImport/easyImport.module.php');

//меняем настройки php.ini
ini_set('display_errors', 1);//закоментить
ini_set("upload_max_filesize","15M");
ini_set("post_max_size","15M");
ini_set("max_execution_time","1200"); //20 min.
ini_set("max_input_time","1200"); //20 min.
ini_set('auto_detect_line_endings',1);
date_default_timezone_set('Europe/Moscow');
setlocale (LC_ALL, 'ru_RU.UTF-8');

$moduleurl = 'index.php?a=112&id='.$_GET['id'].'&';
$action = isset($_GET['action']) ? $_GET['action'] : '';


//Глобальные настройки импорта
$set['number'] = 0;
$set['date_start'] = 1;
$set['date_end'] = 2;
$set['sum'] = 3;



// выполнение запросов 
switch ($action) {
  case 'import':
    $tstart = $modx->getMicroTime();
	
	//чистим сразу всю таблицу
	
	$result = $modx->db->query( 'SELECT id FROM '.$modx->getFullTableName('sertificats').' ' );   
if($modx->db->getRecordCount($result)) {  
global $modx, $table_prefix;
while( $row = $modx->db->getRow( $result ) ) {  
$id = $row['id'];
//$modx->db->delete($modx->getFullTableName('sertificats'), "id = $row['id']");
$modx->db->delete($table_prefix."sertificats", "id = $id");

    
} 
} 
/**/
	
	
        
    // открываем файл для чтения
    $fh = fopen ( $_FILES['csv']['tmp_name'], 'r' );
    for($i=0; $info = fgetcsv ($fh, 1000, ";"); $i++)
    {




      //создаем Сертификат  
     if ($i != 0) { //пропускаем первую строку
     //  echo $info[$set["number"]].'<br />';
	 //  echo print_r($info).'<br />';
	   
	   

    $fields = array('number_sertificat'  => $info[$set["number"]],  
                    'date_start' => $info[$set["date_start"]],  
                    'date_end'  => $info[$set["date_end"]],  
                    'sum' => $info[$set["sum"]], 
                    );   
    $modx->db->insert( $fields, $modx->getFullTableName('sertificats')); 
	   
      }    
	  
	 
	  
       
    }
    // закрываем файл
    fclose ( $fh );
    
    if ($i > 0){ 
      $tend = $modx->getMicroTime();
      $totaltime = $tend - $tstart;
      
      $totalimp = ' &nbsp; &nbsp; &nbsp; &nbsp; импортированно позиций: '.$i. ' за '.$totaltime.'c.';
    }

    $tpl = file_get_contents('template.tpl', true);
    $fields = array ('[+moduleurl+]', '[+manager_theme+]', '[+totalimp+]');
    $values = array ($moduleurl, $modx->config['manager_theme'], $totalimp);
    $tpl= str_replace($fields, $values, $tpl);
    echo $tpl;      

  break;

  default:
    $tpl = file_get_contents('template.tpl', true);
    $fields = array ('[+moduleurl+]', '[+manager_theme+]', '[+totalimp+]', '[+cat+]');
    $values = array ($moduleurl, $modx->config['manager_theme'], '', $cat);
    $tpl= str_replace($fields, $values, $tpl);
    echo $tpl;
}
?>
