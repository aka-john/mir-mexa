<?php 

	//подключаем api

ini_set("upload_max_filesize","15M");
ini_set("post_max_size","15M");
//ini_set("max_execution_time","1200"); //20 min.	
set_time_limit (3000);

//подключаем api modx

$base_path = str_replace('\\','/',dirname(__FILE__)) . '/';
if(is_file($base_path . 'assets/cache/siteManager.php'))
    include_once($base_path . 'assets/cache/siteManager.php');
if(!defined('MGR_DIR') && is_dir("{$base_path}manager"))
	define('MGR_DIR','manager');
if(is_file($base_path . 'assets/cache/siteHostnames.php'))
    include_once($base_path . 'assets/cache/siteHostnames.php');
if(!defined('MODX_SITE_HOSTNAMES'))
	define('MODX_SITE_HOSTNAMES','');


	
//require_once('admin/includes/config.inc.php');
require_once(dirname(__FILE__).'/'.MGR_DIR.'/includes/config.inc.php');


//require_once('admin/includes/protect.inc.php');
require_once(dirname(__FILE__).'/'.MGR_DIR.'/includes/protect.inc.php');
define('MODX_API_MODE', true);
//require_once('admin/includes/document.parser.class.inc.php');
include_once(MODX_MANAGER_PATH.'includes/document.parser.class.inc.php');
$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();	
global $modx;	



	
//echo $_SERVER['DOCUMENT_ROOT'];
//$file_name = $_SERVER['DOCUMENT_ROOT'].'/import.csv';
$file_name= '/home/mir-mexa/www/mir-mexa.com/import.csv';

//echo $file_name ;
//	echo '<br>';


if(file_exists($file_name)){

echo 'есть файл';	
	
file_put_contents("vigruzka-load.txt","vigruzka poshla");
	
$count_do = $modx->db->getValue( $modx->db->select( 'count(*)', $table_prefix."sertificats" ) );
	
	
$modx->logEvent('123', '1', 'выгрузка сертификатов запустилась', 'До запуска было - '.$count_do);	
	
	
//Глобальные настройки импорта
$set['number'] = 0;
$set['date_start'] = 1;
$set['date_end'] = 2;
$set['sum'] = 3;	
	
	
		//чистим сразу всю таблицу
	
$result = $modx->db->query( 'SELECT id FROM '.$modx->getFullTableName('sertificats').' ' );   
if($modx->db->getRecordCount($result)) {  
global $modx, $table_prefix;
while( $row = $modx->db->getRow( $result ) ) {  
$id = $row['id'];
$modx->db->delete($table_prefix."sertificats", "id = $id");

    
} 
} 
/**/
	
	

    // открываем файл для чтения
    $fh = fopen ( $file_name, 'r' );
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
	
	//удаляем файл в клнце и пишем в лог или не пишем
	unlink($file_name); 
	$count_posle = $modx->db->getValue( $modx->db->select( 'count(*)', $table_prefix."sertificats" ) );
	$modx->logEvent('123', '1', 'выгрузка сертификатов остановилась', 'После загрузки новых сертификатов стало - '.$count_posle);
}else{
echo 'Файла нет';
}

