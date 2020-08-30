<?php
define (ROOT, dirname(__FILE__));
error_reporting(E_ALL);
error_reporting(0);

require_once(ROOT.'/../../../manager/includes/protect.inc.php');
include_once ROOT.'/../../../manager/includes/config.inc.php';
define('MODX_API_MODE', true);
include_once(MODX_MANAGER_PATH.'/includes/document.parser.class.inc.php');

$modx = new DocumentParser();
$modx->db->connect();
$modx->getSettings();
global $modx;
startCMSSession();

define('DEFAULT_CONTROLLER', 'Product');
define('DEFAULT_ACTION', 'index');
define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('BP', dirname(dirname(__FILE__)));
define('MBP', dirname(dirname(__FILE__)).'/modmanager/');
define('MMP', '/assets/modules/modmanager/');

include dirname(realpath(__FILE__))."/Core/Core.php";
include dirname(realpath(__FILE__))."/Core/Components/Func.php";

use Core\Components\PhpExcel;

$excel = new PHPExcel();

$files = array();
$export_path = ROOT.'/../../../assets/export/export_files/';

foreach(scandir($export_path) as $file) {
    if ($file != "." && $file != "..") {                                            
        $files[] = $file;
    }
}

if ($files[0] != '') {
	$t = $excel->import('file', $files[0]);
}

unset($_SESSION['flesh']);

die('END');